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
        $mform->addElement('html', html_writer::start_tag('div', ['class' => 'toolbar', 'style' => 'margin-bottom: 10px; display: flex; align-items: center; gap: 10px;']));
        
        $mform->addElement('html', '<button type="button" id="bold-btn" title="Bold" class="toolbar-btn"><img src="' . new moodle_url('/question/type/sheet/svgs/compressed/bold.svg') . '" alt="Bold" class="toolbar-icon"></button>');
        $mform->addElement('html', '<button type="button" id="italic-btn" title="Italic" class="toolbar-btn"><img src="' . new moodle_url('/question/type/sheet/svgs/compressed/italic.svg') . '" alt="Italic" class="toolbar-icon"></button>');
        $mform->addElement('html', '<button type="button" id="underline-btn" title="Underline" class="toolbar-btn"><img src="' . new moodle_url('/question/type/sheet/svgs/compressed/underline.svg') . '" alt="Underline" class="toolbar-icon"></button>');
        
        $mform->addElement('html', '<div class="toolbar-separator"></div>');
        $mform->addElement('html', '<div style="position: relative;"><button type="button" id="text-color-btn" title="Text Color" class="toolbar-btn"><img src="' . new moodle_url('/question/type/sheet/svgs/compressed/text-color.svg') . '" alt="Text Color" class="toolbar-icon"></button><input type="color" id="text-color-picker" style="position: absolute; top: 0; left: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;"></div>');
        $mform->addElement('html', '<div style="position: relative;"><button type="button" id="fill-color-btn" title="Fill Color" class="toolbar-btn"><img src="' . new moodle_url('/question/type/sheet/svgs/compressed/fill-color.svg') . '" alt="Fill Color" class="toolbar-icon"></button><input type="color" id="fill-color-picker" style="position: absolute; top: 0; left: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;"></div>');
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
    
        // Include Handsontable CSS and JS files
        $PAGE->requires->css(new moodle_url('/question/type/sheet/style/handsontable.full.min.css'));
        $mform->addElement('html', '<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>');
        $mform->addElement('html', '<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/hyperformula/dist/hyperformula.full.min.js"></script>');
    
        // Include Handsontable initialization script
        $mform->addElement('html', '
            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    let selectedCell = null;

                    var container = document.getElementById("spreadsheet-editor");
                    var formulaBar = document.getElementById("formula-bar");
                    var dataElement = document.getElementById("id_spreadsheetdata");
                    var storedData = dataElement ? JSON.parse(dataElement.value) : { data: [], meta: [] };

                    var data = storedData.data.length > 0 ? storedData.data : Array(26).fill(Array(26).fill(""));
                    var meta = storedData.meta || [];

                    class IRRPlugin extends HyperFormula.FunctionPlugin {
                        static implementedFunctions = {
                            "IRR": {
                                method: "irr",
                                parameters: [
                                    { argumentType: HyperFormula.FunctionArgumentType.RANGE},
                                    { argumentType: HyperFormula.FunctionArgumentType.NUMBER, optionalArg: true }
                                ],
                            },
                        };

                        irr(ast, state) {
                            try {
                                const cashFlows = this.evaluateAst(ast.args[0], state);
                                const guess = ast.args[1] ? this.evaluateAst(ast.args[1], state) : 0.1;

                                const flatCashFlows = cashFlows.data.flat();

                                if (flatCashFlows.length === 0) {
                                    throw new Error("Invalid cash flow data provided to IRR function.");
                                }

                                const hasPositive = flatCashFlows.some(value => value > 0);
                                const hasNegative = flatCashFlows.some(value => value < 0);

                                if (!hasPositive || !hasNegative) {
                                    console.error("Cash flows must include both positive and negative values.");
                                    return HyperFormula.ErrorType.NUM;
                                }
                                return this.calculateIRR(flatCashFlows, guess);
                                
                            } catch (error) {
                                console.error("Error during IRR calculation:", error);
                                return HyperFormula.ErrorType.NUM; 
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
                                    irr += 0.01;
                                    continue;
                                }

                                const newIRR = irr - npv / npvDerivative;

                                if (Math.abs(newIRR - irr) < precision) {
                                    return newIRR.toFixed(2);
                                }

                                irr = newIRR;
                            }

                            console.warn("Max iterations reached in IRR calculation. Returning last computed IRR:", irr.toFixed(2));
                            return irr.toFixed(2);
                        } 
                    }

                    HyperFormula.registerFunctionPlugin(IRRPlugin, {
                        enGB: {
                            IRR: "IRR",
                        },
                    });

                    const hyperformulaInstance = HyperFormula.buildEmpty({
                        licenseKey: "gpl-v3",
                    });
    
                    var hot = new Handsontable(container, {
                        data: data,
                        rowHeaders: true,
                        colHeaders: true,
                        width: "100%",
                        height: "100%",
                        rowCount: 26,
                        colCount: 26,
                        dropdownMenu: ["alignment"], 
                        contextMenu: ["copy", "cut", "paste", "alignment", "readOnly"], 
                        formulas: {
                            engine: hyperformulaInstance,
                        },
                        licenseKey: "non-commercial-and-evaluation",
                        cells: function(row, col) {
                            var cellProperties = {};
                            meta.forEach(function(metaItem) {
                                if (metaItem.row === row && metaItem.col === col) {
                                    if (metaItem.readOnly) {
                                        cellProperties.readOnly = true;
                                    }
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
                        afterSetCellMeta: function(row, col, key, value) {
                            if (key === "readOnly" || key === "className" || key === "style") {
                                meta = meta.filter(function(metaItem) {
                                    return !(metaItem.row === row && metaItem.col === col && metaItem[key]);
                                });

                                if (value !== false && value !== "") {
                                    var newMeta = { row: row, col: col };
                                    newMeta[key] = value;
                                    meta.push(newMeta);
                                }

                                var dataToStore = {
                                    data: hot.getSourceData(),
                                    meta: meta,
                                };

                                dataElement.value = JSON.stringify(dataToStore);

                                if (key === "readOnly") {
                                    var cellProperties = hot.getCellMeta(row, col);
                                    if (cellProperties.readOnly) {
                                        formulaBar.disabled = true;
                                    } else {
                                        formulaBar.disabled = false;
                                    }
                                }

                                
                            }
                        },
                        afterChange: function(changes, source) {
                            if (source !== "loadData") {
                                var formulaData = hot.getSourceData();
                                var dataToStore = {
                                    data: formulaData,
                                    meta: meta,
                                };
                                dataElement.value = JSON.stringify(dataToStore);
                            }
                        },
                        afterSelection: function(r, c) {
                            var cellProperties = hot.getCellMeta(r, c);
                            var cellValue = hot.getSourceDataAtCell(r, c);

                            formulaBar.value = cellValue || "";

                            if (cellProperties.readOnly) {
                                formulaBar.disabled = true;
                            } else {
                                formulaBar.disabled = false;
                            }
                            selectedCell = { row: r, col: c };
                        },
                    });

                    // Toolbar button actions
                    document.getElementById("bold-btn").addEventListener("click", function() {
                        if (selectedCell) {
                            let className = hot.getCellMeta(selectedCell.row, selectedCell.col).className || "";
                            className = className.includes("htBold") ? className.replace("htBold", "") : className + " htBold";
                            hot.setCellMeta(selectedCell.row, selectedCell.col, "className", className.trim());
                            hot.render(); // Re-render the table to apply the class
                        }
                    });

                    document.getElementById("italic-btn").addEventListener("click", function() {
                        if (selectedCell) {
                            let className = hot.getCellMeta(selectedCell.row, selectedCell.col).className || "";
                            className = className.includes("htItalic") ? className.replace("htItalic", "") : className + " htItalic";
                            hot.setCellMeta(selectedCell.row, selectedCell.col, "className", className.trim());
                            hot.render();
                        }
                    });

                    document.getElementById("underline-btn").addEventListener("click", function() {
                        if (selectedCell) {
                            let className = hot.getCellMeta(selectedCell.row, selectedCell.col).className || "";
                            className = className.includes("htUnderline") ? className.replace("htUnderline", "") : className + " htUnderline";
                            hot.setCellMeta(selectedCell.row, selectedCell.col, "className", className.trim());
                            hot.render();
                        }
                    });

                    // Font Color Picker action with change event
                    document.getElementById("text-color-picker").addEventListener("change", function(event) {
                        if (selectedCell) {
                            let currentStyle = hot.getCellMeta(selectedCell.row, selectedCell.col).style || {};
                            currentStyle.color = event.target.value;
                            hot.setCellMeta(selectedCell.row, selectedCell.col, "style", currentStyle);
                            hot.render();
                        }
                    });

                    // Background Fill Color Picker action with change event
                    document.getElementById("fill-color-picker").addEventListener("change", function(event) {
                        if (selectedCell) {
                            let currentStyle = hot.getCellMeta(selectedCell.row, selectedCell.col).style || {};
                            currentStyle.backgroundColor = event.target.value;
                            hot.setCellMeta(selectedCell.row, selectedCell.col, "style", currentStyle);
                            hot.render();
                        }
                    });
    
                    // Monitor the editor input directly for accurate real-time updates
                    Handsontable.hooks.add("afterBeginEditing", function(row, column) {
                        var editor = hot.getActiveEditor();
                        if (editor && editor.TEXTAREA) {
                            editor.TEXTAREA.addEventListener("input", function() {
                                formulaBar.value = editor.TEXTAREA.value;
                            });
                        }
                    });
    
                    // Update cell content as the user types in the formula bar
                    formulaBar.addEventListener("input", function(event) {
                        if (selectedCell) {
                            hot.setDataAtCell(selectedCell.row, selectedCell.col, formulaBar.value);
                        }
                    });
    
                    // Prevent form submission or page refresh on Enter key in the formula bar
                    formulaBar.addEventListener("keydown", function(event) {
                        if (event.key === "Enter") {
                            event.preventDefault();
                        }
                    });
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
