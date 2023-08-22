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
 * Mindmap core API.
 *
 * @package    mod_mindmap
 * @author     Tonis Tartes <tonis.tartes@gmail.com>
 * @copyright  2020 Tonis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod.html) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted $mindmap record
 **/
function mindmap_add_instance($mindmap) {

    global $USER, $DB;

    $mindmap->mindmapdata = '';
    $mindmap->userid = $USER->id;
    $mindmap->timecreated = time();

    return $DB->insert_record('mindmap', $mindmap);

}

/**
 * @param $mindmapid
 * @param $userid
 * @return bool|int
 * @throws dml_exception
 */
function mindmap_add_individual_instance($mindmapid, $userid) {

    global $DB;

    $mindmap = new stdClass();
    $mindmap->mindmapid = $mindmapid;
    $mindmap->mindmapdata = '';
    $mindmap->userid = $userid;
    $mindmap->timecreated = time();
    $mindmap->timemodified = time();

    return $DB->insert_record('mindmap_individual', $mindmap);

}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod.html) this function
 * will update an existing instance with new data.
 *
 * @param object $instance An object from the form in mod.html
 * @return boolean Success/Fail
 **/
function mindmap_update_instance($mindmap) {

    global $DB;

    $mindmap->timemodified = time();
    $mindmap->id = $mindmap->instance;

    return $DB->update_record('mindmap', $mindmap);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 **/
function mindmap_delete_instance($id) {

    global $DB;

    if (!$mindmap = $DB->get_record('mindmap', array('id' => $id))) {
        return false;
    }

    $result = true;

    if (!$DB->delete_records('mindmap', array('id' => $mindmap->id))) {
        $result = false;
    }

    if (!$DB->delete_records('mindmap_individual', array('mindmapid' => $mindmap->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Called by course/reset.php
 */
function mindmap_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'mindmapheader', get_string('modulenameplural', 'mindmap'));

    $mform->addElement('checkbox', 'delete_mindmap_all_content', get_string('deleteallmindmapscontent','mindmap'));
}

function mindmap_reset_course_form_defaults($course) {
    return array('delete_mindmap_all_content' => 1);
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 * This function will clean up all mindmaps.
 *
 * @global object
 * @global object
 * @param $data the data submitted from the reset course.
 * @return array status array
 */
function mindmap_reset_userdata($data) {
    global $DB;

    $status = array();
    $componentstr = get_string('modulenameplural', 'mindmap');

    if (!empty($data->delete_mindmap_all_content)) {
        $course = get_course($data->courseid);
        $mindmaps = get_all_instances_in_course('mindmap', $course);
        foreach ($mindmaps as $mindmap) {
            $mindmap->mindmapdata = '';
            $DB->update_record('mindmap', $mindmap);
            // Individual mindmaps must be deleted too.
            if (isset($mindmap->mindmapmode) && $mindmap->mindmapmode == 2) {
                $DB->delete_records("mindmap_individual", array("mindmapid" => $mindmap->id));
            }
        }
        $status[] = array('component' => $componentstr, 'item' => get_string('deleteallmindmapscontent', 'mindmap'), 'error' => false);
    }

    return $status;
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 **/
function mindmap_user_outline($course, $user, $mod, $mindmap) {
    global $DB;

    if ($logs = $DB->get_records('log', array('userid' => $user->id, 'module' => 'mindmap',
        'action' => 'view', 'info' => $mindmap->id), 'time ASC')) {

        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new stdClass();
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;

        return $result;
    }

    return null;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function mindmap_user_complete($course, $user, $mod, $mindmap) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in $mindmap activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @uses $CFG
 * @todo Finish documenting this function
 **/
function mindmap_print_recent_activity($course, $isteacher, $timestart) {
    return false;
}

/**
 * No cron in mindmap.
 *
 * @return boolean
 * @uses $CFG
 * @todo Finish documenting this function
 **/
function mindmap_cron() {
    return false;
}

/**
 * No grading in mindmap.
 * @param int $mindmapid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 **/
function mindmap_grades($mindmapid) {
    return null;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of $mindmap. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $mindmapid ID of an instance of this module
 * @return mixed boolean/array of students
 **/
function mindmap_get_participants($mindmapid) {
    return false;
}

/**
 * This function returns if a scale is being used by one $mindmap
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $mindmap ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 **/
function mindmap_scale_used($mindmapid, $scaleid) {
    return false;
}

/**
 * Checks if scale is being used by any instance of mindmap
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any $mindmap
 */
function mindmap_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * @param $feature
 * @return bool|int|null
 */
function mindmap_supports($feature) {
    if (defined('FEATURE_MOD_PURPOSE')) {
        if ($feature == FEATURE_MOD_PURPOSE) {
            return MOD_PURPOSE_COLLABORATION;
        }
    }
    
    switch ($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_OTHER;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Returns all other caps used in module
 * @return array
 */
function mindmap_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * List of view style log actions
 * @return array
 */
function mindmap_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List of update style log actions
 * @return array
 */
function mindmap_get_post_actions() {
    return array('update', 'add');
}

/**
 * Return a list of page types
 *
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array
 */
function mindmap_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $modulepagetype = array('mod-mindmap-*' => get_string('page-mod-mindmap-x', 'mod_mindmap'));
    return $modulepagetype;
}

/**
 * Extend module settings navigation and add conversion link.
 *
 * @param $settingsnav
 * @param $context
 */
function mindmap_extend_settings_navigation(settings_navigation $settings, navigation_node $mindmap) {
    global $PAGE;
}
