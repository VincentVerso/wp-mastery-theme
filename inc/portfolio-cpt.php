  <?php
    /**
     * Portfolio Custom Post Type
     */

    class PortfolioManager{

        //Constructor is called upon instantiation
        public function __construct() {
            //Register the post type and taxonomy
            //The init hook is called before any html is sent to the browser
            //WordPress Core, plugins and theme files are loaded first
            add_action('init', [$this, 'register_portfolio_cpt']);
            add_action('init', [$this, 'register_portfolio_taxonomy']);

            //Modify columns in the admin bar
            add_filter('manage_portfolio_posts_columns', [$this, 'custom_columns']);
            add_action('manage_portfolio_posts_custom_column', [$this, 'custom_column_content'], 10, 2);

            //Add meta boxes
            add_action('add_meta_boxes', [$this, 'add_portfolio_meta_boxes']);
            add_action('save_post_portfolio', [$this, 'save_portfolio_meta_box']);

            //Modify main query
            add_action('pre_get_posts', [$this, 'modify_portfoliol_query']);
        }
        
        //Register the post type
        public function register_portfolio_cpt(){
            $labels = array(
                'name'                  => 'Portfolio',
                'singular_name'         => 'Portfolio Item',
                'menu_name'             => 'Portfolio',
                'add_new'               => 'Add New',
                'add_new_item'          => 'Add New Portfolio Item',
                'edit_item'             => 'Edit Portfolio Item',
                'new_item'              => 'New Portfolio Item',
                'view_item'             => 'View Portfolio Item',
                'search_items'          => 'Search Portfolio',
                'not_found'             => 'No portfolio items found',
                'not_found_in_trash'    => 'No portfolio items found in trash',
            );

            $args = array(
                'labels'                => $labels,
                'public'                => true,
                'publicly_queryable'    => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'query_var'             => true,
                'rewrite'               => array( 'slug' => 'portfolio' ),
                'capability_type'       => 'post',
                'has_archive'           => true,
                'hierarchical'          => false,
                'menu_position'         => 5,
                'menu_icon'             => 'dashicons-portfolio',
                'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
                'show_in_rest'          => true, // Enable Gutenberg editor
            );

            register_post_type('portfolio', $args);
        }

        /**
         * Registers a custom taxonomy 'Project Type' for the 'Portfolio' post type.
         *
         * This function sets up a new way to categorize portfolio items, similar to how
         * posts have categories.
        */
        public function register_portfolio_taxonomy(){
            $labels = array(
                'name'              => 'Project Types',
                'singular_name'     => 'Project Type',
                'search_items'      => 'Search Project Types',
                'all_items'         => 'All Project Types',
                'parent_item'       => 'Parent Project Type',
                'parent_item_colon' => 'Parent Project Type:',
                'edit_item'         => 'Edit Project Type',
                'update_item'       => 'Update Project Type',
                'add_new_item'      => 'Add New Project Type',
                'new_item_name'     => 'New Project Type Name',
                'menu_name'         => 'Project Types',
            );

            $args = array(
                'hierarchical'      => true, // Like categories
                'labels'            => $labels,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array( 'slug' => 'project-type' ),
                'show_in_rest'      => true,
            );

            register_taxonomy('project_type', array('portfolio'), $args);
        }


        public function custom_columns($columns){
            unset($columns['date']); //Remove date column
            
            //Add the custom columns
            $columns['project_type'] = 'Project Type';
            $columns['client'] = 'Client Name';
            $columns['featured_image'] = 'Image';
            $columns['date'] = 'Date';

            return $columns;
        }

        public function custom_column_content($column, $post_id){
            switch($column) {

                case 'project_type':
                    $terms = get_the_terms($post_id, 'project_type');
                    //If the terms are not null and there is no error associated with it...
                    if ($terms && !is_wp_error($terms)) {
                        $output = array();
                        foreach ($terms as $term) {
                            $output[] = $term->name;
                        }
                        echo implode(', ', $output);
                    }else{
                       echo '--'; 
                    }
                    break;

                case 'client':
                    $client = get_post_meta($post_id, '_portfolio_client', true);
                    echo $client ? esc_html($client) : '--';
                    break;

                case 'featured_image':
                    
                    if(has_post_thumbnail($post_id)) {
                        //Gets the posts thumbnail and resizes it to 50x50 pixels
                        echo get_the_post_thumbnail($post_id, array(50, 50));
                    }else{
                        echo '--';
                    }
                    break;
            }
        }

        //Adds meta boxes for additional fields
        public function add_portfolio_meta_boxes(){
            add_meta_box(
                'portfolio_details',
                'Portfolio Details',
                [$this, 'render_portfolio_meta_box'],
                'portfolio',
                'normal',
                'high'
            );
        }

        public function render_portfolio_meta_box($post){
            wp_nonce_field('portfolio_meta_box', 'portfolio_meta_box_nonce');

            $client = get_post_meta($post->ID, '_portfolio_client', true);
            $url = get_post_meta($post->ID, '_portfolio_url', true);
            $year = get_post_meta($post->ID, '_portfolio_year', true);
            $featured = get_post_meta($post->ID, '_portfolio_featured', true);
            ?>
                <table class="form-table">
                    <tr>
                        <th><label for="portfolio_client">Client Name</label></th>
                        <td>
                            <input type="text"
                                id="portfolio_client"
                                name="portfolio_client"
                                value="<?php echo esc_attr($client); ?>"
                                class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="portfolio_url">Project URL</label></th>
                        <td>
                            <input type="url"
                                id="portfolio_url"
                                name="portfolio_url"
                                value="<?php echo esc_url($url); ?>"
                                class="regular-text"
                                placeholder="https://example.com">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="portfolio_year">Year Completed</label></th>
                        <td>
                            <input type="number"
                                id="portfolio_year"
                                name="portfolio_year"
                                value="<?php echo esc_attr($year); ?>"
                                min="2000"
                                max="<?php echo date('Y'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="portfolio_featured">Featured Project</label></th>
                        <td>
                            <input type="checkbox"
                                id="portfolio_featured"
                                name="portfolio_featured"
                                value="1"
                                <?php checked($featured, '1'); ?>>
                            <label for="portfolio_featured">Mark as featured</label>
                        </td>
                    </tr>
                </table>
            <?php
        }

        //Save the meta box data
        public function save_portfolio_meta_box($post_id){

            //Security Checks
            if (!isset($_POST['portfolio_meta_box_nonce'])) {
                return;
            }

            if (!wp_verify_nonce($_POST['portfolio_meta_box_nonce'], 'portfolio_meta_box')) {
                return;
            }

            if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if(!current_user_can('edit_post', $post_id)) {
                return;
            }

            //Save the data
            if(isset($_POST['portfolio_client'])) {
                update_post_meta($post_id, '_portfolio_client',
                     sanitize_text_field($_POST['portfolio_client'])
                );
            }

            if(isset($_POST['portfolio_url'])) {
                update_post_meta($post_id, '_portfolio_url',
                     esc_url_raw($_POST['portfolio_url'])
                );
            }

            if(isset($_POST['portfolio_year'])) {
                update_post_meta($post_id, '_portfolio_year',
                     sanitize_text_field($_POST['portfolio_year'])
                );
            }

            //Checkbox handling
            $featured = isset($_POST['portfolio_featured']) ? '1' : '0';
            update_post_meta($post_id, '_portfolio_featured', $featured);

        }

        //Modify portfolio archive query
        public function modify_portfoliol_query($query){

            if(!is_admin() && $query->is_main_query() && $query->is_post_type_archive('portfolio')) {
                //Show 12 items
                $query->set('posts_per_page', 12);
                
                //Order by featured fist, then date
                $query->set('meta_key', '_portfolio_featured');
                $query->set('orderby', array(
                    'meta_value' => 'DESC',
                    'date' => 'DESC'
                ));
            }
        }
    }
?>