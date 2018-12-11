<?php
// Needs field in db table
define('ACTIVITES_LIST',  ["assign", "quiz", "glossary", "forum", "wiki", "data", "choice", "lesson", "feedback", "attendance", "workshop"]);
define('RESOURCES_LIST',  ["folder", "imscp", "label", "page", "resource", "url", "book"]);

function local_statistic_get()
{
    global $DB;

    //TODO i modulio configa perkelt
     //turi būti užkonfiginta
    $interval_start = get_config('local_statistics', 'interval_start');
    $interval_end = get_config('local_statistics', 'interval_end');
    $interval1 = (!empty($interval_start))? strtotime($interval_start) : null;
    $interval2 = (!empty($interval_end))? strtotime($interval_end) : null;

    mtrace("Generuojama kursų statistika");

    $courseCount = get_config('local_statistics', 'course_per_cron');
    $lastCourseId = get_config('local_statistics', 'last_course_id');

    $ids = $DB->get_records_sql('SELECT id FROM {course} WHERE id > ' . $lastCourseId . ' LIMIT ' . $courseCount);

    foreach ($ids as $CurrentId) {
        $d = new stdClass();
        local_statistics_set_default_fields($d);
        $id = $CurrentId->id;
        $d->courseid = (int)$id;
        $r = $DB->get_record_sql('SELECT * FROM {course} WHERE id = ?', array($id));
        $courseidnumber = $r->idnumber;
        $d->coursename = $r->fullname;
        $d->idnumber = get_ais_courseid_from_idnumber($courseidnumber);

        $r = $DB->get_record_sql('SELECT * FROM {course_categories} WHERE id = ?', array($r->category));
        isset($r->name) ? $category = $r->name : $category = "-";

        if (isset($r->parent) && $r->parent != 0) {
            $r = $DB->get_record_sql('SELECT * FROM {course_categories} WHERE id = ?', array($r->parent));
            $d->category = $r->name;
            $d->subcategory = $category;
        } else {
            $d->category= $category;
            $d->subcategory = "";
        }

        $coursecontext = context_course::instance($id);
        $enrolinstances = enrol_get_instances($id, false);

        $teachers = local_statistics_get_users_from_course(3, $enrolinstances, $coursecontext->id);
        $data = local_statistics_get_user_data($teachers);

        $d->teachers = substr(local_statistics_get_teachers($courseidnumber, $teachers), 0, 255);
        $d->teachers_count = $data[0];
        $d->active_teachers = $data[1];
        if($data[2]>0)
            $d->teacher_last_access = date('Y-m-d H:i:s', $data[2]);
        else
            $d->teacher_last_access = "-";

        $students = local_statistics_get_users_from_course(5, $enrolinstances, $coursecontext->id);
        $data = local_statistics_get_user_data($students);

        $d->students_count = $data[0];
        $d->active_students = $data[1];
        if($data[2]>0)
            $d->student_last_access = date('Y-m-d H:i:s', $data[2]);
        else
            $d->student_last_access = "-";


        $posts = $DB->get_records_sql('SELECT
        f.course,
        count(1) as count
        FROM
            mdl_forum AS f
        JOIN mdl_forum_discussions AS d ON f.id = d.forum
        JOIN mdl_forum_posts AS p ON p.discussion = d.id
        WHERE
            f.course = ' . $id);

        isset($posts[$id]) ? $d->forum_posts = (int)$posts[$id]->count : $d->forum_posts = 0;

        $d->forum_notnews = 0;
        $forum_notnews = $DB->get_record_sql("SELECT course, COUNT(*) AS `count`
            FROM {forum} 
            WHERE `type` != 'news' AND course = ?
            GROUP BY course", array($id));
        if (!empty($forum_notnews)) {
            $d->forum_notnews = $forum_notnews->count;
        }

        $d->forum_notnews_posts = 0;
        $forum_notnews_posts = $DB->get_record_sql("SELECT f.course, COUNT(*) AS `count`
            FROM mdl_forum AS f
            INNER JOIN mdl_forum_discussions d ON d.forum = f.id AND f.`type` != 'news' AND f.course = ?
            INNER JOIN mdl_forum_posts AS p ON p.discussion = d.id
            GROUP BY f.course", array($id));
        if (!empty($forum_notnews_posts)) {
            $d->forum_notnews_posts = $forum_notnews_posts->count;
        }

        $questionCount = $DB->get_records_sql('SELECT
            mdl_question_categories.contextid,
            count(1) as count
        FROM
            mdl_question_categories
        JOIN mdl_question on mdl_question.category = mdl_question_categories.id
        WHERE
            mdl_question_categories.contextid = ' . $coursecontext->id . ' and mdl_question.parent = 0');

        isset($questionCount[$coursecontext->id]) ? $d->quiz_questions = (int)$questionCount[$coursecontext->id]->count : $d->quiz_questions  = 0;

        $filessql = "SELECT a.course AS course, SUM(af.numfiles) AS count
            FROM {assignsubmission_file} AS af
            INNER JOIN {assign} AS a ON a.id = af.assignment 
            WHERE a.course = $id
            GROUP BY a.course ";

        $files = $DB->get_records_sql($filessql);

        isset($files[$id]) ? $d->files = (int)$files[$id]->count : $d->files = 0;


        $glossaryEntries = $DB->get_records_sql('SELECT
            g.course,
            COUNT(1) AS count
        FROM
            {glossary} AS g
        JOIN {glossary_entries} AS ge ON g.id = ge.glossaryid
        WHERE
            g.course = ?', array($id));

        isset($glossaryEntries[$id]) ? $d->glossary_entries = (int)$glossaryEntries[$id]->count : $d->glossary_entries = 0;

        $d->epas = 0;
        $d->epas_files = 0;

        $plagiarismsettings = get_config('plagiarism');
        if (!empty($plagiarismsettings->epas_use) and $plagiarismsettings->epas_use = 1) {
            // Get how many course modules using plagiarism epas
            $epas_count = $DB->get_record_sql("SELECT cm.course, COUNT(*) AS count 
                FROM  {plagiarism_epas_config} ec 
                INNER JOIN {course_modules} cm ON ec.cm = cm.id AND ec.value = 1 AND cm.course = ?
                GROUP BY cm.course", array($id));

            if (!empty($epas_count) and $epas_count->count > 0) {
                $d->epas = $epas_count->count;
            }

            $epas_files_count = $DB->get_record_sql("SELECT cm.course, COUNT(*) AS count 
                FROM {plagiarism_epas_files} AS ef
                INNER JOIN mdl_course_modules AS cm ON ef.cm = cm.id AND cm.course = ? 
                GROUP BY cm.course", array($id));

            if (!empty($epas_files_count) and $epas_files_count->count > 0) {
                $d->epas_files = $epas_files_count->count;
            }
        }

        $d->date = date('Y-m-d H:i:s', time());

        $coursemodulescount = local_statistics_get_course_modules_count($id);
        $d = (object) array_merge((array) $d, (array) $coursemodulescount);

        $exists = $DB->get_records_sql("SELECT * FROM mdl_statistics WHERE courseid = ".$id);

        if(count($exists) == 1) {
            $d->id = key($exists);
            $DB->update_record("statistics", $d, false);
        }
        else
            $DB->insert_record("statistics", $d, false);

        if (count($ids) == $courseCount)
            set_config('last_course_id', $id, 'local_statistics');
        else
            set_config('last_course_id', 1, 'local_statistics');
    }
    mtrace("Sugeneruoti " . count($ids) . " kursai");
    return true;
}

function local_statistics_get_users_from_course($roleid, $enrols, $context)
{
    global $DB;

    $t = "";
    if (!empty($enrols)) {
        $t = "AND ue.enrolid IN (";
        foreach ($enrols as $enrol) {
            $t .= $enrol->id . " ,";
        }
        $t = rtrim($t, ",");
        $t .= ")";
    }


    $r = $DB->get_records_sql("SELECT DISTINCT
        u.id,
        u.firstname,
        u.lastname,
        u.lastaccess
    FROM
        mdl_user u
    JOIN mdl_user_enrolments ue ON (
        ue.userid = u.id
        $t
    )
    JOIN mdl_enrol e ON (e.id = ue.enrolid)
    LEFT JOIN mdl_user_lastaccess ul ON (
        ul.courseid = e.courseid
        AND ul.userid = u.id
    )
    LEFT JOIN mdl_groups_members gm ON u.id = gm.userid
    WHERE
        u.id <> 1
    AND u.deleted = 0
    AND u.confirmed = 1
    AND (
        SELECT
            COUNT(1)
        FROM
            mdl_role_assignments ra
        WHERE
            ra.userid = u.id
        AND ra.roleid = $roleid
        AND ra.contextid = $context
    ) > 0
    ORDER BY
      u.lastaccess DESC");

    return $r;
}

function local_statistics_get_user_data($data)
{
    $active = time() - 60 * 60 * 24 * 30;
    $activeCount = 0;
    $newest = 0;
    $d = array();
    foreach ($data as $user) {
        if (isset($user->lastaccess) && $user->lastaccess > $active) $activeCount++;
        if ($user->lastaccess > $newest) $newest = $user->lastaccess;
    }
    $count = count($data);
    $d[0] = $count;
    $d[1] = $activeCount;
    $d[2] = $newest;
    return $d;
}

function local_statistics_extend_settings_navigation(settings_navigation $nav, context $context){
    global $CFG, $PAGE, $COURSE;
    if (is_siteadmin()) {
        $url = new moodle_url($CFG->wwwroot . '/local/statistics/statistika.php?task=download');
        $settingnode = $nav->get("root"); //  add('setting', null, navigation_node::TYPE_CONTAINER);
        if($settingnode)
            $settingnode->add(get_string('download_statistics', 'local_statistics'), $url, navigation_node::TYPE_RESOURCE, null, null, new pix_icon('i/export', ''));
    }    
}


/**
 * Parse ldap_uid from idnumber
 * example T120B001_namsurn_1510824845
 * so should return namsurn
 * @param $idnumber
 * @return string
 */
function local_statistics_get_uid_from_idnumber($idnumber) {
    if (empty($idnumber)) return '';
    $courseparts = explode('_', $idnumber);
    $uid = $courseparts[1];
    if (empty($uid)) return '';
    return $uid;
}

function get_ais_courseid_from_idnumber($idnumber) {
    if (empty($idnumber)) return '-';
    $courseparts = explode('_', $idnumber);
    $ais_courseid = $courseparts[0];
    if (empty($ais_courseid)) return $idnumber;
    return $ais_courseid;
}

/**
 * Finds full user name by uid(ldap_uid)
 * @param $uid
 * @return string
 */
function get_user_fullname_by_uid($uid) {
    global $DB;

    if (empty($uid)) return '-';
    $user = $DB->get_record('user', array('alternatename' => 'ldap_uid#' . $uid . ';'));

    if ($user) {
        return $user->firstname . ' ' . $user->lastname;
    }
    else {
        return $uid;
    }
}


function local_statistics_get_course_modules_count($courseid) {
    global $DB;


    $coursemodules = $DB->get_records_sql('SELECT
            m.`name`,
            count(*) as count,
            cm.module
        FROM
            mdl_course_modules AS cm
        RIGHT JOIN mdl_modules as m ON cm.module = m.id
        WHERE
            cm.course = ' . $courseid . '
        GROUP BY
            cm.module');

    $count = new stdClass();
    $count->other = 0;

    foreach ($coursemodules as $cm) {
        $module = $cm->name;
        if (in_array($module, ACTIVITES_LIST) or in_array($module, RESOURCES_LIST)) {
            $count->$module = $cm->count;
        } else {
            $count->other += $cm->count;
        }
    }

    return $count;
}

function local_statistics_get_teachers($courseidnumber, $teachers) {
    if (empty($courseidnumber)) {
        $tnames = array();
        foreach ($teachers as $t) {
            $tnames[] = $t->firstname . ' ' . $t->lastname;
        }
        return implode(', ', $tnames);
    } else {
        return get_user_fullname_by_uid(local_statistics_get_uid_from_idnumber($courseidnumber));
    }
}

function local_statistics_set_default_fields(&$dataobject) {
    foreach (ACTIVITES_LIST as $a) {
        $dataobject->$a = 0;
    }
    foreach (RESOURCES_LIST as $r) {
        $dataobject->$r = 0;
    }
}
