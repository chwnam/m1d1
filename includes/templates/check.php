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
    <h2>1D1M Check</h2>
    <p class="total-count">
        <?php
	    printf( _nx( 'Total %d song collected.', 'Total %d songs collected.', 'Total number of songs.', 'm1d1' ), $total_count );
	    ?>
    </p>
    <form action="" method="get">
        <fieldset>
            <legend>Search</legend>
            <label for="keyword">Keyword</label>
            <input id="keyword" name="keyword" type="search" autocomplete="off"
                   value="<?php echo esc_attr( $keyword ); ?>">
        </fieldset>
        <p>
            <button type="submit">Search</button>
        </p>
    </form>

	<?php if ( $keyword ) : ?>
		<?php if ( $rows ) : ?>
            <p class="rows-count">
				<?php
				printf( _nx( '%d record found.', '%d records found.', 'Total count of found records.', 'm1d1' ), count( $rows ) );
				?>
            </p>
            <table class="">
                <thead>
                <tr>
                    <th>Seq.</th>
                    <th>Artist</th>
                    <th>Title</th>
                    <th>Rating</th>
                    <th>Link</th>
                </tr>
                </thead>
                <tbody>
				<?php foreach ( $rows as $row ): ?>
                <tr>
                    <td><?php echo esc_html( $row->sequence ); ?></td>
                    <td><?php echo esc_html( $row->artist ); ?></td>
                    <td><?php echo esc_html( $row->title ); ?></td>
                    <td><?php echo esc_html( $row->rating ); ?></td>
                    <td><?php echo esc_html( $row->yt_id ); ?></td>
                </tr>
				<?php endforeach; ?>
                </tbody>
            </table>
		<?php else: ?>
            <p class="no-data">No results.</p>
		<?php endif; ?>
	<?php endif; ?>
</div>

