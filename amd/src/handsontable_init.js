define(['jquery', 'core/log', 'qtype_sheet/handsontable.full.min', 'qtype_sheet/hyperformula.full.min'], function($, Log, Handsontable, HyperFormula) {
    return {
        init: function() {
            Log.debug('Initializing Handsontable...');
        
            var container = document.getElementById("spreadsheet-editor");
            // spreadsheetdata = JSON.parse(spreadsheetdata);
            var data = document.getElementById("id_spreadsheetdata").value;

            Log.debug(data);
            var hot = new Handsontable(container, {
                data: JSON.parse(data), // Load initial data
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
                    }
                }
            });
        }
    };
});
