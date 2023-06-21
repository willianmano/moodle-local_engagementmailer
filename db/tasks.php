<?php
// This file is part of eMailTest plugin for Moodle - http://moodle.org/
//
// eMailTest is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// eMailTest is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with eMailTest.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Tasks definition file
 *
 * @package    local_engagementmailer
 * @copyright  2023 We Are Boq
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$tasks = [
    [
        'classname' => 'local_engagementmailer\task\course_not_finished_in_a_period',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '4',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
    [
        'classname' => 'local_engagementmailer\task\global_not_access_platform_in_a_period',
        'blocking' => 0,
        'minute' => '30',
        'hour' => '4',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
    [
        'classname' => 'local_engagementmailer\task\global_completed_course_did_not_enroll_new_course',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '5',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];
