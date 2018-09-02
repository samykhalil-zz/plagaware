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
 * userconfig_form.php - allows to configure plagaware settings for individual account
 *
 * @package     plagiarism_plagaware
 * @subpackage  plagiarism
 * @author      Daniel Gockel  <dgockel@plagaware.com>
 * @copyright   2015 plagaware GmbH {@link https://www.plagaware.com/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class plagaware_userconfig_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $languageoptions = array( 0 => get_string('english', 'plagiarism_plagaware'), 1 => get_string('german', 'plagiarism_plagaware'), 2 => get_string('spanish', 'plagiarism_plagaware'), 3 => get_string('french', 'plagiarism_plagaware'));
        $emailoptions = array( 0 => get_string('never', 'plagiarism_plagaware'), 1 => get_string('always', 'plagiarism_plagaware'), 2 => get_string('if_plagiarism_level', 'plagiarism_plagaware'));
        $dataoptions = array( 0 => get_string('noone', 'plagiarism_plagaware'), 1 => get_string('noonedocs', 'plagiarism_plagaware'), 2 => get_string('myinstitution', 'plagiarism_plagaware'), 3 => get_string('generaldatabase', 'plagiarism_plagaware'));
        $autostartoptions = array( 0 => get_string('no'), 1 => get_string('yes'));
        $autodel = array( 0 => get_string('week', 'plagiarism_plagaware'), 1 => get_string('weeks', 'plagiarism_plagaware'), 2 => get_string('months', 'plagiarism_plagaware'), 3 => get_string('neverdelete', 'plagiarism_plagaware'));
        $docx = array( 0 => get_string('docxemail', 'plagiarism_plagaware'),
                       1 => get_string('docxgenerate', 'plagiarism_plagaware'),
                       2 => get_string('docxnone', 'plagiarism_plagaware'));
        $ssty=array ( 0 => get_string('sstylow','plagiarism_plagaware'),
					  1 => get_string('sstymedium','plagiarism_plagaware'),
					  2 => get_string('sstyhigh','plagiarism_plagaware'));
	    $yellow= array ( 10 => '1 %',
						 20 => '2 %',
						 30 => '3 %',
						 40 => '4 %',
						 50 => '5 %', 
						 60 => '6 %', 
						 70 => '7 %',
						 80 => '8 %',
						 90 => '9 %',
						 100 => '10 %'
						);
		$red= array ( 50 => '5 %',
					  60 => '6 %',
						 70 => '7 %',
						 80 => '8 %',
						 90 => '9 %', 
						 100 => '10 %', 
						 110 => '11 %',
						 120 => '12 %',
						 130 => '13 %',
						 140 => '14 %',
						 150 => '15 %'
						);

        $mform->addElement('select', 'plagaware_language', get_string("api_language", "plagiarism_plagaware"), $languageoptions);
        $mform->setDefault('plagaware_language', '0');


        $mform->addElement('select', 'plagaware_email_policy', get_string("email_policy", "plagiarism_plagaware"), $emailoptions);
        $mform->setDefault('plagaware_email_policy', '0');

        $mform->addElement('select', 'plagaware_docx', get_string('handledocx', 'plagiarism_plagaware'), $docx);
        $mform->setDefault('plagaware_docx', 0);

        $mform->addElement('select', 'plagaware_data_policy', get_string("data_policy", "plagiarism_plagaware"), $dataoptions);
        $mform->disabledIf('plagaware_data_policy', 'plagaware_use', 'eq', 0);
        $mform->addHelpButton('plagaware_data_policy', 'datapolicyhelp', 'plagiarism_plagaware');

        $mform->addElement('select', 'plagaware_autodel', get_string("autodel", "plagiarism_plagaware"), $autodel);
        $mform->setDefault('plagaware_autodel', '3');
        
        $mform->addElement('advcheckbox', 'plagaware_web', get_string('webonly', 'plagiarism_plagaware'), '', array('group' => 1), array(0, 1));
        
        $mform->addElement('select', 'plagaware_ssty', get_string("ssty", "plagiarism_plagaware"), $ssty);
        $mform->setDefault('plagaware_ssty', 1);
       
		$mform->addElement('select', 'plagaware_yellow', get_string("yellow", "plagiarism_plagaware"), $yellow);
        $mform->setDefault('plagaware_yellow', 10);
        
        $mform->addElement('select', 'plagaware_red', get_string("red", "plagiarism_plagaware"), $red);
        $mform->setDefault('plagaware_red', 50);
        /*
        $radioarray=array();
		$radioarray[] =& $mform->createElement('radio', 'yesno', '', $share->0 , 1, 1);
		$radioarray[] =& $mform->createElement('radio', 'yesno', '', $share->0, 0, 2);
		$mform->addGroup($radioarray, 'radioar', '', array(' '), false);
		*/
        $this->add_action_buttons(false);
    }
}
