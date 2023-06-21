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
 * Mailjet utility class.
 *
 * @package    local_engagementmailer
 * @copyright  2023 We Are Boq
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mailjet extends mailbase {
    protected $renderer;

    public function __construct() {
        global $PAGE;

        $this->renderer = $PAGE->get_renderer('core');
    }

    public function send($mailer, $student) {
        $this->send_bulk($mailer, [$student]);
    }

    public function send_bulk($mailer, $students) {
        global $CFG;

        $messages = $this->get_messages($mailer, $students);

        if (!$messages) {
            return;
        }

        $jsondata = json_encode($messages);

        $key = $CFG->mailjetapikey;
        $secret = $CFG->mailjetsecretkey;
        $auth = base64_encode($key . ":" . $secret);

        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.mailjet.com/v3.1/send",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $jsondata,
                CURLOPT_HTTPHEADER => array(
                    "authorization: Basic " . $auth,
                    "Content-length: " . strlen($jsondata),
                    "Content-Type: application/json"
                ),
            ));

            $response = curl_exec($curl);

            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                throw new \Exception($err);
            }

            $response = json_decode($response);

            if (!empty($response) && isset($response->StatusCode) && $response->StatusCode == 400) {
                throw new \Exception($response->ErrorMessage, 400);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function get_messages($mailer, $students) {
        $admin = get_admin();

        $from = [
            'Email' => $admin->email,
            'Name' => fullname($admin)
        ];

        $messages = [];
        foreach ($students as $student) {
            $finalbody = $this->replace_body_variables($student, $mailer->body);

            $messagehtml = $this->renderer->render_from_template('local_engagementmailer/mail', [
                'content' => $finalbody,
                'year' => date('Y')
            ]);

            $messages['Messages'][] = [
                'From' => $from,
                'To' => [
                    [
                        'Email' => $student->email,
                        'Name' => fullname($student)
                    ]
                ],
                'Subject' => $mailer->subject,
                'HTMLPart' => $messagehtml,
                'TextPart' => $finalbody
            ];
        }

        return $messages;
    }
}
