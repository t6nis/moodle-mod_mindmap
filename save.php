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
 * Saving mindmap nodes
 *
 * @package    mod
 * @subpackage mindmap
 * @author ekpenso.com
 * @copyright  2012 Tõnis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT); // Course Module ID, or
$xml = optional_param('mindmap', '', PARAM_RAW); 

if ($id) {
    if (!$mindmap = $DB->get_record('mindmap', array('id' => $id))) {
        error('Course module is incorrect');
    }
}

require_login($mindmap->course);

if($xml) {

    if(get_magic_quotes_gpc()) {
        $xml = stripslashes($xml);
    }

    $new = new stdClass();
    $new->id = $id;
    $new->xmldata = $xml;

    $DB->update_record('mindmap', $new);

}