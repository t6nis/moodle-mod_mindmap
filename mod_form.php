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
 * Mindmap instance add/edit form.
 *
 * @package    mod_mindmap
 * @author     Tonis Tartes <tonis.tartes@gmail.com>
 * @copyright  2020 Tonis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_mindmap_mod_form extends moodleform_mod {

    function definition() {

        global $CFG, $COURSE, $DB;
        $mform =& $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('mindmapname', 'mindmap'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        $mform->addElement('advcheckbox', 'editable', get_string('editable', 'mindmap'), '', array('group' => 1), array(0, 1));
        $mform->setDefault('editable', 1);
        $mform->addElement('advcheckbox', 'locking', get_string('locking', 'mindmap'), '', array('group' => 1), array(0, 1));
        $mform->setDefault('locking', 1);

        $mindmapmodeoptions = array ('1' => get_string('mindmapmodecollaborative', 'mindmap'), '2' => get_string('mindmapmodeindividual', 'mindmap'));
        // Don't allow changes to the wiki type once it is set.
        $mindmaptype_attr = array();
        if (!empty($this->_instance)) {
            $mindmaptype_attr['disabled'] = 'disabled';
        }
        $mform->addElement('select', 'mindmapmode', get_string('mindmapmode', 'mindmap'), $mindmapmodeoptions, $mindmaptype_attr);
        $mform->addHelpButton('mindmapmode', 'mindmapmode', 'mindmap');

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

    }
}