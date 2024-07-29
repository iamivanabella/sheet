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

defined('MOODLE_INTERNAL') || die();

function xmldb_qtype_sheet_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    // Check if the old version is less than the new version.
    if ($oldversion < 2024072523) {

        // Define table qtype_sheet_options to be modified.
        $table = new xmldb_table('qtype_sheet_options');

        // Check if the table already exists.
        if ($dbman->table_exists($table)) {
            // Check if the old columns exist and need to be dropped.
            $field1 = new xmldb_field('responseformat');
            $field2 = new xmldb_field('responserequired');
            $field3 = new xmldb_field('responsefieldlines');
            $field4 = new xmldb_field('responsetemplate');
            $field5 = new xmldb_field('responsetemplateformat');

            // Drop the old columns if they exist.
            if ($dbman->field_exists($table, $field1)) {
                $dbman->drop_field($table, $field1);
            }
            if ($dbman->field_exists($table, $field2)) {
                $dbman->drop_field($table, $field2);
            }
            if ($dbman->field_exists($table, $field3)) {
                $dbman->drop_field($table, $field3);
            }
            if ($dbman->field_exists($table, $field4)) {
                $dbman->drop_field($table, $field4);
            }
            if ($dbman->field_exists($table, $field5)) {
                $dbman->drop_field($table, $field5);
            }
        } else {
            // If the table does not exist, create it.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('questionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('spreadsheetdata', XMLDB_TYPE_TEXT, null, null, null, null, null);

            // Adding keys to table qtype_sheet_options.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('questionid', XMLDB_KEY_UNIQUE, array('questionid'));

            // Conditionally launch create table for qtype_sheet_options.
            $dbman->create_table($table);
        }

        // Sheet savepoint reached.
        upgrade_plugin_savepoint(true, 2024072523, 'qtype', 'sheet');
    }

    return true;
}
