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
 * check the current status of one or more files uploaded to plagaware
 *
 * @since 2.0
 * @package    plagiarism_plagaware
 * @subpackage plagiarism
 * @author     Davo Smith (based on work by Anuj Dalal)
 * @copyright  @2012 Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/plagiarism/plagaware/lib.php');
global $CFG, $DB;

$cmid = optional_param('cmid', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$return = required_param('return', PARAM_TEXT);

require_login();

if (!$cmid && !$id) {
    print_error('nocmorid', 'plagiarism_plagaware');
}

if ($id) {
    $plagaware = $DB->get_record('plagiarism_plagaware', array('id' => $id), '*', MUST_EXIST);
    $cmid = $plagaware->cmid;
}

if ($CFG->version < 2011120100) {
    $context = get_context_instance(CONTEXT_MODULE, $cmid);
} else {
    $context = context_module::instance($cmid);
}
if (!(has_capability('plagiarism/plagaware:viewfullreport', $context) || has_capability('plagiarism/plagaware:control', $context))) {
    throw new moodle_exception('Permission denied!', 'plagiarism_plagaware');
}

if (!get_config('plagiarism', 'plagaware_use')) {
    // Disabled at the site level
    print_error('disabledsite', 'plagiarism_plagaware');
}

$instanceconfig = plagaware_get_instance_config($cmid);
if ($instanceconfig->upload == plagiarism_plugin_plagaware::RUN_NO) {
    // Disabled for this module
    print_error('disabledmodule', 'plagiarism_plagaware');
}

$connection = new plagaware_connection();
$connection->set_username($instanceconfig->username);
if ($id) {
    $msg = get_string('filechecked', 'plagiarism_plagaware');
    $connection->update_file_status($plagaware);
} else {
    $msg = get_string('allfileschecked', 'plagiarism_plagaware');
    $connection->update_module_status($cmid);
}
$return = $return."&action=grading";
$return = urldecode($return);
redirect($return, $msg, 2);
