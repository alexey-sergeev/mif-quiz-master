<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	
	<div class="post-wrapper">

	<?php if ( has_post_thumbnail() ) { ?>
        <header class="entry-header with-image">
        	<div class="title-wrapper" style="background-image: url('<?php echo get_the_post_thumbnail_url( get_the_ID(), 'large' ); ?>')">
	<?php } else { ?>
		<header class="entry-header">
			<div class="title-wrapper">
	<?php } ?>

			<?php
			if ( function_exists( 'epc_get_primary_term_posts' ) ) {
				$primary_category = get_post_meta( get_the_ID(), 'epc_primary_category', true );
				if ( $primary_category ) {
					$exclude_cat_id = $primary_category;
					$the_category = '<a href="' . get_category_link( $exclude_cat_id ) . '" class="entry-cat">' . get_cat_name( $primary_category ) . '</a><span class="entry-cat-sep"> / </span>';
				} else {
					$categories = get_the_category();
					if ( ! empty( $categories ) ) {
						$exclude_cat_id = $categories[0]->term_id;
						$the_category = '<a href="' . get_category_link( $exclude_cat_id ) . '" class="entry-cat">' . esc_html( $categories[0]->name ) . '</a><span class="entry-cat-sep"> / </span>';
					} else {
						$exclude_cat_id = '';
						$the_category = '';
					}					
				}
			} else {
				$categories = get_the_category();
				if ( ! empty( $categories ) ) {
					$exclude_cat_id = $categories[0]->term_id;
					$the_category = '<a href="' . get_category_link( $exclude_cat_id ) . '" class="entry-cat">' . esc_html( $categories[0]->name ) . '</a><span class="entry-cat-sep"> / </span>';
				} else {
					$exclude_cat_id = '';
					$the_category = '';
				}
			}

			the_title( '<h2 class="entry-title">' . $the_category . '<i class="latest-entry-icon"></i><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
			?>
			</div>

			<?php if ( 'post' === get_post_type() ) : ?>
			<div class="entry-meta">
				<?php latest_posted_on(); ?>
			</div><!-- .entry-meta -->
			<?php endif; ?>

		</header><!-- .entry-header -->

		<div class="entry-content">
			<?php if ( get_post_format() == 'audio' ) {
				$audio_content = apply_filters( 'the_content', get_the_content() );
				$audio = false;
				// Only get audio from the content if a playlist isn't present.
				if ( false === strpos( $audio_content, 'wp-playlist-script' ) ) {
					$audio = get_media_embedded_in_content( $audio_content, array( 'audio' ) );
				}
				if ( ! empty( $audio ) ) {
					$first_audio = true;
					foreach ( $audio as $audio_html ) {
						if ( $first_audio ) {
							echo '<div class="entry-audio">';
								echo $audio_html;
							echo '</div>';
							$first_audio = false;
						}
					}
				} else {
					// the_excerpt();
				}
			} else {
				// the_excerpt();
			}
			?>
			<?php
				wp_link_pages( array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'latest' ),
					'after'  => '</div>',
				) );
			?>

    		<?php if ( 'post' === get_post_type()) : ?>
        	<a class="more-tag button" href="<?php echo esc_url( get_the_permalink() ); ?>" title="<?php echo esc_html( get_the_title() ); ?>"><?php esc_html_e( 'Continue Reading', 'latest' ); ?></a>
    		<?php endif; ?>

		</div><!-- .entry-content -->

		<footer class="entry-footer">
			<?php latest_entry_footer( $exclude_cat_id ); ?>
		</footer><!-- .entry-footer -->
	</div>
</article>
