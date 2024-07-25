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
        // Add Handsontable container
        $mform->addElement('html', '<div id="spreadsheet-editor"></div>');

        // Spreadsheet options (replace with your actual options)
        $spreadsheetOptions = array(
            'blank' => get_string('blank_spreadsheet', 'qtype_sheet'),
            'preformatted' => get_string('preformatted_spreadsheet', 'qtype_sheet'),
        );
        $mform->addElement('select', 'spreadsheettype', get_string('spreadsheettype', 'qtype_sheet'), $spreadsheetOptions);

        // Hidden field to store pre-formatted spreadsheet data (if applicable)
        $mform->addElement('hidden', 'preformatteddata');
        $mform->setType('preformatteddata', PARAM_RAW); // Allow HTML content

        // Grading options (for manual grading)
        // ... (add your existing grading options here if needed)

        // Handsontable initialization (in JavaScript)
        $mform->addElement('html', '<script>
            var container = document.getElementById("spreadsheet-editor");
            var hot = new Handsontable(container, {
                data: [], // Initial data (empty or pre-formatted based on selection)
                rowHeaders: true,
                colHeaders: true,
                formulas: true, // Enable formulas
                licenseKey: "non-commercial-and-evaluation",
            });
        </script>');
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        // Check if options exist and response template is set
        if (isset($question->options) && isset($question->options->responsetemplate)) {
            $question->responsetemplate = array(
                'text' => $question->options->responsetemplate,
                'format' => $question->options->responsetemplateformat,
            );
        } else {
            // Set default response template if none exists (for new questions)
            $question->responsetemplate = array(
                'text' => '', // or a default template if desired
                'format' => FORMAT_HTML,
            );
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
