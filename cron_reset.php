<?php


require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/plagiarism/plagaware/lib.php');
global $CFG, $DB;

        
        // Make sure multiple plagaware cron sessions don't overlap (as uploads could take a long time).

        $running = get_config('plagiarism_plagaware', 'plagaware_cronrunning');
        
        set_config('plagaware_cronrunning', 0, 'plagiarism_plagaware');
           
        $msg = get_string('cron_reset', 'plagiarism_plagaware');
        redirect("./settings.php", $msg, 2);
        