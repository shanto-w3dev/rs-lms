<?php

if(!defined('ABSPATH')){
    exit; // Exit if accessed directly.
}

/**
 * Class to handle Enqueueing Assets
 */ 
class RS_LMS_Enqueue_Assets {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_resources' ], 999 );
        if(! is_admin() ) {
            add_filter('show_admin_bar', '__return_false');
        }
    }

    public function enqueue_resources() {
        if(is_singular('course')){
            //dequeue Hello Elementor theme styles
            add_filter( 'hello_elementor_enqueue_styles', '__return_false' );
            add_filter('hello_elementor_enqueue_theme_styles', '__return_false' );
            add_filter('hello_elementor_header_footer', '__return_false' );

            // Enqueue Tailwind CSS from CDN
            wp_enqueue_script(
                'rs-lms-tailwind', 
                '//cdn.tailwindcss.com?plugins=typography',
                [], 
                null, 
                false  // Load in header
            );

            //Enqueue google fonts
            wp_enqueue_style(
                'rs-lms-google-fonts',
                'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
                [],
                null,
            );

            //Enqueue font awesome
            wp_enqueue_style(   
                'rs-lms-font-awesome', 
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', 
                [], 
                null, 
            );

            // Enqueue Highlight.js CSS
            wp_enqueue_style(
                'rs-lms-highlightjs-css', 
                'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css', 
                [], 
                null, 
            );

            // Enqueue Highlight.js JS
            wp_enqueue_script(
                'rs-lms-highlightjs', 
                'https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js', 
                [], 
                null, 
                false  // Load in header
            );

            // Enqueue custom CSS
            wp_enqueue_style(   
                'rs-lms-custom-css', 
                RS_LMS_PLUGIN_URL . 'assets/css/style.css', 
                [], 
                time(), 
            );

            // Marked.js for Markdown parsing
            wp_enqueue_script(
                'rs-lms-marked',
                'https://cdn.jsdelivr.net/npm/marked/marked.min.js',
                [],
                '4.3.0',
                true
            );

            // Enqueue custom JS
            wp_enqueue_script( 
                'rs-lms-custom-js', 
                RS_LMS_PLUGIN_URL . 'assets/js/script.js', 
                ['rs-lms-marked', 'rs-lms-highlightjs'], 
                time(), 
                true  // Load in footer
            );

            $nonce = wp_create_nonce('wp_rest');
            wp_localize_script(
                'rs-lms-custom-js',
                'rsLmsRest',
                [
                    'nonce'   => $nonce,
                    'apiUrl' => esc_url( rest_url('rs-lms/v1/') ),
                    // 'siteUrl' => esc_url( site_url('/') ),
                    // 'ajaxUrl' => esc_url( admin_url('admin-ajax.php') ),
                    'courseId' => get_the_ID(),
                    'storageKeyPrefix' => 'rsLms:lastWatched',
                    'userEmail' => is_user_logged_in() ? base64_encode(wp_get_current_user()->user_email) : '',
                ]
            );
        }
    }
}
new RS_LMS_Enqueue_Assets();