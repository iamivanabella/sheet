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
 * The editing form for sheet question type is defined here.
 *
 * @package     qtype_sheet
 * @copyright   2024 Ivan Abella abellawebdev@gmail.com
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * sheet question editing form defition.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/edit_question_form.php.
 */
class qtype_sheet_edit_form extends question_edit_form {

    protected function definition_inner($mform) {
        global $PAGE; // Ensure $PAGE is available

        // Add Handsontable container
        $mform->addElement('html', '<div id="spreadsheet-editor"></div>');

        // Hidden field to store spreadsheet data
        $mform->addElement('hidden', 'spreadsheetdata');
        $mform->setType('spreadsheetdata', PARAM_RAW); // Allow HTML content
        $mform->getElement('spreadsheetdata')->updateAttributes(array('id' => 'id_spreadsheetdata'));

        // Include Handsontable CSS and JS files
        $PAGE->requires->css(new moodle_url('/question/type/sheet/amd/build/handsontable.full.min.css'));
        $PAGE->requires->js(new moodle_url('/question/type/sheet/amd/build/handsontable.full.min.js'));
        $PAGE->requires->js(new moodle_url('/question/type/sheet/amd/build/hyperformula.full.min.js'));

        // Include Handsontable initialization script
        $PAGE->requires->js_call_amd('qtype_sheet/handsontable_init', 'init', array());
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        // Check if options exist and response template is set
        if (isset($question->options) && isset($question->options->spreadsheetdata)) {
            $question->spreadsheetdata = $question->options->spreadsheetdata;
        } else {
            // Set default response template if none exists (for new questions)
            $question->spreadsheetdata = json_encode(array_fill(0, 20, array_fill(0, 20, '')));
        }

        return $question;
    }

    /**
     * Returns the question type name.
     *
     * @return string The question type name.
     */
    public function qtype() {
        return 'sheet';
    }
}
