<?php
/**
 * 체크 템플릿
 *
 * @var string $keyword
 * @var int    $total_count
 * @var int    $max_page
 * @var int    $cur_page
 * @var array  $rows
 */
?>

<div class="m1d1">
    <h1>M1D1 Check</h1>

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
			printf( _n( '%d track found.', '%d tracks found.', $total_count, 'm1d1' ), $total_count );
			?>
        </p>
        <div id="playlist-table" class="grid-table">
            <section class="table table-header">
                <div class="row row-0 contents">
                    <span class="col col seq">Seq.</span>
                    <span class="col col-artist">Artist</span>
                    <span class="col col-title">Title</span>
                    <span class="col col-rating">Rating</span>
                </div>
            </section>
            <section class="table table-body">
				<?php foreach ( $rows as $idx => $row ): ?>
                    <div class="row row-<?php echo esc_attr( $idx ); ?> contents"
                         data-row="<?php echo esc_attr( json_encode( $row ) ); ?>">
                        <span class="col col-seq"><?php echo esc_html( $row->sequence ); ?></span>
                        <span class="col col-artist"><?php echo esc_html( $row->artist ); ?></span>
                        <span class="col col-title"><?php echo esc_html( $row->title ); ?></span>
                        <span class="col col-rating"><?php echo esc_html( $row->rating ); ?></span>
                    </div>
				<?php endforeach; ?>
            </section>
        </div>

		<?php m1d1_pagination( $cur_page, $max_page, 'playlist' ); ?>

        <dialog id="track-info">
            <ul>
                <li>
                    <span class="label">ID</span>
                    <span id="dialog-id" class="value"></span>
                </li>
                <li>
                    <span class="label">Facebook</span>
                    <span id="dialog-fb-link" class="value"></span>
                </li>
                <li>
                    <span class="label">Youtube Music</span>
                    <span id="dialog-yt-link" class="value"></span>
                </li>
                <li>
                    <span class="label">Artist</span>
                    <span id="dialog-artist" class="value"></span>
                </li>
                <li>
                    <span class="label">Title</span>
                    <span id="dialog-title" class="value"></span>
                </li>
                <li>
                    <span class="label">Length</span>
                    <span id="dialog-length" class="value"></span>
                </li>
                <li>
                    <span class="label">Rating</span>
                    <span id="dialog-rating" class="value"></span>
                </li>
                <li>
                    <span class="label">Created At</span>
                    <span id="dialog-created_at" class="value"></span>
                </li>
                <li>
                    <span class="label">Updated At</span>
                    <span id="dialog-updated_at" class="value"></span>
                </li>
                <li>
                    <p class="label">Description</p>
                    <p id="dialog-description" class="value"></p>
                </li>
            </ul>
            <p class="close-wrap ">
                <button class="close" autofocus>Close</button>
            </p>
        </dialog>
	<?php else: ?>
        <p class="no-rows">No found rows.</p>
	<?php endif; ?>
</div>
