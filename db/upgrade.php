<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_statistics_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

//    if ($oldversion < 2018090606) {
//        if ($dbman->table_exists('statistics')) {
//            $table = new xmldb_table('statistics');
//            $dbman->drop_table($table);
//        }
//        if (file_exists($CFG->dirroot . '/local/statistics/db/install.xml')) {
//            $dbman->install_from_xmldb_file($CFG->dirroot . '/local/statistics/db/install.xml');
//        }
//    }

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

    return true;
}
