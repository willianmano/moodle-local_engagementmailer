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

use Matrix\Exception;

defined('MOODLE_INTERNAL') || die;

/**
 * Mail utility class.
 *
 * @package    local_engagementmailer
 * @copyright  2023 We Are Boq
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mail {
    public function send($mailer, $students) {
        global $DB;

        $mail = $this->get_mail_method();

        $transaction = $DB->start_delegated_transaction();

        try {
            $mail->send_bulk($mailer, $students);

            $data = [];
            foreach ($students as $student) {
                $log = new \stdClass();
                $log->mailerid = $mailer->id;
                $log->userid = $student->id;
                $log->timecreated = time();

                $data[] = $log;
            }

            $DB->insert_records('engagementmailer_logs', $data);

            $DB->commit_delegated_transaction($transaction);
        } catch (\Exception $e) {
            $DB->rollback_delegated_transaction($transaction, $e);
        }
    }

    private function get_mail_method() {
        global $CFG;

        if (isset($CFG->mailjetapikey) && isset($CFG->mailjetsecretkey)) {
            return new mailjet();
        }

        return new mailmoodle();
    }
}
