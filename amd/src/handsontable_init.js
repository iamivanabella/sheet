define(['jquery', 'core/log'], function($, Log) {
    return {
        init: function() {
            Log.debug('Initializing Handsontable...');
        
            var container = document.getElementById("spreadsheet-editor");
            var data = document.getElementById("id_spreadsheetdata").value;

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
                    }
                }
            });
        }
    };
});
