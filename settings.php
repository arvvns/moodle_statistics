<?php
defined('MOODLE_INTERNAL') || die;
global $PAGE, $COURSE;

if ($hassiteconfig) {


    $settings = new admin_settingpage('local_statistics', new lang_string('pluginname', 'local_statistics'));
    $ADMIN->add('localplugins', $settings);

    $settings->add( new admin_setting_configtext('local_statistics/course_per_cron', get_string('course_per_cron', 'local_statistics'),
        get_string('course_per_cron_desc', 'local_statistics'), 10, PARAM_INT));

    $settings->add( new admin_setting_configtext('local_statistics/last_course_id', get_string('last_course_id', 'local_statistics'),
        get_string('last_course_id_desc', 'local_statistics'), 1, PARAM_INT));




}