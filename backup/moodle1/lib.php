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
 * Provides support for the conversion of moodle1 backup to the moodle2 format
 *
 * @package    mod_mindmap
 * @author Tonis Tartes <tonis.tartes@gmail.com>
 * @copyright  2011 Tonis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Mindmap conversion handler.
 */
class moodle1_mod_mindmap_handler extends moodle1_mod_handler {

    /** @var moodle1_file_manager */
    protected $fileman = null;

    /** @var int cmid */
    protected $moduleid = null;

    /**
     * Declare the paths in moodle.xml we are able to convert
     *
     * The method returns list of {@link convert_path} instances. For each path returned,
     * at least one of on_xxx_start(), process_xxx() and on_xxx_end() methods must be
     * defined. The method process_xxx() is not executed if the associated path element is
     * empty (i.e. it contains none elements or sub-paths only).
     *
     * Note that the path /MOODLE_BACKUP/COURSE/MODULES/MOD/MINDMAP does not
     * actually exist in the file. The last element with the module name was
     * appended by the moodle1_converter class.
     *
     * @return array of {@link convert_path} instances
     */
    public function get_paths() {
        return array(
            new convert_path('mindmap', '/MOODLE_BACKUP/COURSE/MODULES/MOD/MINDMAP')
        );
    }

    /**
     * This is executed every time we have one /MOODLE_BACKUP/COURSE/MODULES/MOD/MINDMAP
     * data available.
     * @param array $data
     */
    public function process_mindmap($data) {
        global $CFG;

        // Get the course module id and context id.
        $instanceid     = $data['id'];
        $cminfo         = $this->get_cminfo($instanceid);
        $this->moduleid = $cminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $this->moduleid);

        // Replay the upgrade step 2009042006.
        if ($CFG->texteditors !== 'textarea') {
            $data['intro']       = text_to_html($data['intro'], false, false, true);
            $data['introformat'] = FORMAT_HTML;
        }

        // Get a fresh new file manager for this instance.
        $this->fileman = $this->converter->get_file_manager($contextid, 'mod_mindmap');

        // Convert course files embedded into the intro.
        $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $data['intro'] = moodle1_converter::migrate_referenced_files($data['intro'], $this->fileman);

        // Start writing mindmap.xml.
        $this->open_xml_writer("activities/mindmap_{$this->moduleid}/mindmap.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $this->moduleid,
            'modulename' => 'mindmap', 'contextid' => $contextid));
        $this->xmlwriter->begin_tag('mindmap', array('id' => $instanceid));

        foreach ($data as $field => $value) {
            if ($field <> 'id') {
                $this->xmlwriter->full_tag($field, $value);
            }
        }
    }

    /**
     * This is executed when we reach the closing </MOD> tag of our 'mindmap' path
     */
    public function on_mindmap_end() {
        // Finalize mindmap.xml.
        $this->xmlwriter->end_tag('mindmap');
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

        // Write inforef.xml.
        $this->open_xml_writer("activities/mindmap_{$this->moduleid}/inforef.xml");
        $this->xmlwriter->begin_tag('inforef');
        $this->xmlwriter->end_tag('inforef');
        $this->close_xml_writer();
    }
}
