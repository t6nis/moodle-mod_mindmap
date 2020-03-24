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

defined('MOODLE_INTERNAL') || die;

$services = array(
    'Mindmap service' => array(
        'functions' => array('mod_mindmap_submit_mindmap'),
        'enabled' => 1
    )
);

$functions = array(
    'mod_mindmap_submit_mindmap' => array(
        'classname' => 'mod_mindmap_external',
        'methodname' => 'submit_mindmap',
        'classpath' => 'mod/mindmap/externallib.php',
        'description' => 'Save mindmap form by ajax',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
);

