<?php
/**
 * 체크 템플릿
 *
 * @var string $keyword
 * @var int    $total_count
 * @var array  $rows
 */
?>

<div class="d1m1">
    <h1>1D1M Check</h1>
    <p class="total-count">
		<?php
		printf( _nx( 'Total %d song collected.', 'Total %d songs collected.', $total_count, 'Total number of songs.', 'm1d1' ), $total_count );
		?>
    </p>
    <form action="" method="get">
        <fieldset>
            <legend>Search</legend>
            <label for="keyword">Keyword</label>
            <input id="keyword" name="keyword" type="search" autocomplete="off"
                   value="<?php echo esc_attr( $keyword ); ?>">
        </fieldset>
        <p class="button-wrap">
            <button type="submit">Submit</button>
        </p>
    </form>

	<?php if ( $rows ) : ?>
		<?php if ( $keyword ) : ?>
            <p class="rows-count">
				<?php
				printf( _nx( '%d record found.', '%d records found.', count( $rows ), 'Total count of found records.', 'm1d1' ), count( $rows ) );
				?>
            </p>
		<?php endif; ?>
        <table class="">
            <thead>
            <tr>
                <th class="col-seq">Seq.</th>
                <th class="col-artist">Artist</th>
                <th class="col-title">Title</th>
                <th class="col-rating">Rating</th>
            </tr>
            </thead>
            <tbody>
			<?php foreach ( $rows as $row ): ?>
                <tr>
                    <td class="col-seq"><?php echo esc_html( $row->sequence ); ?></td>
                    <td class="col-artist"><?php echo esc_html( $row->artist ); ?></td>
                    <td class="col-title">
						<?php if ( $row->yt_id ) : ?>
                            <a href="<?php echo esc_url( "https://music.youtube.com/watch?v=$row->yt_id" ); ?>"
                               target="_blank"><?php echo esc_html( $row->title ); ?></a>
						<?php else: ?>
							<?php echo esc_html( $row->title ); ?>
						<?php endif; ?>
                    </td>
                    <td class="col-rating"><?php echo esc_html( $row->rating ); ?></td>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
	<?php else: ?>
        <p class="no-data">No results.</p>
	<?php endif; ?>
</div>

