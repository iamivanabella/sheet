define(['jquery', 'core/log', 'handsontable'], function($, Log, Handsontable) {
    return {
        init: function(config) {
            Log.debug('Initializing Handsontable');

            var container = document.getElementById('handsontable');
            var handsontable_data = $('#handsontable_data').val();
            var handsontable_config = config;

            if (handsontable_data) {
                handsontable_data = JSON.parse(handsontable_data);
            } else {
                handsontable_data = [];
            }

            var hot = new Handsontable(container, {
                data: handsontable_data,
                colHeaders: handsontable_config.colHeaders,
                rowHeaders: handsontable_config.rowHeaders,
                columns: new Array(handsontable_config.cols).fill({}),
                minRows: handsontable_config.rows,
                maxRows: handsontable_config.rows,
                minCols: handsontable_config.cols,
                maxCols: handsontable_config.cols
            });

            $('#mform1').submit(function() {
                $('#handsontable_data').val(JSON.stringify(hot.getData()));
            });
        }
    };
});