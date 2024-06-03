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
 * random email logs.
 *
 * @package   local_randomemail
 * @copyright 2024, Gautam Shukla
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
global $DB;

// Set up page.
$url = new moodle_url('/local/randomemail/logs.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$pagetitle = get_string('pluginname', 'local_randomemail');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

echo $OUTPUT->header();
echo $OUTPUT->single_button(new moodle_url('/local/randomemail/uploaduser.php'), 'Back', 'POST', ['class' => 'float-right']);
$page = optional_param('page', 0, PARAM_INT);
$perpage = 10;
$offset = $page * $perpage;
$sql = "SELECT e.status,e.timemailsent,u.firstname,u.lastname,u.email
  FROM {local_randomemail} e
  JOIN {user} u ON e.userid = u.id
 WHERE e.status = 1
       AND e.timemailsent IS NOT NULL
 ORDER BY e.timemailsent DESC";
$rs = $DB->get_recordset_sql($sql, null, $offset, $perpage);

// Check if records exist.
if ($rs->valid()) {
    // Set up table.
    $table = new html_table();
    $table->head = [
        get_string('firstname', 'local_randomemail'),
        get_string('lastname', 'local_randomemail'),
        get_string('email', 'local_randomemail'),
        get_string('timemailsent', 'local_randomemail'),
    ];
    foreach ($rs as $row) {
        // Format timemailsent as date.
        $timemailsentdate = date('j F Y, g:i:s A', $row->timemailsent);

        $table->data[] = [
            $row->firstname,
            $row->lastname,
            $row->email,
            $timemailsentdate,
        ];
    }

    echo html_writer::table($table);

    $totalcount = $DB->count_records_sql("SELECT COUNT(*) FROM {local_randomemail} e WHERE e.status = 1
                                              AND e.timemailsent IS NOT NULL");
    $paging = new paging_bar($totalcount, $page, $perpage, $url);
    echo $OUTPUT->render($paging);
} else {
    echo get_string('norecords', 'local_randomemail');
}
$rs->close();
echo $OUTPUT->footer();
