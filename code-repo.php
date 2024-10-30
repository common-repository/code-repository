<?php
/*
Plugin Name: Code Repository
Plugin URI: http://jamesonaranda.com/code/code-repository/
Description: This plugin adds a custom post type for writing, revisioning, a displaying code in any language.
Version: 0.2.1
Author: Jameson Aranda
Author URI: http://jamesonaranda.com/
License: GPLv2
*/
?>
<?php
/*  Copyright 2014 Jameson Aranda  (email : mail@jamesonaranda.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
// Post type options
function create_code_repo() {
$labels = array(
		'name'               => _x( 'Scripts', 'post type general name' ),
		'singular_name'      => _x( 'Script', 'post type singular name' ),
		'add_new'            => _x( 'Add New', 'script' ),
		'add_new_item'       => __( 'Add New Script' ),
		'edit_item'          => __( 'Edit Script' ),
		'new_item'           => __( 'New Script' ),
		'all_items'          => __( 'All Scripts' ),
		'view_item'          => __( 'View Script' ),
		'search_items'       => __( 'Search Scripts' ),
		'not_found'          => __( 'No scripts found' ),
		'not_found_in_trash' => __( 'No scripts found in the Trash' ), 
		'parent_item_colon'  => '',
		'menu_name'          => 'Scripts',
	);
	$rewrite = array(
		'slug'		=> 'code',
		'with_front'	=> false,
		'feeds'		=> true,
		'pages'		=> false,
		);
	$args = array(
		'labels'        => $labels,
		'rewrite'	=> $rewrite,
		'description'   => 'Original Software',
		'taxonomies' => array('script_category', 'post_tag'),
		'public'        => true,
		'menu_position' => 10,
		'menu_icon'	=> 'dashicons-editor-code',
		'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions' ),
		'has_archive'   => true,
	);
	register_post_type( 'script', $args );	
	flush_rewrite_rules( false );
}
// Build post type
add_action( 'init', 'create_code_repo' );

// Disable visual editor for post type
add_filter( 'user_can_richedit', 'disable_for_cpt' );
function disable_for_cpt( $default ) {
	global $post;
	if ( 'script' == get_post_type( $post ) )
		return false;
	return $default;
}

// Type taxonomy options
function my_taxonomies_type() {
  $labels = array(
    'name'              => _x( 'Types', 'taxonomy general name' ),
    'singular_name'     => _x( 'Type', 'taxonomy singular name' ),
    'search_items'      => __( 'Search Type' ),
    'all_items'         => __( 'All Script Types' ),
    'parent_item'       => __( 'Parent Type' ),
    'parent_item_colon' => __( 'Parent Type:' ),
    'edit_item'         => __( 'Edit Script Type' ), 
    'update_item'       => __( 'Update Script Type' ),
    'add_new_item'      => __( 'Add New Script Type' ),
    'new_item_name'     => __( 'New Script Type' ),
    'menu_name'         => __( 'Script Types' ),
  );
  $rewrite = array(
    'slug'		=> 'code/category',
    'with_front'	=> false,
		);
  $args = array(
    'labels' => $labels,
    'rewrite'	=> $rewrite,
    'hierarchical' => true,
  );
  register_taxonomy( 'script_category', 'script', $args );
}
// Build taxonomy
add_action( 'init', 'my_taxonomies_type', 0 );


// Replace Taxonomy slug with Post Type slug in url
function taxonomy_slug_rewrite($wp_rewrite) {
    $rules = array();
    // get all custom taxonomies
    $taxonomies = get_taxonomies(array('_builtin' => false), 'objects');
    // get all custom post types
    $post_types = get_post_types(array('public' => true, '_builtin' => false), 'objects');
     
    foreach ($post_types as $post_type) {
        foreach ($taxonomies as $taxonomy) {
         
            // go through all post types which this taxonomy is assigned to
            foreach ($taxonomy->object_type as $object_type) {
                 
                // check if taxonomy is registered for this custom type
                if ($object_type == $post_type->rewrite['slug']) {
             
                    // get category objects
                    $terms = get_categories(array('type' => $object_type, 'taxonomy' => $taxonomy->name, 'hide_empty' => 0));
             
                    // make rules
                    foreach ($terms as $term) {
                        $rules[$object_type . '/' . $term->slug . '/?$'] = 'index.php?' . $term->taxonomy . '=' . $term->slug;
                    }
                }
            }
        }
    }
    // merge with global rules
    $wp_rewrite->rules = $rules + $wp_rewrite->rules;
}
add_filter('generate_rewrite_rules', 'taxonomy_slug_rewrite');


// Include custom post type in tag archive
function add_custom_types_to_tax( $query ) {
 if( is_category() || is_tag() && empty( $query->query_vars['suppress_filters'] ) ) {
  $post_types = get_post_types();
  $query->set( 'post_type', $post_types );
  return $query;
 }
}
add_filter( 'pre_get_posts', 'add_custom_types_to_tax' );

//Change excerpt metabox title
function custom_post_type_boxes(){
    remove_meta_box( 'postexcerpt', 'script', 'normal' );
    add_meta_box( 'postexcerpt', __( 'Description' ), 'post_excerpt_meta_box', 'script', 'normal', 'high' );
}
add_action('do_meta_boxes', 'custom_post_type_boxes');

//Set post type template
function get_custom_post_type_template($single_template) {
     global $post;

     if ($post->post_type == 'script') {
     	if (strpos( $_SERVER['HTTP_USER_AGENT'], 'curl') !== false) {
          $single_template = dirname( __FILE__ ) . '/bare-script.php';
        }
     }
     return $single_template;
}
add_filter( 'single_template', 'get_custom_post_type_template' );
