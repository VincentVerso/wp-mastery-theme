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

?>