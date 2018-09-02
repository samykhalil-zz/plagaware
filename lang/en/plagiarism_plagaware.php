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
 * plagiarism_plagaware.php - All Strings
 *
 * @package     plagiarism_plagaware
 * @subpackage  plagiarism
 * @author      Ruben Olmedo <rolmedo@plagaware.com>
 * @copyright   2016 plagaware GmbH {@link https://www.plagaware.com/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['allfileschecked'] = 'Check status of all documents on plagaware’s server';
$string['always'] = 'always';
$string['api_language']='Report language';
$string['api_language_help']= 'All plagaware reports will be downloaded in this language.';
$string['assignments']='Assignments';
$string['autodel']='Auto save document';
$string['autodescription'] = 'Upload documents automatically when the deadline has approached';
$string['autodescriptionsubmitted'] = 'The files were automatically uploaded to plagaware on {$a} - switch to the \'manual\' mode to resubmit individual documents';
$string['autostart'] = 'Autostart Plagiarism checks';
$string['badcredentials'] = 'plagaware did not recognize the account details - please check if the server URL, username ({$a->user}), API key, and version number ({$a->version}) is correct';
$string['checkallstatus'] = 'Update status of all submitted files';
$string['checkstatus'] = 'Check status';
$string['compareinternet'] = 'Data policy';
$string['connectionfailed'] = 'Failed to connect to plagaware server';
$string['data_policy']='Data policy';
$string['datapolicyhelp']='Share my documents for analysis with (compare to)';
$string['datapolicyhelp_help']='Share my documents for analysis with (compare to)';
$string['docxemail'] = 'Generate and email .docx report';
$string['docxgenerate'] = 'Generate only .docx report';
$string['docxnone'] = 'Do not generate .docx report';
$string['donotgenerate'] = 'Do not generate';
$string['downloadreport'] = 'Download .docx report';
$string['email_policy'] = 'Email policy';
$string['email_policy_always'] = 'Email all reports';
$string['email_policy_ifred'] = 'Email only if a red level is indicated';
$string['email_policy_never'] = 'Don’t email reports';
$string['email_policy_notification_account'] = 'Notification for new credentials';
$string['email_policy_notification_account_help'] = 'By <b>activating the checkbox</b>, all new generated <b>account credentials</b> will be immediately sent to you.';
$string['english'] = 'English';
$string['filechecked'] = 'Document status checked on plagaware server';
$string['filesassociated'] = 'Document will be uploaded to account \'{$a}\'';
$string['filesubmitted'] = 'Document \'{$a}\' is submitted to plagaware';
$string['filetypeunsupported'] = 'The document type \'{$a}\' is not a file type supported by the plagaware server';
$string['french'] = 'French';
$string['generaldatabase']='Compare with general database';
$string['generateemail'] = 'Generate and email';
$string['generateonly'] = 'Generate only';
$string['german'] = 'German';
$string['handledocx'] = 'Docx option';
$string['if_plagiarism_level'] = 'Only red PlagLevel';
$string['individualaccounts'] = 'Individual teacher accounts';
$string['invalidupload'] = 'The plagaware server did not accept the file {$a->filename}. The response was: {$a->content}';
$string['max_file_size'] = 'Maximum file size';
$string['maxfilesize'] = 'Maximum file size';
$string['maxfilesize_help'] = 'The file size is too big and can not be uploaded. Recommended value is 1000000.';
$string['months'] = 'After six months';
$string['myinstitution']='Compare to institution database';
$string['never'] = 'Never';
$string['neverdelete'] = 'Never Delete';
$string['newexplain'] = 'For more information on this plugin see: ';
$string['nodeadlinewarning'] = 'Warning: An automatic submission to plagaware was selected without indicating a deadline for this assignment';
$string['nomultipleaccounts'] = 'The use of individual teacher accounts for plagaware is not possible on this server';
$string['nondisclosure_notice_desc'] = 'All non-disclosure documents will be submitted in "{$a}".<br /><br />';
$string['noone']='Compare to web sources only';
$string['noonedocs']='Compare to web and my documents';
$string['notprocessed'] = 'plagaware has not analyzed this file yet';
$string['notsubmitted'] = 'Not submitted to plagaware';
$string['onlyassignmentwarning'] = 'Warning: An automatic submission to plagaware only functions while using assignment activities';
$string['optin'] = 'Plagiarism opt-in';
$string['optin_explanation'] = 'You chose to opt-in to plagiarism detection. From now on, any assignments you submit will be uploaded to the plagaware server to be compared with other documents';
$string['optout'] = 'Plagiarism opt-out';
$string['optout_explanation'] = 'You chose to opt-out of plagiarism detection. Your submitted assignments will <b>not</b> be compared with other documents submitted to the plagaware server';
$string['plagaware']  ='plagaware';
$string['plagaware:control'] = 'Submit/Resubmit plagaware Submissions';
$string['plagaware:enable'] = 'Enable/Disable plagaware inside an acivity';
$string['plagaware:viewfullreport'] = 'View/Download plagaware Reports';
$string['pluginname'] = 'plagaware';
$string['plagaware_API_key'] = 'API Key';
$string['plagaware_API_key_help'] = 'You can see your API key on <a href="https://www.plagaware.com/apisetup" target="_blank">https://www.plagaware.com/apisetup</a>';
$string['plagaware_API_method'] = 'method';
$string['plagaware_API_username']='API Username';
$string['plagaware_API_version'] = 'API Version';
$string['plagaware_API_version_help'] = 'Your latest API version is <b>2.1</b>';
$string['plagaware_call_back_script'] = 'Call back script URL';
$string['plagaware_multipleaccounts'] = 'Associate uploaded files with';
$string['plagaware_nondisclosure_notice_email'] = 'Non-disclosure documents';
$string['plagaware_nondisclosure_notice_email_desc'] = 'name@example.com';
$string['plagaware_nondisclosure_notice_email_help'] = 'All documents with blocking notice will be delivered to a separated plagaware account. All documents located in account will <b>not be shared</b> with other organization users. The entered <b>email can not be part of another plagaware account</b>.';
$string['plagaware_studentpermission'] = 'Students can refuse permission to upload to plagaware';
$string['plagawareerror'] = ' plagaware server Error: {$a}';
$string['plagawareexplain'] = 'plagaware is a plagiarism checker. <br />The software compares documents within your own institution and external web sources. <br/>To take advantage of this plugin, you will need to create an <a href="https://www.plagaware.com" target="_blank">organization account</a>. <br /><br />You can find a guideline at <a href="https://www.plagaware.com/system-integration-moodle" target="_blank">www.plagaware.com/system-integration-moodle</a>. <br />Ask us for a free trial <a href="mailto:pro@plagaware.com">pro@plagaware.com</a> and read about our latest news on <a href="https://twitter.com/plagaware" target="_blank">Twitter</a>. <br /><br />General information can be found at <a href="https://www.plagaware.com" target="_blank">www.plagaware.com</a><hr />';
$string['plagawaremethod']  ='Submit';
$string['plagawareserver'] = 'plagaware server';
$string['plagawareserver_help'] = 'The standard configuration is "<b>ssl://api.plagaware.com/</b>" or "<b>https://api.plagaware.com/</b>" if Moodle uses a proxy-server.';
$string['plagawareversion']  ='2.3';
$string['psreport'] = 'PS Report';
$string['red']='The red PlagLevel starts at';
$string['report'] = 'Report';
$string['resubmit'] = 'Resubmit to plagaware';
$string['runalways']= "Start immediately";
$string['runautomatic'] = 'Start immediately after the first due date';
$string['runduedate'] = 'Start immediately after all due dates';
$string['runmanual'] = 'Start manually';
$string['save']='Save';
$string['savedapiconfigerror'] = 'An error occurred updating your plagaware settings';
$string['savedconfigsuccess'] = 'plagaware settings saved successfully';
$string['serverconnectionproblem'] = 'Problem connecting to plagaware server';
$string['serverrejected'] = 'The plagaware server rejected this file - the file is broken or protected.';
$string['settings_cancelled'] = 'Antiplagiarism settings have been canceled';
$string['settings_saved'] = 'Antiplagiarism Settings Saved Successfully';
$string['settingsfor'] = 'Update account settings \'{$a}\'';
$string['settingsreset']='Formular l&ouml;schen';
$string['show_to_students'] = 'Share results with students';
$string['show_to_students_actclosed'] = 'after due date';
$string['show_to_students_always'] = 'always';
$string['show_to_students_help'] = 'All participants can see the plagaware analysis results.';
$string['show_to_students_never'] = 'Never';
$string['singleaccount'] = 'The main plagaware account';
$string['spanish'] = 'Spanish';
$string['ssty']='Sensitivity';
$string['sstyhigh']='High';
$string['sstylow']='Low';
$string['sstymedium']='Medium';
$string['studentdisclosure'] = 'Student Disclosure';
$string['studentdisclosure_help'] = 'This text will be displayed to all students on the file upload page.';
$string['studentdisclosuredefault']  ='All files uploaded will be submitted to a plagiarism detection service';
$string['studentdisclosureoptedout'] = 'You have opted-out from plagiarism detection';
$string['studentdisclosureoptin'] = 'Click here to opt-in to plagiarism detection';
$string['studentdisclosureoptout'] = 'Click here to opt-out from plagiarism detection';
$string['submit'] = 'Submit to plagaware';
$string['submituseroptedout'] = 'File \'{$a}\' not submitted - the user has opted-out of plagiarism detection';
$string['testconnection']='Test Connection';
$string['testconnection_fail']='Connection failed!';
$string['testconnection_success']='Connection was successful!';
$string['unsupportedfiletype'] = 'This file type is not supported by plagaware';
$string['updateyoursettings'] = 'To your plagaware settings';
$string['useplagaware'] = 'Enable plagaware';
$string['useplagaware_help'] = '';
$string['useroptedout'] = 'Opted-out of plagiarism detection';
$string['viewmatches'] = 'View matches';
$string['viewreport'] = 'View report';
$string['wasoptedout'] = 'User had opted-out of plagiarism detection';
$string['webonly']='Search the web';
$string['week'] = 'After one week';
$string['weeks'] = 'After three months';
$string['windowsize'] = 'Window size';
$string['windowsize_help'] = 'Window size represents how granular the tech search will be. Recommended value is 60.';
$string['yellow']='The yellow PlagLevel starts at';
$string['report_type']='Report Type:';
$string['newrp_wait']='Please wait, we are generating the link';
$string['newrp_redirect']='You would be redirect automaticaly';

$string['show_to_students_opt2']="Share these results";
$string['show_to_students_opt2_help']="This allows the student to see only the PlagLevel or the full reports";
$string['show_to_students_plvl']="PlagLevel";
$string['show_to_students_links']="PlagLevel and Reports";
$string['allowgroups']="Allow Categories";
$string['allowgroups_help']="Type the category name that allows you to use plagaware ( Ex: category1, category2, ... ). Leave the field blank to allow all categories";
$string['cron_reset_link']="RESET CRON";
$string['cron_reset']="The cron job has been reset";
$string['cron_normal']="The cron job configuration is accepted";
$string['cron_running1']="Cron job is running since";
$string['cron_running2']=" To reset click ";
$string['cron_help']="If you reset cron job duplicate files could be send to plagaware";
        