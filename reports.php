<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/plagiarism/plagaware/lib.php');
global $CFG, $DB;

$id = required_param('id', PARAM_INT);
$type = optional_param('type', plagaware_connection::REPORT_HTML, PARAM_INT);

require_login();

$plagaware = $DB->get_record('plagiarism_plagaware', array('id' => $id), '*', MUST_EXIST);

$cmid = $plagaware->cmid;
if ($CFG->version < 2011120100) {
    $context = get_context_instance(CONTEXT_MODULE, $cmid);
} else {
    $context = context_module::instance($cmid);
}
require_capability('plagiarism/plagaware:viewlinks', $context);

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
$connection->send_report($plagaware, $type);
