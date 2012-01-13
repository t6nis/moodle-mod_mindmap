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

/**
 * Mindmap plugin upgrade code
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2011 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_mindmap_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager(); /// loads ddl manager and xmldb classes
    
    $result = true;

/// And upgrade begins here. For each one, you'll need one
/// block of code similar to the next one. Please, delete
/// this comment lines once this file start handling proper
/// upgrade code.

/// if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
///     $result = result of "/lib/ddllib.php" function calls
/// }

/// Lines below (this included)  MUST BE DELETED once you get the first version
/// of your module ready to be installed. They are here only
/// for demonstrative purposes and to show how the newmodule
/// iself has been upgraded.

/// For each upgrade block, the file newmodule/version.php
/// needs to be updated . Such change allows Moodle to know
/// that this file has to be processed.

/// To know more about how to write correct DB upgrade scripts it's
/// highly recommended to read information available at:
///   http://docs.moodle.org/en/Development:XMLDB_Documentation
/// and to play with the XMLDB Editor (in the admin menu) and its
/// PHP generation posibilities.

/// First example, some fields were added to the module on 20070400
    if ($oldversion < 2007040100) {

    /// Define field course to be added to newmodule
        $table = new xmldb_table('mindmap');
        $field = new xmldb_field('course');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'id');
    /// Launch add field course
        $dbman->add_field($table, $field);

    /// Define field intro to be added to newmodule
        $table = new xmldb_table('mindmap');
        $field = new xmldb_field('intro');
        $field->set_attributes(XMLDB_TYPE_TEXT, 'medium', null, null, null, null, 'name');
    /// Launch add field intro
        $dbman->add_field($table, $field);

    /// Define field introformat to be added to newmodule
        $table = new xmldb_table('mindmap');
        $field = new xmldb_field('introformat');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'intro');
    /// Launch add field introformat
        $dbman->add_field($table, $field);
        
        upgrade_mod_savepoint(true, 2007040100, 'mindmap');
    }

/// Second example, some hours later, the same day 20070401
/// two more fields and one index were added (note the increment
/// "01" in the last two digits of the version
    if ($oldversion < 2007040101) {

    /// Define field timecreated to be added to newmodule
        $table = new xmldb_table('mindmap');
        $field = new xmldb_field('timecreated');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'introformat');
    /// Launch add field timecreated
        $dbman->add_field($table, $field);

    /// Define field timemodified to be added to newmodule
        $table = new xmldb_table('mindmap');
        $field = new xmldb_field('timemodified');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '0', 'timecreated');
    /// Launch add field timemodified
        $dbman->add_field($table, $field);

    /// Define index course (not unique) to be added to newmodule
        $table = new xmldb_table('mindmap');
        $index = new xmldb_index('course');
        $index->set_attributes(XMLDB_INDEX_NOTUNIQUE, array('course'));
    /// Launch add index course
        $dbman->add_index($table, $index);
        
        upgrade_mod_savepoint(true, 2007040101, 'mindmap');
    }

/// Third example, the next day, 20070402 (with the trailing 00), some inserts were performed, related with the module

/// And that's all. Please, examine and understand the 3 example blocks above. Also
/// it's interesting to look how other modules are using this script. Remember that
/// the basic idea is to have "blocks" of code (each one being executed only once,
/// when the module version (version.php) is updated.

/// Lines above (this included) MUST BE DELETED once you get the first version of
/// yout module working. Each time you need to modify something in the module (DB
/// related, you'll raise the version and add one upgrade block here.

/// Final return of upgrade result (true/false) to Moodle. Must be
/// always the last line in the script
    return $result;
}

?>
