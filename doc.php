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
 *
 * @package     plagiarism_plagaware
 * @subpackage  plagiarism
 * @author      Ruben Olmedo  <rolmedo@plagaware.com>
 * @copyright   2016 plagaware GmbH {@link https://www.plagaware.com/}
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/plagiarism/plagaware/lib.php');
global $CFG, $DB;

function getXMLtag($key, $xmldata, $start) {
        $startpos = stripos($xmldata, "<" . $key . ">", $start);
        if (!$startpos){ //second chance
            $startpos = stripos($xmldata, "<" . $key . " ", $start);
        }
        if (!$startpos){
            return $startpos;
        }
        $startpos = stripos($xmldata, ">", $startpos);
        if (!$startpos){
            return $startpos;
        }
        $lastPos = $startpos + 2;
        return substr($xmldata, $startpos + 1, stripos($xmldata, "</" . $key . ">", $startpos + 1) - $startpos - 1);
 }

$pid = intval($_SERVER["QUERY_STRING"]);
$type = 7;

require_login();

$plagaware = $DB->get_record('plagiarism_plagaware', array('pid' => $pid), '*', MUST_EXIST);

$cmid = $plagaware->cmid;
if ($CFG->version < 2011120100) {
    $context = get_context_instance(CONTEXT_MODULE, $cmid);
} else {
    $context = context_module::instance($cmid);
}
if (!(has_capability('plagiarism/plagaware:viewfullreport', $context) || has_capability('plagiarism/plagaware:control', $context))) {
    $instanceconfig = plagaware_get_instance_config($cmid);
    if ($instanceconfig->show_students_links != plagiarism_plugin_plagaware::SHOWS_LINKS){
        throw new moodle_exception('Permission denied! You do not have the right capabilities.', 'plagiarism_plagaware');
    }    
}

if (!get_config('plagiarism', 'plagaware_use')) {
    // Disabled at the site level
    print_error('disabledsite', 'plagiarism_plagaware');
}

$instanceconfig = plagaware_get_instance_config($cmid);
if ($instanceconfig->upload == plagiarism_plugin_plagaware::RUN_NO) {
    // Disabled for this module
    print_error('disabledmodule', 'plagiarism_plagaware');
}

$connection = new plagaware_connection();
$connection->set_username($instanceconfig->username);

echo '<body style="font-family:sans-serif;" align="center">
		<div style="margin:120px 0px 30px 0px;">
        <img src="'.new moodle_url("/plagiarism/plagaware/images/plagaware_logo.png").'" width=250px;>
        </div>
        <div style="margin-bottom: 30px;">
        <p>Please wait, we are generating the link</p>
		</div>
        <img src="'.new moodle_url("/plagiarism/plagaware/images/loader.gif").'">
<div style="margin-bottom: 30px;">
	 <p>You would be redirect automaticaly</p>
        </div>';

$report = $connection->send_report($plagaware, $type);
$xml=$report["content"];

$reportlink = getXMLtag("REPORTLINK", $xml, 0);

//$res = explode("http",$report["content"]);
//for ($i = 0; $i<sizeof($res); $i++){
//    
//    if(strpos($res[$i], "s://www.plagaware.com")===0){
//        $url="http".$res[$i];
//        break;
//    }
//    
//}

 echo "<meta http-equiv=\"refresh\" content=\"3;URL='".$reportlink."\" />"; 
echo '</body>';