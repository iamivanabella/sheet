<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Question type class for sheet is defined here.
 *
 * @package     qtype_sheet
 * @copyright   2024 Ivan Abella abellawebdev@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/questionlib.php');

/**
 * Class that represents a sheet question type.
 *
 * The class loads, saves and deletes questions of the type sheet
 * to and from the database and provides methods to help with editing questions
 * of this type. It can also provide the implementation for import and export
 * in various formats.
 */
class qtype_sheet extends question_type {

    // Override functions as necessary from the parent class located at
    // /question/type/questiontype.php.

    public function is_manual_graded() {
        return true;
    }

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('qtype_sheet_options', 
            array('questionid' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    public function save_question_options($formdata) {
        global $DB;

        $options = new stdClass();
        $options->questionid = $formdata->id;
        $options->responseformat = $formdata->spreadsheetdata;  

        // Insert or update the options record in the database
        if ($DB->record_exists('qtype_sheet_options', ['questionid' => $formdata->id])) {
            $options->id = $DB->get_field('qtype_sheet_options', 'id', ['questionid' => $formdata->id]);
            $DB->update_record('qtype_sheet_options', $options);
        } else {
            $DB->insert_record('qtype_sheet_options', $options);
        }
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $question->spreadsheetdata = $questiondata->options->spreadsheetdata;
    }

    public function delete_question($questionid, $contextid) {
        global $DB;

        $DB->delete_records('qtype_sheet_options', array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid); 
    }
}