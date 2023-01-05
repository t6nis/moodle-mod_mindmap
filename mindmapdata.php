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

$id = optional_param('id', 0, PARAM_INT);
$mindmapmode = optional_param('mindmapmode', 0, PARAM_INT);

global $DB;

if ($id) {
    if ($mindmapmode == 2) {
        if (!$mindmap = $DB->get_record('mindmap_individual', array('id' => $id))) {
            print_error('Course module is incorrect');
        }
        if (!$mindmap_parent = $DB->get_record('mindmap', array('id' => $mindmap->mindmapid))) {
            print_error('Mindmap parent is incorrect');
        }
        if (!$course = $DB->get_record('course', array('id' => $mindmap_parent->course))) {
            print_error('Course is misconfigured');
        }
    } else {
        if (!$mindmap = $DB->get_record('mindmap', array('id' => $id))) {
            print_error('Course module is incorrect');
        }
        if (!$course = $DB->get_record('course', array('id' => $mindmap->course))) {
            print_error('Course is misconfigured');
        }
    }
}

require_login($course->id);

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
