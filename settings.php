<?php
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/statistics/lib.php');

global $PAGE, $COURSE;

if ($hassiteconfig) {


    $settings = new admin_settingpage('local_statistics', new lang_string('pluginname', 'local_statistics'));
    $ADMIN->add('localplugins', $settings);

    $settings->add( new admin_setting_configtext('local_statistics/course_per_cron', get_string('course_per_cron', 'local_statistics'),
        get_string('course_per_cron_desc', 'local_statistics'), 10, PARAM_INT));

    $settings->add( new admin_setting_configtext('local_statistics/time_to_update', get_string('time_to_update', 'local_statistics'),
        get_string('time_to_update_desc', 'local_statistics'), 720, PARAM_INT));

    $settings->add( new admin_setting_configtext('local_statistics/last_course_id', get_string('last_course_id', 'local_statistics'),
        get_string('last_course_id_desc', 'local_statistics'), 1, PARAM_INT));

    $settings->add( new admin_setting_configtext('local_statistics/interval_start', get_string('interval_start', 'local_statistics'),
        get_string('interval_start_desc', 'local_statistics'), '17-8-1', PARAM_TEXT));

    $settings->add( new admin_setting_configtext('local_statistics/interval_end', get_string('interval_end', 'local_statistics'),
        get_string('interval_end_desc', 'local_statistics'), '17-12-20', PARAM_TEXT));

    $settings->add( new admin_setting_configtext('local_statistics/export_fields', get_string('export_fields', 'local_statistics'),
        get_string('export_fields_desc', 'local_statistics'), implode(",", EXPORT_FIELDS), PARAM_TEXT));

    $settings->add( new admin_setting_configtext('local_statistics/elasticsearch_url', get_string('elasticsearch_url', 'local_statistics'),
        get_string('elasticsearch_url_desc', 'local_statistics'),''));

    $settings->add( new admin_setting_configtext('local_statistics/elasticsearch_moodle_id', get_string('elasticsearch_moodle_id', 'local_statistics'),
        get_string('elasticsearch_moodle_id_desc', 'local_statistics'),''));

    $settings->add( new admin_setting_configcheckbox('local_statistics/ktu_functionality', get_string('ktu_functionality', 'local_statistics'),
        get_string('ktu_functionality_desc', 'local_statistics'), false));


}