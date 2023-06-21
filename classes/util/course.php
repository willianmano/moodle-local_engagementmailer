<?php
// This file is part of the eMailTest plugin for Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_engagementmailer\util;

defined('MOODLE_INTERNAL') || die;

/**
 * Course utility class.
 *
 * @package    local_engagementmailer
 * @copyright  2023 We Are Boq
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course {
    function get_courses() {
        global $DB;

        $sql = "SELECT id, fullname, shortname FROM {course} WHERE id > 1 AND visible = 1";

        $courses = $DB->get_records_sql($sql);

        if (!$courses) {
            return [];
        }

        $data = [];

        $data[0] = get_string('chooseanoption', 'local_engagementmailer');
        foreach ($courses as $course) {
            $data[$course->id] = $course->shortname . ' - ' . $course->fullname;
        }

        return $data;
    }

    public function get_course_moments_options($courseid) {
        if ($courseid == 1) {
            return [
                'global_not_access_platform_in_a_period' => get_string('global_not_access_platform_in_a_period', 'local_engagementmailer'),
                'global_completed_course_did_not_enroll_new_course' => get_string('global_completed_course_did_not_enroll_new_course', 'local_engagementmailer'),
            ];
        }

        return [
            'course_not_finished_in_a_period' => get_string('course_not_finished_in_a_period', 'local_engagementmailer')
        ];
    }
}