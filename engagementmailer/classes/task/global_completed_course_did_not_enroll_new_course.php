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
 * This task class sends email for users who made not access the platform for some days.
 *
 * @package    local_engagementmailer
 * @copyright  2023 We Are Boq
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class global_completed_course_did_not_enroll_new_course extends scheduled_task {
    public function get_name() {
        return get_string('global_completed_course_did_not_enroll_new_course', 'local_engagementmailer');
    }

    public function execute() {
        global $DB;

        $sql = 'SELECT * FROM {engagementmailer_mailers} WHERE moment = :moment AND enabled = 1';

        $mailers = $DB->get_records_sql($sql, ['moment' => 'global_completed_course_did_not_enroll_new_course']);

        if (!$mailers) {
            return true;
        }

        // TODO: Remover progress > 10 apos demo
        foreach ($mailers as $mailer) {
            $sql = 'SELECT userid, MAX(timemodified) lastcompletiontime
                    FROM {uncc_course_progress} cp
                    WHERE cp.progress > 10
                    AND cp.userid NOT IN (SELECT userid FROM {engagementmailer_logs} WHERE mailerid = :mailerid)
                    GROUP BY userid';

            $params = [
                'mailerid' => $mailer->id
            ];

            $students = $DB->get_records_sql($sql, $params);

            if (!$students) {
                return true;
            }

            $timestart = time() - ($mailer->mindays * 60 * 60 * 24);
            $timeend = time() - ($mailer->maxdays * 60 * 60 * 24);

            $studentstomail = [];
            foreach ($students as $student) {
                // Aluno concluiu um curso recentemente, em outras palavras, antes tempo que o minimo para fazer outro tenha expirado.
                // Se o valor para se inscrever em outro curso for 30 dias, o aluno concluiu um curso há menos de 30 dias atrás.
                if ($student->lastcompletiontime > $timestart || $student->lastcompletiontime < $timeend) {
                    continue;
                }

                $sql = 'SELECT * FROM {user_enrolments} WHERE userid = :userid AND timecreated > :lastcompletiontime';
                $enrolments = $DB->get_records_sql($sql, ['userid' => $student->userid, 'lastcompletiontime' => $student->lastcompletiontime]);
                // Se o aluno se matriculou em novos cursos apos a conclusao do ultimo curso, nao envia email.
                if ($enrolments) {
                    continue;
                }

                $studentstomail[] = $student;
            }

            $students = null;

            if ($studentstomail) {
                $mail = new \local_engagementmailer\util\mail();

                $mail->send($mailer, $studentstomail);
            }
        }
    }
}
