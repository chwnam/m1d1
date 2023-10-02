<?php

use JetBrains\PhpStorm\NoReturn;

if ( ! class_exists( 'M1D1_CLI' ) ) {
	class M1D1_CLI {
		/**
		 * Authentication
		 *
		 * @return void
		 * @throws WP_CLI\ExitException
		 */
		public function auth(): void {
			$app_id       = m1d1_get_app_id();
			$client_token = m1d1_get_client_token();

			if ( ! $app_id || ! $client_token ) {
				WP_CLI::error( "'M1D1_APP_ID' or 'M1D1_CLIENT_TOKEN' are not properly set in the wp-config.php!" );
			}

			$response = wp_remote_post(
				'https://graph.facebook.com/v2.6/device/login',
				array(
					'body' => array(
						'access_token' => "$app_id|$client_token",
						'scope'        => 'public_profile,user_posts',
					),
				)
			);

			$status_code = wp_remote_retrieve_response_code( $response );
			$data        = json_decode( wp_remote_retrieve_body( $response ) );

			if ( 200 !== $status_code ) {
				WP_CLI::error( "Status code: $status_code" );
			}

			/**
			 * @var stdClass{
			 *     code: string,
			 *     user_code: string,
			 *     verification_uri: string,
			 *     expires_in: int,
			 *     interval: int,
			 * } $data
			 */
			WP_CLI::line( sprintf( 'Visit %s and type %s.', $data->verification_uri, $data->user_code ) );

			$code     = $data->code;
			$interval = $data->interval;

			/** Polling. */
			while ( true ) {
				sleep( $interval );

				$response = wp_remote_post(
					'https://graph.facebook.com/v2.6/device/login_status',
					array(
						'body' => array(
							'access_token' => '976937806905669|fe87d12b40fbd3595fe7925487e5e78b',
							'code'         => $code,
						),
					)
				);

				$status_code = wp_remote_retrieve_response_code( $response );
				$data        = json_decode( wp_remote_retrieve_body( $response ) );

				if ( 200 !== $status_code ) {
					wp_die( $data );
				}

				if ( isset( $data->error ) ) {
					/**
					 * @var stdClass{
					 *     error: stdClass{
					 *         message: string,
					 *         code: int,
					 *         error_subcode: int,
					 *         error_user_title: int,
					 *         error_user_msg: int,
					 *     }
					 * } $data
					 */
					$subcode = $data->error->error_subcode;

					if ( 1349174 === $subcode ) {
						// Polling.
						continue;
					} elseif ( 1349172 === $subcode ) {
						// Too frequent polling.
						WP_CLI::error( $data->error->error_user_msg );
					} elseif ( 1349152 === $subcode ) {
						// Expired.
						WP_CLI::error( $data->error->error_user_msg );
					}
				} else {
					break;
				}
			}

			/**
			 * Data structure.
			 *
			 * @var stdClass{
			 *     access_token: string,
			 *     data_access_expiration_time: int,
			 *     expires_in: int,
			 * } $data
			 */

			/**
			 * Add 'user_id' field here.
			 */
			$url = add_query_arg(
				[
					'input_token'  => $data->access_token,
					'access_token' => $data->access_token,
				],
				'https://graph.facebook.com/debug_token'
			);

			$response    = wp_remote_get( $url );
			$status_code = wp_remote_retrieve_response_code( $response );
			$body        = wp_remote_retrieve_body( $response );

			if ( 200 !== $status_code ) {
				wp_die( 'Debug token request failed.' );
			}

			/**
			 * Decoded debug value.
			 *
			 * @var array{
			 *      data: array{
			 *         app_id: string,
			 *         application: string,
			 *         data_access_expires_at: int,
			 *         expires_at: int,
			 *         is_valid: bool,
			 *         issued_at: int,
			 *         scopes: string[],
			 *         type: string,
			 *         user_id: string,
			 *     }
			 *  } $decoded
			 */
			$decoded       = json_decode( $body, true );
			$data->user_id = $decoded['data']['user_id'];

			m1d1_update_settings( $data );
			WP_CLI::success( 'Authoriztaion is complete.' );
		}

		/**
		 * @throws WP_CLI\ExitException
		 */
		#[NoReturn] public function posts(): void {
			$app_id     = m1d1_get_app_id();
			$app_secret = m1d1_get_app_secret();
			$setting    = m1d1_get_settings();
			$now        = time();

			if ( $setting->data_access_expiration_time < 0 ) {
				WP_CLI::error( 'Authorization, please!' );
			} elseif ( $now > $setting->data_access_expiration_time ) {
				WP_CLI::error( 'The access token is expired.' );
			}

			if ( $setting->data_access_expiration_time - $now < WEEK_IN_SECONDS ) {
				// Refresh.
				$url = add_query_arg(
					urlencode_deep(
						array(
							'grant_type'        => 'fb_exchange_token',
							'client_id'         => $app_id,
							'client_secret'     => $app_secret,
							'fb_exchange_token' => $setting->access_token,
						)
					),
					'https://graph.facebook.com/v18.0/oauth/access_token'
				);

				$response    = wp_remote_get( $url );
				$status_code = wp_remote_retrieve_response_code( $response );
				$body        = wp_remote_retrieve_body( $response );

				if ( 200 !== $status_code ) {
					wp_die( 'Long-lived token request failed. ' . print_r( $body, true ) );
				}

				/**
				 * @var object{access_token: string, token_type: string, expires_in: int} $decoded
				 */
				$decoded = json_decode( $body );

				$setting->access_token                = $decoded->access_token;
				$setting->expires_in                  = $decoded->expires_in;
				$setting->data_access_expiration_time = time() + $decoded->expires_in;

				m1d1_update_settings( $setting );
			}

			$fields = implode(
				',',
				[
					'id',
					'created_time',
					'message',
					'link',
					'permalink_url',
					'updated_time',
				]
			);

			$url = add_query_arg(
				urlencode_deep(
					array(
						'access_token' => $setting->access_token,
						'fields'       => $fields,
					)
				),
				"https://graph.facebook.com/v18.0/$setting->user_id/posts"
			);

			$response = wp_remote_get( $url );
			$code     = wp_remote_retrieve_response_code( $response );
			$body     = wp_remote_retrieve_body( $response );

			if ( 200 !== $code ) {
				WP_CLI::error( "Wrong response: " . print_r( $body, true ) );
			}

			$decoded = json_decode( $body );

			WP_CLI::line(
				json_encode( $decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
			);
		}
	}
}
