define(['jquery', 'core/log'], function($, Log) {
    return {
        init: function() {

            function loadScript(url, callback) {
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = url;
                script.onload = callback;
                document.head.appendChild(script);
            }

            var handsontableUrl = 'https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js';
            var hyperFormulaUrl = 'https://cdn.jsdelivr.net/npm/hyperformula/dist/hyperformula.full.min.js';

            Log.debug('Initializing Handsontable...');

            

            // Initialize Handsontable with HyperFormula
            loadScript(hyperFormulaUrl, function() {
                loadScript(handsontableUrl, function() {

                    var container = document.getElementById("spreadsheet-editor");
                    var dataElement = document.getElementById("id_spreadsheetdata");
                    var data = dataElement ? dataElement.value : '';

                    if (data === '') {
                        data = JSON.stringify(array_fill(0, 20, array_fill(0, 20, '')));
                    }

                    Log.debug(data);

                    var hot = new Handsontable(container, {
                        data: JSON.parse(data),
                        rowHeaders: true,
                        colHeaders: true,
                        rowCount: 20,
                        colCount: 20,
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
                });
            });
        }
    };
});
