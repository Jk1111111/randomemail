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

namespace local_randomemail\task;

/**
 * Class send_random_emails_task
 *
 * @package   local_randomemail
 * @copyright 2024, Gautam shukla
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class send_random_emails_task extends \core\task\adhoc_task {
    /**
     * Returns the name of the task.
     *
     * @return string
     */
    public function get_name() {
        return get_string('sendrandomemail', 'local_randomemail');
    }

    /**
     * Executes the task.
     */
    public function execute() {
        global $DB, $SITE;
        $records = $DB->get_records('local_randomemail', ['status' => 0]);
        foreach ($records as $record) {
            $user = $DB->get_record('user', ['id' => $record->userid]);
            if ($user) {
                // Construct email parameters.
                $emailparams = [
                    'subject' => get_string('email_subject', 'local_randomemail'),
                    'body' => get_string('email_body', 'local_randomemail', $user->username),
                    'text' => get_string('email_text', 'local_randomemail', $user),
                ];
                // Send email.
                email_to_user($user, $SITE->shortname, $emailparams['subject'], $emailparams['body'], $emailparams['text']);

                // Update record status after sending email.
                $record->status = 1;
                $record->timemailsent = time();
                $DB->update_record('local_randomemail', $record);
            }
        }
    }
}
