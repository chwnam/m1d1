<?php
/**
 * Plugin Name:       1일1메탈 데이터베이스
 * Version:           1.3.1
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
const M1D1_VERSION = '1.3.1';

require_once __DIR__ . '/vendor/autoload.php';

if ( ! function_exists( 'm1d1_check' ) ) {
	function m1d1_check(): string {
		global $wpdb;

		$per_page = 10;
		$cur_page = max( 1, absint( $_GET['pg'] ?? '0' ) );
		$offset   = ( $cur_page - 1 ) * $per_page;
		$keyword  = esc_sql( sanitize_text_field( wp_unslash( $_GET['keyword'] ?? '' ) ) );
		$where    = ' WHERE 1=1';
		$orderby  = ' ORDER BY sequence DESC';
		$limit    = $wpdb->prepare( ' LIMIT %d, %d', $offset, $per_page );

		if ( $keyword ) {
			$where = $wpdb->prepare(
				" WHERE artist LIKE '%%%s%' OR title LIKE '%%%s%'",
				$wpdb->esc_like( $keyword ),
				$wpdb->esc_like( $keyword ),
			);
		}

		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}m1d1_playlist $where$orderby$limit";
		$rows  = array_map( function ( $row ) {
			if ( $row->description ) {
				$row->description = wp_kses( nl2br( $row->description ), [ 'br' => [] ] );
			}
			return $row;
		}, $wpdb->get_results( $query ) );

		$total_count = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' );
		$max_page    = (int) ceil( (float) $total_count / 10.0 );
		$context     = compact( 'cur_page', 'max_page', 'keyword', 'total_count', 'rows' );

		wp_enqueue_script(
			'm1d1',
			plugins_url( 'includes/assets/script.js', M1D1_MAIN ),
			[ 'jquery' ],
			M1D1_VERSION
		);

		wp_enqueue_style( 'm1d1', plugins_url( 'includes/assets/style.css', M1D1_MAIN ), array(), M1D1_VERSION );

		return m1d1_template( 'check', $context, true );
	}

	add_shortcode( 'm1d1_check', 'm1d1_check' );
}


if ( ! function_exists( 'm1d1_excluded_artists' ) ) {
	function m1d1_excluded_artists(): string {
		global $wpdb;

		$per_page = 10;
		$cur_page = max( 1, absint( $_GET['pg'] ?? '0' ) );
		$offset   = ( $cur_page - 1 ) * $per_page;
		$fields   = 'a.id, a.name, e.excluded_at';
		$keyword  = esc_sql( sanitize_text_field( wp_unslash( $_GET['keyword'] ?? '' ) ) );
		$where    = ' WHERE 1=1';
		$orderby  = ' ORDER BY e.excluded_at DESC, a.name ASC';
		$limit    = $wpdb->prepare( ' LIMIT %d, %d', $offset, $per_page );

		if ( $keyword ) {
			$where = $wpdb->prepare( " AND a.name LIKE '%%%s%'", $wpdb->esc_like( $keyword ) );
		}

		$query = "SELECT SQL_CALC_FOUND_ROWS $fields FROM {$wpdb->prefix}rapl_excluded_artists AS e" .
		         " INNER JOIN {$wpdb->prefix}rapl_artists AS a ON a.id = e.artist_id" .
		         "$where$orderby$limit";

		$rows        = $wpdb->get_results( $query );
		$total_count = (int) $wpdb->get_var( 'SELECT FOUND_ROWS()' );
		$max_page    = (int) ceil( (float) $total_count / 10.0 );
		$context     = compact( 'cur_page', 'max_page', 'keyword', 'total_count', 'rows' );

		wp_enqueue_script(
			'm1d1',
			plugins_url( 'includes/assets/script.js', M1D1_MAIN ),
			[ 'jquery' ],
			M1D1_VERSION
		);

		wp_enqueue_style( 'm1d1', plugins_url( 'includes/assets/style.css', M1D1_MAIN ), array(), M1D1_VERSION );

		return m1d1_template( 'excluded-artists', $context, true );
	}

	add_shortcode( 'm1d1_excluded_artists', 'm1d1_excluded_artists' );
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
