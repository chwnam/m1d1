<?php
/**
 * @var string $pagination_id
 * @var string $base_url
 * @var int    $max_page
 * @var int    $cur_page
 */


$pagination_id = esc_attr( $pagination_id );

$first_url = esc_url( add_query_arg( 'pg', 1, $base_url ) );
$last_url  = esc_url( add_query_arg( 'pg', $max_page, $base_url ) );
$prev_url  = esc_url( add_query_arg( 'pg', max( 1, $cur_page - 1 ), $base_url ) );
$next_url  = esc_url( add_query_arg( 'pg', min( $max_page, $cur_page + 1 ), $base_url ) );

?>
<div class="pagination-wrap">
    <div class="pagination" id="pagination-<?php echo $pagination_id; ?>">
        <a id="first-<?php echo $pagination_id ?>" class="arrow" href="<?php echo $first_url; ?>">&laquo;</a>
        <a id="prev-<?php echo $pagination_id ?>" class="arrow" href="<?php echo $prev_url; ?>">&lsaquo;</a>
        <div class="page-wrap">
            <label class="screen-reader-text" for="page-<?php echo $pagination_id; ?>">Page</label>
            <input id="page-<?php echo $pagination_id; ?>"
                   class="page no-spinner"
                   type="number"
                   value="<?php echo esc_attr( $cur_page ); ?>">
            <span class="slash">/</span>
            <span class="total-page"><?php echo esc_html( $max_page ); ?></span>
        </div>
        <a id="next-<?php echo $pagination_id ?>" class="arrow" href="<?php echo $next_url; ?>">&rsaquo;</a>
        <a id="last-<?php echo $pagination_id ?>" class="arrow" href="<?php echo $last_url; ?>">&raquo;</a>
    </div>
</div>
