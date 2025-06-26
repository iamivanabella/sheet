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
        global $PAGE;

        // Add the toolbar with formatting buttons using SVG icons
        $mform->addElement('html', html_writer::start_tag('div', ['class' => 'toolbar', 'style' => 'margin-bottom: 10px; display: flex; flex-wrap: wrap; align-items: center; gap: 10px;']));
        
        $mform->addElement('html', '<select id="font-size-dropdown" class="toolbar-btn" data-initial-value="12"><option value="12" selected="selected">12</option><option value="14">14</option><option value="18">18</option><option value="24">24</option><option value="36">36</option></select>');
        
        $mform->addElement('html', '<div class="toolbar-separator"></div>');
        
        $mform->addElement('html', '<button type="button" id="bold-btn" title="Bold" class="toolbar-btn"><img src="' . new moodle_url('/question/type/sheet/svgs/compressed/bold.svg') . '" alt="Bold" class="toolbar-icon"></button>');
        $mform->addElement('html', '<button type="button" id="italic-btn" title="Italic" class="toolbar-btn"><img src="' . new moodle_url('/question/type/sheet/svgs/compressed/italic.svg') . '" alt="Italic" class="toolbar-icon"></button>');
        $mform->addElement('html', '<button type="button" id="underline-btn" title="Underline" class="toolbar-btn"><img src="' . new moodle_url('/question/type/sheet/svgs/compressed/underline.svg') . '" alt="Underline" class="toolbar-icon"></button>');
        
        $mform->addElement('html', '<div class="toolbar-separator"></div>');

        $mform->addElement('html', '<div style="position: relative;"><button type="button" id="text-color-btn" title="Text Color" class="toolbar-btn"><img src="' . new moodle_url('/question/type/sheet/svgs/compressed/text-color.svg') . '" alt="Text Color" class="toolbar-icon"></button><input type="color" id="text-color-picker" style="position: absolute; top: 0; left: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;"></div>');
        $mform->addElement('html', '<div style="position: relative;"><button type="button" id="fill-color-btn" title="Fill Color" class="toolbar-btn"><img src="' . new moodle_url('/question/type/sheet/svgs/compressed/fill-color.svg') . '" alt="Fill Color" class="toolbar-icon"></button><input type="color" id="fill-color-picker" style="position: absolute; top: 0; left: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;"></div>');
        
        $mform->addElement('html', '<div class="toolbar-separator"></div>');

        // Add Alignment Dropdown Button
        $mform->addElement('html', html_writer::start_tag('div', ['class' => 'dropdown', 'style' => 'position: relative; display: inline-block;']));
        $mform->addElement('html', html_writer::tag('button', '<img src="' . new moodle_url('/question/type/sheet/svgs/compressed/align-left.svg') . '" alt="Align Left" class="toolbar-icon">', ['type' => 'button', 'id' => 'align-dropdown-btn', 'title' => 'Align', 'class' => 'toolbar-btn dropdown-toggle']));
        $mform->addElement('html', html_writer::start_tag('div', ['class' => 'dropdown-content', 'style' => 'display: none; position: absolute; top: 100%; left: 0px; border: 1px solid #ccc; background: white; z-index: 1000;']));
        $mform->addElement('html', html_writer::tag('button', '<img src="' . new moodle_url('/question/type/sheet/svgs/compressed/align-left.svg') . '" alt="Align Left" class="toolbar-icon">', ['type' => 'button', 'id' => 'align-left-btn', 'title' => 'Align Left', 'class' => 'toolbar-btn', 'style' => 'display: block;']));
        $mform->addElement('html', html_writer::tag('button', '<img src="' . new moodle_url('/question/type/sheet/svgs/compressed/align-center.svg') . '" alt="Align Center" class="toolbar-icon">', ['type' => 'button', 'id' => 'align-center-btn', 'title' => 'Align Center', 'class' => 'toolbar-btn', 'style' => 'display: block;']));
        $mform->addElement('html', html_writer::tag('button', '<img src="' . new moodle_url('/question/type/sheet/svgs/compressed/align-right.svg') . '" alt="Align Right" class="toolbar-icon">', ['type' => 'button', 'id' => 'align-right-btn', 'title' => 'Align Right', 'class' => 'toolbar-btn', 'style' => 'display: block;']));
        $mform->addElement('html', html_writer::end_tag('div')); // Close the dropdown-menu
        $mform->addElement('html', html_writer::end_tag('div')); // Close the dropdown div

        $mform->addElement('html', html_writer::end_tag('div'));
            
        // Add the formula bar with a label
        $mform->addElement('html', html_writer::start_tag('div', ['style' => 'margin-bottom: 10px; display: flex; align-items: center;']));
        $mform->addElement('html', '<label for="formula-bar" style="margin-right: 10px; font-weight: bold;">fx</label>');
        $mform->addElement('html', '<input type="text" id="formula-bar" style="flex: 1;" placeholder="Enter formula here...">');
        $mform->addElement('html', html_writer::end_tag('div'));
    
        // Spreadsheet container
        $mform->addElement('html', html_writer::start_tag('div', ['class' => 'spreadsheet-container', 'style' => 'height: 400px;']));
        $mform->addElement('html', '<div id="spreadsheet-editor"></div>');
        $mform->addElement('html', html_writer::end_tag('div'));
    
        // Hidden field to store spreadsheet data
        $mform->addElement('hidden', 'spreadsheetdata');
        $mform->setType('spreadsheetdata', PARAM_RAW);
        $mform->getElement('spreadsheetdata')->updateAttributes(array('id' => 'id_spreadsheetdata'));
    
        // // Include Handsontable CSS and JS files
        // $PAGE->requires->css(new moodle_url('/question/type/sheet/style/handsontable.full.min.css'));
        // $mform->addElement('html', '<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>');
        // $mform->addElement('html', '<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/hyperformula/dist/hyperformula.full.min.js"></script>');

        $PAGE->requires->css(new moodle_url('/question/type/sheet/style/handsontable.full.min.css'));
        $PAGE->requires->js(new moodle_url('/question/type/sheet/handsontable/handsontable.full.min.js'));
        $PAGE->requires->js(new moodle_url('/question/type/sheet/handsontable/hyperformula.full.min.js'));
        
        $mform->addElement('html', '<script>
            const alignLeftIcon = "' . new moodle_url('/question/type/sheet/svgs/compressed/align-left.svg') . '";
            const alignCenterIcon = "' . new moodle_url('/question/type/sheet/svgs/compressed/align-center.svg') . '";
            const alignRightIcon = "' . new moodle_url('/question/type/sheet/svgs/compressed/align-right.svg') . '";
        </script>');

        $PAGE->requires->js_call_amd('qtype_sheet/spreadsheet', 'init', array('editingOn' => true));
    }
    
    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        // Check if options exist and response template is set
        if (isset($question->options) && isset($question->options->spreadsheetdata)) {
            $question->spreadsheetdata = $question->options->spreadsheetdata;
        } else {
            // Set default response template if none exists (for new questions)
            $question->spreadsheetdata = json_encode([
                'data' => array_fill(0, 26, array_fill(0, 26, '')),
                'meta' => []
            ]);
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
