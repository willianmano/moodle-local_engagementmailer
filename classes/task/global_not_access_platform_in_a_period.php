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
class global_not_access_platform_in_a_period extends scheduled_task {
    public function get_name() {
        return get_string('global_not_access_platform_in_a_period', 'local_engagementmailer');
    }

    public function execute() {
        global $DB;

        $sql = 'SELECT * FROM {engagementmailer_mailers} WHERE moment = :moment AND enabled = 1';

        $mailers = $DB->get_records_sql($sql, ['moment' => 'global_not_access_platform_in_a_period']);

        if (!$mailers) {
            return true;
        }

        // TODO: Remover o <> 0, pois estes sao alunos que nunca acessaram.
        foreach ($mailers as $mailer) {
            $sql = 'SELECT * FROM {user}
                    WHERE lastaccess < :timestart AND lastaccess > :timeend AND lastaccess <> 0
                    AND id NOT IN (SELECT userid FROM {engagementmailer_logs} WHERE mailerid = :mailerid)';

            $timestart = time() - ($mailer->mindays * 60 * 60 * 24);
            $timeend = time() - ($mailer->maxdays * 60 * 60 * 24);

            $params = [
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
