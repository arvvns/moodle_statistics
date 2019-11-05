<?php


namespace local_statistics\task;

class moodle_stats_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     * @throws \coding_exception
     */
    public function get_name()
    {
        return get_string('crontask_moodlestats', 'local_statistics');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     */
    public function execute()
    {
        global $CFG;
        require_once($CFG->dirroot . '/local/statistics/lib.php');
        $courseStats = new \CourseStatistics();
        $courseStats->collect_moodle_statistic();
    }
}