<?php
class RS_LMS_Metabox {


    function __construct() {
        add_action('cmb2_admin_init', [$this, 'register_course_metabox']);
        add_action('cmb2_admin_init', [$this, 'register_chapter_metabox']);
    }

    public function get_chapters() {
        $chapters = get_posts([
            'post_type' => 'chapter',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
        $options = [];
        if ($chapters && is_array($chapters)) {
            foreach ($chapters as $chapter) {
                $options[$chapter->ID] = $chapter->post_title;
            }
        }
        return $options;
    }
    
    public function get_products() {
        $products = get_posts([
            'post_type' => 'product',
            'numberposts' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
        $options = [];
        if ($products && is_array($products)) {
            foreach ($products as $product) {
                $options[$product->ID] = $product->post_title;
            }
        }
        return $options;
    }

    function register_course_metabox() {
        $cmb = new_cmb2_box([
            'id' => 'rs_lms_course_metabox',
            'title' => __('Course Details - CMB2', 'rs_lms'),
            'object_types' => ['course'],
            //add context and priority
            'context' => 'normal',
            'priority' => 'high',
        ]);

        $cmb->add_field([
            'name' => __('Product', 'rs_lms'),
            'id' => 'rs_lms_course_product',
            'type' => 'select',
            'options' => $this->get_products(),
            'description' => __('Select the product associated with this post', 'rs_lms'),
        ]);

        $group_field_id = $cmb->add_field([
            'id' => 'rs_lms_course_chapters',
            'type' => 'group',
            'description' => __('Add chapters to this course', 'rs_lms'),
            'options' => [
                'group_title' => __('Chapter {#}', 'rs_lms'),
                'add_button' => __('Add Chapter', 'rs_lms'),
                'remove_button' => __('Remove Chapter', 'rs_lms'),
                'sortable' => true,
            ],
        ]);

        $cmb->add_group_field($group_field_id, [
            'name' => __('Chapter', 'rs_lms'),
            'id' => 'chapter_id',
            'type' => 'select',
            'options_cb' => [$this, 'get_chapters'],
        ]);
    }

    function register_chapter_metabox(){
        // name, video type, video_source, length,  note_url, resoucre_url
        $cmb = new_cmb2_box([
            'id' => 'rs_lms_chapter_metabox',
            'title' => __('Chapter Episodes', 'rs_lms'),
            'object_types' => ['chapter'],
        ]);

        $group_field_id = $cmb->add_field([
            'id' => 'rs_lms_chapter_episodes',
            'type' => 'group',
            'description' => __('Add episodes for this chapter', 'rs_lms'),
            'options' => [
                'group_title' => __('Episode {#}', 'rs_lms'),
                'add_button' => __('Add Episode', 'rs_lms'),
                'remove_button' => __('Remove Episode', 'rs_lms'),
                'sortable' => true,
            ],
        ]);

        $cmb->add_group_field($group_field_id, [
            'name' => __('Episode Title', 'rs_lms'),
            'id' => 'title',
            'type' => 'text',
        ]);

        $cmb->add_group_field($group_field_id, [
            'name' => __('Video Type', 'rs_lms'),
            'id' => 'video_type',
            'type' => 'select',
            'options' => [
                'youtube' => __('YouTube', 'rs_lms'),
                'vimeo' => __('Vimeo', 'rs_lms'),
                'wistia' => __('Wistia', 'rs_lms'),
                'self_hosted' => __('Self-hosted', 'rs_lms'),
            ],
        ]);

        $cmb->add_group_field($group_field_id, [
            'name' => __('Video URL', 'rs_lms'),
            'id' => 'video_url',
            'type' => 'text_url',
        ]);

        $cmb->add_group_field($group_field_id, [
            'name' => __('Episode Length', 'rs_lms'),
            'id' => 'length',
            'type' => 'text',
        ]);

        $cmb->add_group_field($group_field_id, [
            'name' => __('Note Link', 'rs_lms'),
            'id' => 'note_link',
            'type' => 'text_url',
        ]);

        $cmb->add_group_field($group_field_id, [
            'name' => __('Resource Download Link', 'rs_lms'),
            'id' => 'resource_download',
            'type' => 'text_url',
        ]);

    }
}

new RS_LMS_Metabox();