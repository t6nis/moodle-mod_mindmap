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
 * @author ekpenso.com
 * @copyright  2011 Tonis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_mindmap_upgrade($oldversion=0) {

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
    
    return $result;
}