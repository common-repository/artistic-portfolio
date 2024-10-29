<?php
/**
*Plugin Name: Artistic Portfolio
*Plugin URI: http://wordpress.org/plugins/artistic-portfolio/
*Description: Create you own portfolio with multiple images!
*Version: 1.0
*Author: Shalaeva
*Author URI: http://withzest.ru
*/
/**
*Adding file with translations
*
*@return bool TRUE as textdomain well loaded or FALSE on failure
*/
function artistic_portfolio_plugin_init(){
	load_plugin_textdomain( 'artistic-portfolio', false, dirname( plugin_basename( __FILE__ ) ). '/languages' ); 
}
add_action('plugins_loaded', 'artistic_portfolio_plugin_init');

/**
*Registration of new post type for portfolio. You can add several images on the right side of admin page (by holding Shift key) for this type - they will be displayed as slideshow.
*
*@return WP_Post_Type|WP_Error The registered post type object or an error object
*/
function artistic_portfolio_register_post_types() {
	register_post_type( 'portfolio_item', array(
		'labels' => array(
			'name'               => __( 'Portfolio items', 'artistic-portfolio' ), 
			'singular_name'      => __( 'Portfolio item', 'artistic-portfolio' ), 
			'add_new'            => __( 'Add item', 'artistic-portfolio' ),
			'add_new_item'       => __( 'Add item', 'artistic-portfolio' ),
			'edit_item'          => __( 'Edit item', 'artistic-portfolio' ),
			'new_item'           => __( 'New item', 'artistic-portfolio' ), 
			'view_item'          => __( 'View item', 'artistic-portfolio' ), 
			'search_items'       => __( 'Search item', 'artistic-portfolio' ), 
			'not_found'          => __( 'Nothing found', 'artistic-portfolio' ), 
			'not_found_in_trash' => __( 'Nothing found in trash', 'artistic-portfolio' ), 
			'menu_name'          => __( 'Portfolio items', 'artistic-portfolio' )
		),
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => false,
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => null,
        'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'post-formats' ),
        'taxonomies'          => array( 'portfolio_category', 'portfolio_tag' )
		) );
}
add_action( 'init', 'artistic_portfolio_register_post_types' );

/**
*Creation category and tag taxonomies for portfolio items
*@return nothing|WP_Error Returns nothing or WP_Error
*/
function artistic_portfolio_create_taxonomies() {
    $labels = array(
		'name'                        => __( 'Portfolio categories', 'artistic-portfolio' ),
		'singular_name'               => __( 'Portfolio category', 'artistic-portfolio' ),
		'search_items'                =>  __( 'Search portfolio category', 'artistic-portfolio' ),
		'popular_items'               => null,
		'all_items'                   => __( 'All portfolio categories', 'artistic-portfolio' ),
		'parent_item'                 => __( 'Parent portfolio category', 'artistic-portfolio' ),
		'parent_item_colon'           => __( 'Parent portfolio category: ', 'artistic-portfolio' ),
		'edit_item'                   => __( 'Edit portfolio category', 'artistic-portfolio' ),
		'update_item'                 => __( 'Update portfolio category', 'artistic-portfolio' ),
		'add_new_item'                => __( 'Add new portfolio category', 'artistic-portfolio' ),
		'new_item_name'               => __( 'New portfolio category', 'artistic-portfolio' ),
		'separate_items_with_commas'  => null,
		'add_or_remove_items'         => __( 'Add/Remove portfolio categories', 'artistic-portfolio' ),
		'choose_from_most_used'       => null,
		'menu_name'                   => __( 'Portfolio categories', 'artistic-portfolio' ),
	);
    
	register_taxonomy('portfolio_category', 'portfolio_item', array(
		'hierarchical' => true,
		'labels'       => $labels,
		'show_ui'      => true,
		'query_var'    => true,
		'rewrite'      => array( 'slug' => 'portfolio_category' ),
	));
    
    $labels = array(
		'name'                        => __( 'Portfolio tags', 'artistic-portfolio' ),
		'singular_name'               => __( 'Portfolio tag', 'artistic-portfolio' ),
		'search_items'                =>  __( 'Search portfolio tags', 'artistic-portfolio' ),
		'popular_items'               => __( 'Popular portfolio tags', 'artistic-portfolio' ),
		'all_items'                   => __( 'All portfolio tags', 'artistic-portfolio' ),
		'parent_item'                 => null,
		'parent_item_colon'           => null,
		'edit_item'                   => __( 'Edit portfolio tag', 'artistic-portfolio' ),
		'update_item'                 => __( 'Update portfolio tag', 'artistic-portfolio' ),
		'add_new_item'                => __( 'Add portfolio tag', 'artistic-portfolio' ),
		'new_item_name'               => __( 'New portfolio tag', 'artistic-portfolio' ),
		'separate_items_with_commas'  => __( 'Please separate tags by comma', 'artistic-portfolio' ),
		'add_or_remove_items'         => __( 'Add/Remove portfolio tags', 'artistic-portfolio' ),
		'choose_from_most_used'       => __( 'Choose from the most popular portfolio tags', 'artistic-portfolio' ),
		'menu_name'                   => __( 'Portfolio tags', 'artistic-portfolio' ),
	);
    
	register_taxonomy('portfolio_tag', 'portfolio_item', array(
		'hierarchical' => false,
		'labels'       => $labels,
		'show_ui'      => true,
		'query_var'    => true,
		'rewrite'      => array( 'slug' => 'portfolio_tag' ),
	));
}
add_action( 'init', 'artistic_portfolio_create_taxonomies', 0 );

/**
*Adding portfolio page if not exists and new portfolio item was created.
*
*@return void
*/
function artistic_portfolio_create_portfolio() {
    $count_of_portfolio_items = wp_count_posts( 'portfolio_item' );
    if ( $count_of_portfolio_items > 0 ) {
        $current_theme = wp_get_theme();
        $theme_path = get_theme_root() . "/" . $current_theme->stylesheet;
        $template_file = $theme_path . "/portfolio.php";
        if ( !file_exists( $template_file ) ) {
            $content = "<?php
            /**
            * Template name: Portfolio
            */
            ?>
            <?php get_header(); ?>
            <main>
                <div class=\"container-fluid\">
                    <div class=\"row articles\">
                        <?php 
                            \$k = 0;
                            \$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
                            \$query = query_posts( array( 
                                'post_type'      => 'portfolio_item',
                                'posts_per_page' => get_option( 'posts_per_page' ),
                                'paged'          => \$paged
                            ) );
                            while ( have_posts() ) {
                                the_post(); 
                                if ( \$k > 0 && \$k % 2 == 0 ) { ?>
                    </div>
                    <div class=\"row articles\">
                               <?php }
                                get_template_part( 'content', 'portfolio-list' ); 
                                \$k++;
                            } 
                            wp_reset_postdata();
                        ?>
                    </div>
                    <nav class=\"page-navigation\">
                        <?php posts_nav_link(); ?>
                    </nav>
                    <div class=\"row\">
                        <div class=\"hidden-xs hidden-sm col-md-1 col-lg-1\"></div>
                        <div class=\"col-xs-12 col-sm-12 col-md-10 col-lg-10\">
                            <?php 
                                if ( is_active_sidebar( 'sidebar-bottom' ) ) {
                                    get_sidebar( 'bottom' ); 
                                }
                            ?>
                        </div>
                        <div class=\"hidden-xs hidden-sm col-md-1 col-lg-1\"></div>
                    </div>
                </div> 
            </main>
            <?php get_footer(); 
            ?>";
            $filehandle = fopen( $template_file, "wb" ) or die( 'Cannot open file:  ' . $template_file );
            fwrite( $filehandle, $content );
            fclose( $filehandle );
        }        
        $new_page_title = __( 'Portfolio', 'artistic-portfolio' );
        $new_page = array(
            'post_type'     => 'page',
            'post_name'     => 'portfolio',
            'post_title'    => $new_page_title,
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_author'   => 1
        );
        $page_check = get_page_by_title( $new_page_title );
        if ( !isset ( $page_check->ID ) ) {
            $new_page_id = wp_insert_post( $new_page );
            echo $new_page_id;
            update_post_meta( $new_page_id, '_wp_page_template', 'portfolio.php' );
        }
    }
}
add_action( 'save_post_portfolio_item', 'artistic_portfolio_create_portfolio' );

/**
*Define the custom attachment for portfolio item.
*@return void
*/
function artistic_portfolio_add_custom_meta_boxes() { 
    add_meta_box(
        'ap_custom_attachment',
        __( 'Upload portfolio images', 'artistic-portfolio' ),
        'artistic_portfolio_custom_attachment',
        'portfolio_item',
        'side'
    );
}
add_action( 'add_meta_boxes', 'artistic_portfolio_add_custom_meta_boxes' );

/**
*Custom attachment settings are presented in portfolio item creation/editing admin page on right side.
*/
function artistic_portfolio_custom_attachment() { 
    wp_nonce_field( plugin_basename( __FILE__ ), 'ap_custom_attachment_nonce' );   
    $count = count( get_post_meta( get_the_ID(), 'ap_custom_attachment' ) );
    $html = $count > 0 ? '<p>' . __( 'You have uploaded the following number of files: ', 'artistic-portfolio' ) . $count . '</p><p class="description">' : '<p class="description">';
    $html .= __( 'Upload your images here.', 'artistic-portfolio' );
    $html .= '</p>';
    $html .= '<input type="file" id="ap_custom_attachment" name="ap_custom_attachment[]" value="" size="25" multiple="multiple" />';
    echo $html;
}

/**
*Saving custom images for portfolio items.
*@param integer $id The post ID
*@return integer|boolean|nothing Post ID in cases when file upload is insecure or user has no permitts to edit post, True in case of successful file upload and nothing in case of error or incorrect file format. 
*/
function artistic_portfolio_save_custom_meta_data( $id ) { 
    if ( !wp_verify_nonce( $_POST['ap_custom_attachment_nonce'], plugin_basename( __FILE__ ) ) ) {
        return $id;
    }       
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $id;
    }        
    if ( $_POST['post_type'] == 'portfolio_item') {
        if ( !current_user_can( 'edit_page', $id ) ) {
            return $id;
        }
    } 
    elseif ( !current_user_can( 'edit_page', $id ) ) {
            return $id;
    }
    
    if ( !empty( $_FILES['ap_custom_attachment']['name'] ) ) {
        $supported_types = array( 'image/jpg', 'image/jpeg', 'image/png', 'image/gif' );
        
        $total = count ( $_FILES['ap_custom_attachment']['name'] );
        for ( $i = 0; $i < $total; $i++ ) {
            $arr_file_type = wp_check_filetype( basename( $_FILES['ap_custom_attachment']['name'][ $i ] ) );
            $uploaded_type = $arr_file_type['type'];
            if ( in_array( $uploaded_type, $supported_types ) ) {   
                $upload = wp_upload_bits( $_FILES['ap_custom_attachment']['name'][ $i ], null, file_get_contents( $_FILES['ap_custom_attachment']['tmp_name'][ $i ] ) );
                if ( isset( $upload['error'] ) && $upload['error'] != 0 ) {
                    wp_die( __('There was an error uploading your file. The error is: ', 'artistic-portfolio' ). $upload['error'] );
                } 
                else {
                    add_post_meta( $id, 'ap_custom_attachment', $upload, false );
                    update_post_meta( $id, 'ap_custom_attachment', $upload, 'ap_custom_attachment' );
                }
            }       
            else {
                if ( !empty( $uploaded_type ) ) {
                    wp_die( __( "The file type that you've uploaded is not supported. Please upload jpg, jpeg, png or gif.", 'artistic-portfolio' ) );
                }
            } 
        }
    }
}
add_action('save_post', 'artistic_portfolio_save_custom_meta_data');
/**
*Action to support file upload. 
*/
function artistic_portfolio_update_edit_form() {
    echo ' enctype="multipart/form-data"';
} 
add_action('post_edit_form_tag', 'artistic_portfolio_update_edit_form');
?>