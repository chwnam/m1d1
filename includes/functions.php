<?php

if ( ! function_exists( 'm1d1_template' ) ) {
	function m1d1_template( string $template_name, array $context = [], bool $return = false ): string {
		$part = dirname( M1D1_MAIN ) . '/includes/templates/' . ltrim( $template_name, '/' );
		$exts = [ '.inc.php', '.php', '.html', '.htm' ];
		$path = '';

		foreach ( $exts as $ext ) {
			$tmpl = $part . $ext;
			if ( file_exists( $tmpl ) ) {
				$path = $tmpl;
				break;
			}
		}

		$output = '';

		if ( $return ) {
			ob_start();
		}

		$closure = function () use ( $path, $context ) {
			if ( $path ) {
				if ( $context ) {
					extract( $context, EXTR_SKIP );
				}
				include $path;
			}
		};

		$closure();

		if ( $return ) {
			$output = ob_get_clean();
		}

		return $output;
	}
}

if ( ! function_exists( 'm1d1_get_app_id' ) ) {
	/**
	 * Get APP ID
	 *
	 * @return string
	 */
	function m1d1_get_app_id(): string {
		return defined( 'M1D1_APP_ID' ) ? M1D1_APP_ID : '';
	}
}


if ( ! function_exists( 'm1d1_get_app_secret' ) ) {
	/**
	 * Get APP SECRET
	 *
	 * @return string
	 */
	function m1d1_get_app_secret(): string {
		return defined( 'M1D1_APP_SECRET' ) ? M1D1_APP_SECRET : '';
	}
}


if ( ! function_exists( 'm1d1_get_client_token' ) ) {
	/**
	 * Get CLIENT TOKEN
	 *
	 * @return string
	 */
	function m1d1_get_client_token(): string {
		return defined( 'M1D1_CLIENT_TOKEN' ) ? M1D1_CLIENT_TOKEN : '';
	}
}


if ( ! function_exists( 'm1d1_get_python_path' ) ) {
	/**
	 * Get PYTHON PATH
	 *
	 * @return string
	 */
	function m1d1_get_python_path(): string {
		return defined( 'M1D1_PYTHON_PATH' ) ? M1D1_PYTHON_PATH : '';
	}
}


if ( ! function_exists( 'm1d1_get_playlist_path' ) ) {
	/**
	 * Get PLAYLIST PATH
	 *
	 * @return string
	 */
	function m1d1_get_playlist_path(): string {
		return defined( 'M1D1_PLAYLIST_PATH' ) ? M1D1_PLAYLIST_PATH : '';
	}
}

if ( ! function_exists( 'm1d1_get_settings' ) ) {
	/**
	 * @return object{access_token: string, data_access_expiration_time: int, expires_in: int, user_id: string}
	 */
	function m1d1_get_settings(): stdClass {
		return get_option( 'm1d1_settings' );
	}
}


if ( ! function_exists( 'm1d1_update_settings' ) ) {
	function m1d1_update_settings( stdClass $settings ): bool {
		return update_option( 'm1d1_settings', $settings );
	}
}


if ( ! function_exists( 'm1d1_sanitize_settings' ) ) {
	/**
	 * Sanitizer function.
	 *
	 * @param mixed $value
	 *
	 * @return object{access_token: string, data_access_expiration_time: int, expires_in: int, user_id: string}
	 * @used-by m1d1_init_settings()
	 */
	function m1d1_sanitize_settings( mixed $value ): stdClass {
		$default   = m1d1_default_settings();
		$sanitized = m1d1_default_settings();

		$sanitized->access_token                = sanitize_text_field( $value->access_token ?? $default->access_token );
		$sanitized->data_access_expiration_time = intval( $value->data_access_expiration_time ?? $default->data_access_expiration_time );
		$sanitized->expires_in                  = intval( $value->expires_in ?? $default->expires_in );
		$sanitized->user_id                     = sanitize_text_field( $value->user_id ?? $default->user_id );

		return $sanitized;
	}
}


if ( ! function_exists( 'm1d1_default_settings' ) ) {
	/**
	 * @return object{access_token: string, data_access_expiration_time: int, expires_in: int, user_id: string}
	 */
	function m1d1_default_settings(): stdClass {
		return (object) [
			'access_token'                => '',
			'data_access_expiration_time' => - 1,
			'expires_in'                  => - 1,
			'user_id'                     => '',
		];
	}
}


if ( ! function_exists( 'm1d1_get_fb_api' ) ) {
	function m1d1_get_fb_api(): M1D1_FB_API {
		$app_id       = m1d1_get_app_id();
		$app_secret   = m1d1_get_app_secret();
		$client_token = m1d1_get_client_token();

		if ( ! ( $app_id && $app_secret && $client_token ) ) {
			wp_die(
				"'M1D1_APP_ID' or 'M1D1_APP_SECRET', or 'M1D1_CLIENT_TOKEN'" .
				' are not properly set in the wp-config.php!'
			);
		}

		return new M1D1_FB_API( $app_id, $app_secret, $client_token );
	}
}


if ( ! function_exists( 'm1d1_get_facebook_permalink_url' ) ) {
	function m1d1_get_facebook_permalink_url( string $fb_id ): string {
		if ( preg_match( '/^(\d+)_(\d+)$/', $fb_id, $matches ) ) {
			return "https://www.facebook.com/$matches[1]/posts/$matches[2]";
		} else {
			return '';
		}
	}
}


if ( ! function_exists( 'm1d1_get_youtube_music_url' ) ) {
	function m1d1_get_youtube_music_url( string $fb_id ): string {
		$fb_id = urlencode( $fb_id );
		if ( $fb_id ) {
			return "https://youtube.com/watch?v=$fb_id";
		} else {
			return '';
		}
	}
}


if ( ! function_exists( 'm1d1_get_datetime' ) ) {
	function m1d1_get_datetime( $input ): DateTimeImmutable|false {
		$timezone = new DateTimeZone( 'Asia/Seoul' );
		$datetime = date_create_immutable_from_format( 'Y-m-d H:i:s', $input, $timezone );

		if ( $datetime ) {
			$datetime->setTimezone( $timezone );
		}

		return $datetime;
	}
}
