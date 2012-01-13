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
 * Mindmap instance add/edit form
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2011 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * This file defines de main newmodule configuration form
 * It uses the standard core Moodle (>1.8) formslib. For
 * more info about them, please visit:
 * 
 * http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * The form must provide support for, at least these fields:
 *   - name: text element of 64cc max
 *
 * Also, it's usual to use these fields:
 *   - intro: one htmlarea element to describe the activity
 *            (will be showed in the list of activities of
 *             newmodule type (index.php) and in the header 
 *             of the newmodule main page (view.php).
 *   - introformat: The format used to write the contents
 *             of the intro field. It automatically defaults 
 *             to HTML when the htmleditor is used and can be
 *             manually selected if the htmleditor is not used
 *             (standard formats are: MOODLE, HTML, PLAIN, MARKDOWN)
 *             See lib/weblib.php Constants and the format_text()
 *             function for more info
 */

require_once ('moodleform_mod.php');

class mod_mindmap_mod_form extends moodleform_mod {

    function definition() {

        global $CFG, $COURSE, $DB;
        $mform    =& $this->_form;

        /// Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));
        
        /// Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('mindmapname', 'mindmap'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('editor', 'introeditor', get_string('mindmapintro', 'mindmap'), null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->context));
        $mform->setType('introeditor', PARAM_RAW); // no XSS prevention here, users must be trusted
        $mform->addRule('introeditor', get_string('required'), 'required', null, 'client');

        $mform->addElement('checkbox', 'editable', get_string('editable', 'mindmap'));
        //$mform->setAdvanced('groupmembersonly');

        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();

        // add standard buttons, common to all modules
        $this->add_action_buttons();

    }
}

?>
