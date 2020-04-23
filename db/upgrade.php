<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_statistics_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.


    if ($oldversion < 2018090612) {

        // Define table statistics to be created.
        $table = new xmldb_table('statistics');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
            $table = new xmldb_table('statistics');
        }


        // Adding fields to table statistics.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('coursename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('idnumber', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('subcategory', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('category', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('teachers', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('teachers_count', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('active_teachers', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('teacher_last_access', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('students_count', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('active_students', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('student_last_access', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('resource', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('page', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('url', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('book', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('assign', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('forum', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('forum_posts', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('quiz', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('quiz_questions', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('files', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('glossary', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('glossary_entries', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('wiki', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('data', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('choice', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('lesson', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('feedback', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('attendance', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('folder', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('imscp', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('label', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('other', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('date', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table statistics.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table statistics.
        $table->add_index('courseid_index', XMLDB_INDEX_UNIQUE, array('courseid'));

        // Conditionally launch create table for statistics.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Statistics savepoint reached.
        upgrade_plugin_savepoint(true, 2018090612, 'local', 'statistics');
    }

    if ($oldversion < 2018120701) {
        $table = new xmldb_table('statistics');

        $field_workshop = new xmldb_field('workshop', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0', 'label');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field_workshop)) {
            $dbman->add_field($table, $field_workshop);
        }

        $field_epas = new xmldb_field('epas', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0', 'workshop');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field_epas)) {
            $dbman->add_field($table, $field_epas);
        }

        $field_epas_files = new xmldb_field('epas_files', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0', 'epas');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field_epas_files)) {
            $dbman->add_field($table, $field_epas_files);
        }

        $field_forum_notnews = new xmldb_field('forum_notnews', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0', 'forum_posts');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field_forum_notnews)) {
            $dbman->add_field($table, $field_forum_notnews);
        }

        $field_forum_notnews_posts = new xmldb_field('forum_notnews_posts', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0', 'forum_notnews');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field_forum_notnews_posts)) {
            $dbman->add_field($table, $field_forum_notnews_posts);
        }

        // Statistics savepoint reached.
        upgrade_plugin_savepoint(true, 2018120701, 'local', 'statistics');

    }

    if ($oldversion < 2019011100) {
        $table = new xmldb_table('statistics');

        $field_quiz_attempts= new xmldb_field('quiz_attempts', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0', 'other');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field_quiz_attempts)) {
            $dbman->add_field($table, $field_quiz_attempts);
        }

        upgrade_plugin_savepoint(true, 2019011100, 'local', 'statistics');
    }

    if ($oldversion < 201902200) {
        $table = new xmldb_table('statistics');

        $field_quiz_attempts= new xmldb_field('hvp', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0', 'epas_files');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field_quiz_attempts)) {
            $dbman->add_field($table, $field_quiz_attempts);
        }

        upgrade_plugin_savepoint(true, 201902200, 'local', 'statistics');
    }

    if ($oldversion < 2019050300) {
        $table = new xmldb_table('statistics');

        $field_quiz_attempts= new xmldb_field('data_entries', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0', 'data');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field_quiz_attempts)) {
            $dbman->add_field($table, $field_quiz_attempts);
        }

        upgrade_plugin_savepoint(true, 2019050300, 'local', 'statistics');
    }

    if ($oldversion < 2019052800) {
        $table = new xmldb_table('statistics');

        $groups_conversations = new xmldb_field('groups_conversations', XMLDB_TYPE_INTEGER, '13', null, XMLDB_NOTNULL, null, '0', 'other');

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $groups_conversations)) {
            $dbman->add_field($table, $groups_conversations);
        }

        upgrade_plugin_savepoint(true, 2019052800, 'local', 'statistics');
    }

    if ($oldversion < 2019110500) {

        // Define field course_creator_idnumber to be added to statistics.
        $table = new xmldb_table('statistics');
        $field = new xmldb_field('course_creator_idnumber', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'date');

        // Conditionally launch add field course_creator_idnumber.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Statistics savepoint reached.
        upgrade_plugin_savepoint(true, 2019110500, 'local', 'statistics');
    }

    if ($oldversion < 2019110501) {

        // Define field course_language to be added to statistics.
        $table = new xmldb_table('statistics');
        $field = new xmldb_field('course_language', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'course_creator_idnumber');

        // Conditionally launch add field course_language.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Statistics savepoint reached.
        upgrade_plugin_savepoint(true, 2019110501, 'local', 'statistics');
    }

    if ($oldversion < 2020021400) {

        // Define field vpl to be added to statistics.
        $table = new xmldb_table('statistics');
        $field = new xmldb_field('vpl', XMLDB_TYPE_INTEGER, '13', null, null, null, '0', 'course_language');

        // Conditionally launch add field vpl.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Statistics savepoint reached.
        upgrade_plugin_savepoint(true, 2020021400, 'local', 'statistics');
    }

    if ($oldversion < 2020042000) {

        // Define field vpl to be added to statistics.
        $table = new xmldb_table('statistics');
        $field_bigbluebuttonbn = new xmldb_field('bigbluebuttonbn', XMLDB_TYPE_INTEGER, '13', null, null, null, '0', 'vpl');
        $field_knockplop = new xmldb_field('knockplop', XMLDB_TYPE_INTEGER, '13', null, null, null, '0', 'bigbluebuttonbn');
        $field_zoom = new xmldb_field('zoom', XMLDB_TYPE_INTEGER, '13', null, null, null, '0', 'knockplop');

        // Conditionally launch add field .
        if (!$dbman->field_exists($table, $field_bigbluebuttonbn)) {
            $dbman->add_field($table, $field_bigbluebuttonbn);
        }

        if (!$dbman->field_exists($table, $field_knockplop)) {
            $dbman->add_field($table, $field_knockplop);
        }

        if (!$dbman->field_exists($table, $field_zoom)) {
            $dbman->add_field($table, $field_zoom);
        }

        // Statistics savepoint reached.
        upgrade_plugin_savepoint(true, 2020042000, 'local', 'statistics');
    }

    if ($oldversion < 2020042300) {

        // Define field lti to be added to statistics.
        $table = new xmldb_table('statistics');
        $field = new xmldb_field('lti', XMLDB_TYPE_INTEGER, '13', null, null, null, '0', 'zoom');

        // Conditionally launch add field lti.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Statistics savepoint reached.
        upgrade_plugin_savepoint(true, 2020042300, 'local', 'statistics');
    }

    return true;
}
