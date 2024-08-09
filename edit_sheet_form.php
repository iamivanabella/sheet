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

        $mform->addElement('html', html_writer::start_tag('div', ['class' => 'spreadsheet-container', 'style' => ' height: 400px;']));
        $mform->addElement('html', '<div id="spreadsheet-editor"></div>');
        $mform->addElement('html', html_writer::end_tag('div'));

        // Hidden field to store spreadsheet data
        $mform->addElement('hidden', 'spreadsheetdata');
        $mform->setType('spreadsheetdata', PARAM_RAW); // Allow HTML content
        $mform->getElement('spreadsheetdata')->updateAttributes(array('id' => 'id_spreadsheetdata'));

        // Include Handsontable CSS and JS files
        $PAGE->requires->css(new moodle_url('/question/type/sheet/style/handsontable.full.min.css'));
        // $PAGE->requires->js(new moodle_url('/question/type/sheet/handsontable/handsontable.full.min.js'));
        // $PAGE->requires->js(new moodle_url('/question/type/sheet/handsontable/hyperformula.full.min.js'));
        $mform->addElement('html', '<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>');
        $mform->addElement('html', '<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/hyperformula/dist/hyperformula.full.min.js"></script>');

        // Include Handsontable initialization script
        $mform->addElement('html', '
            <script>
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
            </script>
        ');
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);

        // Check if options exist and response template is set
        if (isset($question->options) && isset($question->options->spreadsheetdata)) {
            $question->spreadsheetdata = $question->options->spreadsheetdata;
        } else {
            // Set default response template if none exists (for new questions)
            $question->spreadsheetdata = json_encode(array_fill(0, 26, array_fill(0, 26, '')));
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
