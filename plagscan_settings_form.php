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
 * plagaware_setting_form.php - shows the settings elements
 *
 * @package     plagiarism_plagaware
 * @subpackage  plagiarism
 * @author      Ruben Olmedo <rolmedo@plagaware.com>
 * @copyright   2016 plagaware GmbH {@link https://www.plagaware.com/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}
require_once($CFG->dirroot.'/lib/formslib.php');

class plagaware_settings_form extends moodleform {
   
    /// Define the form
    function definition () {
        global $CFG;
        
        $mform =& $this->_form;
        
        //initial variables
        $languageoptions = array( 0 => get_string('english', 'plagiarism_plagaware'), 1 => get_string('german', 'plagiarism_plagaware'), 2 => get_string('spanish', 'plagiarism_plagaware'), 3 => get_string('french', 'plagiarism_plagaware'));
        $emailoptions = array( 0 => get_string('email_policy_never', 'plagiarism_plagaware'), 1 => get_string('email_policy_always', 'plagiarism_plagaware'), 2 => get_string('email_policy_ifred', 'plagiarism_plagaware'));
        $dataoptions = array( 0 => get_string('noone', 'plagiarism_plagaware'), 1 => get_string('noonedocs', 'plagiarism_plagaware'), 2 => get_string('myinstitution', 'plagiarism_plagaware'), 3 => get_string('generaldatabase', 'plagiarism_plagaware'));
        $autostartoptions = array( 0 => get_string('no'), 1 => get_string('yes'));
        $autodel = array( 0 => get_string('week', 'plagiarism_plagaware'), 1 => get_string('weeks', 'plagiarism_plagaware'), 2 => get_string('months', 'plagiarism_plagaware'), 3 => get_string('neverdelete', 'plagiarism_plagaware'));
        $docx = array( 0 => get_string('docxemail', 'plagiarism_plagaware'),
                       1 => get_string('docxgenerate', 'plagiarism_plagaware'),
                       2 => get_string('docxnone', 'plagiarism_plagaware'));
        $accountsopts = array(0 => get_string('singleaccount', 'plagiarism_plagaware'),
                              1 => get_string('individualaccounts', 'plagiarism_plagaware'));

        //build form
        $mform->addElement('html',"<div style='margin-left: 10%;margin-right: 30%;'><img style='margin-left:32%;' src='images/logo-new.png'/> <br /><div style='margin-left: 15%;'>".get_string('plagawareexplain', 'plagiarism_plagaware')."</div><br />");
        
        $last_cron_exe = get_config('plagiarism_plagaware', 'plagaware_cronrunning');
        
        $url = new moodle_url('/plagiarism/plagaware/cron_reset.php');
        
        if($last_cron_exe==0){
        $mform->addElement('html',"<div style='margin-left:0%;color:green;text-align: center;'>".get_string("cron_normal","plagiarism_plagaware")."</div><br/>");
        }else{
        $mform->addElement('html',"<div style='margin-left:0%;color:red;text-align: center;'>".get_string("cron_running1","plagiarism_plagaware")." ".date('H:i:s', $last_cron_exe)." ".get_string("cron_running2","plagiarism_plagaware")." ".html_writer::link($url, get_string('cron_reset_link', 'plagiarism_plagaware'))." <br/> ".get_string('cron_help','plagiarism_plagaware')."</div><br/>");
        }
         $mform->addElement('html',"<div>");
        
        $mform->addElement('checkbox', 'plagaware_use', get_string('useplagaware', 'plagiarism_plagaware'));

        
        $mform->addElement('text', 'plagaware_server', get_string('plagawareserver', 'plagiarism_plagaware'), array('size' => '40', 'style' => 'height: 33px'));
        $mform->addHelpButton('plagaware_server', 'plagawareserver', 'plagiarism_plagaware');
        if (isset($CFG->proxyhost) && $CFG->proxyhost!='') {
            $mform->setDefault('plagaware_server', 'https://api.plagaware.com/');
        } else {
            $mform->setDefault('plagaware_server', 'ssl://api.plagaware.com/');  
        }
        $mform->addRule('plagaware_server', null, 'required', null, 'client');
        $mform->setType('plagaware_server', PARAM_TEXT);

        $mform->addElement('text', 'plagaware_version', get_string('plagaware_API_version', 'plagiarism_plagaware'), array('style' => 'height: 33px'));
        $mform->addHelpButton('plagaware_version', 'plagaware_API_version', 'plagiarism_plagaware');
        $mform->setDefault('plagaware_version', '2.1');
        $mform->addRule('plagaware_version', null, 'required', null, 'client');
        $mform->setType('plagaware_version', PARAM_TEXT);

        $mform->addElement('text', 'plagaware_key', get_string('plagaware_API_key', 'plagiarism_plagaware'), array('size' => '40', 'style' => 'height: 33px'));
        $mform->addHelpButton('plagaware_key', 'plagaware_API_key', 'plagiarism_plagaware');
        $mform->addRule('plagaware_key', null, 'required', null, 'client');
        $mform->setType('plagaware_key', PARAM_TEXT);

        $mform->addElement('text', 'plagaware_username', get_string('plagaware_API_username', 'plagiarism_plagaware'), array('style' => 'height: 33px'));
        $mform->addRule('plagaware_username', null, 'required', null, 'client');
        $mform->setType('plagaware_username', PARAM_TEXT);

        $mform->addElement('select', 'plagaware_language', get_string("api_language", "plagiarism_plagaware"), $languageoptions);
        $mform->addHelpButton('plagaware_language', 'api_language', 'plagiarism_plagaware');
        $mform->setDefault('plagaware_language', '0');


        $mform->addElement('select', 'plagaware_email_policy', get_string("email_policy", "plagiarism_plagaware"), $emailoptions);
        $mform->setDefault('plagaware_email_policy', '0');

        $mform->addElement('checkbox', 'plagaware_email_notification_account', get_string("email_policy_notification_account", "plagiarism_plagaware"));
        $mform->addHelpButton('plagaware_email_notification_account', 'email_policy_notification_account', 'plagiarism_plagaware');
        $mform->setDefault('plagaware_email_notification_account', 1);

        $mform->addElement('select', 'plagaware_data_policy', get_string("data_policy", "plagiarism_plagaware"), $dataoptions);
        $mform->disabledIf('plagaware_data_policy', 'plagaware_use', 'eq', 0);
        $mform->addHelpButton('plagaware_data_policy', 'datapolicyhelp', 'plagiarism_plagaware');

        $mform->addElement('selectyesno', 'plagaware_studentpermission', get_string('plagaware_studentpermission', 'plagiarism_plagaware'), 0);

        $mform->addElement('textarea', 'plagaware_student_disclosure', get_string('studentdisclosure','plagiarism_plagaware'),'wrap="virtual" rows="6" cols="50"');
        $mform->addHelpButton('plagaware_student_disclosure', 'studentdisclosure', 'plagiarism_plagaware');
        $mform->setDefault('plagaware_student_disclosure', get_string('studentdisclosuredefault','plagiarism_plagaware'));
        
        $mform->addElement('text', 'plagaware_groups', get_string('allowgroups','plagiarism_plagaware'),array('size' => '40', 'style' => 'height: 33px'));
        $mform->addHelpButton('plagaware_groups', 'allowgroups', 'plagiarism_plagaware');
        $mform->setType('plagaware_groups', PARAM_TEXT);         
        
        $mform->addElement('text', 'plagaware_nondisclosure_notice_email', get_string('plagaware_nondisclosure_notice_email','plagiarism_plagaware'), array('style' => 'height: 33px', 'placeholder' => get_string('plagaware_nondisclosure_notice_email_desc','plagiarism_plagaware')));
        $mform->addHelpButton('plagaware_nondisclosure_notice_email', 'plagaware_nondisclosure_notice_email', 'plagiarism_plagaware');
        $mform->setType('plagaware_nondisclosure_notice_email', PARAM_TEXT);

        $mform->addElement('select', 'plagaware_autodel', get_string("autodel", "plagiarism_plagaware"), $autodel);
        $mform->setDefault('plagaware_autodel', '3');

        $mform->addElement('select', 'plagaware_docx', get_string('handledocx', 'plagiarism_plagaware'), $docx);
        $mform->setDefault('plagaware_docx', 0);

        $mform->addElement('select', 'plagaware_multipleaccounts', get_string('plagaware_multipleaccounts', 'plagiarism_plagaware'), $accountsopts, 0);
        
        $mform->addElement('html',"</div>");

        //$mform->addElement('button', 'plagaware_connectiontester', get_string('testconnection', 'plagiarism_plagaware'));
		
		
		/*
		$radioarray=array();
		$radioarray[] =& $mform->createElement('radio', 'share', '', "compare only with web" , 0, "");
		$radioarray[] =& $mform->createElement('radio', 'share', '', "compare to my documents", 1, "");
		$radioarray[] =& $mform->createElement('radio', 'share', '', "compare to my institution database", 2, "");
		$radioarray[] =& $mform->createElement('radio', 'share', '', "compare with general database", 3, "");
		$mform->addGroup($radioarray, 'compPoly', 'Compare With', array(' '), false);
		
		$mform->addElement('advcheckbox', 'web', "Data Policy", 'compare only with web', array('group' => 1), array(-1, 0));
		$mform->addElement('advcheckbox', 'documents', '', 'compare to my documents', array('group' => 1), array(-1, 1));
		$mform->addElement('advcheckbox', 'database', '', 'compare to my institution database', array('group' => 1), array(-1, 2));
		$mform->addElement('advcheckbox', 'generaldb', '', 'compare with general database', array('group' => 1), array(-1, 3));
        //$mform->addElement('text', 'plagaware_call_back_script', get_string('plagaware_call_back_script', 'plagiarism_plagaware'));
        
          $mform->addElement('text', 'plagaware_max_file_size', get_string('max_file_size', 'plagiarism_plagaware'));
          $mform->addHelpButton('plagaware_max_file_size', 'maxfilesize', 'plagiarism_plagaware');
          $mform->setDefault('plagaware_max_file_size', '1000000');
          $mform->addRule('plagaware_max_file_size', null, 'numeric', null, 'client');
        */
                
        $this->add_action_buttons(true);
        $mform->addElement('html',"</div>");
    }
}

