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
require_once('lib.php');
global $CFG, $DB, $USER, $PAGE, $OUTPUT;
require_once($CFG->libdir . '/completionlib.php');

$id = required_param('id', PARAM_INT); // Course Module ID, or
$userid = optional_param('userid', 0, PARAM_INT);
$mindmapid = optional_param('mindmapid', 0, PARAM_INT);
$create = optional_param('create', 0, PARAM_INT);

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
    // Individual mindmap feature.
    if ($create == 1 && !empty($userid) && !empty($mindmapid)) {
        mindmap_add_individual_instance($mindmapid, $userid);
        unset($create);
        redirect(new moodle_url('/mod/mindmap/view.php', array('id' => $id)));
    }
}

require_login($course, false, $cm);
$context = context_module::instance($cm->id);

// Individual mindmap feature.
if ($mindmap->mindmapmode == 2) {
    if (!$userid) {
        $userid = $USER->id;
    }
    // This is meant so that users cant peek each others mindmaps! Only teachers can!
    if ($userid !== $USER->id && !has_capability('moodle/course:manageactivities', $context, $USER->id)) {
        $userid = $USER->id;
    }
    $mindmap_individual = $DB->get_record('mindmap_individual', array('mindmapid' => $mindmap->id, 'userid' => $userid));
}

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

$enrolled_users = get_enrolled_users($context, '', 0, 'u.id, u.firstname, u.lastname', 'u.lastname');

if ($mindmap->mindmapmode == 2 && !$mindmap_individual) {

    $jsmodule = array(
        'name' => 'mod_mindmap',
        'fullpath' => '/mod/mindmap/module.js',
        'requires' => array('base', 'io', 'io-base', 'io-form', 'node', 'json')
    );
    $PAGE->requires->js_init_call('M.mod_mindmap.user_selector', null, false, $jsmodule);

    echo $OUTPUT->header();
    echo $OUTPUT->box(format_module_intro('mindmap', $mindmap, $cm->id), 'generalbox', 'intro');

    echo html_writer::start_tag('div', array('class' => 'mindmap_actions'));
    if (has_capability('moodle/course:manageactivities', $context, $USER->id)) {
        $mindmap_options = '';
        foreach($enrolled_users as $key => $value) {
            $mindmap_options .= '<option value="'.$key.'" '.($userid == $key ? 'selected' : '').'>'.$value->firstname.' '.$value->lastname.'</option>';
        }
        echo html_writer::start_tag('div', array('class' => 'mindmap_action_select'));
        echo get_string('viewuser', 'mindmap').': ';
        echo html_writer::tag('select', $mindmap_options, array('id' => 'mindmap_select', 'value' => 'Select'));
        echo html_writer::end_tag('div');
    }
    echo html_writer::end_tag('div');

    echo $OUTPUT->box_start('generalbox', 'mindmap_view');
    if (isset($userid) && $userid == $USER->id) {
        echo "<form method=\"post\" action=\"\" id=\"mindmapform\">";
        echo "<input type=\"hidden\" name=\"id\" value=\"$cm->id\" />";
        echo "<input type=\"hidden\" name=\"userid\" value=\"$USER->id\" />";
        echo "<input type=\"hidden\" name=\"mindmapid\" value=\"$mindmap->id\" />";
        echo "<input type=\"hidden\" name=\"create\" value=\"1\" />";
        echo html_writer::tag('input', '', ['type' => 'submit', 'id' => 'mindmapcreate', 'value' => get_string('mindmapcreate', 'mindmap')]);
        echo "</form>";
    } else {
        echo get_string('mindmapnotcreated', 'mindmap');
    }
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer($course);
    exit();
}

if ($mindmap->mindmapmode == 2 && !empty($mindmap_individual)) {
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
    $hide_lockbtn = 0;
    if ($mindmap->locking > 0 && $mindmap_individual->locked > 0 && $mindmap_individual->lockedbyuser != $USER->id) {
        $locked = 1;
    }
    if (isloggedin() && isguestuser()) {
        $locked = 1;
    }
    if ($mindmap->editable == 0 && !has_capability('moodle/course:manageactivities', $context, $USER->id)) {
        $locked = 1;
    }
    if ($USER->id != $mindmap_individual->userid) {
        $locked = 1;
        $hide_lockbtn = 1;
    }
    if ($mindmap->locking > 0) {
        $PAGE->requires->js_init_call('M.mod_mindmap.init_lock', array($mindmap_individual->id, $mindmap_individual->locked,
            $mindmap_individual->lockedbyuser, $USER->id), false, $jsmodule);
    }
    $PAGE->requires->js('/mod/mindmap/javascript/vis-network.min.js', true);
    $PAGE->requires->js('/mod/mindmap/javascript/jscolor.js', true);
    $PAGE->requires->js_init_call('M.mod_mindmap.user_selector', null, false, $jsmodule);
    $strings = get_strings(
        array('visjsedit', 'visjsdel', 'visjsback', 'visjsaddnode', 'visjsaddedge', 'visjseditnode',
            'visjseditedge', 'visjsadddescription', 'visjsedgedescription', 'visjseditedgedescription',
            'visjscreateedgeerror', 'visjsdeleteclustererror', 'visjseditclustererror'), 'mod_mindmap');
    $PAGE->requires->js_call_amd('mod_mindmap/mindmap-vis', 'Init', array($mindmap->id, $locked, current_language(),
        $strings, $mindmap->mindmapmode, $mindmap_individual->id));

    echo $OUTPUT->header();

    echo $OUTPUT->box(format_module_intro('mindmap', $mindmap, $cm->id), 'generalbox', 'intro');
    echo $OUTPUT->box_start('generalbox', 'mindmap_view');

    // Locking info
    if ($locked == 1) {
        echo html_writer::start_tag('div', array('class' => 'mindmap_actions'));
        if (has_capability('moodle/course:manageactivities', $context, $USER->id)) {
            $mindmap_options = '';
            foreach($enrolled_users as $key => $value) {
                $mindmap_options .= '<option value="'.$key.'" '.($userid == $key ? 'selected' : '').'>'.$value->firstname.' '.$value->lastname.'</option>';
            }
            echo html_writer::start_tag('div', array('class' => 'mindmap_action_select'));
            echo get_string('viewuser', 'mindmap').': ';
            echo html_writer::tag('select', $mindmap_options, array('id' => 'mindmap_select', 'value' => 'Select'));
            echo html_writer::end_tag('div');
        }
        echo html_writer::end_tag('div');
        echo html_writer::start_tag('div', array('class' => 'mindmap_locked'));
        if (!isguestuser()) {
            if ($mindmap_individual->lockedbyuser > 0) {
                $user = $DB->get_record('user', array('id' => $mindmap_individual->lockedbyuser), 'firstname, lastname', MUST_EXIST);
                echo html_writer::tag('span', get_string('mindmaplocked', 'mindmap', $user));
            }
        }
        // Override lock for teachers.
        if (has_capability('moodle/course:manageactivities', $context, $USER->id) && $hide_lockbtn == 0) {
            echo "<div class=\"mindmap-unlock-button\">";
            echo "<form method=\"post\" action=\"unlock.php\" id=\"mindmapform\">";
            echo "<input type=\"hidden\" name=\"id\" value=\"$mindmap_individual->id\" />";
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
        echo html_writer::start_tag('div', array('class' => 'mindmap_actions'));
        if ($USER->id == $mindmap_individual->userid) {
            echo html_writer::start_tag('div', array('class' => 'mindmap_action_save'));
            echo html_writer::tag('input', '', array('type' => 'hidden', 'id' => 'mindmapid', 'name' => 'mindmapid', 'value' => $mindmap_individual->id));
            echo html_writer::tag('input', '', array('type' => 'button', 'id' => 'export_button', 'value' => get_string('mindmapsave', 'mindmap')));
            echo html_writer::end_tag('div');
        }
        if (has_capability('moodle/course:manageactivities', $context, $USER->id)) {
            $mindmap_options = '';
            foreach($enrolled_users as $key => $value) {
                $mindmap_options .= '<option value="'.$key.'" '.($userid == $key ? 'selected' : '').'>'.$value->firstname.' '.$value->lastname.'</option>';
            }
            echo html_writer::start_tag('div', array('class' => 'mindmap_action_select'));
            echo get_string('viewuser', 'mindmap').': ';
            echo html_writer::tag('select', $mindmap_options, array('id' => 'mindmap_select', 'value' => 'Select'));
            echo html_writer::end_tag('div');
        }
        echo html_writer::end_tag('div');
    }
} else {

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
    if (isloggedin() && isguestuser()) {
        $locked = 1;
    }
    if ($mindmap->editable == 0 && !has_capability('moodle/course:manageactivities', $context, $USER->id)) {
        $locked = 1;
    }
    if ($mindmap->locking > 0) {
        $PAGE->requires->js_init_call('M.mod_mindmap.init_lock', array($mindmap->id, $mindmap->locked, $mindmap->lockedbyuser, $USER->id), false, $jsmodule);
    }
    $PAGE->requires->js('/mod/mindmap/javascript/vis-network.min.js', true);
    $PAGE->requires->js('/mod/mindmap/javascript/jscolor.js', true);
    $PAGE->requires->js_init_call('M.mod_mindmap.user_selector', null, false, $jsmodule);
    $strings = get_strings(
        array('visjsedit', 'visjsdel', 'visjsback', 'visjsaddnode', 'visjsaddedge', 'visjseditnode',
            'visjseditedge', 'visjsadddescription', 'visjsedgedescription', 'visjseditedgedescription',
            'visjscreateedgeerror', 'visjsdeleteclustererror', 'visjseditclustererror'), 'mod_mindmap');
    $PAGE->requires->js_call_amd('mod_mindmap/mindmap-vis', 'Init', array($mindmap->id, $locked, current_language(), $strings, $mindmap->mindmapmode));

    echo $OUTPUT->header();

    echo $OUTPUT->box(format_module_intro('mindmap', $mindmap, $cm->id), 'generalbox', 'intro');
    echo $OUTPUT->box_start('generalbox', 'mindmap_view');

    // Locking info
    if ($locked == 1) {
        echo html_writer::start_tag('div', array('class' => 'mindmap_locked'));
        if (!isguestuser()) {
            if ($mindmap->lockedbyuser > 0) {
                $user = $DB->get_record('user', array('id' => $mindmap->lockedbyuser), 'firstname, lastname', MUST_EXIST);
                echo html_writer::tag('span', get_string('mindmaplocked', 'mindmap', $user));
            }
        }
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
}

echo html_writer::start_tag('div', array('id' => 'network-container', 'class' => 'network-container'));
echo html_writer::tag('div', '', array('class' => 'resetzoom'));
echo html_writer::start_tag('div', array('id' => 'network', 'class' => 'network'));
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
?>

<?php
echo $OUTPUT->box_end();
echo $OUTPUT->footer($course);