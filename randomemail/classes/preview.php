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
 * Display the preview of a CSV file
 *
 * @package     local_randomemail
 * @copyright   2024, Gautam Shukla
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class preview extends \html_table {
    /** @var \csv_import_reader */
    protected $cir;

    /** @var array */
    protected $filecolumns;

    /**
     * Constructor.
     *
     * @param \csv_import_reader $cir          CSV import reader object.
     * @param array              $filecolumns  Array of file columns.
     */
    public function __construct(\csv_import_reader $cir, array $filecolumns) {
        parent::__construct();
        $this->cir = $cir;
        $this->filecolumns = $filecolumns;

        // Initialize table properties.
        $this->id = "randomemailpreview";
        $this->attributes['class'] = 'generaltable';

        // Initialize table head.
        $this->head = [];
        $this->data = $this->read_data();

        // Add headers.
        $this->head[] = get_string('uucsvline', 'local_randomemail');
        foreach ($filecolumns as $column) {
            $this->head[] = $column;
        }
        // Add extra column.
        $this->head[] = get_string('status', 'local_randomemail');
    }

    /**
     * Read data.
     *
     * @return array Processed data.
     */
    public function read_data() {
        global $DB;

        $data = [];
        $this->cir->init();
        // Column header is first line.
        $linenum = 1;
        while ($fields = $this->cir->next()) {
            $linenum++;
            $rowcols = [];
            $rowcols['line'] = $linenum;
            foreach ($fields as $key => $field) {
                $rowcols[$this->filecolumns[$key]] = s(trim($field));
            }

            // Initialize status.
            $rowcols['status'] = '';

            if (isset($rowcols['email'])) {
                if (!validate_email($rowcols['email'])) {
                    $rowcols['status'] = get_string('invalidemail');
                } else {
                    // Check if email exists in the user table.
                    $emailexists = $DB->record_exists('user', ['email' => $rowcols['email']]);
                    if ($emailexists) {
                        $rowcols['status'] = get_string('userfound', 'local_randomemail');
                    } else {
                        $rowcols['status'] = get_string('usernotfound', 'local_randomemail');
                    }
                }
            }

            $data[] = $rowcols;
        }
        if ($fields = $this->cir->next()) {
            $data[] = array_fill(0, count($fields) + 2, '...');
        }
        $this->cir->close();

        return $data;
    }
}
