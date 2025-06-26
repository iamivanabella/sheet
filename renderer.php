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
        global $PAGE;

        $inputname = $qa->get_qt_field_name('spreadsheetdata');
        $id = 'id_spreadsheetdata';

        $storedData = $step->get_qt_var('spreadsheetdata') 
            ?? $question->spreadsheetdata 
            ?? json_encode(['data' => array_fill(0, 26, array_fill(0, 26, '')), 'meta' => []]);

    
        $output = html_writer::tag('label', get_string('answer', 'qtype_sheet'), ['class' => 'sr-only', 'for' => $id]);

        // Start modal trigger button and modal container.
        $output .= html_writer::tag('button', get_string('opensheet', 'qtype_sheet'), [
            'type' => 'button',
            'class' => 'btn btn-secondary',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#sheet-modal',
            'data-toggle' => 'modal',
            'data-target' => '#sheet-modal'
        ]);

        $output .= html_writer::start_div('modal fade', [
            'id' => 'sheet-modal',
            'tabindex' => '-1',
            'role' => 'dialog',
            'aria-hidden' => 'true'
        ]);

        $output .= html_writer::start_div('modal-dialog modal-xl', ['role' => 'document']);
        $output .= html_writer::start_div('modal-content');
        $output .= html_writer::start_div('modal-header');
            $output .= html_writer::tag('h5', get_string('spreadsheettitle', 'qtype_sheet'), ['class' => 'modal-title']);
            $output .= html_writer::tag('button',
                html_writer::span('&times;', '', ['aria-hidden' => 'true']),
                [
                    'type'             => 'button',
                    'class'            => 'close',
                    'data-dismiss'  => 'modal',
                    'aria-label'       => get_string('closebuttontitle', 'moodle'),
                ]
            );
        $output .= html_writer::end_div(); // header
        // $output .= html_writer::start_div('modal-body');

        // Add the toolbar
        $output .= html_writer::start_tag('div', ['class' => 'toolbar', 'style' => 'margin-bottom: 10px; display: flex; flex-wrap: wrap; align-items: center; gap: 10px; width: 100%;']);        

            $output .= html_writer::start_tag('select', ['id' => 'font-size-dropdown', 'class' => 'toolbar-btn']);
                $sizes = [12, 14, 18, 24, 36];
                foreach ($sizes as $size) {
                    $output .= html_writer::tag('option', $size, ['value' => $size, 'selected' => $size == 12 ? 'selected' : null]);
                }
            $output .= html_writer::end_tag('select');

            $output .= html_writer::tag('div', '', ['class' => 'toolbar-separator']);

            $output .= html_writer::tag('button', '<img src="' . new moodle_url('/question/type/sheet/svgs/compressed/bold.svg') . '" alt="Bold" class="toolbar-icon">', ['type' => 'button', 'id' => 'bold-btn', 'title' => 'Bold', 'class' => 'toolbar-btn']);
            $output .= html_writer::tag('button', '<img src="' . new moodle_url('/question/type/sheet/svgs/compressed/italic.svg') . '" alt="Italic" class="toolbar-icon">', ['type' => 'button', 'id' => 'italic-btn', 'title' => 'Italic', 'class' => 'toolbar-btn']);
            $output .= html_writer::tag('button', '<img src="' . new moodle_url('/question/type/sheet/svgs/compressed/underline.svg') . '" alt="Underline" class="toolbar-icon">', ['type' => 'button', 'id' => 'underline-btn', 'title' => 'Underline', 'class' => 'toolbar-btn']);
            
            $output .= html_writer::tag('div', '', ['class' => 'toolbar-separator']);

            // Add Font Color Picker
            $output .= html_writer::start_tag('div', ['style' => 'position: relative;']);
                $output .= html_writer::tag('button', '<img src="' . new moodle_url('/question/type/sheet/svgs/compressed/text-color.svg') . '" alt="Text Color" class="toolbar-icon">', ['type' => 'button', 'id' => 'text-color-btn', 'title' => 'Text Color', 'class' => 'toolbar-btn']);
                $output .= html_writer::empty_tag('input', ['type' => 'color', 'id' => 'text-color-picker', 'class' => 'toolbar-btn', 'style' => 'position: absolute; top: 0; left: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;']);
            $output .= html_writer::end_tag('div');

            // Add Background Fill Color Picker
            $output .= html_writer::start_tag('div', ['style' => 'position: relative;']);
                $output .= html_writer::tag('button', '<img src="' . new moodle_url('/question/type/sheet/svgs/compressed/fill-color.svg') . '" alt="Fill Color" class="toolbar-icon">', ['type' => 'button', 'id' => 'fill-color-btn', 'title' => 'Fill Color', 'class' => 'toolbar-btn']);
                $output .= html_writer::empty_tag('input', ['type' => 'color', 'id' => 'fill-color-picker', 'class' => 'toolbar-btn', 'style' => 'position: absolute; top: 0; left: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;']);
            $output .= html_writer::end_tag('div');

            $output .= html_writer::tag('div', '', ['class' => 'toolbar-separator']);

            // Add Alignment Dropdown Button
            $output .= html_writer::start_tag('div', ['class' => 'dropdown', 'style' => 'position: relative; display: inline-block;']);
                $output .= html_writer::tag('button', '<img src="' . new moodle_url('/question/type/sheet/svgs/compressed/align-left.svg') . '" alt="Align Left" class="toolbar-icon">', ['type' => 'button', 'id' => 'align-dropdown-btn', 'title' => 'Align', 'class' => 'toolbar-btn dropdown-toggle']);
                $output .= html_writer::start_tag('div', ['class' => 'dropdown-content', 'style' => 'display: none; position: absolute; top: 100%; left: 0; background: white; border: 1px solid #ccc; z-index: 1000;']);
                    $output .= html_writer::tag('button', '<img src="' . new moodle_url('/question/type/sheet/svgs/compressed/align-left.svg') . '" alt="Align Left" class="toolbar-icon">', ['type' => 'button', 'id' => 'align-left-btn', 'title' => 'Align Left', 'class' => 'toolbar-btn', 'style' => 'display: block;']);
                    $output .= html_writer::tag('button', '<img src="' . new moodle_url('/question/type/sheet/svgs/compressed/align-center.svg') . '" alt="Align Center" class="toolbar-icon">', ['type' => 'button', 'id' => 'align-center-btn', 'title' => 'Align Center', 'class' => 'toolbar-btn', 'style' => 'display: block;']);
                    $output .= html_writer::tag('button', '<img src="' . new moodle_url('/question/type/sheet/svgs/compressed/align-right.svg') . '" alt="Align Right" class="toolbar-icon">', ['type' => 'button', 'id' => 'align-right-btn', 'title' => 'Align Right', 'class' => 'toolbar-btn', 'style' => 'display: block;']);
                $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');

        $output .= html_writer::end_tag('div');

        // Add the formula bar with a label
        $output .= html_writer::start_tag('div', ['style' => 'margin-bottom: 10px; display: flex; align-items: center;']);
            $output .= html_writer::tag('label', 'fx', ['style' => 'margin-right: 10px; font-weight: bold;']);
            $output .= html_writer::empty_tag('input', ['type' => 'text', 'id' => 'formula-bar', 'style' => 'flex: 1;', 'placeholder' => 'Enter formula here...']);
        $output .= html_writer::end_tag('div');

        $output .= html_writer::start_tag('div', ['class' => 'spreadsheet-container', 'style' => 'height: 400px;']);
            $output .= html_writer::tag('div', '', ['id' => 'spreadsheet-editor']);
        $output .= html_writer::end_tag('div');
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'id' => $id, 'name' => $inputname, 'value' => $storedData]);
    
        // Include Handsontable CSS from CDN
        $output .= html_writer::empty_tag('link', ['rel' => 'stylesheet', 'type' => 'text/css', 'href' => new moodle_url('/question/type/sheet/style/handsontable.full.min.css')]);
        
        // Include Handsontable JS from CDN
        $output .= html_writer::tag('script', '', ['src' => new moodle_url('/question/type/sheet/handsontable/handsontable.full.min.js') ]);
        $output .= html_writer::tag('script', '', ['src' => new moodle_url('/question/type/sheet/handsontable/hyperformula.full.min.js') ]);

        $output .= html_writer::tag('script', '
            const alignLeftIcon = "' . new moodle_url('/question/type/sheet/svgs/compressed/align-left.svg') . '";
            const alignCenterIcon = "' . new moodle_url('/question/type/sheet/svgs/compressed/align-center.svg') . '";
            const alignRightIcon = "' . new moodle_url('/question/type/sheet/svgs/compressed/align-right.svg') . '";
        ');

        $PAGE->requires->js_call_amd('qtype_sheet/spreadsheet', 'init');
        
        // $output .= html_writer::end_div(); // modal-body
        $output .= html_writer::end_div(); // modal-content
        $output .= html_writer::end_div(); // modal-dialog
        $output .= html_writer::end_div(); // modal
        
        return $output;
    }    

    private function render_response_readonly(question_attempt $qa, $question, $step) {
        $inputname = $qa->get_qt_field_name('spreadsheetdata');
        $id = 'id_spreadsheetdata';
        $storedData = $step->get_qt_var('spreadsheetdata');

        if (empty($storedData)) {
            $storedData = $question->spreadsheetdata;
        }
    
        $output = html_writer::tag('label', get_string('answer', 'qtype_sheet'), ['class' => 'sr-only', 'for' => $id]);

        // Add a formula bar input field (disabled)
        $output .= html_writer::start_tag('div', ['style' => 'margin-bottom: 10px; display: flex; align-items: center;']);
        $output .= html_writer::tag('label', 'fx', ['style' => 'margin-right: 10px; font-weight: bold;']);
        $output .= html_writer::empty_tag('input', ['type' => 'text', 'id' => 'formula-bar', 'style' => 'flex: 1;', 'disabled' => 'disabled']);
        $output .= html_writer::end_tag('div');

        $output .= html_writer::start_tag('div', ['class' => 'spreadsheet-container', 'style' => 'height: 400px;']);
        $output .= html_writer::tag('div', '', ['id' => 'spreadsheet-editor']);
        $output .= html_writer::end_tag('div');
        $output .= html_writer::empty_tag('input', ['type' => 'hidden', 'id' => $id, 'name' => $inputname, 'value' => $storedData]);
    
        // Include Handsontable CSS from CDN
        $output .= html_writer::empty_tag('link', ['rel' => 'stylesheet', 'type' => 'text/css', 'href' => new moodle_url('/question/type/sheet/style/handsontable.full.min.css')]);
        
        // Include Handsontable JS from CDN
        $output .= html_writer::tag('script', '', ['src' => new moodle_url('/question/type/sheet/handsontable/handsontable.full.min.js')]);
        $output .= html_writer::tag('script', '', ['src' => new moodle_url('/question/type/sheet/handsontable/hyperformula.full.min.js')]);
    
        // Include Handsontable initialization script with IRR function integrated
        $output .= html_writer::tag('script', '
            document.addEventListener("DOMContentLoaded", function() {
                class IRRPlugin extends HyperFormula.FunctionPlugin {
                    static implementedFunctions = {
                        "IRR": {
                            method: "irr",
                            parameters: [
                                { argumentType: HyperFormula.FunctionArgumentType.RANGE },
                                { argumentType: HyperFormula.FunctionArgumentType.NUMBER, optionalArg: true } // Optional guess parameter
                            ],
                        },
                    };
    
                    irr(ast, state) {
                        try {
                            const cashFlows = this.evaluateAst(ast.args[0], state);
                            const guess = ast.args[1] ? this.evaluateAst(ast.args[1], state) : 0.1;
    
                            // Flatten the cashFlows array to handle both vertical and horizontal ranges
                            const flatCashFlows = cashFlows.data.flat();
    
                            if (flatCashFlows.length === 0) {
                                throw new Error("Invalid cash flow data provided to IRR function.");
                            }
    
                            // Validate that the cash flows contain both positive and negative values
                            const hasPositive = flatCashFlows.some(value => value > 0);
                            const hasNegative = flatCashFlows.some(value => value < 0);
    
                            if (!hasPositive || !hasNegative) {
                                console.error("Cash flows must include both positive and negative values.");
                                return HyperFormula.ErrorType.NUM; // Return #NUM! error
                            }
    
                            return this.calculateIRR(flatCashFlows, guess);
                        } catch (error) {
                            console.error("Error during IRR calculation:", error);
                            return HyperFormula.ErrorType.NUM; // Return #NUM! error if any other error occurs
                        }
                    }
    
                    calculateIRR(cashFlows, guess) {
                        const maxIterations = 1000;
                        const precision = 1e-7;
                        let irr = guess;
    
                        for (let i = 0; i < maxIterations; i++) {
                            const npv = cashFlows.reduce((acc, cashFlow, period) => acc + cashFlow / Math.pow(1 + irr, period), 0);
                            const npvDerivative = cashFlows.reduce((acc, cashFlow, period) => acc - period * cashFlow / Math.pow(1 + irr, period + 1), 0);
    
                            if (npvDerivative === 0) {
                                console.warn("Division by zero encountered in IRR calculation. Adjusting IRR value to continue.");
                                irr += 0.01; // Adjust IRR slightly to avoid division by zero
                                continue;
                            }
    
                            const newIRR = irr - npv / npvDerivative;
    
                            if (Math.abs(newIRR - irr) < precision) {
                                return newIRR.toFixed(2);
                            }
    
                            irr = newIRR;
                        }
    
                        console.warn("Max iterations reached in IRR calculation. Returning last computed IRR:", irr.toFixed(2));
                        return irr.toFixed(2); // Return the last calculated IRR if max iterations are reached
                    }
                }
    
                // Register the custom IRR function in HyperFormula
                HyperFormula.registerFunctionPlugin(IRRPlugin, {
                    enGB: {
                        IRR: "IRR",
                    },
                });
    
                const hyperformulaInstance = HyperFormula.buildEmpty({
                    licenseKey: "gpl-v3",
                });
    
                var container = document.getElementById("spreadsheet-editor");
                var formulaBar = document.getElementById("formula-bar");
                var dataElement = document.getElementById("id_spreadsheetdata");
                var stored = JSON.parse(dataElement.value);
    
                var data = stored.data || Array(26).fill(Array(26).fill(""));
                var meta = stored.meta || [];
    
                var hot = new Handsontable(container, {
                    data: data,
                    rowHeaders: true,
                    colHeaders: true,
                    width: "100%",
                    height: "100%",
                    rowCount: 26,
                    colCount: 26,
                    readOnly: true, // Make the entire table read-only
                    formulas: {
                        engine: hyperformulaInstance,
                    },
                    licenseKey: "non-commercial-and-evaluation",
                    cells: function(row, col) {
                        var cellProperties = {
                            readOnly: true // Ensure all cells are read-only
                        };
                        meta.forEach(function(metaItem) {
                            if (metaItem.row === row && metaItem.col === col) {
                                if (metaItem.className) {
                                    cellProperties.className = metaItem.className;
                                }
                                if (metaItem.style) {
                                    cellProperties.renderer = function(hotInstance, td, row, col, prop, value, cellProperties) {
                                        Handsontable.renderers.TextRenderer.apply(this, arguments);
                                        td.style.color = metaItem.style.color || "";
                                        td.style.backgroundColor = metaItem.style.backgroundColor || "";
                                    };
                                }
                            }
                        });
                        return cellProperties;
                    },
                    afterSelection: function(r, c) {
                        var cellProperties = hot.getCellMeta(r, c);
                        var cellValue = hot.getSourceDataAtCell(r, c);

                        formulaBar.value = cellValue || "";
                    },
                });
            });
        ');
    
        return $output;
    }
    
    
}