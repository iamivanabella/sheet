define(['jquery', 'core/log'], function($, Log) {
    return {
        init: function(data) {
            Log.debug('Initializing Handsontable...');

            var container = document.getElementById("spreadsheet-editor");
            var hot = new Handsontable(container, {
                data: JSON.parse(data.spreadsheetdata), // Load initial data
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
                        document.getElementById("id_spreadsheetdata").value = JSON.stringify(hot.getData());
                    }
                }
            });

            if (data.spreadsheetdata) {
                hot.loadData(JSON.parse(data.spreadsheetdata));
            }
        }
    };
});
