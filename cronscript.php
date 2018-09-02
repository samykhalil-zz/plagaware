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
 * automatically submit a files to plagaware for analysis
 * (when assignment deadline is reached)
 *
 * @since 2.0
 * @package    plagiarism_plagaware
 * @author     Ruben Olmedo based on work by Davo Smith
 * @copyright  @2012 Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
//require_once(dirname(__FILE__) . '/../../config.php');
//require_once($CFG->dirroot.'/plagiarism/plagaware/lib.php');
global $DB, $CFG;
mtrace("Starting the plagaware cron");
// Find all the 'assignment' modules with 'autostart' enabled, where the deadline has passed and the
// last time anything was automatically uploaded to the server was before the deadline
 $assigns=Array();
// Repeat the above for the 'assign' module.
if ($CFG->version >= 2012062500) { // Moodle 2.3+ only
		
	/*$sql= "SELECT cm.id AS cmid, pc.id AS configid, a.name, pc.username, 'assign' AS modname
			FROM {course_modules} cm, {modules} m, {plagiarism_plagaware_config} pc, {assign} a
			WHERE (cm.module = m.id AND m.name = 'assign' AND cm.id = pc.cm AND a.id = cm.instance) AND pc.upload = :runauto";    
	*/	
 $sql='';
 if($CFG->dbtype=="mysql" || $CFG->dbtype=="mysqli"){
     
     mtrace("Detected MySQL Database");
     $sql="SELECT cm.id AS cmid, pc.id AS configid, a.name, pc.username, 'assign' AS modname
			FROM {course_modules} cm, {modules} m, {plagiarism_plagaware_config} pc, {assign} a
			WHERE (cm.module = m.id AND m.name = 'assign' AND cm.id = pc.cm AND a.id = cm.instance)                               
                        AND (
                              (
                                pc.upload = :runauto  
                                    AND ( 
                                            (a.duedate < :now AND pc.complete < a.duedate  AND a.cutoffdate = 0 AND a.duedate > 0)   
                                        OR    
                                            ( :now2 > a.cutoffdate AND pc.complete < a.cutoffdate AND a.cutoffdate > 0)  
                                        ) 
                              OR 
                                pc.upload = :always
                                
                              OR 
                                ( pc.upload = :due AND (a.cutoffdate > :now3 OR a.cutoffdate = 0 ) AND a.duedate < :now4 )
                               )
                            ) 
                                 GROUP BY cmid; "; 
 }else{
     mtrace("Not MySQL Database");
     $sql="SELECT cm.id AS cmid, pc.id AS configid, a.name, pc.username, 'assign' AS modname
			FROM {course_modules} cm, {modules} m, {plagiarism_plagaware_config} pc, {assign} a
			WHERE (cm.module = m.id AND m.name = 'assign' AND cm.id = pc.cm AND a.id = cm.instance)                               
                        AND (
                              (
                                pc.upload = :runauto  
                                    AND ( 
                                            (a.duedate < :now AND pc.complete < a.duedate  AND a.cutoffdate = 0 AND a.duedate > 0)   
                                        OR    
                                            ( :now2 > a.cutoffdate AND pc.complete < a.cutoffdate AND a.cutoffdate > 0)  
                                        ) 
                              OR 
                                pc.upload = :always
                                
                              OR 
                                ( pc.upload = :due AND (a.cutoffdate > :now3 OR a.cutoffdate = 0 ) AND a.duedate < :now4 )
                               )
                            ) 
                                 GROUP BY cmid, configid, a.name, pc.username, modname "; 
    
 }
					
	
   $assigns = $DB->get_records_sql($sql, array('runauto' => plagiarism_plugin_plagaware::RUN_AUTO, 'now' => time(),'now2' => time(),'always' =>plagiarism_plugin_plagaware::RUN_ALL,'due'=>plagiarism_plugin_plagaware::RUN_DUE,'now3'=> time(),'now4'=> time()));

    mtrace("looking in assings ");
    // Merge the results 
}
	$sql = "SELECT cm.id AS cmid, pc.id AS configid, a.name, pc.username, 'assignment' AS modname
          FROM {course_modules} cm
          JOIN {modules} m
             ON cm.module = m.id
             AND m.name = 'assignment'
          JOIN {plagiarism_plagaware_config} pc
             ON cm.id = pc.cm
             AND pc.upload = :runauto OR pc.upload = :runal
          JOIN {assignment} a
             ON a.id = cm.instance
         WHERE (a.timedue < :now AND pc.complete < a.timedue AND pc.upload = :runauto2) OR (pc.upload = :runal2)";
      $assignments = $DB->get_records_sql($sql, array('runauto' => plagiarism_plugin_plagaware::RUN_AUTO, 'now' => time(),'runal' => plagiarism_plugin_plagaware::RUN_ALL,'runauto2' => plagiarism_plugin_plagaware::RUN_AUTO,'runal2' => plagiarism_plugin_plagaware::RUN_ALL));	
      mtrace("looking in assingment");
   
     
  $assignments = array_merge($assignments, $assigns);
$connect = null;
if (empty($assignments)) {
    mtrace("No assignments are due for automatic upload to plagaware");
} else {
    $connect = new plagaware_connection();
    foreach ($assignments as $assignment) {
        $connect->set_username($assignment->username);
        mtrace("plagaware: uploading all submitted files for assignment '{$assignment->name}'\n");
        
        if ($connect->submit_all_files($assignment->cmid, $assignment->modname,$assignment->username)) {
            $upd = new stdClass();
            $upd->id = $assignment->configid;
            $upd->complete = time();
            $DB->update_record('plagiarism_plagaware_config', $upd);
            mtrace("plagaware: All files for assignment '{$assignment->name}' uploaded successfully\n");
        }
        
    }
}

// Check for any files that have been updated on the plagaware server and retrieve their details
$sql = 'SELECT p.*, pc.username
          FROM {plagiarism_plagaware} p
          JOIN {plagiarism_plagaware_config} pc ON p.cmid = pc.cm
         WHERE p.updatestatus = 1';
$files = $DB->get_records_sql($sql);
if (empty($files)) {
    mtrace("No new reports on plagaware server");
} else {
    mtrace("Trying to connect to plagaware");
    if (is_null($connect)) {
        $connect = new plagaware_connection();
    }
    $count = 0;
        $individual=true;
        if (!get_config('plagiarism_plagaware', 'plagaware_multipleaccounts')){
            $individual=false;
        }
    foreach ($files as $file) {
            if($individual){
                $connect->set_username($file->username);
            }else{
                $connect->set_username(get_config('plagiarism_plagaware','plagaware_username'));
            }
        try{
        if ($connect->update_file_status($file)) {
            $count++;
        }
        }  catch (plagaware_credentials_exception $e){
             mtrace("There is a bad credentials error for the user ".$file->username);
        }
    }
    mtrace("plagaware: updated details for $count file(s)");
}