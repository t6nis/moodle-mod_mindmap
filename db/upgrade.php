<?php
// This file is part of Mindmap module for Moodle - http://moodle.org/
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

defined('MOODLE_INTERNAL') || die();

/**
 * Mindmap plugin upgrade code.
 *
 * @package    mod_mindmap
 * @author     Tonis Tartes <tonis.tartes@gmail.com>
 * @copyright  2020 Tonis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_mindmap_upgrade($oldversion = 0) {

    global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    $result = true;

    if ($oldversion < 2012032300) {
        upgrade_mod_savepoint(true, 2012032300, 'mindmap');
    }

    if ($oldversion < 2012061300) {

        $table = new xmldb_table('mindmap');
        $field = new xmldb_field('editable', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'userid');
        $dbman->change_field_type($table, $field);

        upgrade_mod_savepoint(true, 2012061300, 'mindmap');
    }

    if ($oldversion < 2012070400) {
        upgrade_mod_savepoint(true, 2012070400, 'mindmap');
    }

    // Locking functionality.
    if ($oldversion < 2013030100) {

        $table = new xmldb_table('mindmap');
        $field = new xmldb_field('locking');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'timemodified');
        $dbman->add_field($table, $field);

        $table = new xmldb_table('mindmap');
        $field = new xmldb_field('locked');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'locking');
        $dbman->add_field($table, $field);

        $table = new xmldb_table('mindmap');
        $field = new xmldb_field('lockedbyuser');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '12', null, XMLDB_NOTNULL, null, '0', 'locked');
        $dbman->add_field($table, $field);

        upgrade_mod_savepoint(true, 2013030100, 'mindmap');

    }

    if ($oldversion < 2020022200) {

        $table = new xmldb_table('mindmap');
        $field = new xmldb_field('mindmapdata');
        $field->set_attributes(XMLDB_TYPE_TEXT, null, null, null, null, null, 'xmldata');
        $dbman->add_field($table, $field);

        upgrade_mod_savepoint(true, 2020022200, 'mindmap');

    }

    if ($oldversion < 2020220204) {
        $table = new xmldb_table('mindmap');
        $field = new xmldb_field('xmldata', XMLDB_TYPE_TEXT, null, null, null, null, null, null);
        $dbman->change_field_type($table, $field);

        upgrade_mod_savepoint(true, 2020220204, 'mindmap');
    }

    if ($oldversion < 2021102601) {

        $table = new xmldb_table('mindmap');
        $field = new xmldb_field('xmldata');

        // Conditionally launch drop field.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2021102601, 'mindmap');
    }

    // Individual Mindmaps feature.
    if ($oldversion < 2022102100) {

        $table = new xmldb_table('mindmap');
        $field = new xmldb_field('mindmapmode');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'lockedbyuser');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define table mindmap_individual to be created.
        $table = new xmldb_table('mindmap_individual');

        // Adding fields to table assignfeedback_editpdf_rot.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('mindmapid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('mindmapdata', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('locked', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('lockedbyuser', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table assignfeedback_editpdf_rot.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('mindmapfk', XMLDB_KEY_FOREIGN, ['mindmapid'], 'mindmap', ['id']);

        // Conditionally launch create table for assignfeedback_editpdf_rot.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_mod_savepoint(true, 2022102100, 'mindmap');
    }
    return $result;
}