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
 * @copyright  2011 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//require configs & libs
require_once("../../config.php");
require_once("lib.php");

global $DB, $OUTPUT, $PAGE, $USER;

$id = required_param('id', PARAM_INT);   // course

if (!$course = $DB->get_record("course", array("id" => $id))) {
    error("Course ID is incorrect");
}

require_login($course->id);

add_to_log($course->id, "mindmap", "view all", "index.php?id=$course->id", "");

/// Get all required stringsnewmodule
$strmindmaps = get_string("modulenameplural", "mindmap");
$strmindmap  = get_string("modulename", "mindmap");

//$PAGE params
$PAGE->set_title($strmindmaps);
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_url('/mod/mindmap/index.php', array('id'=>$course->id));  
$PAGE->set_pagelayout('admin'); //this is a bloody hack!
    
/// Print the header   
echo $OUTPUT->header();
echo $OUTPUT->heading($strmindmaps);

/// Get all the appropriate data
if (! $mindmaps = get_all_instances_in_course("mindmap", $course)) {
    notice("There are no mindmaps", "../../course/view.php?id=$course->id");
    die;
}

$timenow = time();
$strname  = get_string("name");
$strweek  = get_string("week");
$strtopic  = get_string("topic");

//init table
$table = new html_table();
$table->width = "100%";
$table->size = array('10%', '90%');
if ($course->format == "weeks") {
    $table->head  = array ($strweek, $strname);
    $table->align = array ("center", "center");
} else if ($course->format == "topics") {
    $table->head  = array ($strtopic, $strname);
    $table->align = array ("center", "center", "center", "center");
} else {
    $table->head  = array ($strname);
    $table->align = array ("center", "center", "center");
}

foreach ($mindmaps as $mindmap) {
    if (!$mindmap->visible) {
        //Show dimmed if the mod is hidden
        $link = "<a class=\"dimmed\" href=\"view.php?id=$mindmap->coursemodule\">$mindmap->name</a>";
    } else {
        //Show normal if the mod is visible
        $link = "<a href=\"view.php?id=$mindmap->coursemodule\">$mindmap->name</a>";
    }

    if ($course->format == "weeks" or $course->format == "topics") {
        $table->data[] = array ($mindmap->section, $link);
    } else {
        $table->data[] = array ($link);
    }
}

echo html_writer::table($table);

/// Finish the page
echo $OUTPUT->footer($course);

?>
