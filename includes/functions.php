<?php

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
