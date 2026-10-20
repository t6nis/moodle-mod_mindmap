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
 * Mindmap xml parsing.
 *
 * @package    mod_mindmap
 * @author     Tonis Tartes <tonis.tartes@gmail.com>
 * @copyright  2020 Tonis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

$id = required_param('id', PARAM_INT);
$mindmapmode = optional_param('mindmapmode', 0, PARAM_INT);

global $DB, $USER;

if ($mindmapmode == 2) {
    if (!$mindmap = $DB->get_record('mindmap_individual', array('id' => $id))) {
        print_error('Course module is incorrect');
    }
    if (!$mindmap_parent = $DB->get_record('mindmap', array('id' => $mindmap->mindmapid))) {
        print_error('Mindmap parent is incorrect');
    }
} else {
    if (!$mindmap = $DB->get_record('mindmap', array('id' => $id))) {
        print_error('Course module is incorrect');
    }
    $mindmap_parent = $mindmap;
}
// The requested mode must match the real mode of the activity.
if (($mindmap_parent->mindmapmode == 2) != ($mindmapmode == 2)) {
    print_error('Course module is incorrect');
}
if (!$course = $DB->get_record('course', array('id' => $mindmap_parent->course))) {
    print_error('Course is misconfigured');
}
$cm = get_coursemodule_from_instance('mindmap', $mindmap_parent->id, $course->id, false, MUST_EXIST);

require_login($course, false, $cm);
$context = context_module::instance($cm->id);

// Users cant peek each others individual mindmaps! Only teachers can!
if ($mindmapmode == 2 && $mindmap->userid != $USER->id) {
    require_capability('moodle/course:manageactivities', $context);
}

// Get old flash data for conversion.
if ($mindmap->mindmapdata) {
    echo $mindmap->mindmapdata;
} else {
    echo '[{"x": 400,
        "y": 370,
        "id": "moodle",
        "label": "Moodle",
        "font": {"color": "#fff"},
        "color": {"background": "#c45400"},
        "connections": []
    }]';
}
