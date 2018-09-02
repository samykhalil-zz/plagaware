<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
* database upgrade code for plagaware plugin
*
* @package      plagiarism_plagaware
* @subpackage   plagiarism
* @author       Daniel Gockel <dgockel@plagaware.com>
* @copyright    @2015 plagaware GmbH
* @license      http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

function xmldb_plagiarism_plagaware_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012031201) {
        // Define field value to be dropped from plagaware_config
        $table = new xmldb_table('plagaware_config');
        $field = new xmldb_field('assignmentid');

        // Conditionally launch drop field value
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2012031201, 'plagiarism', 'plagaware');
    }

    if ($oldversion < 2012031204) {
        $table = new xmldb_table('plagaware');

        // Remove the 'assignmentid' field
        $field = new xmldb_field('assignmentid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Remove the 'fileid' field
        $field = new xmldb_field('fileid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Remove the 'courseid' field
        $field = new xmldb_field('courseid');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Remove the 'file' field
        $field = new xmldb_field('file');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Create a new 'cmid' field
        $field = new xmldb_field('cmid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'status');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Create a new 'filehash' field
        $field = new xmldb_field('filehash', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'cmid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2012031204, 'plagiarism', 'plagaware');
    }

    if ($oldversion < 2012031300) {
        $table = new xmldb_table('plagaware_config');

        // Remove the 'assignmentid' field
        $field = new xmldb_field('name');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Remove the 'fileid' field
        $field = new xmldb_field('value');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Add the 'upload' field (for when to do an upload)
        $field = new xmldb_field('upload', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'cm');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add the 'complete' field (for when an automatic upload has been done)
        $field = new xmldb_field('complete', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNUL, null, '0', 'upload');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2012031300, 'plagiarism', 'plagaware');
    }

    if ($oldversion < 2012031400) {
        $table = new xmldb_table('plagaware');

        // Change 'pstatus' into a number
        $field = new xmldb_field('pstatus', XMLDB_TYPE_NUMBER, '4, 1', null, null, null, null, 'pid');
        $dbman->change_field_type($table, $field);

        // Remove the unused 'preport' field
        $field = new xmldb_field('preport');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2012031400, 'plagiarism', 'plagaware');
    }

    if ($oldversion < 2012031401) {
        // Rename 'plagaware' table to 'plagiarism_plagaware' (to meet Moodle guidelines)
        $table = new xmldb_table('plagaware');
        $dbman->rename_table($table, 'plagiarism_plagaware');

        // Rename 'plagaware_config' table to 'plagiarism_plagaware_config' (to meet Moodle guidelines)
        $table = new xmldb_table('plagaware_config');
        $dbman->rename_table($table, 'plagiarism_plagaware_config');

        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2012031401, 'plagiarism', 'plagaware');
    }

    if ($oldversion < 2012031402) {
        // Add the 'updatestatus' field
        $table = new xmldb_table('plagiarism_plagaware');
        $field = new xmldb_field('updatestatus', XMLDB_TYPE_INTEGER, '2', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'filehash');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2012031402, 'plagiarism', 'plagaware');
    }

    if ($oldversion < 2012060600) {
        // Add the 'username' field to the plagaware_config table
        $table = new xmldb_table('plagiarism_plagaware_config');
        $field = new xmldb_field('username', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'complete');

        // Conditionally launch add field username
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2012060600, 'plagiarism', 'plagaware');
    }

    if ($oldversion < 2012062501) {
        // Make the pid index non-unique (as mutliple records might be '0')
        $table = new xmldb_table('plagiarism_plagaware');
        $index = new xmldb_index('pid', XMLDB_INDEX_NOTUNIQUE, array('pid'));

        // Conditionally launch add index pid
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        $dbman->add_index($table, $index);

        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2012062501, 'plagiarism', 'plagaware');
    }

    if ($oldversion < 2015110700) {
        //add nondisclosure field to plagiarism_plagaware_config
        $table = new xmldb_table('plagiarism_plagaware_config');
        $field = new xmldb_field('nondisclosure', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'username');
        
        // Conditionally launch add field nondisclosure
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


        $field = new xmldb_field('show_report_to_students', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'nondisclosure');

        // Conditionally launch add field nondisclosure
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2015110700, 'plagiarism', 'plagaware');
    }
    
    
    if ($oldversion < 2016052501) {
        //add groups_capabilities field to plagiarism_plagaware_config
        $table = new xmldb_table('plagiarism_plagaware_config');
        $field = new xmldb_field('show_students_links', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'show_report_to_students');
       
        
        // Conditionally launch add field groups
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        
        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2016052501, 'plagiarism', 'plagaware');
    }
    
        if ($oldversion < 2016052502) {
        
        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2016052502, 'plagiarism', 'plagaware');
    }
        
        if ($oldversion < 2016061401) {
        
        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2016061401, 'plagiarism', 'plagaware');
    }    
        if ($oldversion < 2017120601) {
        
        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2017120601, 'plagiarism', 'plagaware');
    }
         if ($oldversion < 2018072301) {
        
        // plagaware savepoint reached
        upgrade_plugin_savepoint(true, 2018072301, 'plagiarism', 'plagaware');
    }
    

    return true;
}