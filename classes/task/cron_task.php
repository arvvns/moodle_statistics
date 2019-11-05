<?php


namespace local_statistics\task;

class cron_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_name() {
        return get_string('crontask', 'local_statistics');
    }

    /**
     * Run forum cron.
     */
    public function execute() {
        global $CFG;
        require_once($CFG->dirroot . '/local/statistics/lib.php');
        $courseStats = new \CourseStatistics();
        $courseStats->collect();
    }

}
