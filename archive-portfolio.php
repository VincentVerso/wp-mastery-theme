<?php
/**
 * Portfolio Archive Template
 *
 * This displays all portfolio items
 */

get_header();
?>

<div class="portfolio-archive-wrapper">
    <header class="portfolio-header">
        <h1>Our Portfolio</h1>
        <p>Explore our latest projects and creative work</p>
        <?php
        // Display taxonomy filter
        $terms = get_terms(array(
            'taxonomy'   => 'project_type',
            'hide_empty' => true,
        ));

        if ($terms && ! is_wp_error($terms)) : ?>
            <div class="portfolio-filters">
                <button class="filter-btn active" data-filter="*">All Projects</button>
                <?php foreach ( $terms as $term ) : ?>
                    <button class="filter-btn" data-filter=".<?php echo esc_attr( $term->slug ); ?>">
                        <?php echo esc_html( $term->name ); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </header>

    <?php if (have_posts()) : ?>
        <div class="portfolio-grid">
            <?php
            while (have_posts()) : the_post();
                // Get custom fields
                $client      = get_post_meta( get_the_ID(), '_portfolio_client', true );
                $year        = get_post_meta( get_the_ID(), '_portfolio_year', true );
                $url         = get_post_meta( get_the_ID(), '_portfolio_url', true );
                $is_featured = get_post_meta( get_the_ID(), '_portfolio_featured', true );

                // Get project types for filtering
                $terms = get_the_terms( get_the_ID(), 'project_type' );
                $term_classes = '';
                if ( $terms && ! is_wp_error( $terms ) ) {
                    foreach ( $terms as $term ) {
                        $term_classes .= ' ' . $term->slug;
                    }
                }
            ?>
            <article class="portfolio-item<?php echo esc_attr( $term_classes ); echo $is_featured ? ' featured' : ''; ?>">

                <?php if ( $is_featured ) : ?>
                    <span class="featured-badge">Featured</span>
                <?php endif; ?>

                <div class="portfolio-image">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'large' ); ?>
                    <?php else : ?>
                        <img src="https://via.placeholder.com/600x400" alt="Placeholder">
                    <?php endif; ?>
                    <div class="portfolio-overlay">
                        <a href="<?php the_permalink(); ?>" class="portfolio-link">
                            View Project
                        </a>
                    </div>
                </div>

                <div class="portfolio-content">
                    <h2><?php the_title(); ?></h2>

                    <?php if ($client) : ?>
                        <p class="portfolio-client">Client: <?php echo esc_html( $client ); ?></p>
                    <?php endif; ?>

                    <?php if ($year) : ?>
                        <p class="portfolio-year">Year: <?php echo esc_html( $year ); ?></p>
                    <?php endif; ?>

                    <?php if ($terms) : ?>
                        <div class="portfolio-terms">
                            <?php foreach ( $terms as $term ) : ?>
                                <span class="term-badge"><?php echo esc_html($term->name); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="portfolio-excerpt">
                        <?php the_excerpt(); ?>
                    </div>

                    <?php if ($url) : ?>
                        <a href="<?php echo esc_url( $url ); ?>"
                           class="external-link"
                           target="_blank"
                           rel="noopener noreferrer">
                            Visit Live Site →
                        </a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endwhile; ?>
        </div>

        <?php
        // Pagination
        the_posts_pagination(array(
            'mid_size'  => 2,
            'prev_text' => '← Previous',
            'next_text' => 'Next →',
        ));
        ?>

    <?php else : ?>
        <p>No portfolio items found.</p>
    <?php endif; ?>

</div>

<?php get_footer(); ?>