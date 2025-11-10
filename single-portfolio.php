<?php
/**
 * The template for displaying single portfolio projects
 */

get_header(); 
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php if ( have_posts() ) : ?>
            <?php while ( have_posts() ) : the_post(); ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class('portfolio-single-item'); ?>>
                    
                    <header class="entry-header">
                        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                    </header>

                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="portfolio-featured-image">
                            <?php 
                                // Use the 'blog-featured' size you created in functions.php
                                the_post_thumbnail('blog-featured'); 
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="portfolio-meta-box">
                        <?php 
                        // Get all your custom fields
                        $client    = get_post_meta( get_the_ID(), '_portfolio_client', true );
                        $year      = get_post_meta( get_the_ID(), '_portfolio_year', true );
                        $url       = get_post_meta( get_the_ID(), '_portfolio_url', true );
                        $is_featured = get_post_meta( get_the_ID(), '_portfolio_featured', true );

                        if ( $is_featured == '1' ) {
                            echo '<p class="portfolio-featured-status"><strong>Status:</strong> <span class="featured-badge">Featured Project</span></p>';
                        }
                        if ( $client ) {
                            echo '<p class="portfolio-client"><strong>Client:</strong> ' . esc_html( $client ) . '</p>';
                        }
                        if ( $year ) {
                            echo '<p class="portfolio-year"><strong>Year:</strong> ' . esc_html( $year ) . '</p>';
                        }
                        
                        // Get the taxonomy terms (Project Types)
                        $terms = get_the_terms( get_the_ID(), 'project_type' );
                        if ( $terms && ! is_wp_error( $terms ) ) {
                            echo '<p class="project-types-wrapper"><strong>Services:</strong> ';
                            $term_list = array();
                            //Loop over each term
                            foreach ( $terms as $term ) {
                                $term_list[] = '<span class="term-badge">' . esc_html( $term->name ) . '</span>';
                            }
                            echo implode( ' ', $term_list );
                            echo '</p>';
                        }
                        
                        if ( $url ) {
                            echo '<p class="portfolio-url"><a href="' . esc_url( $url ) . '" class="external-link" target="_blank" rel="noopener noreferrer">Visit Live Site →</a></p>';
                        }
                        ?>
                    </div>

                    <div class="entry-content">
                        <?php the_content(); // This displays the main project description from the editor ?>
                    </div>
                    
                </article>

            <?php endwhile; ?>
        <?php endif; ?>

    </main>
</div>

<?php 
get_footer(); 
?>