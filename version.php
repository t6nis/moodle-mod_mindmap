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
 * Mindmap plugin version info.
 *
 * @package    mod_mindmap
 * @author     Tonis Tartes <tonis.tartes@gmail.com>
 * @copyright  2020 Tonis Tartes <tonis.tartes@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version = 2024111800;  // The current module version (Date: YYYYMMDDXX).
$plugin->requires = 2022112800; // Requires Moodle 4.1.
$plugin->cron = 0;           // Period for cron to check this module (secs).
$plugin->component = 'mod_mindmap'; // Full name of the plugin (used for diagnostics).

$plugin->maturity = MATURITY_STABLE;
$plugin->release = "2024111800"; // User-friendly version number.
