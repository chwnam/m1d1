<?php
/**
 * Plugin Name:       1일1메탈 데이터베이스
 * Version:           1.2.3
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
const M1D1_VERSION = '1.2.3';

require_once __DIR__ . '/vendor/autoload.php';

if ( ! function_exists( 'm1d1_check' ) ) {
	function m1d1_check(): string {
		global $wpdb;

		$keyword = esc_sql( sanitize_text_field( wp_unslash( $_GET['keyword'] ?? '' ) ) );
		$where   = '';
		$orderby = ' ORDER BY sequence DESC';
		$limit   = ' LIMIT 0, 30';

		if ( $keyword ) {
			$where = $wpdb->prepare(
				" WHERE artist LIKE '%%%s%' OR title LIKE '%%%s%'",
				$wpdb->esc_like( $keyword ),
				$wpdb->esc_like( $keyword ),
			);
		}

		$query       = "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}m1d1_playlist $where$orderby$limit";
		$rows        = $wpdb->get_results( $query );
		$total_count = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' );

		wp_enqueue_script(
			'm1d1',
			plugins_url( 'includes/assets/script.js', M1D1_MAIN ),
			[ 'jquery' ],
			M1D1_VERSION
		);

		wp_enqueue_style( 'm1d1', plugins_url( 'includes/assets/style.css', M1D1_MAIN ), array(), M1D1_VERSION );

		return m1d1_template( 'check', compact( 'keyword', 'total_count', 'rows' ), true );
	}

	add_shortcode( 'm1d1_check', 'm1d1_check' );
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


if ( defined( 'WP_CLI' ) ) {
	try {
		WP_CLI::add_command( 'm1d1', M1D1_CLI::class );
	} catch ( Exception $e ) {
		die( $e->getMessage() );
	}
}
