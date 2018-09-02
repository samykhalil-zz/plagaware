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
 * submit a file to plagaware for analysis
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

$userid = required_param('user', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$filehash = required_param('filehash', PARAM_TEXT);
$return = urldecode(required_param('return', PARAM_TEXT));
$return = $return."&action=grading"; //this can fail !!check at the end of checkstatus.php
$PAGE->set_url($return);

require_login();

if ($CFG->version < 2011120100) {
    $context = get_context_instance(CONTEXT_MODULE, $cmid);
} else {
    $context = context_module::instance($cmid);
}
require_capability('plagiarism/plagaware:control', $context);
require_sesskey();

if (!get_config('plagiarism', 'plagaware_use')) {
    // Disabled at the site level
    print_error('disabledsite', 'plagiarism_plagaware');
}

$instanceconfig = plagaware_get_instance_config($cmid);
if ($instanceconfig->upload == plagiarism_plugin_plagaware::RUN_NO) {
    // Disabled for this module
    print_error('disabledmodule', 'plagiarism_plagaware');
}

// Retrieve the file and check everything is OK
$fs = get_file_storage();
if (!$file = $fs->get_file_by_hash($filehash)) {
    print_error('invalidfilehash', 'plagiarism_plagaware');
}

if ($file->get_contextid() != $context->id) {
    print_error('wrongfilecontext', 'plagiarism_plagaware');
}

if ($file->get_userid() != $userid) {
    print_error('wrongfileuser', 'plagiarism_plagaware');
}

// Everything seems OK - let's submit the file
set_time_limit(0);

$filedata = array('filename' => $file->get_filename(),
                  'content' => $file->get_content(),
                  'mimetype' => $file->get_mimetype());

$plagaware = new stdClass();
$plagaware->userid = $userid;
$plagaware->cmid = $cmid;
$plagaware->filehash = $filehash;

$connection = new plagaware_connection();
$connection->set_username($instanceconfig->username);
$result = $connection->submit_file($plagaware, $filedata);
switch ($result) {
case plagaware_connection::SUBMIT_UNSUPPORTED:
    $msg = get_string('filetypeunsupported', 'plagiarism_plagaware', $filedata['filename']);
    break;
case plagaware_connection::SUBMIT_OPTOUT:
    $msg = get_string('submituseroptedout', 'plagiarism_plagaware', $filedata['filename']);
    break;
case plagaware_connection::SUBMIT_OK:
default:
    $msg = get_string('filesubmitted', 'plagiarism_plagaware', $filedata['filename']);
    break;
}
redirect($return, $msg, 5);
die();
