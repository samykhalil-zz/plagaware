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
* lib.php - Contains Plagiarism plugin specific functions called by Modules.
*
* @package      plagiarism_plagaware
* @subpackage   plagiarism
* @author       Ruben Olmedo <rolmedo@plagaware.com>  (based on work by Davo Smith (based on work by Anuj Dalal))
* @copyright    2016 plagaware GmbH {@link https://www.plagaware.com/}
* @license      http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

define('LOG_SERVER_COMMUNICATION', 0);

//get global class
global $CFG;
require_once($CFG->dirroot.'/plagiarism/lib.php');

class plagaware_credentials_exception extends moodle_exception {}

///// plagaware Class ////////////////////////////////////////////////////
class plagiarism_plugin_plagaware extends plagiarism_plugin {

    const RUN_NO = 0;
    const RUN_MANUAL = 1;
    const RUN_AUTO = 2;
    const RUN_ALL = 3;
    const RUN_DUE = 4;

    const SHOWSTUDENTS_NEVER = 0;
    const SHOWSTUDENTS_ALWAYS = 1;
    const SHOWSTUDENTS_ACTCLOSED = 2;
    
    const SHOWS_ONLY_PLVL = 0;
    const SHOWS_LINKS = 1;
    /**
     * hook to add plagiarism specific settings to a module settings page
     * @param object $mform  - Moodle form
     * @param object $context - current context
     */
    public function get_form_elements_module($mform, $context,$modulename = "") {
      global $DB, $USER, $COURSE; 

      if (!has_capability('plagiarism/plagaware:enable', $context)) {
        return '';
      }
      $groups = trim(get_config('plagiarism_plagaware', 'plagaware_groups'));
      
      $groups = explode(",", $groups);
      $enable_plagaware=false;
      
      if(sizeof($groups)== 1 && $groups[0]==""){
          $enable_plagaware=true;
      }
      $category = $DB->get_record('course_categories',array('id'=>$COURSE->category));
      $category = $category->name;
      
      
      if(!$enable_plagaware){
        for ($i=0;$i<sizeof($groups);$i++){
            if(strtolower($groups[$i])==strtolower($category)){
                $enable_plagaware=true;
                break;
            }
        }
      }
      
      if(!$enable_plagaware){
          return '';
      }
      
         
	  if($modulename!="mod_forum"){
        $plagawareopts = array( self::RUN_NO => get_string('no'),
                               self::RUN_MANUAL => get_string('runmanual', 'plagiarism_plagaware'),
                               self::RUN_ALL => get_string('runalways', 'plagiarism_plagaware'),
                               self::RUN_AUTO => get_string('runautomatic', 'plagiarism_plagaware'),
                               self::RUN_DUE => get_string('runduedate', 'plagiarism_plagaware'));
        
        $showstudentsopt = array( self::SHOWSTUDENTS_NEVER => get_string('show_to_students_never', 'plagiarism_plagaware'),
                               self::SHOWSTUDENTS_ALWAYS => get_string('show_to_students_always', 'plagiarism_plagaware'),
                               self::SHOWSTUDENTS_ACTCLOSED => get_string('show_to_students_actclosed', 'plagiarism_plagaware'));
        
        $showstudentslinks = array( self::SHOWS_ONLY_PLVL => get_string('show_to_students_plvl', 'plagiarism_plagaware'),
                                self::SHOWS_LINKS => get_string('show_to_students_links', 'plagiarism_plagaware'));
        
        $mform->addElement('header', 'plagawaredesc', get_string('plagaware', 'plagiarism_plagaware'));
        
        $mform->addElement('select', 'plagaware_upload', get_string("useplagaware", "plagiarism_plagaware"), $plagawareopts);
        $mform->addHelpButton('plagaware_upload', 'useplagaware', 'plagiarism_plagaware');

        // prints non-disclosure notice if it's activated
        if (get_config('plagiarism_plagaware', 'plagaware_nondisclosure_notice_email')) { 
            $mform->addElement('checkbox', 'nondisclosure_notice', get_string('plagaware_nondisclosure_notice_email', 'plagiarism_plagaware'));
            $mform->addHelpButton('nondisclosure_notice', 'plagaware_nondisclosure_notice_email', 'plagiarism_plagaware');
            $mform->addElement('static', 'nondisclosure_notice_desc', '', get_string('nondisclosure_notice_desc', 'plagiarism_plagaware', get_config('plagiarism_plagaware', 'plagaware_nondisclosure_notice_email')));
        }

        $mform->addElement('select', 'show_to_students', get_string("show_to_students", "plagiarism_plagaware"), $showstudentsopt);
        $mform->addHelpButton('show_to_students', 'show_to_students', 'plagiarism_plagaware');
        $mform->setDefault('show_to_students', self::SHOWSTUDENTS_NEVER);
        
        $mform->addElement('select', 'show_students_links', get_string("show_to_students_opt2", "plagiarism_plagaware"), $showstudentslinks);
        $mform->addHelpButton('show_students_links', 'show_to_students_opt2', 'plagiarism_plagaware');
        $mform->setDefault('show_students_links', self::SHOWS_ONLY_PLVL);
        
        //cm update
        $cmid = optional_param('update', 0, PARAM_INT);
        if ($cmid) {
            $instanceconfig = plagaware_get_instance_config($cmid);
            $mform->setDefault('plagaware_upload', $instanceconfig->upload);
            $mform->setDefault('show_to_students', $instanceconfig->show_report_to_students);
            $mform->setDefault('show_students_links', $instanceconfig->show_students_links);

            if (get_config('plagiarism_plagaware', 'plagaware_nondisclosure_notice_email')) {
                $mform->setDefault('nondisclosure_notice', $instanceconfig->nondisclosure);
            } 

            if (get_config('plagiarism_plagaware', 'plagaware_multipleaccounts')) {
                if (!empty($instanceconfig->username)) {
                    if ($instanceconfig->username == $USER->email) {
                        $user = $USER;
                    } else {
                        $user = $DB->get_record('user', array('email' => $instanceconfig->username));
                    }
                    if ($user) {
                        $name = fullname($user)." ({$user->email})";
                        $mform->addElement('static', 'plagaware_username', '', get_string('filesassociated', 'plagiarism_plagaware', $name));
                    }
                }
                $url = new moodle_url('/plagiarism/plagaware/userconfig.php', array('sesskey' => sesskey()));
                $link = html_writer::link($url, get_string('updateyoursettings', 'plagiarism_plagaware'), array('target' => '_blank'));
                $mform->addElement('html', $link);
            }
        } else {
            $mform->setDefault('plagaware_upload', self::RUN_NO);
        }
      }
    }

    /* hook to save plagiarism specific settings on a module settings page
     * @param object $data - data from an mform submission.
     */
    public function save_form_elements($data) {
        global $DB, $USER;
		
        $cmid = $data->coursemodule;
        if (isset($data->plagaware_upload)) {
            $config = new stdClass();
            $config->upload = $data->plagaware_upload;

            if ($config->upload !== self::RUN_NO) {
                // If plagaware is enabled and there is no teacher currently configured to
                // 'own' the uploads, then set the username from the moodle platform now.
                $oldconfig = plagaware_get_instance_config($cmid);
                if (empty($oldconfig->username)) {
                    $config->username = $USER->email;
                } else {
                    $config->username = $oldconfig->username;
                }
                $config->show_report_to_students = $data->show_to_students;
                $config->show_students_links = $data->show_students_links;
                //nondisclosure document
                if (isset($data->nondisclosure_notice) && $data->nondisclosure_notice == 1 && get_config('plagiarism_plagaware', 'plagaware_nondisclosure_notice_email')) {
                    $config->nondisclosure = 1;
                    $config->username = get_config('plagiarism_plagaware', 'plagaware_nondisclosure_notice_email');
                }
                //END nondisclosure document
            }

            plagaware_set_instance_config($cmid, $config);
        }
    }

    /**
     * called by admin/cron.php
     *
     */
    public function plagiarism_cron() {
		return cron();
    }
    
    public function cron() {
        //do any scheduled task stuff
        global $CFG;
        
        // Make sure multiple plagaware cron sessions don't overlap (as uploads could take a long time).

        $running = get_config('plagiarism_plagaware', 'plagaware_cronrunning');
        
        if ($running && $running > time()) {
            mtrace("plagaware cron still running");
            return true; // Already running.
        }   
        $running = time() + 86400; // Timeout after 1 day and allow another cron job to start
	
        set_config('plagaware_cronrunning', $running, 'plagiarism_plagaware');

        require_once($CFG->dirroot.'/plagiarism/plagaware/cronscript.php');

        set_config('plagaware_cronrunning', 0, 'plagiarism_plagaware');
        
        return true;
    }

    public function print_disclosure($cmid) {
        global $PAGE;

        $disclosure = '';

        if (get_config('plagiarism_plagaware', 'plagaware_studentpermission')) {
            $returl = urlencode($PAGE->url->out());
            $url = new moodle_url('/plagiarism/plagaware/optout.php', array('sesskey' => sesskey(),
                                                                           'return' => $returl));
            if (get_user_preferences('plagiarism_plagaware_optout', false)) {
                $disclosure .= get_string('studentdisclosureoptedout', 'plagiarism_plagaware');
                $disclosure .= html_writer::empty_tag('br');

                $url->param('optout', 0);
                $disclosure .= html_writer::link($url, get_string('studentdisclosureoptin', 'plagiarism_plagaware'));
            } else {
                $disclosure .= get_config('plagiarism_plagaware', 'plagaware_student_disclosure');
                $disclosure .= html_writer::empty_tag('br');

                $url->param('optout', 1);
                $disclosure .= html_writer::link($url, get_string('studentdisclosureoptout', 'plagiarism_plagaware'));
            }
        } else {
            $disclosure .= get_config('plagiarism_plagaware', 'plagaware_student_disclosure');
        }
        return $disclosure;
    }


    /**
     * hook to allow plagiarism specific information to be displayed beside a submission
     * @param array  $linkarray contains all relevant information for the plugin to generate a link
     * @return string
     *
     */
public $PS_CFG_RED=null;
public $PS_CFG_YELLOW=null;
 
    public function get_links($linkarray) {
        global $CFG, $USER, $COURSE, $DB, $PAGE,$PS_CFG_RED,$PS_CFG_YELLOW;

        if(isset($linkarray['content'])){
			return null; //get_string('unsupportedfiletype', 'plagiarism_plagaware');
        }
		
        $cmid = $linkarray['cmid'];

        // Check if plagaware is enabled for this module instance (and cache the result)
        static $plagawareenabled = array();
        if (!isset($plagawareenabled[$cmid])) {
            $instanceconfig = plagaware_get_instance_config($cmid);
            $plagawareenabled[$cmid] = $instanceconfig->upload;
        }
        if ($plagawareenabled[$cmid] == self::RUN_NO) {
            return '';
        }

        if ($CFG->version < 2011120100) {
            $context = get_context_instance(CONTEXT_MODULE, $cmid);
        } else {
            $context = context_module::instance($cmid);
        }

        //get viewreport variable
        //get assigned closed
        $viewreport = false;
        $modulesql = 'SELECT m.id, m.name, cm.instance'.
                ' FROM {course_modules} cm' .
                ' INNER JOIN {modules} m on cm.module = m.id ' .
                'WHERE cm.id = ?';
        $moduledetail = $DB->get_record_sql($modulesql, array($cmid));
        if (!empty($moduledetail)) {
            $sql = "SELECT * FROM " . $CFG->prefix . $moduledetail->name . " WHERE id= ?";
            $module = $DB->get_record_sql($sql, array($moduledetail->instance));
        }
        if (empty($module)) {
            // No such cmid.
            return false;
        }
        $assignclosed = false;
        $time = time();
        if (!empty($module->preventlate) && !empty($module->timedue)) {
            $assignclosed = ($module->timeavailable <= $time && $time <= $module->timedue);
        } else if (!empty($module->timeavailable)) {
            $assignclosed = ($module->timeavailable <= $time);
        } else if (!empty($module->duedate)) {
            $assignclosed = ($module->duedate <= $time);
        }
        //END get assigned closed
        if (isset($instanceconfig->show_report_to_students) && (
                    $instanceconfig->show_report_to_students == self::SHOWSTUDENTS_ALWAYS ||
                    $instanceconfig->show_report_to_students == self::SHOWSTUDENTS_ACTCLOSED && $assignclosed)) {
            $viewreport = true;
        }
        $showplvl=false;
        $showlinks=false;
        if($viewreport){
            if($instanceconfig->show_students_links == self::SHOWS_LINKS){
                $showplvl=true;
                $showlinks=true;
            }else{
                $showplvl=true;
            }
        }
        //END get viewreport variable

        // Check if the user is able to view links (and cache the result)
        static $viewlinks = array();
        if (!isset($viewlinks[$cmid])) {
            $viewlinks[$cmid] = has_capability('plagiarism/plagaware:control', $context);
        }
        $viewlinksb = false; //viewlinks can see message and submit; viewreport just the plaglevel
        if ($viewlinks[$cmid]) {
            $viewlinksb = true;
        }
        
        $userid = $linkarray['userid'];
        $file = $linkarray['file'];
        $filehash = $file->get_pathnamehash();
        $filename = $file->get_filename();
		
        if (!plagaware_supported_filetype($filename)) {
            return get_string('unsupportedfiletype', 'plagiarism_plagaware');
        }

        if (plagaware_user_opted_out($userid)) {
            return get_string('useroptedout', 'plagiarism_plagaware');
        }

        $message = '';

        // Find the plagaware entry for this file
        // TODO - load all records for this assignment at once, to save DB queries
        $plagaware = $DB->get_record('plagiarism_plagaware', array('cmid' => $cmid,
                                                      'userid' => $userid,
                                                      'filehash' => $filehash));
		
	    if($PS_CFG_RED==null && $PS_CFG_YELLOW==null){
			try{
    			$psconn = new plagaware_connection();
    			$psconn->set_username($instanceconfig->username);
                if ($instanceconfig->nondisclosure == 1 ) {
                    $psconn->enable_nondisclosure();
                }
    			$data=(array)$psconn->get_user_settings();
    			$PS_CFG_RED=$data['RED'];
    			$PS_CFG_YELLOW=$data['YLW'];
			}catch(moodle_exception $exce){
				return get_string('connectionfailed', 'plagiarism_plagaware');
			}
		}
		
        $pageurl = $PAGE->url;

        // Hack to fix the missing 'userid' when marking a single submission
        $test = $pageurl->out_omit_querystring();
        $cmp = '/mod/assignment/submissions.php';
        if (substr_compare($test, $cmp, -strlen($cmp)) == 0) {
            $params = $pageurl->params();
            if (array_key_exists('mode', $params)) {
                if ($params['mode'] == 'single') {
                    if (!array_key_exists('userid', $params)) {
                        $pageurl->param('userid', optional_param('userid', 0, PARAM_INT));
                    }
                    if (!array_key_exists('offset', $params)) {
                        $pageurl->param('offset', optional_param('offset', 0, PARAM_INT));
                    }
                    if (!array_key_exists('filter', $params)) {
                        $pageurl->param('filter', optional_param('filter', 0, PARAM_INT));
                    }
                }
            }
        }

        //create $message
        if (!$plagaware) {
            $message = get_string('notsubmitted', 'plagiarism_plagaware');
        } else {
            if ($plagaware->status >= plagaware_connection::STATUS_FAILED) {
                if ($plagaware->status == plagaware_connection::STATUS_FAILED_FILETYPE) {
                    $message = get_string('unsupportedfiletype', 'plagiarism_plagaware');
                } elseif ($plagaware->status == plagaware_connection::STATUS_FAILED_OPTOUT) {
                    $message = get_string('wasoptedout', 'plagiarism_plagaware');
                } else if ($plagaware->status == plagaware_connection::STATUS_FAILED_CONNECTION) {
                    $message = get_string('serverconnectionproblem', 'plagiarism_plagaware');
                } else { // STATUS_FAILED_UNKNOWN
                    $message = get_string('serverrejected', 'plagiarism_plagaware');
                }
            } else if ($plagaware->status != plagaware_connection::STATUS_FINISHED) {
                $message = get_string('notprocessed', 'plagiarism_plagaware');

                $checkurl = new moodle_url('/plagiarism/plagaware/checkstatus.php', array('id' => $plagaware->id, 'return' => urlencode($pageurl)));

                if ($viewlinksb) {
                    $message .= ' '.html_writer::link($checkurl, get_string('checkstatus', 'plagiarism_plagaware'));
                }
            } else {
                $percent = '';
                if (!is_null($plagaware->pstatus)) {
                    $percentclass = 'plagaware_good';
                    if ($plagaware->pstatus > ($PS_CFG_RED/10)) {
                        $percentclass = 'plagaware_bad';
                    } else if ($plagaware->pstatus > ($PS_CFG_YELLOW/10)) {
                        $percentclass = 'plagaware_warning';
                    }
                  // $percent = html_writer::tag('span', sprintf('%0.1f%%', $plagaware->pstatus), array('class' => $percentclass));
                }
                $matchurl = new moodle_url('/plagiarism/plagaware/report?' . $plagaware->pid);
                $psurl = new moodle_url('/plagiarism/plagaware/view?' . $plagaware->pid);
                $downloadurl =  new moodle_url('/plagiarism/plagaware/getdocx?' . $plagaware->pid);
                $newreport = new moodle_url('/plagiarism/plagaware/doc?' . $plagaware->pid);
                $message = '';
                
                if (($viewlinksb || has_capability('plagiarism/plagaware:viewfullreport', $context))) {
                    
                    $message .= html_writer::tag('span', sprintf('%0.1f%%', $plagaware->pstatus), array('class' => $percentclass));
                    $message .= "<label>".get_string('report_type', 'plagiarism_plagaware')."</label>";
                    $message .= "<div style='    margin-left: -8px;'>";
                    $message .= html_writer::link($matchurl, "<label style='background-image:url(".new moodle_url('/plagiarism/plagaware/images/docicons.png').");width: 32px;height: 24px;background-position-y: 73px;min-width: 25px;max-width: 25px;min-height: 24px;max-height: 24px;'></label>", array('target' => '_blank', 'alt'=> "".get_string('viewmatches', 'plagiarism_plagaware'), 'title'=> "".get_string('viewmatches', 'plagiarism_plagaware'), 'style'=>'float:left;'));                  
                    $message .= html_writer::link($psurl, "<label style='background-image:url(".new moodle_url('/plagiarism/plagaware/images/docicons.png').");width: 32px;height: 24px;background-position-y: 125px;min-width: 25px;max-width: 25px;min-height: 24px;max-height: 24px;'></label>",array('target' => '_blank', 'alt'=> "".get_string('psreport', 'plagiarism_plagaware'), 'title'=> "".get_string('psreport', 'plagiarism_plagaware'), 'style'=>'float:left;')); 
                    $message .= html_writer::link($downloadurl, "<label style='background-image:url(".new moodle_url('/plagiarism/plagaware/images/docicons.png').");width: 32px;height: 24px;background-position-y: 229px;min-width: 25px;max-width: 25px;min-height: 24px;max-height: 24px;'></label>",array('target' => '_blank', 'alt'=> "".get_string('downloadreport', 'plagiarism_plagaware'), 'title'=> "".get_string('downloadreport', 'plagiarism_plagaware'), 'style'=>'float:left;')); 
                    $message .= html_writer::link($newreport, "<label style='background-image:url(".new moodle_url('/plagiarism/plagaware/images/docicons.png').");width: 32px;height: 24px;background-position-y: 286px;min-width: 25px;max-width: 25px;min-height: 24px;max-height: 24px;'></label>",array('target' => '_blank', 'alt'=> "New Report (Beta)", 'title'=> "New Report (Beta)", 'style'=>'float:left;')); 
                    $message .= "</div>";
                    
                }else{
                    if(!$showlinks){
                        $message .= html_writer::tag('span', sprintf('%0.1f%%', $plagaware->pstatus), array('class' => $percentclass));
                    }else{
                        $message .= html_writer::tag('span', sprintf('%0.1f%%', $plagaware->pstatus), array('class' => $percentclass));
                        $message .= "<label>".get_string('report_type', 'plagiarism_plagaware')."</label>";
                        $message .= "<div style='    margin-left: -8px;'>";
                        $message .= html_writer::link($matchurl, "<label style='background-image:url(".new moodle_url('/plagiarism/plagaware/images/docicons.png').");width: 32px;height: 24px;background-position-y: 73px;min-width: 25px;max-width: 25px;min-height: 24px;max-height: 24px;'></label>", array('target' => '_blank', 'alt'=> "".get_string('viewmatches', 'plagiarism_plagaware'), 'title'=> "".get_string('viewmatches', 'plagiarism_plagaware'), 'style'=>'float:left;'));                  
                        $message .= html_writer::link($psurl, "<label style='background-image:url(".new moodle_url('/plagiarism/plagaware/images/docicons.png').");width: 32px;height: 24px;background-position-y: 125px;min-width: 25px;max-width: 25px;min-height: 24px;max-height: 24px;'></label>",array('target' => '_blank', 'alt'=> "".get_string('psreport', 'plagiarism_plagaware'), 'title'=> "".get_string('psreport', 'plagiarism_plagaware'), 'style'=>'float:left;')); 
                        $message .= html_writer::link($downloadurl, "<label style='background-image:url(".new moodle_url('/plagiarism/plagaware/images/docicons.png').");width: 32px;height: 24px;background-position-y: 229px;min-width: 25px;max-width: 25px;min-height: 24px;max-height: 24px;'></label>",array('target' => '_blank', 'alt'=> "".get_string('downloadreport', 'plagiarism_plagaware'), 'title'=> "".get_string('downloadreport', 'plagiarism_plagaware'), 'style'=>'float:left;')); 
                        $message .= html_writer::link($newreport, "<label style='background-image:url(".new moodle_url('/plagiarism/plagaware/images/docicons.png').");width: 32px;height: 24px;background-position-y: 286px;min-width: 25px;max-width: 25px;min-height: 24px;max-height: 24px;'></label>",array('target' => '_blank', 'alt'=> "New Report (Beta)", 'title'=> "New Report (Beta)", 'style'=>'float:left;')); 
                        $message .= "</div>";
                    }
                }
                $message .= html_writer::empty_tag('br');
            }
        }
        //END create $message

        //create $submit
        $submit = '';
        if ($plagawareenabled[$cmid] != self::RUN_AUTO && $plagawareenabled[$cmid] != self::RUN_ALL) {
            $submit = html_writer::empty_tag('br');
            $submiturl = new moodle_url('/plagiarism/plagaware/submit.php', array('user' => $userid,
                                                                                 'cmid' => $cmid,
                                                                                 'filehash' => $filehash,
                                                                                 'return' => urlencode($pageurl),
                                                                                 'sesskey' => sesskey()));
            if ($plagaware) {
                $submit .= html_writer::link($submiturl, get_string('resubmit', 'plagiarism_plagaware'));
            } else {
                $submit .= html_writer::link($submiturl, get_string('submit', 'plagiarism_plagaware'));
            }
        }
        //END create $submit



        $result = '';
        if ($viewreport || $viewlinksb || has_capability('plagiarism/plagaware:viewfullreport', $context)) {
            $result = ' ' . $message;
        }
        if ($viewlinksb) {
            $result .= $submit;
        }
        return $result;
    }

    /**
     * hook to allow status of submitted files to be updated - called on grading/report pages.
     *
     * @param object $course - full Course object
     * @param object $cm - full cm object
     */
    public function update_status($course, $cm) {
        global $PAGE, $DB;

        //called at top of submissions/grading pages - allows printing of admin style links or updating status
        $run = plagaware_get_instance_config($cm->id);

        if ($run->upload == self::RUN_NO) {
            return '';
        }

        $output = '';

        if ($run->upload == self::RUN_AUTO) {
            $modinfo = get_fast_modinfo($course);
            $cminfo = $modinfo->get_cm($cm->id);
            if ($cminfo->modname != 'assignment' && $cminfo->modname != 'assign') {
                // Not an assignment - auto submission to plagaware will not work
                $output .= get_string('onlyassignmentwarning', 'plagiarism_plagaware');
            } else {
                if ($cminfo->modname == 'assignment') {
                    $timedue = $DB->get_field('assignment', 'timedue', array('id' => $cm->instance));
                } else {
                    $timedue = $DB->get_field('assign', 'duedate', array('id' => $cm->instance));
                }
                if (!$timedue) {
                    // No deadline set - auto submission will never happen
                    $output .= get_string('nodeadlinewarning', 'plagiarism_plagaware');
                } else {
                    if ($timedue < $run->complete) {
                        $output .= get_string('autodescriptionsubmitted', 'plagiarism_plagaware', userdate($run->complete, get_string('strftimedatetimeshort')));
                    } else {
                        $output .= get_string('autodescription', 'plagiarism_plagaware');
                    }
                }
            }
            $output .= '<br/>';
        }

        $checkstatus = new moodle_url('/plagiarism/plagaware/checkstatus.php', array('cmid' => $cm->id,
                                                                                    'return' => urlencode($PAGE->url)));

        $output .= html_writer::link($checkstatus, get_string('checkallstatus', 'plagiarism_plagaware'));

        return $output;
    }

}

class plagaware_connection {

    const STATUS_NOT_STARTED = 0;
    const STATUS_WAITING = 1;
    const STATUS_ONGOING = 2;
    const STATUS_FINISHED = 3;
    const STATUS_QUEUED = 4;
    const STATUS_FAILED = 1000;
    const STATUS_FAILED_FILETYPE = 1001;
    const STATUS_FAILED_UNKNOWN = 1002;
    const STATUS_FAILED_OPTOUT = 1003;
    const STATUS_FAILED_CONNECTION = 1004;

    const REPORT_STATS = 0;
    const REPORT_LINKS = 1;
    const REPORT_SOURCES = 2;
    const REPORT_DOCX = 3;
    const REPORT_HTML = 4;
    const REPORT_MATCHES = 5;
    const REPORT_PS = 6;
    const NEW_REPORT = 7;
    //const REPORT_RESERVED = 7;
    const REPORT_PDFHTML = 8;
    const REPORT_PDFREPORT = 9;
    const REPORT_HIGHLIGHT = 25;
    const REPORT_GETSOURCE = 26;
     

    const SUBMIT_OK = 0;
    const SUBMIT_UNSUPPORTED = 1;
    const SUBMIT_OPTOUT = 2;

    protected $config;
    protected $username = -1;
    protected $nondisclosure = false;

    function __construct($notinstance = false) {
        $this->config = get_config('plagiarism_plagaware');
        if ($notinstance) {
            $this->username = false;
        }
    }

    public function get_user_settings_mapping() {
        return array('plagaware_language' => 'LANG',
                     'plagaware_email_policy' => 'EMAIL',
                     'plagaware_data_policy' => 'SHARE',
                     'plagaware_autodel' => 'DEL',
                     'plagaware_docx' => 'DOCX',
                     'plagaware_ssty' => 'SSTY',
                     'plagaware_red' => 'RED',
                     'plagaware_yellow' => 'YLW');
    }

    public function enable_nondisclosure() {
        $this->nondisclosure = true;
    }

    public function set_username($username) {
        $this->username = $username;
    }

    protected function get_username($adminuser = false, $originaluser = '', $tryfallback = false) {
        if ($adminuser) {
            return $this->config->plagaware_username; // Admin request - always send the admin user.
        }

        if (!empty($originaluser)) {
            return $originaluser; // File already has a user associated with it - use this.
        }

        if (empty($this->config->plagaware_multipleaccounts) && !$this->nondisclosure) {
            // Using the global user account.
            if (!$tryfallback) { // Fallback = try local account instead.
                return $this->config->plagaware_username;
            }
        }

        // Check the local username is OK
        if ($this->username === -1) {
            throw new coding_exception("Must call 'set_username' if associated with a particular instance");
        }

        if (empty($this->username)) {
            return $this->config->plagaware_username; // No local username - just return the global account.
        }

        if (!$tryfallback || empty($this->config->plagaware_multipleaccounts)) {
            return $this->username; // Default for 'use local username' + fallback for 'use global account'
        } else {
            return $this->config->plagaware_username; // Fallback for 'use local username'
        }
    }

    protected function get_basic_post_data($adminuser = false, $originaluser = '') {
        return array(
            "USER" => $this->get_username($adminuser, $originaluser),
            "KEY" => $this->config->plagaware_key,
            "VERSION" => $this->config->plagaware_version
        );
    }

    protected function set_file_status($plagaware, $status) {
        global $DB;

        $current = $DB->get_record('plagiarism_plagaware', array('userid' => $plagaware->userid,
                                                                'cmid' => $plagaware->cmid,
                                                                'filehash' => $plagaware->filehash));
        if ($current) {
            if ($status != $current->status) {
                $upd = new stdClass();
                $upd->id = $current->id;
                $upd->status = $status;
                $DB->update_record('plagiarism_plagaware', $upd);
            }
        } else {
            $plagaware->status = $status;
            $plagaware->pid = 0;
            $plagaware->id = $DB->insert_record('plagiarism_plagaware', $plagaware);
        }
    }

    public function update_module_status($cmid) {
        global $DB;

        $files = $DB->get_records_select('plagiarism_plagaware', 'cmid = ? AND (status != ? OR pstatus IS NULL)', array($cmid, self::STATUS_FINISHED));
        foreach ($files as $file) {
            $this->update_file_status($file);
        }
    }

    public function update_file_status($plagaware) {
        global $DB;

        if ($plagaware->status == self::STATUS_FINISHED && !is_null($plagaware->pstatus) && !$plagaware->updatestatus) {
            // Don't retrieve status if already finished
            return false;
        }

        if ($plagaware->pid <= 0) {
            // File was not submitted properly in the first place - skip it.
            return false;
        }
        $username = "";
        if (isset($plagaware->username) && get_config('plagiarism_plagaware', 'plagaware_multipleaccounts')) {        
            $username = $plagaware->username;
        }else{
            
        }
        $postdata = self::get_basic_post_data(false, $username);
        $postdata['METHOD'] = 'Retrieve';
        $postdata['PID'] = $plagaware->pid;
        $postdata['MODE'] = 0;

        $results = $this->post_to_host($postdata);
        if (isset($results['content']->MSG) && $results['content']->MSG == 'ERR') {
            $results = $this->resend_data_fallback_user($postdata);
        }
        $content = $results['content'];

        $pstatus = null;
        $status = self::STATUS_WAITING;
        if (intval($content->WAIT) >= 0) {
            $status = self::STATUS_FINISHED;
            if (empty($content->LEVEL)) {
                $pstatus = null;
            } else {
                $pstatus = floatval($content->LEVEL) / 10;
            }
        }

        if ($status == $plagaware->status && $pstatus == $plagaware->pstatus && !$plagaware->updatestatus) {
            return false; // Nothing has changed
        }

        $update = new stdClass();
        $update->status = $status;
        $update->pstatus = $pstatus;
        $update->pid = $plagaware->pid;
        $update->updatestatus = 0;

        if (isset($plagaware->id)) {
            $update->id = $plagaware->id;
            $DB->update_record('plagiarism_plagaware', $update);

            // Ready to be returned
            $update->userid = $plagaware->userid;
            $update->cmid = $plagaware->cmid;
            $update->filehash = $plagaware->filehash;

        } else {
            $update->userid = $plagaware->userid;
            $update->cmid = $plagaware->cmid;
            $update->filehash = $plagaware->filehash;
            $update->id = $DB->insert_record('plagiarism_plagaware', $update);
        }

        return true;
    }

    public function send_report($plagaware, $reporttype, $source = null) {
        global $CFG;

        $this->check_allowed_report($reporttype);

        $cmid = $plagaware->cmid;
        if ($CFG->version < 2011120100) {
            $context = get_context_instance(CONTEXT_MODULE, $cmid);
        } else {
            $context = context_module::instance($cmid);
        }
        if($reporttype==self::NEW_REPORT){
              $result = $this->retrieve_report($plagaware, $reporttype, $source);
              return $result;
        }
        // Find this report
        $fs = get_file_storage();
        $filearea = $this->file_area_from_type($reporttype);
        $files = $fs->get_area_files($context->id, 'plagiarism_plagaware', $filearea, $plagaware->id, 'sortorder', false);
        
        if ($reporttype == self::REPORT_HIGHLIGHT || $reporttype == self::REPORT_GETSOURCE) {
            $found = false;
        } else {
            $found = !empty($files);
        }  
        if ($found) {
            // Check the file doesn't contain an error in the first 100 characters.
            $file = reset($files);
            $fp = $file->get_content_file_handle();
            $filecontents = fread($fp, 100);
            if (strpos($filecontents, '<MSG>ERR</MSG>') !== false) {
                // File contains an error - delete it and download a new report.
                $fs->delete_area_files($context->id, 'plagiarism_plagaware', $filearea, $plagaware->id);
                $found = false;
            }
        }

        if (!$found) {
            // Report not found - download from plagaware server
            $this->retrieve_report($plagaware, $reporttype, $source);
            $files = $fs->get_area_files($context->id, 'plagiarism_plagaware', $filearea, $plagaware->id, 'sortorder', false);
        }
        $file = reset($files);

        // Send the report
        send_stored_file($file);
        $fs->delete_area_files($context->id, 'plagiarism_plagaware', $filearea, $plagaware->id);

    }

    protected function check_allowed_report($reporttype) {
        $allowedtypes = array(self::REPORT_DOCX, self::REPORT_HTML, self::REPORT_MATCHES, 
            self::REPORT_PS, self::REPORT_PDFHTML, self::REPORT_PDFREPORT, self::REPORT_HIGHLIGHT, self::REPORT_GETSOURCE, self::NEW_REPORT);
        if (!in_array($reporttype, $allowedtypes)) {
            throw new moodle_exception('incorrectreporttype', 'plagiarism_plagaware');
        }
    }

    protected function file_area_from_type($reporttype) {
        $mapping = array(self::REPORT_DOCX => 'reportdocx',
                         self::REPORT_HTML => 'reporthtml',
                         self::REPORT_MATCHES => 'reportmatches',
                         self::REPORT_PS => 'reportps',
                         self::REPORT_PDFHTML => 'reportpdfhtml',
                         self::REPORT_PDFREPORT => 'reportpdfreport',
                         self::REPORT_HIGHLIGHT => 'reporthighlight',
                         self::REPORT_GETSOURCE => 'reportgetsource',
                         self::NEW_REPORT => 'new_report');

        return $mapping[$reporttype];
    }

    protected function check_result($result) {
        if ($result['status'] != 'ok') {
            return false; // Bad HTTP code.
        }
        if (strpos(substr($result['content'], 0, 100), '<MSG>ERR</MSG>') !== false) {
            return false; // Content includes '<MSG>ERR</MSG>' within the first 100 characters.
        }
        return true;
    }

    protected function retrieve_report($plagaware, $reporttype, $source = null) {
        global $CFG;

        if ($plagaware->status != self::STATUS_FINISHED) {
            throw new moodle_exception('plagawareincomplete', 'plagiarism_plagaware');
        }

        $this->check_allowed_report($reporttype);
        $username = "";
        if(isset($plagaware->username)) {
            $username = $plagaware->username;
        }
        $postdata = $this->get_basic_post_data(false, $username);
        $postdata['METHOD'] = 'Retrieve';
        $postdata['PID'] = $plagaware->pid;
        $postdata['MODE'] = $reporttype;
        if ($source) {
            $postdata['SOURCE'] = intval($source);
        }
         if ($reporttype == self::NEW_REPORT) {
            $result = $this->post_to_host($postdata, null, false);   
            return $result;
        }
        
        $result = $this->post_to_host($postdata, null, false);
        if (!$this->check_result($result)) {
            if (!$plagaware->username) { // Try again with the alternative username.
                $result = $this->resend_data_fallback_user($postdata, false);
            }
            if (!$this->check_result($result)) {
                throw new moodle_exception('failedretrievereport', 'plagiarism_plagaware');
            }
        }    
        if ($reporttype == self::REPORT_DOCX) {
            $filename = array();
            if (!preg_match('/filename="([^"]*)"/', $result['header'], $filename)) {
                throw new moodle_exception('failedretrievereport', 'plagiarism_plagaware');
            }
            $filename = $filename[1];
        } elseif ($reporttype == self::REPORT_PDFREPORT) {
            $filename = 'PSreport_'.intval($plagaware->pid).'.pdf';
        } elseif ($reporttype == self::REPORT_PDFHTML) {
            $filename = 'PSview_'.intval($plagaware->pid).'.pdf';
        } else {
            $filename = 'plagaware_report.html';
        }

        $cmid = $plagaware->cmid;
        if ($CFG->version < 2011120100) {
            $context = get_context_instance(CONTEXT_MODULE, $cmid);
        } else {
            $context = context_module::instance($cmid);
        }

        $file = array(
            'filename' => $filename,
            'contextid' => $context->id,
            'component' => 'plagiarism_plagaware',
            'filearea' => $this->file_area_from_type($reporttype),
            'itemid' => $plagaware->id,
            'filepath' => '/'
        );

        $fs = get_file_storage();
        if ($reporttype == self::REPORT_HIGHLIGHT) {
            $fs->delete_area_files($context->id, 'plagiarism_plagaware', $this->file_area_from_type(self::REPORT_HIGHLIGHT), $plagaware->id);
        } elseif ($reporttype == self::REPORT_GETSOURCE) {
            $fs->delete_area_files($context->id, 'plagiarism_plagaware', $this->file_area_from_type(self::REPORT_GETSOURCE), $plagaware->id);
        }
        $fs->create_file_from_string($file, $result['content']);
    }

    protected function delete_reports($cmid, $pid = false) {
        global $CFG;

        if ($CFG->version < 2011120100) {
            $context = get_context_instance(CONTEXT_MODULE, $cmid);
        } else {
            $context = context_module::instance($cmid);
        }

        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'plagiarism_plagaware', $this->file_area_from_type(self::REPORT_DOCX), $pid);
        $fs->delete_area_files($context->id, 'plagiarism_plagaware', $this->file_area_from_type(self::REPORT_HTML), $pid);
        $fs->delete_area_files($context->id, 'plagiarism_plagaware', $this->file_area_from_type(self::REPORT_MATCHES), $pid);
        $fs->delete_area_files($context->id, 'plagiarism_plagaware', $this->file_area_from_type(self::REPORT_PS), $pid);
        $fs->delete_area_files($context->id, 'plagiarism_plagaware', $this->file_area_from_type(self::REPORT_PDFHTML), $pid);
        $fs->delete_area_files($context->id, 'plagiarism_plagaware', $this->file_area_from_type(self::REPORT_PDFREPORT), $pid);
        $fs->delete_area_files($context->id, 'plagiarism_plagaware', $this->file_area_from_type(self::REPORT_HIGHLIGHT), $pid);
        $fs->delete_area_files($context->id, 'plagiarism_plagaware', $this->file_area_from_type(self::REPORT_GETSOURCE), $pid);
    }

    public function submit_file($plagaware, $filedata) {
        global $DB;

        if (!plagaware_supported_filetype($filedata['filename'])) {
            $this->set_file_status($plagaware, self::STATUS_FAILED_FILETYPE);
            return self::SUBMIT_UNSUPPORTED; // Unsupported file type.
        }

        if (plagaware_user_opted_out($plagaware->userid)) {
            $this->set_file_status($plagaware, self::STATUS_FAILED_OPTOUT);
            return self::SUBMIT_OPTOUT; // User has opted-out of plagaware uploads.
        }

        // Delete any existing reports / records for this file
        $oldrecords = $DB->get_records('plagiarism_plagaware', array('cmid' => $plagaware->cmid,
                                                         'userid' => $plagaware->userid,
                                                         'filehash' => $plagaware->filehash), 'id');
        foreach ($oldrecords as $oldrecord) {
            $this->delete_reports($plagaware->cmid, $oldrecord->id);
            $username = "";
            if (isset($oldrecord->username)) {
                $username = $oldrecord->username;
            }
            if (!$this->delete_submitted_file($oldrecord->pid, $username)) {
                throw new moodle_exception('oldsubmissionnotdeleted', 'plagiarism_plagaware');
            }
            $DB->delete_records('plagiarism_plagaware', array('id' => $oldrecord->id));
        }

        // Send the file to the server
        $postdata = $this->get_basic_post_data();
        $postdata['METHOD'] = 'Submit';

        try {
            $result = $this->post_to_host($postdata, $filedata);
        } catch (moodle_exception $e) {
            $this->set_file_status($plagaware, self::STATUS_FAILED_CONNECTION);
            throw($e);
        }
        $content = $result['content'];

        if ($content->PID < 0) {
			$this->set_file_status($plagaware, self::STATUS_FAILED_UNKNOWN);
            return self::SUBMIT_UNSUPPORTED;
            //new code for Johannes and Ruben
            /*
            $this->set_file_status($plagaware, self::STATUS_FAILED_UNKNOWN);
            $info = new stdClass();
            $info->filename = $filedata['filename'];
            $info->content = $result['content'];
            throw new moodle_exception('invalidupload', 'plagiarism_plagaware', '', $info);
			*/
        }

        // Insert a new record for this file
        $plagaware->pid = intval($content->PID);
        $plagaware->status = self::STATUS_NOT_STARTED;
        $plagaware->pstatus = '';
        $plagaware->username = $postdata['USER'];
        $plagaware->id = $DB->insert_record('plagiarism_plagaware', $plagaware);

        return self::SUBMIT_OK;
    }

    public function delete_submitted_file($pid, $username = '') {

        $postdata = $this->get_basic_post_data(false, $username);
        $postdata['METHOD'] = 'Delete';
        $postdata['PID'] = $pid;

        $result = $this->post_to_host($postdata);
        $xml = $result['content'];
        $success = ($xml->MSG == 'ACK');

        if (!$success && !$username) {
            $result = $this->resend_data_fallback_user($postdata);
            $xml = $result['content'];
            $success = ($xml->MSG == 'ACK');
        }

        return $success;
    }

    public function submit_all_files($cmid, $modname,$plagawareuser) {
        global $CFG, $DB;
        if ($CFG->version < 2011120100) {
            $context = get_context_instance(CONTEXT_MODULE, $cmid);
        } else {
            $context = context_module::instance($cmid);
        }

        $success = true;
        $fs = get_file_storage();
        $submitted = $DB->get_records_select('plagiarism_plagaware', 'cmid = :cmid AND status < :statusfailed',
                                             array('cmid' => $cmid, 'statusfailed' => self::STATUS_FAILED), '', 'filehash');
        if ($modname == 'assignment') {
            $component = 'mod_assignment';
            $filearea = 'submission';
            /** @var $files stored_file[] */
            $files = $fs->get_area_files($context->id, $component, $filearea, false, 'sortorder', false);
            //var_dump($files);
        } else if ($modname == 'assign') {

            require_once($CFG->dirroot.'/mod/assign/locallib.php');

            $cm = get_coursemodule_from_id('assign', $cmid, 0, false, MUST_EXIST);
            $course = $DB->get_record('course', array('id' => $cm->course));
            $assign = new assign($context, $cm, $course);

            // Loop through all the submissions and ask the submission plugins to return a list of files.
            /** @var $plugins assign_submission_plugin[] */
            $plugins = $assign->get_submission_plugins();
            $files = array();
            
            
            $submissions = $DB->get_records('assign_submission', array('assignment' => $cm->instance));
 
            foreach ($submissions as $submission) {
                foreach ($plugins as $plugin) {
                    if (!$plugin->is_enabled() || !$plugin->is_visible()) {
                        continue;
                    }
                    $user= $submission;                  
                    foreach ($plugin->get_files($submission,$user) as $file) {
                        // Files are returned indexed by filename - which causes problems if different students submit
                        // files with the same name.
                        /** @var $file stored_file */
                         if(method_exists($file,'get_id'))
                        $files[$file->get_id()] = $file;
                     
                    }
                }
            }
            
        } else {
            
            throw new coding_exception("plagaware->submit_all_files called with unsupported module type: {$modname}");
        }
       
        foreach ($files as $file) {
            $filename = $file->get_filename();
            $userid = $file->get_userid();
            $pathnamehash = $file->get_pathnamehash();

            $plagaware = new stdClass();
            $plagaware->cmid = $cmid;
            $plagaware->userid = $userid;
            $plagaware->filehash = $pathnamehash;
            
             $oldrecords = $DB->get_records('plagiarism_plagaware', array('cmid' => $plagaware->cmid,
                'userid' => $plagaware->userid,
                'filehash' => $plagaware->filehash), 'id');
                $flag=false;
                 foreach ($oldrecords as $oldrecord) {
                        mtrace("plagaware: Looks like the file '$filename' for user $userid is already on plagaware server");
                        mtrace("plagaware: Ignoring to avoid duplicates\n");
                        $flag=true;
                    }
                 if($flag){
                     continue;
                 }
            if (array_key_exists($pathnamehash, $submitted)) {
                mtrace("plagaware: File '$filename' has already been submitted\n");
                continue; // This file has already been submitted.
            }        
            if (!plagaware_supported_filetype($filename)) {
                mtrace("plagaware: '$filename' is a type of file not supported by the plagaware server\n");
                $this->set_file_status($plagaware, self::STATUS_FAILED_FILETYPE);
                continue; // Don't try to submit unsupported file types.
            }
            if (plagaware_user_opted_out($userid)) {
                mtrace("plagaware: User $userid has opted-out of plagiarism detection - '$filename' not uploaded\n");
                $this->set_file_status($plagaware, self::STATUS_FAILED_OPTOUT);
                continue; // User has opted-out.
            }

            $filedata = array('filename' => $filename,
                              'content' => $file->get_content(),
                              'mimetype' => $file->get_mimetype());

            mtrace("plagaware: uploading file '$filename' for user $userid\n");

            try {
    
                $this->submit_file($plagaware, $filedata);
            } catch (moodle_exception $e) {
                mtrace($e->getMessage());
                $success = false;
            }
        }

        return $success;
    }

    public function get_user_settings() {
        $postdata = $this->get_basic_post_data();
        $postdata['METHOD'] = 'GetConfig';

        $result = $this->post_to_host($postdata);

        return $result['content'];
    }

    public function set_user_setting($field, $value) {
        // Send the setting to the plagaware server
        $postdata = $this->get_basic_post_data();
        $postdata['METHOD'] = 'setconfig';
        $postdata['PROPERTY'] = $field;
        $postdata['VALUE'] = $value;
			
        $result = $this->post_to_host($postdata);
			
        // Check the response was OK
        $header = explode(' ', $result['header']);
        if (count($header) < 3) {
            return false;
        }
        if (intval($header[1]) != 200) {
            return false;
        }

        $content = $result['content'];
        if ($content->MSG != 'ACK') {
            return false;
        }

        return true;
    }

    public function enable_auto_analysis(){
        return $this->set_user_setting('AUTO', 1);
    }

    public function post_to_host($postdata, $filedata = null, $parsecontent = true, $isretry = false) {
        $url = $this->config->plagaware_server;
        global $CFG;
        
        $data = "";
        $boundary = "---------------------".substr(md5(rand(0,32000)),0,10);
        // parse the given URL
        $url = parse_url($url);
 
        // extract host and path:
        $host = $url['host'];
        $path = null;
        if(isset($url['path'])){
        $path = $url['path'];
        }
        if($path==null){
            $path="/";
        }
        $scheme = $url['scheme'];

        $prefix = '';
        $port = 80;

        if (strtolower($scheme) == 'https' || strtolower($scheme) == 'ssl') { //modified to follow api documentation
            if(strtolower($scheme) == 'https'){
                $prefix = 'https://';
            }else{
                $prefix = 'ssl://';
            }
            $port = 443;
        }

        plagaware_log("======================================");
        
        // open a socket connection - timeout: 300 sec
        if (isset($CFG->proxyhost) && $CFG->proxyhost!='') { //connect via proxy if set in moodle
            $fp = fsockopen($CFG->proxyhost, $CFG->proxyport, $errno, $errstr, 300);
            plagaware_log("via proxy $CFG->proxyhost:$CFG->proxyport; $errno; $errstr; 300"); //log the proxy connection
            if(!$fp) {
                throw new moodle_exception('connectionfailed', 'plagiarism_plagaware');
            } else { //connection established, send requests via proxy
                fputs($fp, "POST {$prefix}{$host}:{$port}{$path} HTTP/1.0\n");
                fputs($fp, "Host: {$CFG->proxyhost}\n");
                plagaware_log("POST {$prefix}{$host}:{$port}{$path} HTTP/1.0");
                plagaware_log("Host: {$CFG->proxyhost}");
                if (isset($CFG->proxyuser)) { //use proxy authentication if set in moodle
                    fputs($fp, "Proxy-Authorization: Basic ".base64_encode("$CFG->proxyuser:$CFG->proxypassword") ."\n");
                    plagaware_log("Proxy-Authorization: Basic [[CREDENTIALS OMITTED]]\n"); //log the authenticated connection
                }
            }
        } else { //normal connection direct to plagaware server
            $fp = fsockopen($prefix.$host, $port, $errno, $errstr, 300);
            plagaware_log("{$prefix}{$host}; $port; $errno; $errstr; 300");
            if(!$fp) {
                fputs($fp, "POST $path HTTP/1.0\r\n");
                fputs($fp, "Host: $host\r\n");
                plagaware_log("POST $path HTTP/1.0");
                plagaware_log("Host: $host");
                throw new moodle_exception('connectionfailed', 'plagiarism_plagaware');
            } else { //connection established, send requests
                fputs($fp, "POST $path HTTP/1.0\r\n");
                fputs($fp, "Host: $host\r\n");
                plagaware_log("POST $path HTTP/1.0");
                plagaware_log("Host: $host");
            }
        }

        fputs($fp, "Content-type: multipart/form-data; boundary=".$boundary."\r\n");
        plagaware_log("Content-type: multipart/form-data; boundary=".$boundary);

        // Gather the post data together in a string
        foreach($postdata as $key => $val) {
            $data .= "--$boundary\n";
            $data .= "Content-Disposition: form-data; name=\"".$key."\"\r\n\r\n".$val."\r\n";
        }
        $data .= "--$boundary\n";
        $datawithoutfile = $data; // For logging purposes.
        
        // Gather the file data
        if ($filedata) {
            $data .= 'Content-Disposition: form-data; name="DATA"; filename="'.$filedata['filename']."\"\r\n";
            $data .= "Content-Type: ".$filedata['mimetype']."\r\n";
            $data .= "Content-Transfer-Encoding: binary\r\n\r\n";
            $datawithoutfile = $data;

            $data .= $filedata['content']."\r\n";
            $data .= "--$boundary--\n";

            $datawithoutfile .= "[[FILEDATA OMITTED]]\r\n";
            $datawithoutfile .= "--$boundary--\r\n";
        }

        // Send all the information
        plagaware_log("Content-length: ".strlen($data)."\r\n\r\n");
        plagaware_log($datawithoutfile);

        fputs($fp, "Content-length: ".strlen($data)."\r\n\r\n");
        fputs($fp, $data);

        // Read the result
        $result = '';
        while(!feof($fp)) {
            $result .= fread($fp, 1);
        }
        

        // close the socket connection:
        fclose($fp);

        plagaware_log("-------------------------------");
        if (empty($result)) {
            plagaware_log('No response from server');
        } else {
            plagaware_log($result);
        }

        $headers = explode('\r\n', $result, 2);
        $statusline = $headers[0];
        $statusline = explode(' ', $statusline, 3);
        if (count($statusline) < 2 || $statusline[1] != 200) {
            throw new moodle_exception('connectionfailed', 'plagiarism_plagaware');
        }
        // split the result header from the content
        $result = explode("\r\n\r\n", $result, 2);

        $header = isset($result[0]) ? $result[0] : '';
        $content = isset($result[1]) ? $result[1] : '';

        if ($content && $parsecontent) {
            try {
                $content = $this->parse_content($content);
            } catch (moodle_exception $e) {
                // If the user is not the main admin account, try adding the user.
                if (!$isretry && $this->get_username() !== $this->get_username(true)) {
                    if (!$this->add_user()) {
                        throw $e;
                    }
                    // Repeat the original request.
                    return $this->post_to_host($postdata, $filedata, $parsecontent, true);
                } else {
                    throw $e;
                }
            }
        }

        // return as structured array:
        return array(
            'status' => 'ok',
            'header' => $header,
            'content' => $content
        );
    }

    /**
     * Try sending the data again, but with the 'fallback' user ('local' user if configured for 'global' user, 'global'
     * user if configured to use the 'local' user.
     * @param array $postdata
     * @return array
     */
    protected function resend_data_fallback_user($postdata, $parsecontent = true) {
        $postdata['USER'] = $this->get_username(false, '', true);
        return $this->post_to_host($postdata, null, $parsecontent);
    }

    protected function add_user() {
        global $DB,$USER;

        $username = $this->get_username();

        $firstname = '';
        $lastname = '';
        
        if (isset($user->firstname) && isset($user->lastname)) {
            $firstname = $user->firstname;
            $lastname = $user->lastname;
        }else{
            $firstname = $USER->firstname;
            $lastname = $USER->lastname;
        }

        $postdata = $this->get_basic_post_data(true);
        $postdata['METHOD'] = 'AddUser';
        $postdata['USERNAME'] = $username;
        $postdata['EMAIL'] = $username;
        $postdata['FIRSTNAME'] = $firstname;
        $postdata['LASTNAME'] = $lastname;
        $postdata['SENDMAIL'] = 1;
        if (isset($this->config->plagaware_email_notification_account) && $this->config->plagaware_email_notification_account == 0){
            $postdata['SENDMAIL'] = $this->config->plagaware_email_notification_account;
        }
        $result = $this->post_to_host($postdata);

        $content = $result['content'];

        //enable autostart
        $this->set_username($username);
        $this->enable_auto_analysis();
        $this->set_user_setting("SHARE", 3);
        //END enable autostart

        //nondisclosure support
        if (isset($this->config->plagaware_nondisclosure_notice_email) && $this->config->plagaware_nondisclosure_notice_email == $username) {
            $this->set_user_setting("SHARE", 0);
        }
        //END nondisclosure support

        return ($content->user->UID != 0);
    }

    protected function add_nondisclosure_user() {

    }

    protected function parse_content($content) {
        global $PAGE;

        if (substr($content, 0, 1) !== '<') {
            $badcredentials = 'NO POST DATA OR WRONG CREDENTIALS.';
            if (substr_compare($content, $badcredentials, 0, strlen($badcredentials)) === 0) {
                $details = new stdClass();
                $details->user = $this->get_username();
                $details->version = $this->config->plagaware_version;
                throw new moodle_exception('badcredentials', 'plagiarism_plagaware', $PAGE->url, $details);
            } else {
                throw new moodle_exception('plagawareerror', 'plagiarism_plagaware', $PAGE->url, $content);
            }
        }
        $oldval = libxml_use_internal_errors(true);
        $result = simplexml_load_string($content);
        libxml_use_internal_errors($oldval);

        if (!$result) {
            throw new moodle_exception('plagawareerror', 'plagiarism_plagaware', $PAGE->url, $content);
        }
        return $result;
    }
}

function plagaware_supported_filetype($filename) {
    $allowedtypes = array('docx', 'doc', 'pdf', 'txt', 'html', 'wps', 'wpd',
                          'odt', 'ott', 'rtf', 'sdw', 'sxw', 'xml', 'pdb', 'ltx','pages' , 'key', 'numbers');
    $extn = pathinfo($filename, PATHINFO_EXTENSION);
    return in_array($extn, $allowedtypes);
}

function plagaware_set_instance_config($cmid, $data) {
    global $DB;

    $current = $DB->get_record('plagiarism_plagaware_config', array('cm' => $cmid));
    if ($current) {
        $data->id = $current->id;
        $DB->update_record('plagiarism_plagaware_config', $data);
    } else {
        $data->cm = $cmid;
        $DB->insert_record('plagiarism_plagaware_config', $data);
    }
}

function plagaware_get_instance_config($cmid) {
    global $DB;

    if ($config = $DB->get_record('plagiarism_plagaware_config', array('cm' => $cmid))) {
        return $config;
    }

    $default = new stdClass();
    $default->upload = plagiarism_plugin_plagaware::RUN_NO;
    $default->complete = 0;
    $default->username = '';
    $default->show_report_to_students = plagiarism_plugin_plagaware::SHOWSTUDENTS_NEVER;
    $default->show_students_links = plagiarism_plugin_plagaware::SHOWS_ONLY_PLVL;

    return $default;
}

function plagaware_log($data) {
    if (!defined('LOG_SERVER_COMMUNICATION')) {
        return;
    }

    global $CFG;

    $filename = 'plagaware.log';
    if (!$fp = fopen($filename, 'a')) {
        return;
    }

    fwrite($fp, date('j M Y H:i:s').' - '.$data."\r\n");
    fclose($fp);
}

function plagaware_user_opted_out($userid) {
    static $enabled = null;
    static $optout = array();

    if (is_null($enabled)) {
        $enabled = get_config('plagiarism_plagaware', 'plagaware_studentpermission');
    }
    if (!$enabled) {
        return false;
    }
    if (!isset($optout[$userid])) {
        $optout[$userid] = get_user_preferences('plagiarism_plagaware_optout', false, $userid);
    }

    return $optout[$userid];
}
