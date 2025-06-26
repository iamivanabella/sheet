define(['qtype_sheet/customformula_irr', 'qtype_sheet/toolbar'], function(hyperformulaInstance, Toolbar) {
    return {
        init: function(editingOn = false) {
            const container = document.getElementById("spreadsheet-editor");
            const formulaBar = document.getElementById("formula-bar");
            const dataElement = document.getElementById("id_spreadsheetdata");
            const fontSizeDropdown = document.getElementById("font-size-dropdown");
            const storedData = JSON.parse(dataElement.value || '{"data":[], "meta":[]}');

            var data = storedData.data.length > 0 ? storedData.data : Array(26).fill(Array(26).fill(""));
            var meta = storedData.meta || [];

            var contextMenuOptions = editingOn ? ["make_read_only"] : false;

            let selectedCell = null;
            let selectedCells = [];
            let formulaMode = false;  // Track if we are in formula mode
            let formulaInput = "";    // Track current formula input
            let shiftKeyPressed = false; // Track if shift key is pressed
            let formulaCell = null; // Track the cell where the formula is being added
            let isSelecting = false;
            let arithmeticMode = false;
            let startCellRef = null;
            let endCellRef = null;
            let enterPressed = false;

            // Track Shift key state
            document.addEventListener('keydown', (event) => shiftKeyPressed = event.key === 'Shift' ? true : shiftKeyPressed);
            document.addEventListener('keyup', (event) => shiftKeyPressed = event.key === 'Shift' ? false : shiftKeyPressed);


            var hot = new Handsontable(container, {
                data: data,
                meta: meta,
                rowHeaders: true,
                colHeaders: true,
                contextMenu: contextMenuOptions,
                width: "100%",
                height: "100%",
                rowCount: 26,
                colCount: 26,
                formulas: { engine: hyperformulaInstance },
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
                                    td.style.fontSize = metaItem.style.fontSize || "";
                                    td.style.lineHeight = metaItem.style.lineHeight || "";
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
                        data = hot.getSourceData();
                        dataElement.value = JSON.stringify({data: data, meta: meta}); // Store both data and meta
                    }
                },
                afterSelection: function(r, c, r2, c2) {
                    if (formulaMode && !isSelecting) {
                        const editor = hot.getActiveEditor();
                        const lastChar = editor.TEXTAREA.value.slice(-1);

                        if (!shiftKeyPressed && r === r2 && c === c2){
                            // Single cell selection without Shift key
                            startCellRef = Handsontable.helper.spreadsheetColumnLabel(c) + (r + 1);
                            if (arithmeticMode) {
                                formulaInput = /[\+\-\*\/^%=(,]/.test(lastChar)
                                    ? formulaInput + startCellRef
                                    : formulaInput.replace(/(\b[A-Z]+\d+|[A-Z]+\d+:[A-Z]+\d+)$/, startCellRef);
                            } else {
                                // If not in arithmetic mode, set the formula input to the selected cell
                                formulaInput = `=${startCellRef}`;
                            }
                        } else if (shiftKeyPressed || r !== r2 || c !== c2) {
                            // User is selecting a range
                            endCellRef = Handsontable.helper.spreadsheetColumnLabel(c2) + (r2 + 1);
                            
                            // Handle range selection with Shift key or multi-cell selection
                            if (arithmeticMode) {
                                // Append or replace cell range based on the last character
                                formulaInput = /[\+\-\*\/^%]/.test(lastChar)
                                    ? `${formulaInput}${startCellRef}:${endCellRef}`
                                    : formulaInput.replace(/(\b[A-Z]+\d+|[A-Z]+\d+:[A-Z]+\d+)$/, `${startCellRef}:${endCellRef}`);
                            } else {
                                formulaInput = `=${startCellRef}:${endCellRef}`;
                                arithmeticMode = false;
                            }
                        } 
                        formulaBar.value = formulaInput;
                        reselect();
                    } else {
                        const selectedRange = {from: {row: r, col: c}, to: {row: r2, col: c2}};
                        selectedCells = [];
                        selectedCell = { row: r, col: c };

                        for (let row = selectedRange.from.row; row <= selectedRange.to.row; row++) {
                            for (let col = selectedRange.from.col; col <= selectedRange.to.col; col++) {
                                selectedCells.push({row, col});
                            }
                        }

                        const cellProperties = hot.getCellMeta(r, c);
                        const cellValue = hot.getSourceDataAtCell(r, c);

                        const fontSize = cellProperties.style ? cellProperties.style.fontSize : '12px';
                        fontSizeDropdown.value = parseInt(fontSize);

                        let alignment = cellProperties.className || "htLeft";

                        if (alignment.includes("htCenter")) {
                            Toolbar.updateAlignmentIcon("htCenter");
                        } else if (alignment.includes("htRight")) {
                            Toolbar.updateAlignmentIcon("htRight");
                        } else {
                            Toolbar.updateAlignmentIcon("htLeft");
                        }

                        formulaBar.value = cellValue || "";

                        if (cellProperties.readOnly) {
                            formulaBar.disabled = true;
                            updateToolbarState(true);
                        } else {
                            formulaBar.disabled = false;
                            updateToolbarState(false);
                        }
                    }
                },
                afterBeginEditing: function() {
                    const editor = hot.getActiveEditor();
                    editor?.TEXTAREA?.addEventListener("input", handleEditorInput);
                },
                beforeKeyDown(event) {
                    if (event.key === "Enter" && formulaMode) {
                        if (formulaInput.includes('(') && !formulaInput.includes(')')) {
                            formulaInput += ')';
                        }

                        // Update the editor value before exiting formula mode
                        const editor = hot.getActiveEditor();
                        if (editor?.TEXTAREA) editor.TEXTAREA.value = formulaInput;

                        // Exit formula mode when Enter is pressed
                        formulaMode = arithmeticMode = false;
                        enterPressed = true; // Set flag to true to indicate Enter was pressed
                        hot.deselectCell();

                    }
                }
            });

            Toolbar.initToolbar(hot, () => selectedCells);

            // Update cell content as the user types in the formula bar
            formulaBar.addEventListener("input", function() {
                if (selectedCell) {
                    if (!formulaMode && formulaBar.value === "=") {
                        formulaMode = true;
                        formulaCell = selectedCell; // Record the cell where the formula is being added

                    }
                    hot.setDataAtCell(selectedCell.row, selectedCell.col, formulaBar.value);
                }
            });

            // Prevent form submission or page refresh on Enter key in the formula bar
            formulaBar.addEventListener("keydown", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                }
            });

            function updateToolbarState(isReadonly) {
                const toolbarButtons = document.querySelectorAll(".toolbar-btn");
                toolbarButtons.forEach(button => {
                    button.disabled = isReadonly;
                    button.classList.toggle("disabled", isReadonly);
                });
            }

            // Function to handle input events in the editor
            function handleEditorInput() {
                if (enterPressed) {
                    // If Enter was pressed, ignore further input changes
                    enterPressed = false; // Reset the flag
                    return;
                }
                
                const editor = hot.getActiveEditor();
                const editorValue = editor.TEXTAREA.value;
                formulaBar.value = editor.TEXTAREA.value;
                
                if (editorValue === "=") {
                    // Enter formula mode when '=' is typed
                    formulaMode = true;
                    formulaCell = selectedCell;
                    formulaInput = editorValue;
                } else if (editorValue.slice(-1) === ")") {
                    // Exit formula mode when ')' is detected
                    arithmeticMode = formulaMode = false;
                } else if (editorValue.includes("=") && (/[\+\-\*\/^%=,]$|\b[A-Za-z]+\(/.test(editorValue))) {
                    // Enter arithmetic mode if an operator, comma or a function (e.g., SUM) is detected
                    arithmeticMode = formulaMode = true;
                    formulaInput = editorValue;
                } else {
                    // Exit arithmetic and formula modes if conditions are not met
                    arithmeticMode = formulaMode = false;
                }
            }

            // Function to reselect and focus the formula cell to keep the editor active
            function reselect() {
                isSelecting = true;
                setTimeout(() => {
                    // Reselect the formula cell
                    hot.selectCell(formulaCell.row, formulaCell.col);
                    const activeEditor = hot.getActiveEditor();
                    if (activeEditor && !activeEditor.isOpened()) {
                        // Open the editor and set the value to the current formula input
                        activeEditor.beginEditing();
                        if (activeEditor.TEXTAREA) {
                            activeEditor.TEXTAREA.value = formulaInput;
                            activeEditor.open();
                        }
                    }
                    isSelecting = false;
                }, 0);
            }
        }
    };
});