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
require_once($CFG->libdir . '/completionlib.php');

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
$context = context_module::instance($cm->id);

add_to_log($course->id, 'mindmap', 'view', 'view.php?id='.$cm->id, $mindmap->name, $cm->id);
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

/// Print the page header
$strmindmaps  = get_string('modulenameplural', 'mindmap');
$strmindmap   = get_string('modulename', 'mindmap');
$strname      = format_string($mindmap->name);

//$PAGE params
$PAGE->set_url('/mod/mindmap/view.php', array('id'=>$cm->id));  
$PAGE->set_title($strname);
$PAGE->set_heading($course->fullname);

//JS Lock
$jsmodule = array(
    'name'     => 'mod_mindmap',
    'fullpath' => '/mod/mindmap/module.js',
    'requires' => array('base', 'io', 'io-base', 'io-form', 'node', 'json'),
    'strings' => array(
        array('mindmapunlocked', 'mindmap')
    )
);
if ($mindmap->locking > 0) {
    $PAGE->requires->js_init_call('M.mod_mindmap.init_lock', array($mindmap->id, $mindmap->locked, $mindmap->lockedbyuser, $USER->id), false, $jsmodule);
}

//Header
echo $OUTPUT->header();

//Intro box
echo $OUTPUT->box(format_module_intro('mindmap', $mindmap, $cm->id), 'generalbox', 'intro');

//Mindmap box
echo $OUTPUT->box_start('generalbox', 'mindmap_view'); 

echo html_writer::tag('div', get_string('mindmaphint', 'mindmap'), array('class' => 'mindmap_hint', 'id' => 'mindmap_hint'));
//Locking info 
if ($mindmap->locking > 0 && $mindmap->locked > 0 && $mindmap->lockedbyuser != $USER->id) {
    $user = $DB->get_record('user', array('id' => $mindmap->lockedbyuser), 'firstname, lastname', MUST_EXIST);
    echo html_writer::start_tag('div', array('class' => 'mindmap_locked'));
    echo html_writer::tag('span', get_string('mindmaplocked', 'mindmap', $user));
    //Override lock for teachers
    if (has_capability('moodle/course:manageactivities', $context, $USER->id)) {
        echo "<div class=\"mindmap-unlock-button\">";
        echo "<form method=\"post\" action=\"unlock.php\" id=\"mindmapform\">";        
        echo "<input type=\"hidden\" name=\"id\" value=\"$mindmap->id\" />";
        echo "<input type=\"hidden\" name=\"uid\" value=\"$USER->id\" />";
        echo "<input type=\"submit\" name=\"unlock\" value=\"Unlock\">";
        echo "</form>";
        echo "</div>";
    }
    echo html_writer::end_tag('div');
}

echo html_writer::tag('div', '', array('id' => 'flashcontent'));

?>
<script type="text/javascript" src="./javascript/swfobject.js"></script>	
<script type="text/javascript">
    // <![CDATA[
    var swf_width = document.getElementById('mindmap_hint').offsetWidth; //Set SWF width
    //Width calculations
    if (swf_width > 1200) {
        swf_width = swf_width - 1;
    } else {
        swf_width = swf_width - 11;
    }
    var so = new SWFObject('<?php echo $CFG->wwwroot; ?>/mod/mindmap/viewer.swf?uVal=<?php echo rand(0,100); ?>', 'viewer', swf_width, 600, '9', '#FFFFFF');
    so.addVariable('load_url', '<?php echo $CFG->wwwroot; ?>/mod/mindmap/xml.php?id=<?php echo $mindmap->id;?>');
    <?php if(!isguestuser() && ((has_capability('moodle/course:manageactivities', $context, $USER->id)) || ($mindmap->editable == '1'))): ?>
            so.addVariable('save_url', '<?php echo $CFG->wwwroot; ?>/mod/mindmap/save.php?id=<?php echo $mindmap->id;?>');
            <?php if ($mindmap->locking == 0) { ?>
                    so.addVariable('editable', 'true');
            <?php } else { ?>
                <?php if ($mindmap->locking > 0 && (($mindmap->locked == 1 && $mindmap->lockedbyuser == $USER->id) || ($mindmap->locked < 1))) { ?>
                    so.addVariable('editable', 'true');
                <?php } ?>
            <?php } ?>
    <?php endif; ?>
    so.addVariable('lang', 'en');
    so.addVariable('wmode', 'direct');
    so.write('flashcontent');
    // ]]>
</script>
<?php 

//End Mindmap box
echo $OUTPUT->box_end(); 

//Footer
echo $OUTPUT->footer($course);

?>