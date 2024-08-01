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
        global $PAGE;

        // Ensure CSS and JS files are included in the head section.
        $PAGE->requires->css('/question/type/sheet/style/handsontable.full.css');
        $PAGE->requires->js(new moodle_url('/question/type/sheet/amd/src/handsontable.full.js'));
        $PAGE->requires->js(new moodle_url('/question/type/sheet/amd/src/hyperformula.full.js'));
        $PAGE->requires->js_call_amd('qtype_sheet/handsontable_init', 'init');

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
        $data = $question->spreadsheetdata ?: json_encode(array_fill(0, 10, array_fill(0, 10, '')));

        $output = html_writer::tag('label', get_string('answer', 'qtype_sheet'), ['class' => 'sr-only', 'for' => $id]);
        $output .= html_writer::tag('div', '', ['id' => 'spreadsheet-editor', 'class' => 'form-control', 'style' => 'width: 600px; height: 300px;']);
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'id' => $id, 'name' => $inputname, 'value' => $data]);

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