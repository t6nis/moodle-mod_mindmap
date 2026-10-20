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
                'mindmapdata' => new external_value(PARAM_TEXT, 'Update data'),
                'mindmapmode' => new external_value(PARAM_INT, 'Mindmap mode'))
        );
    }

    public static function submit_mindmap_returns() {
        return new external_value(PARAM_BOOL, 'True if the mindmap was saved');
    }

    public static function submit_mindmap($mindmapid, $mindmapdata, $mindmapmode) {
        global $DB, $USER;

        $params = self::validate_parameters(self::submit_mindmap_parameters(), array(
            'mindmapid' => $mindmapid,
            'mindmapdata' => $mindmapdata,
            'mindmapmode' => $mindmapmode,
        ));

        // Individual mindmaps feature.
        if ($params['mindmapmode'] == 2) {
            $record = $DB->get_record('mindmap_individual', array('id' => $params['mindmapid']), '*', MUST_EXIST);
            $mindmap = $DB->get_record('mindmap', array('id' => $record->mindmapid), '*', MUST_EXIST);
        } else {
            $record = $DB->get_record('mindmap', array('id' => $params['mindmapid']), '*', MUST_EXIST);
            $mindmap = $record;
        }
        // The requested mode must match the real mode of the activity.
        if (($mindmap->mindmapmode == 2) != ($params['mindmapmode'] == 2)) {
            throw new moodle_exception('invalidrecord', 'error', '', 'mindmap');
        }

        $cm = get_coursemodule_from_instance('mindmap', $mindmap->id, $mindmap->course, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        if (isguestuser()) {
            throw new require_login_exception('Guests can not save mindmaps');
        }
        if ($mindmap->editable == 0 && !has_capability('moodle/course:manageactivities', $context)) {
            throw new moodle_exception('nopermissions', 'error', '', 'save mindmap');
        }
        // Users can only edit their own individual mindmap.
        if ($params['mindmapmode'] == 2 && $record->userid != $USER->id) {
            throw new moodle_exception('nopermissions', 'error', '', 'save mindmap');
        }
        // Respect the lock set by another user.
        if ($mindmap->locking > 0 && $record->locked > 0 && $record->lockedbyuser != $USER->id) {
            throw new moodle_exception('nopermissions', 'error', '', 'save locked mindmap');
        }

        $dataobject = new stdClass();
        $dataobject->id = $record->id;
        $dataobject->mindmapdata = $params['mindmapdata'];
        $dataobject->timemodified = time();
        if ($params['mindmapmode'] == 2) {
            return $DB->update_record('mindmap_individual', $dataobject);
        } else {
            return $DB->update_record('mindmap', $dataobject);
        }

    }

}
