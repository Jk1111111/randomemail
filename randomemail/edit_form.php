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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/formslib.php');

/**
 * Form class for uploading CSV in the local_randomemail plugin..
 *
 * @package   local_randomemail
 * @copyright 2024, Gautam Shukla
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_randomemail_uploadcsvform extends moodleform {

    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'settingsheader', get_string('upload'));

        $url = new moodle_url('example.csv');
        $link = html_writer::link($url, 'example.csv');
        $mform->addElement('static', 'examplecsv', get_string('examplecsv', 'tool_uploaduser'), $link);
        $mform->addHelpButton('examplecsv', 'examplecsv', 'tool_uploaduser');

        $mform->addElement('filepicker', 'userfile', get_string('file'));
        $mform->addRule('userfile', null, 'required');

        $this->add_action_buttons(false, get_string('uploadusers', 'tool_uploaduser'));
    }
}
