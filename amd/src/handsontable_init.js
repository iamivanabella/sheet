
define(['jquery', 'core/log'], function($, Log) {
    return {
        init: function() {
            Log.debug('Initializing Handsontable...');

            var container = document.getElementById("spreadsheet-editor");
            var hot = new Handsontable(container, {
                data: [], // Initial data
                rowHeaders: true,
                colHeaders: true,
                rowCount: 10,
                colCount: 10,
                formulas: true,
                licenseKey: "non-commercial-and-evaluation",
                afterChange: function(changes, source) {
                    if (source !== "loadData") {
                        document.getElementById("id_spreadsheetdata").value = JSON.stringify(hot.getData());
                    }
                }
            });

            var existingData = document.getElementById("id_spreadsheetdata").value;
            if (existingData) {
                hot.loadData(JSON.parse(existingData));
            }
        }
    };
});
