<?php
   /*
   Plugin Name: Add Testimonials to Any Page 
   Plugin URI: http://www.github.com/codeb;oo
   description: Add customer testimnials to any page with a shortcode or snippet
   Version: 1.0
   Author: Verona Blue
   Author URI: http://www.codebloo.com
   License: GPL2s 
   */

// Enqeue Styles
function testimonial_plugin_styles(){
   wp_enqueue_style('testimonialsCSS', plugins_url( 'testimonials.css' , __FILE__ ), false, '1.0', 'all' );
}
add_action("wp_enqueue_scripts","testimonial_plugin_styles");

//Add Custom Post Type
add_action( 'init', 'create_testimonials_post_type' );
function create_testimonials_post_type() {

    $labels = array( 
        'name' => _x( 'Testimonials', 'testimonial' ),
        'singular_name' => _x( 'Testimonial', 'testimonial' ),
        'add_new' => _x( 'Add New', 'testimonial' ),
        'add_new_item' => _x( 'Add New Testimonial', 'testimonial' ),
        'edit_item' => _x( 'Edit Testimonial', 'testimonial' ),
        'new_item' => _x( 'New Testimonial', 'testimonial' ),
        'view_item' => _x( 'View Testimonial', 'testimonial' ),
        'search_items' => _x( 'Search Testimonials', 'testimonial' ),
        'not_found' => _x( 'No Testimonials found', 'testimonial' ),
        'not_found_in_trash' => _x( 'No Testimonials found in Trash', 'testimonial' ),
        'parent_item_colon' => _x( 'Parent Testimonial:', 'testimonial' ),
        'menu_name' => _x( 'Testimonials', 'testimonial' ),
    );

    $args = array( 
        'labels' => $labels,
        'hierarchical' => false,
        'supports' => array( 'title', 'editor','thumbnail', 'revisions'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-format-quote',
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type( 'testimonial', $args );
}

//Add thumbnail Size
add_image_size( 'testimonial_square', 600, 600, true ); 


// Add Short Code
function testimonial_shortcode( $atts ) {
    extract( shortcode_atts( array(
        'posts_per_page' => '1',
        'orderby' => '',
        'testimonial_id' => '',
    ), $atts ) );
 
    return get_testimonial( $posts_per_page, $orderby, $testimonial_id );
}

// Display Testimonials
function get_testimonial( $posts_per_page = '1', $orderby = 'none', $testimonial_id = null ) {
    $args = array(
        'post_type' => 'testimonial',
		'posts_per_page' => (int) $posts_per_page,	
        'orderby' => $orderby,
        'no_found_rows' => true,
    );
	
    if ( $testimonial_id )
        $args['post__in'] = array( $testimonial_id );
 
    $query = new WP_Query( $args  );
 
    $testimonials = '';
    if ( $query->have_posts() ) {
       $testimonials .= '<div class="all_testimonials"><ul class="testimonials_list">';
	    while ( $query->have_posts() ) : $query->the_post();
            $post_id = get_the_ID();
           
			$url_thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post_id), 'testimonial_square');
			$testimonials .= '<li class="single_testimonial">';
			$testimonials .= '<div class="testimonial_pic"><img src="'.$url_thumb[0].'" /></div>';
            $testimonials .= '<div class="testimonial_content">';
            $testimonials .= '<p>' . get_the_content() . '</p>';
            $testimonials .= '<span class="testimonial_author">' . get_the_title() . '</span>';
            $testimonials .= '</div>';
            $testimonials .= '</li>';
 
        endwhile;
		$testimonials .= '</ul></div>';
        wp_reset_postdata();
    }
 
    return $testimonials;
}
//Finalize Shortcode
add_shortcode( 'showtestimonials', 'testimonial_shortcode' );

// Display Instructions Page
add_action( 'admin_menu', 'testimonial_plugin_menu' );


function testimonial_plugin_menu() {
	add_submenu_page( 'edit.php?post_type=testimonial','Testimonials Instructions', 'Instructions', 'manage_options', 'testimonials-instruction-page', 'testimonial_plugin_options' );
}
function testimonial_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<h2>How to Use Add Testimonials to Any Page</h2>';
	echo '<p>This plugin can be added to any page with the shortcode <code>[showtestimonials]</code></p>';
	echo '<p>Options for thie shortcode are a) Show 1 specific Testimonial by the ID,  b) Set a maximum of testimonials to show, c) Order & Randomize the testimonials being shown (usually used with the max feature )</p>';
	echo '<p>Specific by ID: <code>[showtestimonials testimonial_id="35"]</code> or  <code>[showtestimonials testimonial_id="35,99"]</code> to show multiple, specific testimonials. This will OVERRIDE the MAX number below. (If you set a max of 2 but only specific 1 ID it will only show 1)</p>';
	echo '<p>Max number shown: <code>[showtestimonials posts_per_page="5"]</code></p>';
	echo '<p>Order or randomize testimonials: <code>[showtestimonials orderby="rand"]</code> to randomize, <code>[showtestimonials orderby="asc"]</code> ascending,  <code>[showtestimonials orderby="desc"]</code> descending</p>';
	echo '</div>';
};	

?>