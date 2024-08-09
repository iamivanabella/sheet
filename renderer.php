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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Sheet question renderer class.
 *
 * @package    qtype
 * @subpackage sheet
 * @copyright  2024 Ivan Abella abellawebdev@gmail.com
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_sheet_renderer extends qtype_renderer {

    protected $displayoptions;

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {

        $question = $qa->get_question();
        $step = $qa->get_last_step_with_qt_var('spreadsheetdata');
        $this->displayoptions = $options;

        if (empty($options->readonly)) { // Student input mode
            $answer = $this->render_response_input($qa, $question, $step);
        } else { // Read-only mode (review)
            $answer = $this->render_response_readonly($qa, $question, $step); 
        }

        $result = html_writer::tag('div', $question->format_questiontext($qa), ['class' => 'qtext']); // Question text
        $result .= html_writer::tag('div', $answer, ['class' => 'answer']); // Answer area

        // Display validation error if the response is invalid
        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::tag('div', $question->get_validation_error($step->get_qt_data()), ['class' => 'validationerror']);
        }

        return $result;
    }

    private function render_response_input(question_attempt $qa, $question, $step) {
        $inputname = $qa->get_qt_field_name('spreadsheetdata');
        $id = 'id_spreadsheetdata';
        $data = $question->spreadsheetdata ?: json_encode(array_fill(0, 26, array_fill(0, 26, '')));

        $output = html_writer::tag('label', get_string('answer', 'qtype_sheet'), ['class' => 'sr-only', 'for' => $id]);
        $output .= html_writer::start_tag('div', ['class' => 'spreadsheet-container', 'style' => 'height: 400px;']);
        $output .= html_writer::tag('div', '', ['id' => 'spreadsheet-editor']);
        $output .= html_writer::end_tag('div');
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'id' => $id, 'name' => $inputname, 'value' => $data]);

        // Include Handsontable CSS from CDN
        $output .= html_writer::empty_tag('link', ['rel' => 'stylesheet', 'type' => 'text/css', 'href' => 'https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css']);
        
        // Include Handsontable JS from CDN
        $output .= html_writer::tag('script', '', ['src' => 'https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js']);
        $output .= html_writer::tag('script', '', ['src' => 'https://cdn.jsdelivr.net/npm/hyperformula/dist/hyperformula.full.min.js']);

        $output .= html_writer::tag('script', '
            document.addEventListener("DOMContentLoaded", function() {
                console.log("Initializing Handsontable...");

                var container = document.getElementById("spreadsheet-editor");
                var dataElement = document.getElementById("id_spreadsheetdata");
                var data = dataElement ? dataElement.value : "";

                if (data === "") {
                    data = JSON.stringify(Array(26).fill(Array(26).fill("")));
                }

                console.log(data);

                try {
                    var hot = new Handsontable(container, {
                        data: JSON.parse(data),
                        rowHeaders: true,
                        colHeaders: true,
                        width: "100%",
                        height: "100%",
                        rowCount: 26,
                        colCount: 26,
                        dropdownMenu: true,
                        contextMenu: true,
                        formulas: {
                            engine: HyperFormula
                        },
                        licenseKey: "non-commercial-and-evaluation",
                        afterChange: function(changes, source) {
                            if (source !== "loadData") {
                                data = JSON.stringify(hot.getData());
                                dataElement.value = data; // Update hidden input value
                            }
                        }
                    });
                } catch (error) {
                    console.error("Error initializing Handsontable:", error);
                }
            });
        ');

        return $output;
    }

    private function render_response_readonly(question_attempt $qa, $question, question_attempt_step $step) {
        $labelbyid = $qa->get_qt_field_name('spreadsheetdata') . '_label';
        $answer = $step->get_qt_var('spreadsheetdata') ?: json_encode(array_fill(0, 10, array_fill(0, 10, '')));

        $output = html_writer::tag('h4', get_string('answer', 'qtype_sheet'), ['id' => $labelbyid, 'class' => 'sr-only']);
        $output .= html_writer::tag('div', format_text($answer, FORMAT_HTML), [
            'role' => 'textbox',
            'aria-readonly' => 'true',
            'aria-labelledby' => $labelbyid,
            'class' => 'qtype_sheet_response readonly',
            'style' => 'min-height: 15em;',
        ]);
        return $output;
    }
}