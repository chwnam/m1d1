<?php
/**
 * Plugin Name:       1일1메탈 데이터베이스
 * Version:           1.1.0
 * Description:       1일1메탈 수집곡을 보관하고 포스팅 아카이빙하는 워드프레스 플러그인.
 * Plugin URI:        https://github.com/chwnam/m1d1
 * Requires at least:
 * Requires PHP:      8.0
 * Author:            changwoo
 * Author URI:        https://blog.changwoo.pe.kr
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       m1d1
 */

const M1D1_MAIN    = __FILE__;
const M1D1_VERSION = '1.1.0';

if ( ! function_exists( 'm1d1_check' ) ) {
	function m1d1_check(): string {
		global $wpdb;

		$keyword = sanitize_text_field( wp_unslash( $_GET['keyword'] ?? '' ) );
		$rows    = array();

		if ( $keyword ) {
			$query = $wpdb->prepare(
				"SELECT * FROM m1d1_playlist WHERE artist LIKE '%%%s%' OR title LIKE '%%%s%' ORDER BY sequence DESC",
				$wpdb->esc_like( $keyword ),
				$wpdb->esc_like( $keyword ),
			);

			$rows = $wpdb->get_results( $query );
		}

		$total_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM m1d1_playlist" );

		ob_start();

		include __DIR__ . '/includes/templates/check.php';

		wp_enqueue_script( 'm1d1', plugins_url( 'includes/assets/script.js', M1D1_MAIN ), array(), M1D1_VERSION );
		wp_enqueue_style( 'm1d1', plugins_url( 'includes/assets/style.css', M1D1_MAIN ), array(), M1D1_VERSION );

		return ob_get_clean();
	}

	add_shortcode( 'm1d1_check', 'm1d1_check' );
}


if ( ! function_exists( 'm1d1_get_app_id' ) ) {
	function m1d1_get_app_id(): string {
		return defined( 'M1D1_APP_ID' ) ? M1D1_APP_ID : '';
	}
}


if ( ! function_exists( 'm1d1_get_app_secret' ) ) {
	function m1d1_get_app_secret(): string {
		return defined( 'M1D1_APP_SECRET' ) ? M1D1_APP_SECRET : '';
	}
}


if ( ! function_exists( 'm1d1_get_client_token' ) ) {
	function m1d1_get_client_token(): string {
		return defined( 'M1D1_CLIENT_TOKEN' ) ? M1D1_CLIENT_TOKEN : '';
	}
}


if ( ! function_exists( 'm1d1_default_settings' ) ) {
	/**
	 * @return object{access_token: string, data_access_expiration_time: int, expires_in: int, user_id: string}
	 */
	function m1d1_default_settings(): stdClass {
		return (object) array(
			'access_token'                => '',
			'data_access_expiration_time' => - 1,
			'expires_in'                  => - 1,
			'user_id'                     => '',
		);
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


if ( ! function_exists( 'm1d1_init_settings' ) ) {
	/**
	 * Register settings to the core.
	 *
	 * @return void
	 * @uses m1d1_sanitize_settings()
	 * @uses m1d1_default_settings()
	 */
	function m1d1_init_settings(): void {
		register_setting(
			'm1d1',
			'm1d1_settings',
			array(
				'type'              => 'object',
				'description'       => '',
				'sanitize_callback' => 'm1d1_sanitize_settings',
				'show_in_rest'      => false,
				'default'           => m1d1_default_settings(),
			),
		);
	}

	add_action( 'init', 'm1d1_init_settings' );
}


if ( ! function_exists( 'm1d1_get_settings' ) ) {
	/**
	 * @return object{access_token: string, data_access_expiration_time: int, expires_in: int}
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


if ( defined( 'WP_CLI' ) ) {
	require_once __DIR__ . '/includes/class-m1d1-cli.php';
	try {
		WP_CLI::add_command( 'm1d1', M1D1_CLI::class );
	} catch ( Exception $e ) {
		die( $e->getMessage() );
	}
}
