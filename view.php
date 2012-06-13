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
 * Mindmap view page
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2012 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//requires configs & libs
require_once('../../config.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$a  = optional_param('a', 0, PARAM_INT);

if ($id) {
    if (!$cm = get_coursemodule_from_id('mindmap', $id)) {
        error('Course Module ID was incorrect');
    }

    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        error('Course is misconfigured');
    }

    if (!$mindmap = $DB->get_record('mindmap', array('id' => $cm->instance))) {
        error('Course module is incorrect');
    }
} else {
    if (!$mindmap = $DB->get_record('mindmap', array('id' => $a))) {
        error('Course module is incorrect');
    }
    if (!$course = $DB->get_record('course', array('id' => $mindmap->course))) {
        error('Course is misconfigured');
    }
    if (!$cm = get_coursemodule_from_instance('mindmap', $mindmap->id, $course->id)) {
        error('Course Module ID was incorrect');
    }
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'mindmap', 'view', 'view.php?id='.$cm->id, $mindmap->id);

/// Print the page header
$strmindmaps  = get_string('modulenameplural', 'mindmap');
$strmindmap   = get_string('modulename', 'mindmap');
$strname      = format_string($mindmap->name);

//$PAGE params
$PAGE->set_url('/mod/mindmap/view.php', array('id'=>$cm->id));  
$PAGE->set_title($strname);
$PAGE->set_heading($course->fullname);

//Header
echo $OUTPUT->header();

//Intro box
echo $OUTPUT->box(format_module_intro('mindmap', $mindmap, $cm->id), 'generalbox', 'intro');

//Mindmap box
echo $OUTPUT->box_start('generalbox', 'intro'); 

echo html_writer::tag('div', get_string('mindmaphint', 'mindmap'), array('class' => 'mindmap_hint'));
echo html_writer::tag('div', '', array('id' => 'flashcontent'));

?>
<script type="text/javascript" src="./javascript/swfobject.js"></script>	
<script type="text/javascript">
    // <![CDATA[
    function mm_save(str)
    {
        alert(decodeURI(str));
    }
    var so = new SWFObject('./viewer.swf', 'viewer', 800, 600, '9', '#FFFFFF');
    so.addVariable('load_url', './xml.php?id=<?php echo $mindmap->id;?>');
    <?php if((has_capability('moodle/course:manageactivities', $context, $USER->id)) || ($mindmap->editable == '1')): ?>
            so.addVariable('save_url', './save.php?id=<?php echo $mindmap->id;?>');
            so.addVariable('editable', 'true');
    <?php endif; ?>
    so.addVariable('lang', 'en');
    so.write('flashcontent');
    // ]]>
</script>
<?php 

//End Mindmap box
echo $OUTPUT->box_end(); 

//Footer
echo $OUTPUT->footer($course);
?>
