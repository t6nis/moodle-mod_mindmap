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
 * @package    mod_mindmap
 * @author     Tonis Tartes <tonis.tartes@gmail.com>
 * @copyright  2020 Tonis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('lib.php');

$id = optional_param('id', 0, PARAM_INT);

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
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$strname = format_string($mindmap->name);

$PAGE->set_url('/mod/mindmap/view.php', array('id' => $cm->id));
$PAGE->set_title($strname);
$PAGE->set_heading($course->fullname);

$PAGE->requires->js('/mod/mindmap/javascript/vis-network.min.js', true);
$PAGE->requires->js('/mod/mindmap/javascript/jscolor.js', true);
$strings = get_strings(
    array('visjsedit', 'visjsdel', 'visjsback', 'visjsaddnode', 'visjsaddedge', 'visjseditnode',
        'visjseditedge', 'visjsadddescription', 'visjsedgedescription', 'visjseditedgedescription',
        'visjscreateedgeerror', 'visjsdeleteclustererror', 'visjseditclustererror'), 'mod_mindmap');
$PAGE->requires->js_call_amd('mod_mindmap/mindmap-vis', 'Init', array($mindmap->id, 1, 1, current_language(), $strings));

echo $OUTPUT->header();
// IF there is no xmldata currently available..
if (empty($mindmap->xmldata)) {
    echo html_writer::tag('div', get_string('nothingtoconvert', 'mindmap'));
    echo $OUTPUT->footer($course);
    exit();
}

echo html_writer::tag('div', get_string('convertinfo', 'mindmap'));
echo html_writer::tag('div', get_string('convertflash', 'mindmap'), array('class' => 'mindmap_hint', 'id' => 'mindmap_hint'));

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
        var so = new SWFObject('<?php echo $CFG->wwwroot; ?>/mod/mindmap/viewer.swf?uVal=<?php echo rand(0, 100); ?>',
            'viewer', swf_width, 600, '9', '#FFFFFF');
        so.addVariable('load_url', '<?php echo $CFG->wwwroot; ?>/mod/mindmap/xml.php?id=<?php echo $mindmap->id;?>');
        so.addVariable('lang', 'en');
        so.addVariable('wmode', 'direct');
        so.write('flashcontent');
        // ]]>
    </script>

<?php echo html_writer::tag('div', '<a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p>', array('class' => 'getflash')); ?>

<?php
echo html_writer::tag('div', get_string('convertjs', 'mindmap'), array('class' => 'mindmap_hint', 'id' => 'mindmap_hint'));

echo html_writer::start_tag('div', array('id' => 'network', 'class' => 'network'));
echo html_writer::end_tag('div');
echo html_writer::start_tag('div', array('id' => 'convert-save', 'class' => 'convert-save'));
echo html_writer::tag('input', '', array('type' => 'hidden', 'id' => 'mindmapid', 'name' => 'mindmapid', 'value' => $mindmap->id));
echo html_writer::tag('input', '', array('type' => 'button', 'id' => 'export_button', 'value' => get_string('mindmapsave', 'mindmap')));
echo html_writer::end_tag('div');
echo $OUTPUT->footer($course);