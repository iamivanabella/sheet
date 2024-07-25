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
        $step = $qa->get_last_step_with_qt_var('answer');
        $this->displayoptions = $options;

        // Display response template if available and question is in readonly mode
        $responsetemplate = '';
        if (!empty($question->responsetemplate) && !$step->has_qt_var('answer') && empty($options->readonly)) {
            $responsetemplate = format_text($question->responsetemplate, $question->responsetemplateformat);
            $step = new question_attempt_step(['answer' => $responsetemplate]);
        }

        if (empty($options->readonly)) { // Student input mode
            $answer = $this->render_response_input($qa, $question->responsefieldlines, $responsetemplate);
        } else { // Read-only mode (review)
            $answer = $this->render_response_readonly($qa, $question->responsefieldlines, $step); 
        }

        $result = html_writer::tag('div', $question->format_questiontext($qa), ['class' => 'qtext']); // Question text
        $result .= html_writer::tag('div', $answer, ['class' => 'answer']); // Answer area

        // Display validation error if the response is invalid
        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::tag('div', $question->get_validation_error($step->get_qt_data()), ['class' => 'validationerror']);
        }

        return $result;
    }

    private function render_response_input(question_attempt $qa, $lines, $responsetemplate) {
        global $CFG;
        require_once($CFG->dirroot . '/repository/lib.php');
        
        $inputname = $qa->get_qt_field_name('answer');
        $id = $inputname . '_id';
        $responseformat = $qa->get_last_qt_var('answerformat') ?: FORMAT_HTML;
        $responselabel = get_string('answer', 'qtype_sheet');
    
        $editor = editors_get_preferred_editor($responseformat);
        $editor->use_editor($id, ['context' => $this->page->context, 'autosave' => false]); // Initialize the editor
    
        $output = html_writer::tag('label', $responselabel, ['class' => 'sr-only', 'for' => $id]);

        // Directly render the textarea:
        $output .= html_writer::tag('textarea', s($responsetemplate . $qa->get_last_qt_var('answer')),
                ['id' => $id, 'name' => $inputname, 'rows' => $lines, 'cols' => 60, 'class' => 'form-control qtype_sheet_response']); 
    
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => $inputname . 'format', 'value' => $responseformat]);
    
        return $output;
    }

    private function render_response_readonly(question_attempt $qa, $lines, question_attempt_step $step) {
        $labelbyid = $qa->get_qt_field_name('answer') . '_label';
        $responselabel = get_string('answer', 'qtype_sheet');
        // Check if there is an actual answer 
        $answer = $qa->get_last_qt_var('answer');
        if (!$step->has_qt_var('answer')) {
            $answer = '';
        }

        $output = html_writer::tag('h4', $responselabel, ['id' => $labelbyid, 'class' => 'sr-only']);
        $output .= html_writer::tag('div', format_text($answer, FORMAT_HTML), [
            'role' => 'textbox',
            'aria-readonly' => 'true',
            'aria-labelledby' => $labelbyid,
            'class' => 'qtype_sheet_response readonly', 
            'style' => 'min-height: ' . ($lines * 1.5) . 'em;',
        ]);
        return $output;
    }
}
