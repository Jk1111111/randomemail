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

namespace local_randomemail;

/**
 * PHPUnit test for the local_randomemail plugin.
 *
 * @package   local_randomemail
 * @subpackage  Tests
 * @copyright 2024, Gautam
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_upload_test extends \advanced_testcase {
    /**
     * PHPUnit test function.
     *
     * @covers \local_randomemail\user_upload_test::test_useremail
     */
    public function test_useremail() {
        global $CFG, $DB, $SITE;

        // Create a user.
        $this->getDataGenerator()->create_user(
            [
                'email' => 'gautamshukla7570@gmail.com',
                'username' => 'user1',
                'firstname' => 'user1',
                'lastname' => 'shukla',
            ]
        );

        $filepath = $CFG->dirroot . '/local/randomemail/sample.csv';

        // Read the file contents.
        $filecontent = file_get_contents($filepath);

        // Now you can process the CSV content.
        $csvdata = array_map('str_getcsv', explode("\n", $filecontent));
        foreach ($csvdata as $line => $row) {
            if ($line == 0) {
                continue;
            }

            // Process each row.
            if (!isset($row[2]) || !$csvuser = $DB->get_record('user', ['email' => $row[2]])) {
                continue;
            }
            unset_config('noemailever');
            $sink = $this->redirectEmails();

            // Construct email parameters.
            $emailparams = [
                'subject' => 'Random Email',
                'body' => 'mail sent to ' . $csvuser->username,
                'text' => 'Hii,' . $csvuser->username,
            ];

            // Send email.
            email_to_user($csvuser, $SITE->shortname, $emailparams['subject'], $emailparams['body'], $emailparams['text']);

            $emails = $sink->get_messages();
            $this->assertCount(1, $emails);
            $sink->clear();
        }
    }

    protected function tearDown(): void {
        parent::tearDown();
        // Reset the database state here.
        $this->resetAfterTest();
    }
}
