<?php
/**
 * Plugin Name:       1일1메탈 데이터베이스
 * Version:           1.0.0
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

		return ob_get_clean();
	}

	add_shortcode( 'm1d1_check', 'm1d1_check' );
}


