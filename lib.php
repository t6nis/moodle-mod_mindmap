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
 * Mindmap core interaction API
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2011 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Library of functions and constants for module newmodule
 * This file should have two well differenced parts:
 *   - All the core Moodle functions, neeeded to allow
 *     the module to work integrated in Moodle.
 *   - All the newmodule specific functions, needed
 *     to implement all the module logic. Please, note
 *     that, if the module become complex and this lib
 *     grows a lot, it's HIGHLY recommended to move all
 *     these module specific functions to a new php file,
 *     called "locallib.php" (see forum, quiz...). This will
 *     help to save some memory when Moodle is performing
 *     actions across all modules.
 */

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will create a new instance and return the id number 
 * of the new instance.
 *
 * @param object $instance An object from the form in mod.html
 * @return int The id of the newly inserted newmodule record
 **/
function mindmap_add_instance($mindmap) {
    
    global $USER, $DB;
    
    $mindmap->xmldata = '<MindMap>
                             <MM>
                               <Node x_Coord="400" y_Coord="270">
                                 <Text>Moodle</Text>
                                 <Format Underlined="0" Italic="0" Bold="0">
                                   <Font>Trebuchet MS</Font>
                                   <FontSize>14</FontSize>
                                   <FontColor>ffffff</FontColor>
                                   <BackgrColor>ff0000</BackgrColor>
                                 </Format>
                               </Node>
                             </MM>
                            </MindMap>';
    
    $mindmap->userid = $USER->id;
    
    if(isset($mindmap->editable)) {
        $mindmap->editable = '1';
    } else { 
        $mindmap->editable = '0';
    }
    
    $mindmap->timecreated = time();
    
    return $DB->insert_record("mindmap", $mindmap);
    
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

    if (!$mindmap->editable) {
        $mindmap->editable = 0;
    }
    
    return $DB->update_record("mindmap", $mindmap);
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
    
    if (! $mindmap = $DB->get_record("mindmap", array("id" => "$id"))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records("mindmap", array("id" => "$mindmap->id"))) {
        $result = false;
    }

    return $result;
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
    return $return;
}

/**
 * Print a detailed representation of what a user has done with 
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function mindmap_user_complete($course, $user, $mod, $newmodule) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity 
 * that has occurred in newmodule activities and print it out. 
 * Return true if there was output, or false is there was none. 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function mindmap_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such 
 * as sending out mail, toggling flags etc ... 
 *
 * @uses $CFG
 * @return boolean
 * @todo Finish documenting this function
 **/
function mindmap_cron () {
    global $CFG;

    return true;
}

/**
 * Must return an array of grades for a given instance of this module, 
 * indexed by user.  It also returns a maximum allowed grade.
 * 
 * Example:
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 *
 * @param int $newmoduleid ID of an instance of this module
 * @return mixed Null or object with an array of grades and with the maximum grade
 **/
function mindmap_grades($newmoduleid) {
   return NULL;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of newmodule. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 *
 * @param int $newmoduleid ID of an instance of this module
 * @return mixed boolean/array of students
 **/
function mindmap_get_participants($newmoduleid) {
    return false;
}

/**
 * This function returns if a scale is being used by one newmodule
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $newmoduleid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 **/
function mindmap_scale_used ($newmoduleid,$scaleid) {
    $return = false;

    //$rec = get_record("newmodule","id","$newmoduleid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}
   
    return $return;
}

/**
 * Checks if scale is being used by any instance of newmodule.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any newmodule
 */
function mindmap_scale_used_anywhere($scaleid) {
    
    global $DB;
    
    if ($scaleid and $DB->record_exists('newmodule', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
    
}

/**
 * Execute post-install custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function mindmap_install() {
     return true;
}

function mindmap_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_OTHER;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_GROUPMEMBERSONLY:        return true;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;

        default: return null;
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
    $module_pagetype = array('mod-mindmap-*'=>get_string('page-mod-mindmap-x', 'mod_mindmap'));
    return $module_pagetype;
}


?>
