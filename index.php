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
 * Mindmap index page.
 *
 * @package    mod_mindmap
 * @author     Tonis Tartes <tonis.tartes@gmail.com>
 * @copyright  2020 Tonis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_course_login($course, true);

// Trigger event.
$params = array(
    'context' => context_course::instance($course->id)
);
$event = \mod_mindmap\event\course_module_instance_list_viewed::create($params);
$event->add_record_snapshot('course', $course);
$event->trigger();

// Get all required stringsnewmodule.
$strmindmaps = get_string('modulenameplural', 'mindmap');
$strmindmap = get_string('modulename', 'mindmap');
$strsectionname = get_string('sectionname', 'format_' . $course->format);
$strname = get_string('name');
$strintro = get_string('moduleintro');

$strweek = get_string('week');
$strtopic = get_string('topic');

$timenow = time();

$PAGE->set_title($strmindmaps);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_url('/mod/mindmap/index.php', array('id' => $course->id));
$PAGE->set_pagelayout('admin'); // This is a bloody hack.

// Print the header.
echo $OUTPUT->header();

// Get all the appropriate data.
if (!$mindmaps = get_all_instances_in_course('mindmap', $course)) {
    notice('There are no mindmaps', $CFG->wwwroot . '/course/view.php?id=' . $course->id);
    exit;
}

$usesections = course_format_uses_sections($course->format);

// Init table.
$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $table->head = array($strsectionname, $strname, $strintro);
    $table->align = array('center', 'left', 'left');
} else {
    $table->head = array($strlastmodified, $strname, $strintro);
    $table->align = array('left', 'left', 'left');
}

$currentsection = '';

foreach ($mindmaps as $mindmap) {

    if (!$mindmap->visible && has_capability('moodle/course:viewhiddenactivities', $context)) {
        // Show dimmed if the mod is hidden.
        $link = html_writer::tag('a', format_string($mindmap->name, true), array('href' => new moodle_url('/mod/mindmap/view.php', array('id' => $mindmap->coursemodule)), 'class' => 'dimmed'));
    } else if ($mindmap->visible) {
        // Show normal if the mod is visible.
        $link = html_writer::tag('a', format_string($mindmap->name, true), array('href' => new moodle_url('/mod/mindmap/view.php', array('id' => $mindmap->coursemodule))));
    } else {
        // Don't show the glossary.
        continue;
    }

    $description = format_module_intro('mindmap', $mindmap, $mindmap->coursemodule);
    $printsection = '';

    if ($usesections) {
        if ($mindmap->section !== $currentsection) {
            if ($mindmap->section) {
                $printsection = get_section_name($course, $mindmap->section);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $mindmap->section;
        }
    } else {
        $printsection = html_writer::tag('span', userdate($mindmap->timemodified), array('class' => 'smallinfo'));
    }

    if ($usesections) {
        $table->data[] = array($printsection, $link, $description);
    } else {
        $table->data[] = array($link);
    }

}

echo html_writer::table($table);

// Finish the page.
echo $OUTPUT->footer($course);