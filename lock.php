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
 * Mindmap locking.
 *
 * @package    mod_mindmap
 * @author     Tonis Tartes <tonis.tartes@gmail.com>
 * @copyright  2020 Tonis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

global $DB;

$id = required_param('id', PARAM_INT);
$lock = required_param('lock', PARAM_RAW);
$uid = required_param('uid', PARAM_RAW);

if ($id) {
    if (!$mindmap = $DB->get_record('mindmap', array('id' => $id))) {
        print_error('Course module is incorrect');
    }
    if (!$course = $DB->get_record('course', array('id' => $mindmap->course))) {
        print_error('Course is misconfigured');
    }
    // Individual mindmap feature.
    if ($mindmap->mindmapmode == 2) {
        if ($mindmap = $DB->get_record('mindmap_individual', array('mindmapid' => $id, 'userid' => $uid))) {
            print_error('Could not get individual mindmap');
        }
    }
}

require_login($course->id);

$update = new stdClass();
$update->id = $id;
$update->locked = $lock;
$update->lockedbyuser = $uid;

// Individual mindmap feature.
if ($mindmap->mindmapmode == 2) {
    $DB->update_record('mindmap_individual', $update);
} else {
    $DB->update_record('mindmap', $update);
}
