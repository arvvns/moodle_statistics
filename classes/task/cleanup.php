<?php

namespace local_statistics\task;

/**
 * Removing records from statistics table where is no associated course
 * Class cleanup
 * @package local_statistics\task
 */
class cleanup extends \core\task\scheduled_task {


    public function get_name() {
        return get_string('cleanup', 'local_statistics');
    }

    /**
     * Run cron.
     */
    public function execute() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/local/statistics/lib.php');


        $sql = "select stat.courseid 
            from {statistics} stat 
            left join {course} c on stat.courseid = c.id
            where c.id is null;";

        $deleted_courses = $DB->get_records_sql($sql);

        foreach ($deleted_courses as $del_course) {
            $DB->delete_records('statistics', array('courseid' => $del_course->courseid));
        }

    }

}
