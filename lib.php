<?php
// Needs field in db table
define('ACTIVITES_LIST',  ["assign", "quiz", "glossary", "forum", "wiki", "data", "choice", "lesson", "feedback", "attendance", "workshop", "hvp"]);
define('RESOURCES_LIST',  ["folder", "imscp", "label", "page", "resource", "url", "book"]);
define('EXPORT_FIELDS',  ['coursename', 'courseid', 'idnumber', 'subcategory', 'category', 'teachers', 'teachers_count',
    'active_teachers', 'teacher_last_access', 'students_count', 'active_students', 'student_last_access', 'resource',
    'page', 'url', 'book', 'other', 'assign', 'forum', 'forum_posts', 'forum_notnews', 'forum_notnews_posts','quiz', 'quiz_questions', 'files', 'glossary',
    'glossary_entries', 'wiki', 'data', 'data_entries', 'choice', 'lesson', 'feedback', 'attendance', 'folder', 'imscp', 'label',
    'workshop', 'epas', 'epas_files', 'quiz_attempts', 'hvp', 'groups_conversations',  'date']);

function local_statistic_get()
{
    global $DB;

     //turi būti užkonfiginta
    $interval_start = get_config('local_statistics', 'interval_start');
    $interval_end = get_config('local_statistics', 'interval_end');
    $interval1 = (!empty($interval_start))? strtotime($interval_start) : null;
    $interval2 = (!empty($interval_end))? strtotime($interval_end) : null;


    mtrace("Generuojama kursų statistika");

    $courseCount = get_config('local_statistics', 'course_per_cron');
    $lastCourseId = get_config('local_statistics', 'last_course_id');

    $ids = $DB->get_records_sql('SELECT id FROM {course} WHERE id > ' . $lastCourseId . ' LIMIT ' . $courseCount);

    foreach ($ids as $currentId) {
        $d = new stdClass();
        local_statistics_set_default_fields($d);
        $id = $currentId->id;
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

        $d->teachers = mb_substr(local_statistics_get_teachers($courseidnumber, $teachers), 0, 255);
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
            {forum} AS f
        JOIN {forum_discussions} AS d ON f.id = d.forum
        JOIN {forum_posts} AS p ON p.discussion = d.id
        WHERE
            f.course = ' . $id);

        isset($posts[$id]) ? $d->forum_posts = (int)$posts[$id]->count : $d->forum_posts = 0;

        $d->forum_notnews = 0;
        $forum_notnews = $DB->get_record_sql("SELECT course, COUNT(*) AS `count`
            FROM {forum} 
            WHERE `type` != 'news' AND course = ?
            GROUP BY course", array($id));
        if (!empty($forum_notnews)) {
            $d->forum_notnews = intval($forum_notnews->count);
        }

        $d->forum_notnews_posts = 0;
        $forum_notnews_posts = $DB->get_record_sql("SELECT f.course, COUNT(*) AS `count`
            FROM {forum} AS f
            INNER JOIN {forum_discussions} d ON d.forum = f.id AND f.`type` != 'news' AND f.course = ?
            INNER JOIN {forum_posts} AS p ON p.discussion = d.id
            GROUP BY f.course", array($id));
        if (!empty($forum_notnews_posts)) {
            $d->forum_notnews_posts = intval($forum_notnews_posts->count);
        }

        $questionCount = $DB->get_records_sql('SELECT
            qc.contextid,
            COUNT(1) as count
        FROM
            {question_categories} AS qc
        JOIN {question} AS q ON q.category = qc.id
        WHERE
            qc.contextid = ' . $coursecontext->id . ' and q.parent = 0');

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
                $d->epas = intval($epas_count->count);
            }

            $epas_files_count = $DB->get_record_sql("SELECT cm.course, COUNT(*) AS count 
                FROM {plagiarism_epas_files} AS ef
                INNER JOIN {course_modules} AS cm ON ef.cm = cm.id AND cm.course = ? 
                GROUP BY cm.course", array($id));

            if (!empty($epas_files_count) and $epas_files_count->count > 0) {
                $d->epas_files = intval($epas_files_count->count);
            }
        }

        // quiz attempts counts by course
        $d->quiz_attempts = 0;
        $quiz_attempts = $DB->get_record_sql("SELECT q.course AS course, COUNT(qa.id) AS attempts_count
            FROM {quiz_attempts} AS qa
            INNER JOIN {quiz} AS q on qa.quiz = q.id AND q.course = ?
            GROUP BY q.course", array($id));
        if (!empty($quiz_attempts->attempts_count) and $quiz_attempts->attempts_count > 0) {
            $d->quiz_attempts = intval($quiz_attempts->attempts_count);
        }

        // activity database entries
        $d->data_entries = 0;
        $data_entries = $DB->get_record_sql("SELECT COUNT(*) AS data_entries, d.course AS course 
            FROM {data_records} AS r
            INNER JOIN {data} AS d ON d.id = r.dataid and d.course = ?
            GROUP BY d.course", array($id));
        if (!empty($data_entries->data_entries) and $data_entries->data_entries > 0) {
            $d->data_entries = intval($data_entries->data_entries);
        }

        // count of messages in groups conversations
        $d->groups_conversations = 0;
        $groups_conversations = $DB->get_record_sql("SELECT COUNT(*) AS groups_conversations, g.courseid AS courseid 
            FROM {messages} AS m 
            INNER JOIN {message_conversations} AS mc ON m.conversationid = mc.id AND mc.itemtype ='groups' 
            INNER JOIN {groups} AS g ON mc.itemid = g.id AND g.courseid = ?;", array($id));
        if (!empty($groups_conversations->groups_conversations) and $groups_conversations->groups_conversations > 0) {
            $d->groups_conversations = intval($groups_conversations->groups_conversations);
        }

        $d->date = date('Y-m-d H:i:s', time());

        $coursemodulescount = local_statistics_get_course_modules_count($id);
        $d = (object) array_merge((array) $d, (array) $coursemodulescount);

        $exists = $DB->get_records_sql("SELECT * FROM {statistics} WHERE courseid = ".$id);

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

        send_data_to_elasticsearch((array)$d);

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
        {user} u
    JOIN {user_enrolments} ue ON (
        ue.userid = u.id
        $t
    )
    JOIN {enrol} e ON (e.id = ue.enrolid)
    LEFT JOIN {user_lastaccess} ul ON (
        ul.courseid = e.courseid
        AND ul.userid = u.id
    )
    LEFT JOIN {groups_members} gm ON u.id = gm.userid
    WHERE
        u.id <> 1
    AND u.deleted = 0
    AND u.confirmed = 1
    AND (
        SELECT
            COUNT(1)
        FROM
            {role_assignments} ra
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
    $uid = '';
    if (!empty($courseparts[1])) {
        $uid = $courseparts[1];
    }
    if (empty($uid)) return '';
    return $uid;
}

function get_ais_courseid_from_idnumber($idnumber) {
    if (empty($idnumber)) return '-';
    $courseparts = explode('_', $idnumber);
    $ais_courseid = '';
    if (!empty($courseparts[0])) {
        $ais_courseid = $courseparts[0];
    }

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
            {course_modules} AS cm
        RIGHT JOIN {modules} as m ON cm.module = m.id
        WHERE
            cm.course = ' . $courseid . '
        GROUP BY
            cm.module');

    $count = new stdClass();
    $count->other = 0;

    foreach ($coursemodules as $cm) {
        $module = $cm->name;
        if (in_array($module, ACTIVITES_LIST) or in_array($module, RESOURCES_LIST)) {
            $count->$module = intval($cm->count);
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

function http_post_json($url, array $params) {
    $data_string = json_encode($params);

    $ch    = curl_init();
    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
    );

    $response = curl_exec($ch);
    $resultStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if (!in_array($resultStatus, array(200, 201))) return false;

    return json_decode($response);
}

function get_moodle_id() {
    global $CFG;

    $moodleId = get_config('local_statistics', 'elasticsearch_moodle_id');
    if (empty($moodleId)) $moodleId = $CFG->wwwroot;
    return $moodleId;
}

function send_data_to_elasticsearch($courseData, $doc = 'coursestats') {
    global $CFG;

    $elasticsearchUrl = get_config('local_statistics', 'elasticsearch_url');
    if (!empty($CFG->local_statistics_elasticsearch_url)) $elasticsearchUrl = $CFG->local_statistics_elasticsearch_url;

    $courseData['moodle_id'] = get_moodle_id();

    $serviceFullUrl = $elasticsearchUrl . '/' . $doc . '/_doc';
    if (empty($elasticsearchUrl)) return;
    $response = http_post_json($serviceFullUrl, $courseData);
    if (empty($response) || ($response->result != 'created' )) mtrace("failed post to elasticsearch");

}

function collect_moodle_statistic() {
    global $DB;
    $usersCount = $DB->get_record_sql("SELECT COUNT(*) AS users_count FROM {user} WHERE deleted = 0");
    $activeUsersCount = $DB->get_record_sql("SELECT COUNT(*) AS active_users_count FROM {user} WHERE (UNIX_TIMESTAMP(NOW()) - lastaccess) < 1209600 ");

    $data = array(
        'users_count' => intval($usersCount->users_count),
        'active_users_count' => intval($activeUsersCount->active_users_count),
        'date' => date('Y-m-d H:i:s')
    );

    send_data_to_elasticsearch($data, 'moodlestats');
}


