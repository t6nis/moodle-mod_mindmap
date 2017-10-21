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
 * @package    mod_mindmap
 * @author Tonis Tartes <tonis.tartes@gmail.com>
 * @copyright  2011 Tonis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Define the complete mindmap structure for backup, with file and id annotations.
 */
class backup_mindmap_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo.
        // Mindmap user data is in the xmldata field in DB - anyway lets skip that.

        // Define each element separated.
        $mindmap = new backup_nested_element('mindmap', array('id'), array(
            'name', 'intro', 'introformat', 'userid', 'editable',
            'xmldata', 'timecreated', 'timemodified'));

        // Build the tree.

        // Define sources.
        $mindmap->set_source_table('mindmap', array('id' => backup::VAR_ACTIVITYID));

        // Define id annotations.

        // Define file annotations.
        $mindmap->annotate_files('mod_mindmap', 'intro', null);

        // Return the root element (mindmap), wrapped into standard activity structure.
        return $this->prepare_activity_structure($mindmap);
    }
}