<?php
/**
 * @var string                                                   $keyword
 * @var int                                                      $total_count
 * @var int                                                      $max_page
 * @var int                                                      $cur_page
 * @var array{object{id: int, name: string, excluded_at: string} $rows
 */
?>

<div class="m1d1">
    <h1>D1M1 Excluded</h1>

    <form action="" method="get">
        <fieldset>
            <legend>Search</legend>
            <label for="keyword">Keyword</label>
            <span class="search-wrap"><input id="keyword"
                                             name="keyword"
                                             type="search"
                                             autocomplete="off"
                                             value="<?php echo esc_attr( $keyword ); ?>"><input id="clear"
                                                                                                type="button"
                                                                                                value="X"></span>
        </fieldset>
        <p class="button-wrap">
            <button type="submit">Submit</button>
        </p>
    </form>

	<?php if ( $rows ) : ?>
        <p class="total-count">
			<?php
			printf( _n( '%d artist found.', '%d artists found.', $total_count, 'm1d1' ), $total_count );
			?>
        </p>
        <div id="excluded-artists" class="grid-table">
            <section class="table table-header">
                <div class="row row-0 contents">
                    <span class="col col-artist_name">Artist Name</span>
                    <span class="col col-excluded_at">Excluded</span>
                </div>
            </section>
            <section class="table table-body">
				<?php foreach ( $rows as $idx => $row ): ?>
                    <div class="contents row row-<?php echo intval( $idx ); ?>">
                        <span class="col col-artist_name"><?php echo esc_html( $row->name ); ?></span>
                        <span class="col col-excluded_at"><?php echo esc_html( $row->excluded_at ); ?></span>
                    </div>
				<?php endforeach; ?>
            </section>
        </div>
        <?php m1d1_pagination( $cur_page, $max_page, 'excluded-artists' ); ?>
	<?php else: ?>
        <p class="no-rows">No found rows.</p>
	<?php endif ?>
</div>
