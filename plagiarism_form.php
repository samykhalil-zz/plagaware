<?php
/**
 * @author Anuj Dalal
 * anuj.dalal@yahoo.co.in
 */
require_once($CFG->dirroot.'/lib/formslib.php');

class plagiarism_setup_form extends moodleform {

    /// Define the form
    function definition () {
        global $CFG;

        $mform = $this->_form;

        $languageoptions = array( 0 => get_string('english', 'plagiarism_plagaware'), 1 => get_string('german', 'plagiarism_plagaware'), 2 => get_string('spanish', 'plagiarism_plagaware'), 3 => get_string('french', 'plagiarism_plagaware'));
        $emailoptions = array( 0 => get_string('never', 'plagiarism_plagaware'), 1 => get_string('always', 'plagiarism_plagaware'), 2 => get_string('if_plagiarism_level', 'plagiarism_plagaware'));
        $dataoptions = array( 0 => get_string('noone', 'plagiarism_plagaware'), 1 => get_string('noonedocs', 'plagiarism_plagaware'), 2 => get_string('myinstitution', 'plagiarism_plagaware'), 3 => get_string('generaldatabase', 'plagiarism_plagaware'));
        $autostartoptions = array( 0 => get_string('no'), 1 => get_string('yes'));
        $autodel = array( 0 => get_string('week', 'plagiarism_plagaware'), 1 => get_string('weeks', 'plagiarism_plagaware'), 2 => get_string('months', 'plagiarism_plagaware'), 3 => get_string('neverdelete', 'plagiarism_plagaware'));
        $docx = array( 0 => get_string('docxemail', 'plagiarism_plagaware'),
                       1 => get_string('docxgenerate', 'plagiarism_plagaware'),
                       2 => get_string('docxnone', 'plagiarism_plagaware'));
        $accountsopts = array(0 => get_string('singleaccount', 'plagiarism_plagaware'),
                              1 => get_string('individualaccounts', 'plagiarism_plagaware'));

        $mform->addElement('html', get_string('plagawareexplain', 'plagiarism_plagaware'));
        $mform->addElement('checkbox', 'plagaware_use', get_string('useplagaware', 'plagiarism_plagaware'));

        $mform->addElement('textarea', 'plagaware_student_disclosure', get_string('studentdisclosure','plagiarism_plagaware'),'wrap="virtual" rows="6" cols="50"');
        $mform->addHelpButton('plagaware_student_disclosure', 'studentdisclosure', 'plagiarism_plagaware');
        $mform->setDefault('plagaware_student_disclosure', get_string('studentdisclosuredefault','plagiarism_plagaware'));

        $mform->addElement('text', 'plagaware_server', get_string('plagawareserver', 'plagiarism_plagaware'), array('size' => '40'));
        $mform->addRule('plagaware_server', null, 'required', null, 'client');
        $mform->setDefault('plagaware_server', 'ssl://api.plagaware.com/');

        $mform->addElement('text', 'plagaware_key', get_string('plagaware_API_key', 'plagiarism_plagaware'), array('size' => '40'));
        $mform->addRule('plagaware_key', null, 'required', null, 'client');

        $mform->addElement('text', 'plagaware_username', get_string('plagaware_API_username', 'plagiarism_plagaware'));
        $mform->addRule('plagaware_username', null, 'required', null, 'client');

        $mform->addElement('text', 'plagaware_version', get_string('plagaware_API_version', 'plagiarism_plagaware'));
        $mform->setDefault('plagaware_version', '2.1');
        $mform->addRule('plagaware_version', null, 'required', null, 'client');


        $mform->addElement('select', 'plagaware_language', get_string("api_language", "plagiarism_plagaware"), $languageoptions);
        $mform->setDefault('plagaware_language', '0');


        $mform->addElement('select', 'plagaware_email_policy', get_string("email_policy", "plagiarism_plagaware"), $emailoptions);
        $mform->setDefault('plagaware_email_policy', '0');

        $mform->addElement('select', 'plagaware_data_policy', get_string("data_policy", "plagiarism_plagaware"), $dataoptions);
        $mform->disabledIf('plagaware_data_policy', 'plagaware_use', 'eq', 0);
        $mform->addHelpButton('plagaware_data_policy', 'datapolicyhelp', 'plagiarism_plagaware');


        $mform->addElement('select', 'plagaware_autostart', get_string("autostart", "plagiarism_plagaware"), $autostartoptions);
        $mform->disabledIf('plagaware_autostart', 'plagaware_use', 'eq', 0);

        $mform->addElement('select', 'plagaware_autodel', get_string("autodel", "plagiarism_plagaware"), $autodel);
        $mform->setDefault('plagaware_autodel', '3');

        $mform->addElement('select', 'plagaware_docx', get_string('handledocx', 'plagiarism_plagaware'), $docx);
        $mform->setDefault('plagaware_docx', 0);

        $mform->addElement('selectyesno', 'plagaware_studentpermission', get_string('plagaware_studentpermission', 'plagiarism_plagaware'), 0);

        $mform->addElement('select', 'plagaware_multipleaccounts', get_string('plagaware_multipleaccounts', 'plagiarism_plagaware'), $accountsopts, 0);
		
		
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
    }
}

