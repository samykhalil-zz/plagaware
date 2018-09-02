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
 * settings.php - allows the admin to configure plagiarism stuff
 *
 * @package     plagiarism_plagaware
 * @subpackage  plagiarism
 * @author      Daniel Gockel  <dgockel@plagaware.com>
 * @copyright   2015 plagaware GmbH {@link https://www.plagaware.com/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

set_time_limit(0);

require_once(dirname(dirname(__FILE__)) . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/plagiarismlib.php');
require_once($CFG->dirroot.'/plagiarism/plagaware/lib.php');
require_once($CFG->dirroot.'/plagiarism/plagaware/plagaware_settings_form.php');

require_login();
admin_externalpage_setup('plagiarismplagaware');
if ($CFG->version < 2011120100) {
    $context = get_context_instance(CONTEXT_SYSTEM);
} else {
    $context = context_system::instance();
}
require_capability('moodle/site:config', $context, $USER->id, true, "nopermissions");

//require form
$mform = new plagaware_settings_form();
$plagiarismsettings = (array) get_config('plagiarism_plagaware');

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/plagiarism/plagaware/settings.php'));
}

echo $OUTPUT->header();

// user clicked on save changes
if (($data = $mform->get_data()) && confirm_sesskey()) {

    //plagaware_use will not be send if it's false
    if (!isset($data->plagaware_use)) {
        $data->plagaware_use = 0;
    }
    if (!isset($data->plagaware_email_notification_account)) {
        $data->plagaware_email_notification_account = 0;
    }
    set_config('plagaware_use', $data->plagaware_use, 'plagiarism'); // This needs to be set differently (as used by Moodle core code)
    set_config('plagaware_use', $data->plagaware_use, 'plagiarism_plagaware');

    // Save all local only settings first (as these could affect the server connection parameters).
    $localonlysettings = array('plagaware_username' => true,
                               'plagaware_key' => true,
                               'plagaware_version' => true,
                               'plagaware_student_disclosure' => false,
                               'plagaware_studentpermission' => false,
                               'plagaware_server' => true,
                               'plagaware_multipleaccounts' => false,
                               'plagaware_nondisclosure_notice_email' => false,
                               'plagaware_email_notification_account' => false,
                               'plagaware_groups' => true);

    //copy submitted localonlysettings to local copy (plagiarism_plagaware)
    $fullserverupdate = false;
    foreach ($localonlysettings as $field => $requirefullupdate) {
        $value = $data->$field;
        if (isset($plagiarismsettings->$field) && $plagiarismsettings->$field == $value) { //local property copy is equal to submitted property
            continue; //Setting unchanged
        }

        // Save the setting
        set_config($field, $value, 'plagiarism_plagaware');

        // Check if changing this setting means a full update of server settings is required
        if ($requirefullupdate) {
            $fullserverupdate = true;
        }
    }
    // Must not do this earlier, as this depends on the settings updated just above here
    $connection = new plagaware_connection(true);

    //set autostart for plagaware analysis
    $connection->enable_auto_analysis();
    //END set autostart for plagaware analysis

    // Links the local setting name to the server setting name
    $result = true; //plagaware API parameters
    $apimapping = $connection->get_user_settings_mapping();
    foreach ($apimapping as $field => $serverfield) {
        $value = $data->$field;
        if (isset($plagiarismsettings->$field) && $plagiarismsettings->$field == $value) {
            if (!$fullserverupdate) {
                continue; // Setting is unchanged - don't update / send to server
            }
        }

        // Save the config setting locally
        set_config($field, $value, 'plagiarism_plagaware');

        // Send the new value to the server
        $result = $connection->set_user_setting($serverfield, $value) & $result;
    }


    //satus message
    if(!$result) {
        $OUTPUT->notification(get_string('savedapiconfigerror', 'plagiarism_plagaware'), 'notifysuccess');
    } else {
        $OUTPUT->notification(get_string('savedconfigsuccess', 'plagiarism_plagaware'), 'notifysuccess');
    }
}

$mform->set_data($plagiarismsettings);
//get configuration from server and save it local
/*
try {
$tempconnection = new plagaware_connection(true);
$serversettings = (array)$tempconnection->get_user_settings();
$apimapping = $tempconnection->get_user_settings_mapping();
foreach ($apimapping as $field => $serverfield) {
    if (isset($serversettings[$serverfield])) {
        $value = $serversettings[$serverfield];
        $plagiarismsettings->$field = $value;
    }
}
} catch (moodle_exception $e) {
    // Ignore any connection problems.
}
*/


echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
$mform->display();
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
