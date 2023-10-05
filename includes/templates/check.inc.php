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
        <p class="total-count">
			<?php
			printf( _nx( '%d track found.', '%d tracks found.', $total_count, 'Total tracks.', 'm1d1' ), $total_count );
			?>
        </p>
        <table class="playlist-table">
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
                <tr class="row" data-id="<?php echo esc_attr( $row->id ); ?>">
                    <td class="col-seq"><?php echo esc_html( $row->sequence ); ?></td>
                    <td class="col-artist"><?php echo esc_html( $row->artist ); ?></td>
                    <td class="col-title"><?php echo esc_html( $row->title ); ?></td>
                    <td class="col-rating"><?php echo esc_html( $row->rating ); ?></td>
                </tr>
                <tr class="data-row hidden" data-id="<?php echo esc_attr( $row->id ); ?>">
                    <td class="data-col" colspan="4">
                        <ul>
                            <li>
                                <span class="label">ID</span>
								<?php echo esc_html( $row->id ); ?>
                            </li>
                            <li>
                                <span class="label">Facebook</span>
								<?php if ( $row->fb_id ) : ?>
                                    <a href="<?php echo esc_url( m1d1_get_facebook_permalink_url( $row->fb_id ) ); ?>"
                                       target="_blank">
                                        Visit
                                    </a>
								<?php else : ?>
                                    No Post ID
								<?php endif; ?>
                            </li>
                            <li>
                                <span class="label">Youtube Music</span>
								<?php if ( $row->yt_id ): ?>
                                    <a href="<?php echo esc_url( m1d1_get_youtube_music_url( $row->yt_id ) ); ?>"
                                       target="_blank">
                                        Watch
                                    </a>
								<?php else : ?>
                                    No Video ID
								<?php endif; ?>
                            </li>
                            <li>
                                <span class="label">Artist</span>
								<?php echo esc_html( $row->artist ); ?>
                            </li>
                            <li>
                                <span class="label">Title</span>
								<?php echo esc_html( $row->title ); ?>
                            </li>
                            <li>
                                <span class="label">Length</span>
								<?php echo esc_html( $row->length ); ?>
                            </li>
                            <li>
                                <span class="label">Rating</span>
								<?php if ( $row->rating ) : ?>
									<?php echo esc_html( $row->rating ); ?>
								<?php else : ?>
                                    No Rating
								<?php endif; ?>
                            </li>
                            <li>
                                <span class="label">Created At</span>
								<?php echo esc_html( $row->created_time ); ?>
                            </li>
                            <li>
                                <span class="label">Updated At</span>
								<?php if ( $row->created_time !== $row->updated_time ) : ?>
									<?php echo esc_html( $row->updated_time ); ?>
								<?php else : ?>
                                    No Update
								<?php endif; ?>
                            </li>
                            <li>
                                <p class="label">Description</p>
								<?php if ( $row->description ) : ?>
									<?php echo esc_html( $row->description ); ?>
								<?php else : ?>
                                    No Description
								<?php endif; ?>
                            </li>
                        </ul>
                    </td>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
	<?php else: ?>
        <p class="no-data">No results.</p>
	<?php endif; ?>
</div>
