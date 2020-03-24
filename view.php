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
 * Mindmap view page.
 *
 * @package    mod_mindmap
 * @author     Tonis Tartes <tonis.tartes@gmail.com>
 * @copyright  2020 Tonis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/completionlib.php');

$id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
$a = optional_param('a', 0, PARAM_INT);

if ($id) {
    if (!$cm = get_coursemodule_from_id('mindmap', $id)) {
        print_error('Course Module ID was incorrect');
    }

    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('Course is misconfigured');
    }

    if (!$mindmap = $DB->get_record('mindmap', array('id' => $cm->instance))) {
        print_error('Course module is incorrect');
    }
} else {
    if (!$mindmap = $DB->get_record('mindmap', array('id' => $a))) {
        print_error('Course module is incorrect');
    }
    if (!$course = $DB->get_record('course', array('id' => $mindmap->course))) {
        print_error('Course is misconfigured');
    }
    if (!$cm = get_coursemodule_from_instance('mindmap', $mindmap->id, $course->id)) {
        print_error('Course Module ID was incorrect');
    }
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Trigger events.
$params = array(
    'context' => $context,
    'objectid' => $mindmap->id
);
$event = \mod_mindmap\event\course_module_viewed::create($params);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('mindmap', $mindmap);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Print the page header.
$strmindmaps = get_string('modulenameplural', 'mindmap');
$strmindmap = get_string('modulename', 'mindmap');
$strname = format_string($mindmap->name);

$PAGE->set_url('/mod/mindmap/view.php', array('id' => $cm->id));
$PAGE->set_title($strname);
$PAGE->set_heading($course->fullname);

// JS Lock.
$jsmodule = array(
    'name' => 'mod_mindmap',
    'fullpath' => '/mod/mindmap/module.js',
    'requires' => array('base', 'io', 'io-base', 'io-form', 'node', 'json'),
    'strings' => array(
        array('mindmapunlocked', 'mindmap')
    )
);
$locked = 0;
if ($mindmap->locking > 0 && $mindmap->locked > 0 && $mindmap->lockedbyuser != $USER->id) {
    $locked = 1;
}
if ($mindmap->locking > 0) {
    $PAGE->requires->js_init_call('M.mod_mindmap.init_lock', array($mindmap->id, $mindmap->locked, $mindmap->lockedbyuser, $USER->id), false, $jsmodule);
}
$PAGE->requires->js('/mod/mindmap/javascript/vis-network.min.js', true);
$PAGE->requires->js('/mod/mindmap/javascript/jscolor.js', true);
$PAGE->requires->js_call_amd('mod_mindmap/mindmap-vis', 'Init', array($mindmap->id, $locked));

echo $OUTPUT->header();

echo $OUTPUT->box(format_module_intro('mindmap', $mindmap, $cm->id), 'generalbox', 'intro');
echo $OUTPUT->box_start('generalbox', 'mindmap_view');

// Locking info
if ($locked == 1) {
    $user = $DB->get_record('user', array('id' => $mindmap->lockedbyuser), 'firstname, lastname', MUST_EXIST);
    echo html_writer::start_tag('div', array('class' => 'mindmap_locked'));
    echo html_writer::tag('span', get_string('mindmaplocked', 'mindmap', $user));
    // Override lock for teachers.
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
} else {
    echo html_writer::start_tag('div', array('id' => 'network-popup'))
    ?>
    <span id="operation">node</span>
    <table>
        <tr>
            <td><?php echo get_string('label', 'mindmap'); ?></td>
            <td><input id="node-label" value=""/></td>
        </tr>
        <tr>
            <td><?php echo get_string('textcolor', 'mindmap'); ?></td>
            <td><input class="jscolor {hash:true}" id="node-font-color" value="#343434"/></td>
        </tr>
        <tr>
            <td><?php echo get_string('bgcolor', 'mindmap'); ?></td>
            <td><input class="jscolor {hash:true}" id="node-color-background" value="#97c1fc"/></td>
        </tr>
        <tr>
            <td><?php echo get_string('shape', 'mindmap'); ?></td>
            <td>
                <select name="node-shape" id="node-shape">
                    <option value="ellipse"><?php echo get_string('ellipse', 'mindmap'); ?></option>
                    <option value="circle"><?php echo get_string('circle', 'mindmap'); ?></option>
                    <option value="box"><?php echo get_string('box', 'mindmap'); ?></option>
                    <option value="text"><?php echo get_string('text', 'mindmap'); ?></option>
                    <option value="database"><?php echo get_string('database', 'mindmap'); ?></option>
                </select>
            </td>
        </tr>
    </table>
    <?php
    echo html_writer::tag('input', '', array('type' => 'hidden', 'id' => 'node-id', 'value' => 'new value'));
    echo html_writer::tag('input', '', array('type' => 'button', 'id' => 'savebutton', 'value' => get_string('save')));
    echo html_writer::tag('input', '', array('type' => 'button', 'id' => 'cancelbutton', 'value' => get_string('cancel')));
    echo html_writer::end_tag('div');
    echo html_writer::tag('input', '', array('type' => 'hidden', 'id' => 'mindmapid', 'name' => 'mindmapid', 'value' => $mindmap->id));
    echo html_writer::tag('input', '', array('type' => 'button', 'id' => 'export_button', 'value' => get_string('mindmapsave', 'mindmap')));
}
echo html_writer::start_tag('div', array('id' => 'network', 'class' => 'network'));
echo html_writer::end_tag('div');
?>

<?php
echo $OUTPUT->box_end();
echo $OUTPUT->footer($course);