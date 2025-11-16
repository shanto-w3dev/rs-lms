<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
/**
 * Class to handle Custom Post Types
 */
class RS_LMS_CPT {

    public function __construct() {
        add_action( 'init', [$this, 'register_course_cpt'] );
        add_action( 'init', [$this, 'register_chapter_cpt'] );
        add_filter('single_template', [$this, 'load_single_course_template']);
    }

    /**
     * Register Course Custom Post Type
     */
    public function register_course_cpt() {
        $labels = [
            'name'               => __( 'Courses', 'rs-lms' ),
            'singular_name'      => __( 'Course', 'rs-lms' ),
            'menu_name'          => __( 'Courses', 'rs-lms' ),
            'name_admin_bar'     => __( 'Course', 'rs-lms' ),
            'add_new'            => __( 'Add New', 'rs-lms' ),
            'add_new_item'       => __( 'Add New Course', 'rs-lms' ),
            'new_item'           => __( 'New Course', 'rs-lms' ),
            'edit_item'          => __( 'Edit Course', 'rs-lms' ),
            'view_item'          => __( 'View Course', 'rs-lms' ),
            'all_items'          => __( 'All Courses', 'rs-lms' ),
            'search_items'       => __( 'Search Courses', 'rs-lms' ),
            'parent_item_colon'  => __( 'Parent Courses:', 'rs-lms' ),
            'not_found'          => __( 'No courses found.', 'rs-lms' ),
            'not_found_in_trash' => __( 'No courses found in Trash.', 'rs-lms' )
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'rewrite'            => ['slug' => 'course'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-welcome-learn-more',
            'supports'           => ['title', 'thumbnail', 'excerpt'],
        ];

        register_post_type( 'course', $args );
    }

    /**
     * Load custom single course template
     */ 
    public function load_single_course_template($single) {
        global $post;

        if ($post->post_type == 'course') {
            if (file_exists(RS_LMS_PLUGIN_DIR . 'includes/single-course.php')) {
                return RS_LMS_PLUGIN_DIR . 'includes/single-course.php';
            }
        }
        return $single;
    }
    /**
     * Register Chapter Custom Post Type
     */
    public function register_chapter_cpt() {
        $labels = [
            'name'               => __( 'Chapters', 'rs-lms' ),
            'singular_name'      => __( 'Chapter', 'rs-lms' ),
            'menu_name'          => __( 'Chapters', 'rs-lms' ),
            'name_admin_bar'     => __( 'Chapter', 'rs-lms' ),
            'add_new'            => __( 'Add New', 'rs-lms' ),
            'add_new_item'       => __( 'Add New Chapter', 'rs-lms' ),
            'new_item'           => __( 'New Chapter', 'rs-lms' ),
            'edit_item'          => __( 'Edit Chapter', 'rs-lms' ),
            'view_item'          => __( 'View Chapter', 'rs-lms' ),
            'all_items'          => __( 'All Chapters', 'rs-lms' ),
            'search_items'       => __( 'Search Chapters', 'rs-lms' ),
            'parent_item_colon'  => __( 'Parent Chapters:', 'rs-lms' ),
            'not_found'          => __( 'No chapters found.', 'rs-lms' ),
            'not_found_in_trash' => __( 'No chapters found in Trash.', 'rs-lms' )
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'rewrite'            => ['slug' => 'chapter'],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'menu_position'      => 6,
            'menu_icon'          => 'dashicons-book',
            'supports'           => ['title', 'thumbnail', 'excerpt' ],
        ];

        register_post_type( 'chapter', $args );
    }
}
new RS_LMS_CPT();
