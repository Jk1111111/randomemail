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
 * Languages configuration for the local_randomemail plugin.
 *
 * @package   local_randomemail
 * @copyright 2024, Gautam Shukla
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once('edit_form.php');
require_once($CFG->libdir . '/csvlib.class.php');
// Add this line after your other require_once statements.
require_once($CFG->dirroot . '/local/randomemail/classes/task/send_random_emails_task.php');

$iid = optional_param('iid', '', PARAM_INT);
$action = optional_param('action', '', PARAM_RAW);

require_login();

$PAGE->set_url('/local/randomemail/uploaduser.php');
$pagetitle = get_string('uploaduser', 'local_randomemail');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);

echo $OUTPUT->header();

echo $OUTPUT->single_button(new moodle_url('/local/randomemail/logs.php'), 'Logs', 'POST', ['class' => 'float-right']);

if (empty($iid)) {
    $uploadcsvform = new local_randomemail_uploadcsvform();

    if ($uploadcsvformdata = $uploadcsvform->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploaduser');
        $cir = new csv_import_reader($iid, 'uploaduser');

        $content = $uploadcsvform->get_file_content('userfile');

        $readcount = $cir->load_csv_content($content, 'UTF-8', 'comma');
        $csvloaderror = $cir->get_error();
        unset($content);

        if (!is_null($csvloaderror)) {
            throw new moodle_exception('csvloaderror', '', new moodle_url('/local/randomemail/uploaduser.php'), $csvloaderror);
        }
        // Continue to preview form.
    } else {
        $uploadcsvform->display();

        echo $OUTPUT->footer();
        die;
    }
} else {
    $cir = new csv_import_reader($iid, 'uploaduser');
}

// Test if columns ok.
$filecolumns = $cir->get_columns();

$previewtable = new \local_randomemail\preview($cir, $filecolumns);
if ($action == 'sendrandomemail') {
    $records = $previewtable->read_data();

    foreach ($records as $record) {
        if (!$user = $DB->get_record('user', ['email' => $record['email']])) {
            continue;
        }
        $randomemaildata = new stdClass();
        $randomemaildata->userid = $user->id;
        $randomemaildata->status = 0;
        $randomemaildata->timemailsent = 0;
        $randomemaildata->fromuserid = $USER->id;
        $randomemaildata->timecreated = time();

        $id = $DB->insert_record('local_randomemail', $randomemaildata);

        $task = new \local_randomemail\task\send_random_emails_task();
        \core\task\manager::queue_adhoc_task($task);
    }
    // Create and display the notification.
    $message = get_string('mailsentnotification', 'local_randomemail');;
    echo $OUTPUT->notification($message, 'notifysuccess');
} else {
    echo html_writer::tag('div', html_writer::table($previewtable), ['class' => 'flexible-wrap']);

    $sendrandomemailurl = new moodle_url($CFG->wwwroot . '/local/randomemail/uploaduser.php', [
        'iid' => $iid,
        'action' => 'sendrandomemail',
    ]);
    echo <<<HTML
    <form action="{$sendrandomemailurl}" method="POST">
        <input type="submit" value="Send Random Email">
    </form>
    HTML;
}

echo $OUTPUT->footer();
die;
