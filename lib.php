<?php


function local_get_statistic()
{
    global $DB;
    $d = new stdClass();

     //turi būti užkonfiginta
    $interval1 = strtotime('17-8-1');
    $interval2 = strtotime('17-12-20');

    mtrace("Generuojama kursų statistika");

//    $servername = "192.168.20.5";
//    $username = "user";
//    $password = "ktuvma@2015";
//
//
//    $mysqli = new mysqli($servername, $username, $password, "mano_ktu_lt");
//
//    if ($mysqli->connect_error) {
//        die("Connection failed: " . $mysqli->connect_error);
//    }
//
//    $mysqli->query("set names 'utf8'");

    $courseCount = get_config('local_statistics', 'course_per_cron');
    $lastCourseId = get_config('local_statistics', 'last_course_id');

    $ids = $DB->get_records_sql('SELECT id FROM {course} WHERE id > ' . $lastCourseId . ' LIMIT ' . $courseCount);

    foreach ($ids as $CurrentId) {
        $id = $CurrentId->id;

//        $sql = "SELECT
//            field_data_field_firstname.field_firstname_value,
//            field_data_field_lastname.field_lastname_value,
//          moodle_int_courses.cid
//        FROM
//            moodle_int_courses
//        JOIN node ON node.nid = moodle_int_courses.nid
//        JOIN field_data_field_firstname ON node.uid = field_data_field_firstname.entity_id
//        JOIN field_data_field_lastname ON node.uid = field_data_field_lastname.entity_id
//        WHERE
//            mid = " . $id;
//
//        $result = $mysqli->query($sql);
//        $data = $result->fetch_row();

        $d->courseid = (int)$id;
        $r = $DB->get_record_sql('SELECT * FROM {course} WHERE id = ?', array($id));
        $courseidnumber = $r->idnumber;
        $d->pavadinimas = $r->fullname;
        //$d->kodas = substr($d->pavadinimas, 0, 8);
        //$d->kodas = trim($d->kodas, "„“");
//        isset($data[2]) ? $d->kodas = $data[2] : $d->kodas = "-";
        $d->kodas = get_ais_courseid_from_idnumber($courseidnumber);

        $r = $DB->get_record_sql('SELECT * FROM {course_categories} WHERE id = ?', array($r->category));
        isset($r->name) ? $katedra = $r->name : $katedra = "-";
        //$katedra = $r->name;

        if (isset($r->parent) && $r->parent != 0) {
            $r = $DB->get_record_sql('SELECT * FROM {course_categories} WHERE id = ?', array($r->parent));
            $d->fakultetas = $r->name;
        } else $d->fakultetas = "";

        if($d->fakultetas == ""){
            $d->fakultetas = $katedra;
            $d->katedra = "";
        }
        else
            $d->katedra = $katedra;

        $d->autorius = get_user_fullname_by_uid(get_uid_from_idnumber($courseidnumber));

        $coursecontext = context_course::instance($id);
        $enrolinstances = enrol_get_instances($id, false);

        $teachers = get_users_from_course(3, $enrolinstances, $coursecontext->id);
        $data = get_user_data($teachers);

        $d->destytoju_skaicius = $data[0];
        $d->aktyvus_destytojai = $data[1];
        if($data[2]>0)
            $d->paskutinio_destytojo_data = date('Y-m-d H:i:s', $data[2]);
        else
            $d->paskutinio_destytojo_data = "-";

        $students = get_users_from_course(5, $enrolinstances, $coursecontext->id);
        $data = get_user_data($students);

        $d->studentu_skaicius = $data[0];
        $d->aktyvus_studentai = $data[1];
        if($data[2]>0)
            $d->paskutinio_studento_data = date('Y-m-d H:i:s', $data[2]);
        else
            $d->paskutinio_studento_data = "-";


        $resources = $DB->get_records_sql('SELECT
            m.`name`,
            count(*) as count,
            cm.module
        FROM
            mdl_course_modules AS cm
        JOIN mdl_modules as m ON cm.module = m.id
        WHERE
            cm.course = ' . $id . '
        AND m.`name` IN ("folder", "imscp", "label", "page", "resource", "url", "book", "assign", "vips", "forum")
        GROUP BY
            cm.module');

        $d->resource = 0;
        $d->page = 0;
        $d->url = 0;
        $d->book = 0;
        $d->other = 0;
        $d->assign = 0;
        $d->vips = 0;
        $d->forumai = 0;

        foreach ($resources as $r) {
            switch ($r->name) {
                case "resource":
                    $d->resource = (int)$r->count;
                    break;
                case "page":
                    $d->page = (int)$r->count;
                    break;
                case "url":
                    $d->url = (int)$r->count;
                    break;
                case "book":
                    $d->book = (int)$r->count;
                    break;
                case "assign":
                    $d->assign = (int)$r->count;
                    break;
                case "vips":
                    $d->vips = (int)$r->count;
                    break;
                case "forum":
                    $d->forumai = (int)$r->count;
                    break;
                default:
                    $d->other += (int)$r->count;
            }
            //if ($r->name == "resource" || $r->name == "page" || $r->name == "url" || $r->name == "book" || $r->name == "assign" || $r->name == "vips")
            //    $d[$r->name] = $r->count;
            //else $d->other += $r->count;
        }


        $posts = $DB->get_records_sql('SELECT
        f.course,
        count(1) as count
        FROM
            mdl_forum AS f
        JOIN mdl_forum_discussions AS d ON f.id = d.forum
        JOIN mdl_forum_posts AS p ON p.discussion = d.id
        WHERE
            f.course = ' . $id);

        isset($posts[$id]) ? $d->forumo_pranesimu_kiekis = (int)$posts[$id]->count : $d->forumo_pranesimu_kiekis = 0;

        $quizCount = $DB->get_records_sql('SELECT
            mdl_quiz.course,
            count(1) as count
        FROM
            mdl_quiz
        WHERE
            course = ' . $id);

        isset($quizCount[$id]) ? $d->testu_skaicius = (int)$quizCount[$id]->count : $d->testu_skaicius = 0;

        $questionCount = $DB->get_records_sql('SELECT
            mdl_question_categories.contextid,
            count(1) as count
        FROM
            mdl_question_categories
        JOIN mdl_question on mdl_question.category = mdl_question_categories.id
        WHERE
            mdl_question_categories.contextid = ' . $coursecontext->id . ' and mdl_question.parent = 0');

        isset($questionCount[$coursecontext->id]) ? $d->klausimu_skaicius = (int)$questionCount[$coursecontext->id]->count : $d->klausimu_skaicius = 0;

        $year = date("Y");

        $files = $DB->get_records_sql('SELECT
        mdl_course_modules.course,
        count(1) as count
        FROM
            mdl_course_modules
        JOIN mdl_context on mdl_context.instanceid = mdl_course_modules.id
        JOIN mdl_files on mdl_context.id = mdl_files.contextid
        WHERE
            course = ' . $id . ' and mdl_course_modules.module = 23 and mdl_files.filesize > 0 and mdl_files.timecreated > ' . $interval1 . ' and mdl_files.timecreated < ' . $interval2);

        
        isset($files[$id]) ? $d->failu_skaicius = (int)$files[$id]->count : $d->failu_skaicius = 0;


        $glossary = $DB->get_records_sql('select course, count(1) as count from mdl_glossary where course = ' . $id);
        isset($glossary[$id]) ? $d->zodynu_skaicius = (int)$glossary[$id]->count : $d->zodynu_skaicius = 0;


        $glossaryEntries = $DB->get_records_sql('SELECT
            course,
            count(1) AS count
        FROM
            mdl_glossary
        JOIN mdl_glossary_entries on mdl_glossary.id = mdl_glossary_entries.glossaryid
        WHERE
            mdl_glossary.course = ' . $id);

        isset($glossaryEntries[$id]) ? $d->terminu_skaicius = (int)$glossaryEntries[$id]->count : $d->terminu_skaicius = 0;

        $d->kada_buvo_surinkta = date('Y-m-d H:i:s', time());


        $d->kitos_veiklos = 0;

        $otherActivities = $DB->get_records_sql('SELECT
            m.`name`,
            count(1) as count
        FROM
            mdl_course_modules AS cm
        JOIN mdl_modules as m ON cm.module = m.id
        WHERE
            cm.course = ' . $id . '
        AND m.`name` NOT IN ("folder", "imscp", "label", "page", "resource", "url", "book", "assign", "vips", "quiz", "glossary", "forum")
        GROUP BY
            cm.module');

        $d->kitos_veiklos = 0;
        foreach ($otherActivities as $r) {
            $d->kitos_veiklos += (int)$r->count;
        }

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

function get_users_from_course($roleid, $enrols, $context)
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

function get_user_data($data)
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

function local_statistics_extends_navigation(global_navigation $nav){
    global $CFG;
    if (is_siteadmin()) {
        //$url = new moodle_url($CFG->wwwroot . '/local/statistics/statistika.php?task=download');
        //$nav->add(get_string('download_statistics', 'local_statistics'), $url, navigation_node::TYPE_RESOURCE, null, null, new pix_icon('i/export', ''));
    }
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
function get_uid_from_idnumber($idnumber) {
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
    if (empty($ais_courseid)) return '-';
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


