<?php
// This file is part of the plagaware plugin for Moodle - http://moodle.org/
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
 * userconfig.php - update a specific user's settings
 *
 * @package     plagiarism_plagaware
 * @subpackage  plagiarism
 * @author      Daniel Gockel  <dgockel@plagaware.com>
 * @copyright   2015 plagaware GmbH {@link https://www.plagaware.com/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/plagiarism/plagaware/lib.php');
require_once($CFG->dirroot.'/plagiarism/plagaware/userconfig_form.php');

$url = new moodle_url('/plagiarism/plagaware/userconfig.php');
$PAGE->set_url($url);
if ($CFG->version < 2011120100) {
    $context = get_context_instance(CONTEXT_SYSTEM);
} else {
    $context = context_system::instance();
}
$PAGE->set_context($context);

require_login();
//require_sesskey();

if (!get_config('plagiarism_plagaware', 'plagaware_multipleaccounts')) {
    print_error('nomultipleaccounts', 'plagiarism_plagaware');
}

$connection = new plagaware_connection();
$connection->set_username($USER->email);
$serversettings = (array)$connection->get_user_settings();

$settings = new stdClass();
$apimapping = $connection->get_user_settings_mapping();
foreach ($apimapping as $field => $serverfield) {
    if (isset($serversettings[$serverfield])) {
        $value = $serversettings[$serverfield];
        $settings->$field = $value;
    }
}

$form = new plagaware_userconfig_form();
$form->set_data($settings);

$msg = '';
if ($data = $form->get_data()) {
    //set autostart for plagaware analysis
    $connection->enable_auto_analysis();
    //END set autostart for plagaware analysis
    
    $result = true;
    // Send settings back to the server.
    foreach ($apimapping as $field => $serverfield) {
        if($serverfield=='WEB'){
            $data->$field=1;
        }
        if (isset($data->$field) && $data->$field != $settings->$field) {
            // Setting has changed - update the server.
            
            $result = $connection->set_user_setting($serverfield, $data->$field) & $result;
        }
    }

    if ($result) {
        $msg = get_string('savedconfigsuccess', 'plagiarism_plagaware');
    } else {
        $msg = get_string('savedconfigerror', 'plagiarism_plagaware');
    }
}

$username = fullname($USER)." ({$USER->email})";

$PAGE->set_title(get_string('pluginname', 'plagiarism_plagaware'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('settingsfor', 'plagiarism_plagaware', $username));
if ($msg) {
    echo html_writer::tag('p', $msg, array('class' => 'notifysuccess'));
}
$form->display();
echo $OUTPUT->footer();