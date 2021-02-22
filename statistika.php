<?php

require_once('../../config.php');
require_once('lib.php');
require_once('../../lib/moodlelib.php');

require_login();
if (!is_siteadmin()) print_error('no_permission');

if (isset($_GET["task"]) && $_GET["task"] == "generate")
    local_statistic_get();
else if(isset($_GET["task"]) && $_GET["task"] == "download"){

    global $CFG;
    require_once($CFG->dirroot.'/lib/excellib.class.php');

    core_php_time_limit::raise(2*60);
    raise_memory_limit(MEMORY_EXTRA);

    $downloadfilename = "Statistics " . date('Y-m-d H:i:s', time());
    $worksheetTitle = "Statistics";

    // Creating a workbook
    $workbook = new MoodleExcelWorkbook("-");
    // Sending HTTP headers
    $workbook->send($downloadfilename);
    // Adding the worksheet
    $myxls = $workbook->add_worksheet($worksheetTitle);


    $export_fields_conf = get_config('local_statistics', 'export_fields');
    if (empty($export_fields_conf) or !$export_fields_conf) {
        $export_fields = EXPORT_FIELDS;
    } else {
        $export_fields = explode(',', $export_fields_conf);
    }

    $column = 0;
    foreach ($export_fields as $field_name) {
        $myxls->write_string(0, $column++, get_string($field_name, 'local_statistics'));
    }


    $data = $DB->get_records_sql('SELECT * FROM {statistics}');
    $row = 1;
    foreach ($data as $d) {
        $column = 0;
        foreach ($export_fields as $field_name) {
            $myxls->write_string($row, $column++, $d->$field_name);
        }
        $row++;
    }

    $myxls->set_column(0, 0, 40);
    $myxls->set_column(1, 1, 10);
    $myxls->set_column(2, 50, 30);

    /// Close the workbook
    $workbook->close();
    exit;
}

