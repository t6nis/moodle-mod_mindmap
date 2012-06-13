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
 * Mindmap index page
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2012 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT);   // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);

add_to_log($course->id, 'mindmap', 'view all', 'index.php?id='.$course->id, '');

/// Get all required stringsnewmodule
$strmindmaps     = get_string('modulenameplural', 'mindmap');
$strmindmap      = get_string('modulename', 'mindmap');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strname         = get_string('name');
$strintro        = get_string('moduleintro');

$strweek  = get_string('week');
$strtopic  = get_string('topic');

$timenow = time();

//$PAGE params
$PAGE->set_title($strmindmaps);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_url('/mod/mindmap/index.php', array('id' => $course->id));  
$PAGE->set_pagelayout('admin'); //this is a bloody hack!
    
/// Print the header   
echo $OUTPUT->header();

/// Get all the appropriate data
if (!$mindmaps = get_all_instances_in_course('mindmap', $course)) {
    notice('There are no mindmaps', $CFG->wwwroot.'/course/view.php?id='.$course->id);
    exit;
}

$usesections = course_format_uses_sections($course->format);
if ($usesections) {
    $sections = get_all_sections($course->id);
}

//init table
$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';

foreach ($mindmaps as $mindmap) {
    $cm = $modinfo->cms[$mindmap->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($mindmap->section !== $currentsection) {
            if ($mindmap->section) {
                $printsection = get_section_name($course, $sections[$mindmap->section]);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $mindmap->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($mindmap->timemodified).'</span>';
    }

    $class = $mindmap->visible ? '' : 'class="dimmed"'; // hidden modules are dimmed
    
    $table->data[] = array (
        $printsection,
        '<a '.$class.' href="view.php?id='.$cm->id.'">'.format_string($mindmap->name).'</a>',
        format_module_intro('mindmap', $mindmap, $cm->id));
}

echo html_writer::table($table);

/// Finish the page
echo $OUTPUT->footer($course);

?>
