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
 * Record the fact that scanning is now complete for a file on the server
 *
 * @since 2.0
 * @package    plagiarism_plagaware
 * @subpackage plagiarism
 * @author     Davo Smith (based on work by Anuj Dalal)
 * @copyright  @2012 Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

$pid = intval($_SERVER['QUERY_STRING']);

if (empty($pid)) {
    die(); // No PID - ignore the request
}

if (!$currentrecord = $DB->get_record('plagiarism_plagaware', array('pid' => $pid, 'updatestatus' => 0))) {
    die(); // Does not match any documents we have (or the document is already marked for updating) - ignore the request
}

if ($currentrecord->status == 3 && !is_null($currentrecord->pstatus)) {
    die(); // We already have the plagiarism % for this file
}

$upd = new stdClass();
$upd->id = $currentrecord->id;
$upd->updatestatus = 1;
$DB->update_record('plagiarism_plagaware', $upd);