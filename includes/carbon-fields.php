<?php
use Carbon_Fields\Container;
use Carbon_Fields\Field;
class RS_LMS_CarbonMetabox {
    function __construct() {
        add_action('carbon_fields_register_fields', [$this, 'rs_lms_course_chapters']);
    }

    function rs_lms_course_chapters() {
        Container::make('post_meta', __('Chapters Carbon', 'rs-lms'))
            ->where('post_type', '=', 'course') // only show our new fields on pages
            ->add_fields(array(
                Field::make('complex', 'crb_chapters', 'Chapters')
                    ->set_layout('tabbed-vertical')
                    ->add_fields(array(
                        Field::make('select', 'crb_chapter', 'Chapter')
                            ->add_options([$this, 'get_chapters'])
                    )),
            ));
    }

    function get_chapters() {
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
}
new RS_LMS_CarbonMetabox();