<?php

if ( ! class_exists( 'M1D1_CLI' ) ) {
	class M1D1_CLI {
		/**
		 * Authentication
		 *
		 * @return void
		 * @throws WP_CLI\ExitException
		 */
		public function auth(): void {
			$fb_api       = m1d1_get_fb_api();
			$device_login = $fb_api->device_login();

			WP_CLI::line(
				sprintf(
					"Visit '%s' and type '%s'.",
					$device_login->verification_uri,
					$device_login->user_code
				)
			);

			$code     = $device_login->code;
			$interval = $device_login->interval;

			/** Polling. */
			while ( true ) {
				sleep( $interval );
				$status = $fb_api->device_login_status( $code );
				if ( isset( $status->error ) ) {
					match ( $status->error->error_subcode ) {
						1349174          => WP_CLI::line( "Polling and waiting response ..." ),
						1349152, 1349172 => WP_CLI::error( $status->error->error_user_msg ),
					};
				} else {
					break;
				}
			}

			/**
			 * Debug token and get 'user_id'.
			 */
			$debug_data      = $fb_api->debug_token( $status->access_token );
			$status->user_id = $debug_data->data->user_id;

			m1d1_update_settings( $status );
			WP_CLI::success( 'Authoriztaion is complete.' );
		}

		/**
		 * Grab user posts
		 *
		 * ## OPTIONS
		 * [<max_loop>]
		 * : Maximum loop.
		 * ---
		 * default: 1
		 * ---
		 *
		 * @throws WP_CLI\ExitException
		 */
		public function posts( array $args ): void {
			$fb_api   = m1d1_get_fb_api();
			$setting  = m1d1_get_settings();
			$max_loop = (int) $args[0];

			$this->init_api( $fb_api, $setting );
			$posts = $fb_api->get_posts( $setting->access_token, $setting->user_id, $max_loop );

			WP_CLI::line(
				json_encode( $posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
			);
		}

		/**
		 * Import #1일1메탈 posts to table
		 *
		 * ## OPTIONS
		 * [<max_loop>]
		 * : Maximum loop.
		 * ---
		 * default: 1
		 * ---
		 *
		 * @throws WP_CLI\ExitException
		 */
		public function import( array $args ): void {
			global $wpdb;

			$fb_api   = m1d1_get_fb_api();
			$setting  = m1d1_get_settings();
			$max_loop = (int) $args[0];
			$timezone = new DateTimeZone( 'Asia/Seoul' ); // Timezone for table should be Asia/Seoul.

			$this->init_api( $fb_api, $setting );
			$posts = $fb_api->get_posts( $setting->access_token, $setting->user_id, $max_loop );

			$playlists = [];
			foreach ( $posts as $post ) {
				$playlist = M1D1_Playlist::from_fb_post( $post );
				if ( $playlist->fb_id ) {
					$playlists[ $playlist->fb_id ] = $playlist;
				}

			}

			$to_insert = [];
			$to_update = [];

			// Check for updates.
			if ( $playlists ) {
				$placeholder    = implode( ', ', array_pad( [], count( $playlists ), '%s' ) );
				$query          = $wpdb->prepare(
					"SELECT fb_id, updated_time FROM {$wpdb->prefix}m1d1_playlist" .
					" WHERE fb_id IN ($placeholder)",
					array_keys( $playlists ),
				);
				$existing_posts = $wpdb->get_results( $query, OBJECT_K );

				foreach ( $playlists as $playlist ) {
					if ( isset( $existing_posts[ $playlist->fb_id ] ) ) {
						$updated_time = date_create_from_format(
							'Y-m-d H:i:s',
							$existing_posts[ $playlist->fb_id ]->updated_time,
							$timezone
						);
						if ( $playlist->updated_time->getTimestamp() != $updated_time->getTimestamp() ) {
							// Changed.
							$to_update[] = $playlist;
							WP_CLI::line( $playlist->sequence . ' will be updated' );
						}
					} else {
						// New items.
						$to_insert[] = $playlist;
					}
				}
			}

			if ( ! $to_update && ! $to_insert ) {
				WP_CLI::success( '🤟 All posts are fetched. Nothing to do for now! 🤟' );
			}

			if ( $to_update ) {
				$to_update = array_reverse( $to_update );
				foreach ( $to_update as $playlist ) {
					$wpdb->update(
						"{$wpdb->prefix}m1d1_playlist",
						[
							'artist'       => $playlist->artist,
							'title'        => $playlist->title,
							'description'  => $playlist->description,
							'rating'       => $playlist->rating ?: null,
							'sequence'     => $playlist->sequence,
							'updated_time' => $playlist->updated_time
								? wp_date( 'Y-m-d H:i:s', $playlist->updated_time->getTimestamp(), $timezone )
								: null,
						],
						[ 'fb_id' => $playlist->fb_id ],
					);
					WP_CLI::success( 'Sequence ' . $playlist->sequence . ' is succeessfully updated.' );
				}
			}

			if ( $to_insert ) {
				$to_insert = array_reverse( $to_insert );
				foreach ( $to_insert as $playlist ) {
					$wpdb->insert(
						"{$wpdb->prefix}m1d1_playlist",
						[
							'fb_id'        => $playlist->fb_id,
							'artist'       => $playlist->artist,
							'title'        => $playlist->title,
							'description'  => $playlist->description,
							'rating'       => $playlist->rating ?: null,
							'sequence'     => $playlist->sequence,
							'created_time' => $playlist->created_time
								? wp_date( 'Y-m-d H:i:s', $playlist->created_time->getTimestamp(), $timezone )
								: null,
							'updated_time' => $playlist->updated_time
								? wp_date( 'Y-m-d H:i:s', $playlist->updated_time->getTimestamp(), $timezone )
								: null,
						]
					);
					WP_CLI::success( 'Sequence ' . $playlist->sequence . ' is succeessfully imported.' );
				}
			}
		}

		/**
		 * @param M1D1_FB_API $fb_api
		 * @param stdClass    $setting
		 *
		 * @return void
		 * @throws WP_CLI\ExitException
		 */
		private function init_api( M1D1_FB_API $fb_api, stdClass &$setting ): void {
			$now = time();

			if ( $setting->data_access_expiration_time < 0 ) {
				WP_CLI::error( 'Authorization, please!' );
			} elseif ( $now > $setting->data_access_expiration_time ) {
				WP_CLI::error( 'The access token is expired.' );
			} elseif ( $setting->data_access_expiration_time - $now < WEEK_IN_SECONDS ) {
				$response = $fb_api->refresh_token( $setting->access_token );

				$setting->access_token                = $response->access_token;
				$setting->expires_in                  = $response->expires_in;
				$setting->data_access_expiration_time = time() + $response->expires_in;
				m1d1_update_settings( $setting );
			}
		}
	}
}
