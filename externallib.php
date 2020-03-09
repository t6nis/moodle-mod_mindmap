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
 * @author     Tonis Tartes <tonis.tartes@gmail.com>
 * @copyright  2020 Tonis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/externallib.php");

/**
 * Class mod_mindmap_external
 */
class mod_mindmap_external extends external_api {

    public static function submit_mindmap_parameters() {
        return new external_function_parameters(
            array(
                'mindmapid' => new external_value(PARAM_INT, 'The item id to operate on'),
                'mindmapdata' => new external_value(PARAM_TEXT, 'Update data'))
        );
    }

    public static function submit_mindmap_returns() {
        return null;
    }

    public static function submit_mindmap($mindmapid, $mindmapdata) {
        global $DB;

        $dataobject = new stdClass();
        $dataobject->id = $mindmapid;
        $dataobject->mindmapdata = $mindmapdata;

        return $DB->update_record('mindmap', $dataobject);

    }

}
