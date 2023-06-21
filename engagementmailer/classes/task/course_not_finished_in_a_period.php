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

namespace local_engagementmailer\task;

defined('MOODLE_INTERNAL') || die();

use core\task\scheduled_task;

/**
 * This task class sends email for users who made their enrolment in a course
 * but did not finish it in a certain period of days.
 *
 * @package    local_engagementmailer
 * @copyright  2023 We Are Boq
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_not_finished_in_a_period extends scheduled_task {
    public function get_name() {
        return get_string('course_not_finished_in_a_period', 'local_engagementmailer');
    }

    public function execute() {
        global $DB;

        $sql = 'SELECT * FROM {engagementmailer_mailers} WHERE moment = :moment AND enabled = 1';

        $mailers = $DB->get_records_sql($sql, ['moment' => 'course_not_finished_in_a_period']);

        if (!$mailers) {
            return true;
        }

        foreach ($mailers as $mailer) {
            $sql = 'SELECT u.*
                    FROM {user} u
                    INNER JOIN {user_enrolments} ue ON ue.userid = u.id AND timeend = 0
                    INNER JOIN {enrol} e ON e.id = ue.enrolid
                    INNER JOIN {course} c ON c.id = e.courseid
                    INNER JOIN {context} co ON co.instanceid = c.id and contextlevel = 50
                    INNER JOIN {role_assignments} ra ON ra.userid = u.id AND ra.contextid = co.id
                    INNER JOIN {role} r ON r.id = ra.roleid AND r.id = 5
                    WHERE
                        c.id = :courseid1
                        AND ue.timestart < :timestart AND ue.timestart > :timeend
                        AND u.id NOT IN (SELECT userid FROM {uncc_course_progress} WHERE courseid = :courseid2 AND progress = 100)
                        AND u.id NOT IN (SELECT userid FROM {engagementmailer_logs} WHERE mailerid = :mailerid)
                    ORDER BY u.id;';

            $timestart = time() - ($mailer->mindays * 60 * 60 * 24);
            $timeend = time() - ($mailer->maxdays * 60 * 60 * 24);

            $params = [
                'courseid1' => $mailer->courseid,
                'courseid2' => $mailer->courseid,
                'timestart' => $timestart,
                'timeend' => $timeend,
                'mailerid' => $mailer->id
            ];

            $students = $DB->get_records_sql($sql, $params);

            if (!$students) {
                return true;
            }

            $mail = new \local_engagementmailer\util\mail();

            $mail->send($mailer, $students);
        }
    }
}
