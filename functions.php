<?php

    require_once(get_stylesheet_directory() . '/inc/portfolio-cpt.php');
    new PortfolioManager();

    /**
     * Child Theme Enqueue
     */
    // function child_theme_enqueue_styles(){
    //     //Enqueue the parent style
    //     wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    
    //     //Enqueue the child style which loads after the parent style
    //     wp_enqueue_style(
    //         'child-style',
    //         get_stylesheet_directory_uri() . '/style.css',
    //         array('parent-style'), //Dependent on the parent
    //         wp_get_theme()->get('Version')
    //     );
    // }
    // add_action('wp_enqueue_scripts', "child_theme_enqueue_styles");

    /**
 * Enqueue styles for the Astra Child Theme.
 */
    function astra_child_theme_enqueue_styles() {
        
        // This is the correct way to load your child theme's stylesheet.
        wp_enqueue_style(
            'astra-child-theme-css',                         // 1. A unique handle for your stylesheet.
            get_stylesheet_directory_uri() . '/style.css',   // 2. The path to your child's style.css.
            array('astra-theme-css'),                        // 3. The dependency on Astra's main CSS handle.
            wp_get_theme()->get('Version')                   // 4. The version for cache-busting.
        );

    }
    add_action( 'wp_enqueue_scripts', 'astra_child_theme_enqueue_styles' );

    //Enqueue the portfolio filter script
    function my_theme_enqueue_scripts() {
    // Enqueue the portfolio filter script only on the portfolio archive page
        if (is_post_type_archive('portfolio')){
            wp_enqueue_script(
                'portfolio-filter-script',
                get_stylesheet_directory_uri() . '/portfolio-filter.js',
                array(), // Dependencies
                '1.0.0', // Version
                true     // Load in footer
            );
        }
    }
    add_action('wp_enqueue_scripts', 'my_theme_enqueue_scripts');

    /**
     * Custom Shop Page Layout
     */

    //Removing default Woocommerce product loop
    remove_action('woocommerce_before_shop_loop_item','woocommerce_template_loop_product_link_open', 10);
    remove_action('woocommerce_shop_loop_item_title','woocommerce_template_loop_product_title', 10);

    //Add custom product card structure
    //This fucntion is called before the product loop item is rendered.
    //This results in the product being placed inside the custom product card inside the echoed div.
    add_action('woocommerce_before_shop_loop_item', 'custom_product_card_open', 10);
    function custom_product_card_open(){
        echo '<div class="custom-product-card">';
    }

    //Close the div containing the product informaiton.
    add_action('woocommerce_after_shop_loop_item', 'custom_product_card_close', 10);
    function custom_product_card_close(){
        echo '</div>';
    }

    //Recent posts shortcode
    add_shortcode('recent_posts', 'custom_recent_posts_shortcode');
    function custom_recent_posts_shortcode($atts){
        $atts = shortcode_atts(array(
            'count' => 5,
            'category' => '',
        ), $atts);

        //Query arguments
        $args = array(
            'posts_per_page' => intval($atts['count']),
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        );

        //Add category if provided
        if(!empty($atts['category'])){ //If the category is not empty
            $args['category_name'] = sanitize_text_field($atts['category']);
        }

        //Execute the query
        $recent_posts = new WP_Query($args);

        //Start the output buffer
        ob_start();


        if($recent_posts->have_posts()){
            echo '<div class="recent-posts-widget">';
            //While there are posts to display
            while($recent_posts->have_posts()){
                $recent_posts->the_post();
                ?>
                <article class="recent-post-item">
                    <!-- If there is a thumbnail, display it-->
                    <?php if(has_post_thumbnail()): ?>
                        <div class="recent-post-thumbnail">
                        <?php the_post_thumbnail('thumbnail'); ?>
                        </div>
                    <?php endif; ?>
                    <div class="recent-post-content">
                        <h3>
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
                            </a>
                        </h3>
                        <span class="recent-post-date">
                            <?php echo get_the_date(); ?>
                        </span>
                        <p> <?php echo wp_trim_words(get_the_content(), 15); ?> </p>
                    </div>
                </article>
                <?php
            }
            echo '</div>';
        
        }else{
            echo 'No posts found';
        }

        //Reset post data(Restores global $post)
        wp_reset_postdata();

        //return the output buffer
        return ob_get_clean();
    
    }

        //Custom exceprt length based on post type.
    add_filter('excerpt_length', 'custom_excerpt_length_by_post_type', 999);
    function custom_excerpt_length_by_post_type($length){
        
        //Get the post from WordPress.
        //Not a local to this function.
        global $post;

        //Guard clause to prevent errors.
        if(!is_object($post)){
            return $length;
        }

        //If the current post is a product
        if($post->post_type == 'product'){
            return 15;
        }elseif($post->post_type == 'portfolio'){
            return 40;
        }
        //Return the default length if it is not a product or portfolio post
        return $length;
    }

    /**
     * Custom body classes.
     * These help target specific pages with CSS and JS
     */

    //TODO: Reimplement this using a different parent theme.
    // add_filter('body_class', 'my_comprehensive_body_classes');
    // //Pass in the classes array as an argument.
    // function my_comprehensive_body_classes($classes){
        
    //     global $post;

    //     //1. Add user roles
    //     //If the useer is logged in
    //     if(is_user_logged_in()){
    //         //Get the current user and store it in a variable
    //         $current_user = wp_get_current_user();
    //         //This could be null
    //         // Check if roles array is not empty before accessing it
    //         if (!empty($current_user->roles)) {
    //             $classes[] = 'user-role-' . $current_user->roles[0];
    //         }

    //     }else{
    //         $classes[] = 'logged-out';
    //     }

    //     //If the post is not an object, return
    //     if(!is_object($post)){
    //         return $classes;
    //     }

    //     //2. Add specific page slug
    //     if(is_page()){
    //         //This line adds the page slug to the classes array
    //         //The final output will be "page'slug'"
    //         //The output would resemble something like <body class="page-contact-us">...</body>
    //         $classes[] = 'page-'.$post->post_name;
            
    //     }

    //     //3. Add parent slug class
    //     //If its a page and has a parent
    //     if(is_page() && $post->post_parent){
    //         $parent = get_post($post->post_parent);
    //         $classes[] = 'child-of-'.$parent->post_name;
    //     }

    //     //4. Add category slugs for posts
    //     if(is_single()){
    //         $categories = get_the_category();
    //         foreach($categories as $category){
    //             $classes[] = 'category-'.$category->slug;
    //         }
    //     }

    //     //5. Add mobile and desktop detection
    //     if(wp_is_mobile()){
    //         $classes[] = 'mobile-device';
    //     }else{
    //         $classes[] = 'desktop-device';
    //     }

    //     //6. Add custom class based on custom field
    //     //Get the meta data of the current post based on ID of the current post in the WP loop
    //     if(get_post_meta(get_the_ID(), 'layout_style', true)){
    //         $classes[] = 'layout-'.get_post_meta(get_the_ID(), 'layout_style', true);
    //     }

    //     //7. Add time-based classes
    //     $hour = date('H');
    //     if($hour >= 12 && $hour < 18){
    //         $classes[] = 'time-afternoon';
    //     }else{
    //         $classes[] = 'time-evening';
    //     }

    //     //Return the array.
    //     return $classes;
    // }

    add_filter('body_class', 'my_comprehensive_body_classes');

    function my_comprehensive_body_classes($classes) {
        global $post;
        // --- NON-POST DEPENDENT CLASSES ---
        // These will run on every page, even archives or 404 pages.
        // 1. Add user role or logged-out status
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            if (!empty($current_user->roles)) {
                $classes[] = 'user-role-' . $current_user->roles[0];
            }
        } else {
            $classes[] = 'logged-out';
        }

        // 5. Add device type
        $classes[] = wp_is_mobile() ? 'mobile-device' : 'desktop-device';

        // 7. Add time of day
        $hour = date('H'); // Assumes your server time is correct
        if ($hour >= 12 && $hour < 18) {
            $classes[] = 'time-afternoon';
        } else {
            $classes[] = 'time-evening';
        }

        // --- POST DEPENDENT CLASSES ---
        // If there's no valid post object, we stop here.
        if (!is_object($post)) {
            return $classes;
        }

        // 2. Add page-specific slug
        if (is_page()) {
            $classes[] = 'page-'.$post->post_name;
        }

        // 3. Add parent page slug
        if (is_page() && $post->post_parent) {
            $parent = get_post($post->post_parent);
            $classes[] = 'child-of-' . $parent->post_name;
        }

        // 4. Add category slugs for single posts
        if (is_single()) {
            $categories = get_the_category($post->ID);
            if ($categories) {
                foreach ($categories as $category) {
                    $classes[] = 'category-' . $category->slug;
                }
            }
        }

        // 6. THE FIX: Add custom class based on custom field using $post->ID
        $layout_style = get_post_meta($post->ID, 'layout_style', true);
        if (!empty($layout_style)) {
            $classes[] = 'layout-' . $layout_style;
        }

        return $classes;
    }

    //Enforce strong passwords
    function enforce_strong_passwords($username, $password, $errors){
        
        if(strlen($password) < 12){
            $errors->add('weak_password', 'Password must be at least 12 characters');
        }

        if(!preg_match('/[A-Z]/', $password)){
            $errors->add('weak_password', 'Password must contain at least one uppercase letter');
        }

        if(!preg_match('/[a-z]/', $password)){
            $errors->add('weak_password', 'Password must contain at least one lowercase letter');
        }

        if(!preg_match('/[0-9]/', $password)){
            $errors->add('weak_password', 'Password must contain at least one number');
        }

        if(!preg_match('/[^a-zA-Z0-9]/', $password)){
            $errors->add('weak_password', 'Password must contain at least one special character');
        }

    }
    add_action('user_profile_update_errors', 'enforce_strong_passwords', 0, 3);

    function trigger_backup_before_update($upgrader, $hook_extra){
        //Trigger backup using UpdraftPlus
        if(class_exists('UpdraftPlus_Options')){
            $updraftplus_admin = new UpdraftPlus_Admin();
            $updraftplus_admin->request_backupnow(array(
                'files' => 1,
                'db' => 1,
            ));
        }
    }
    add_action('upgrader_process_complete', 'trigger_backup_before_update', 10, 2);

    /**
     * Set JPEG compression quality
     */
    add_filter('jpeg_quality', function () {
        return 85;
    });

    /**
     * Disable WordPress from generating unussed image sizes
     */
    add_filter('intermediate_image_sizes_advanced', function($sizes){
        unset($sizes['medium_larger']);
        unset($sizes['1536X1536']);
        unset($sizes['2048X2048']);
        return $sizes;
    });

    /**
     * Add custom image sizes
     */
    add_theme_support('post-thumbnail');
    add_image_size('portfolio-thumb', 600, 400, true);
    add_image_size('blog-featured', 1200, 600, true);

    /**
     * Lazy loading for all images
     */
    function add_lazy_loading_to_iamges($content) {
        //Add loading="lazy" to all images
        $content = preg_replace(
            '/<img(.*?)>/i',
            '<img$1 loading="lazy">', $content
        );
        return $content;
    }
    add_filter('the_content', 'add_lazy_loading_to_iamges');
    add_filter('post_thumbnail_html', 'add_lazy_loading_to_iamges');

    /**
     * JavaScript defered loading.
     */
    // function defer_scripts($tag, $handle, $src){
    //     //Dont defer jQuery or scripts taht depend on jQuery
    //     $defer_excludes = array('jquery', 'jquery-core', 'jquery-migrate');

    //     if(in_array($handle, $defer_excludes)){
    //         return $tag;
    //     }
        
    //     return str_replace(' src', 'defer src', $tag);
    // }
    // add_filter('script_loader_tag', 'defer_scripts', 10, 3);

    /**
     * Move the scripts to the footer
     */
    // function move_scripts_to_footer(){
    //     //Remove defauly jQuery
    //     wp_deregister_script('jquery');

    //     //Re-register jQuery to footer
    //     wp_register_script(
    //         'jquery',
    //         includes_url('/js/jquery/jquery.min.js'),
    //         array(),
    //         null,
    //         true //Load in footer
    //     );

    //     wp_enqueue_script('jquery');
    // }
    // add_action('wp_enqueue_scripts', 'move_scripts_to_footer');

    // function conditional_script_loading(){

    //     if(is_page('contact')){
    //         wp_enqueue_script(
    //             'contact-form',
    //             get_stylesheet_directory_uri() . '/js/contact-form.js',
    //             array('jquery'),
    //             '1.0.0',
    //             true
    //         );
    //     }

    //     //Load slider script only
    //     if(is_front_page()){
    //         wp_enqueue_script(
    //             'slider',
    //             get_stylesheet_directory_uri() . '/js/slider.js',
    //             array(''),
    //             '1.0.0',
    //             true
    //         );
    //     }

    //     if(is_admin()){
    //         return;
    //     }
    // }
    // add_action('wp_enqueue_scripts', 'conditional_script_loading');

    /**
    * Disable WooCommerce CSS/JS on non-shop pages
     */
    function disable_woocommerce_loading() {
        // 1. First, check if WooCommerce is active. If not, stop.
        if ( ! function_exists( 'is_woocommerce' ) ) {
            return;
        }

        // 2. If we are on a WooCommerce page, stop (we want the scripts to load here).
        if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
            return;
        }

        // 3. If the other conditions are not met, we're on a non-WooCommerce page, so dequeue the assets.
        wp_dequeue_style( 'woocommerce-general' );
        wp_dequeue_style( 'woocommerce-layout' );
        wp_dequeue_style( 'woocommerce-smallscreen' );
        wp_dequeue_style( 'woocommerce_frontend_styles' );
        wp_dequeue_style( 'woocommerce_fancybox_styles' );
        wp_dequeue_style( 'woocommerce_chosen_styles' );

        wp_dequeue_script( 'wc-cart-fragments' );
        wp_dequeue_script( 'woocommerce' );
        wp_dequeue_script( 'wc-add-to-cart' );
    }
    add_action( 'wp_enqueue_scripts', 'disable_woocommerce_loading', 99 );

    /**
     * Disable cart fragments on non-shop pages
     */
    // function disable_cart_fragments() {
    //     if ( is_front_page() || is_page() || is_single() ) {
    //         wp_dequeue_script( 'wc-cart-fragments' );
    //     }
    // }
    // add_action( 'wp_enqueue_scripts', 'disable_cart_fragments', 100 );

    /**
     * Disable cart fragment refresh on every page load
     * This can greatly improve performance but may cause stale cart counts.
     * Use with caution or a different cart implementation.
     */
    //add_filter( 'woocommerce_add_to_cart_fragments_refresh', '__return_false' );

    /**
     * Change cart session expiration time
     * This isn't for fragment refresh but for the user's session.
     */
    function custom_cart_fragment_timeout( $timeout ) {
        return DAY_IN_SECONDS; // Sets the session to 24 hours
    }
    add_filter( 'wc_session_expiration', 'custom_cart_fragment_timeout' );

    /**
     * Customize WooCommerce image sizes
     */
    function custom_woocommerce_image_dimensions() {
        // Single product image
        update_option( 'woocommerce_single_image_width', 800 );
        // Thumbnail image
        update_option( 'woocommerce_thumbnail_image_width', 300 );
        // Crop images to exact size (1:1 aspect ratio)
        update_option( 'woocommerce_thumbnail_cropping', '1:1' );
    }
    add_action( 'after_switch_theme', 'custom_woocommerce_image_dimensions' );

    /**
     * Disable zoom, lightbox, slider if not needed
     */
    function remove_woocommerce_product_features() {
        remove_theme_support( 'wc-product-gallery-zoom' );
        remove_theme_support( 'wc-product-gallery-lightbox' );
        remove_theme_support( 'wc-product-gallery-slider' );
    }
    add_action( 'after_setup_theme', 'remove_woocommerce_product_features', 100 );

    // /**
    //  * Limit related products (reduces queries)
    //  */
    function limit_related_products( $args ) {
        $args['posts_per_page'] = 3; // Instead of default 4
        return $args;
    }
    add_filter( 'woocommerce_output_related_products_args', 'limit_related_products' );

    /**
     * Disable WooCommerce reviews if not used
     */
    add_filter( 'woocommerce_product_tabs', function( $tabs ) {
        unset( $tabs['reviews'] );
        return $tabs;
    } );

    // /**
    //  * Remove unnecessary widgets
    //  */
    function remove_woocommerce_widgets() {
        unregister_widget( 'WC_Widget_Cart' );
        unregister_widget( 'WC_Widget_Layered_Nav_Filters' );
        unregister_widget( 'WC_Widget_Layered_Nav' );
        unregister_widget( 'WC_Widget_Price_Filter' );
        unregister_widget( 'WC_Widget_Product_Categories' );
        unregister_widget( 'WC_Widget_Product_Search' );
        unregister_widget( 'WC_Widget_Product_Tag_Cloud' );
        unregister_widget( 'WC_Widget_Products' );
        unregister_widget( 'WC_Widget_Rating_Filter' );
        unregister_widget( 'WC_Widget_Recent_Reviews' );
        unregister_widget( 'WC_Widget_Recently_Viewed' );
        unregister_widget( 'WC_Widget_Top_Rated_Products' );
    }
    add_action( 'widgets_init', 'remove_woocommerce_widgets', 99 );

    /**
     * Simplify checkout fields
     */
    function customize_checkout_fields( $fields ) {
        // Remove unnecessary fields
        unset( $fields['billing']['billing_company'] );
        unset( $fields['billing']['billing_address_2'] );
        unset( $fields['order']['order_comments'] );

        // Make phone optional
        $fields['billing']['billing_phone']['required'] = false;

        return $fields;
    }
    add_filter( 'woocommerce_checkout_fields', 'customize_checkout_fields' );

    /**
     * Disable password strength meter
     */
    function disable_password_strength_meter() {
        if ( wp_script_is( 'wc-password-strength-meter', 'enqueued' ) ) {
            wp_dequeue_script( 'wc-password-strength-meter' );
        }
    }
    add_action( 'wp_print_scripts', 'disable_password_strength_meter', 100 );

    /**
     * Renders a grid of portfolio items via a shortcode.
     *
     * Usage:
     * [portfolio_grid count="6" category="web-design" featured="true" orderby="date"]
     */
    function render_portfolio_shortcode($atts) {
        
        // 1. Process shortcode attributes
        $atts = shortcode_atts(array(
            'count'    => 6,       // How many projects to show
            'category' => '',      // A 'project_type' slug to filter by
            'featured' => 'false', // 'true' to show only featured projects
            'orderby'  => 'default' // 'default' (featured then date), 'date', 'title'
        ), $atts, 'portfolio_grid');

        // 2. Build the query arguments
        $args = array(
            'post_type'      => 'portfolio',
            'posts_per_page' => intval($atts['count']),
            'post_status'    => 'publish',
        );

        // 3. Add category (taxonomy) filter if provided
        if ( ! empty($atts['category']) ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'project_type',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field($atts['category']),
                ),
            );
        }

        // 4. Handle the 'featured' attribute
        if ($atts['featured'] == 'true') {
            $args['meta_key'] = '_portfolio_featured';
            $args['meta_value'] = '1';
        }

        // 5. Handle the 'orderby' attribute
        if ($atts['orderby'] == 'default' && $atts['featured'] != 'true') {
            // This is your custom sort order from PortfolioManager
            $args['meta_key'] = '_portfolio_featured';
            $args['orderby']  = array(
                'meta_value' => 'DESC', // Featured posts (value '1') first
                'date'       => 'DESC'
            );
        } else {
            // Allow simple sorting like 'date' or 'title'
            $args['orderby'] = sanitize_text_field($atts['orderby']);
        }

        // 6. Create the new WordPress query
        $portfolio_query = new WP_Query($args);

        // 7. Start the output buffer
        // This is crucial: shortcodes must "return" HTML, not "echo" it.
        ob_start();

        // 8. The Loop
        if ($portfolio_query->have_posts()) :
            
            // Use the same wrapper classes as your archive page for consistent styling
            echo '<div class="portfolio-archive-wrapper"><div class="portfolio-grid">'; 

            while ($portfolio_query->have_posts()) : $portfolio_query->the_post();
                
                // Get all the data for this post
                $client       = get_post_meta(get_the_ID(), '_portfolio_client', true);
                $terms        = get_the_terms(get_the_ID(), 'project_type');
                $is_featured  = get_post_meta(get_the_ID(), '_portfolio_featured', true);
                
                // Get term classes for filtering (just like your archive)
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
                            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'large' ); ?></a>
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
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

                        <?php if ($client) : ?>
                            <p class="portfolio-client">Client: <?php echo esc_html( $client ); ?></p>
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
                    </div>
                </article>
                
                <?php
            endwhile;

            echo '</div></div>'; // Close .portfolio-grid and .portfolio-archive-wrapper

        else :
            // Show a message if no posts are found
            echo '<p>No portfolio items found.</p>';
        endif;

        // 9. Reset the global post data
        wp_reset_postdata();

        // 10. Return the captured HTML
        return ob_get_clean();
    }
    // 11. Register the shortcode with WordPress
    add_shortcode('portfolio_grid', 'render_portfolio_shortcode');

?>