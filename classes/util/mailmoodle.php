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
 * Mail utility class.
 *
 * @package    local_engagementmailer
 * @copyright  2023 We Are Boq
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mailmoodle extends mailbase {
    public function send_bulk($mailer, $students) {
         foreach ($students as $student) {
            $this->send($mailer, $student);
        }
    }

    public function send($mailer, $student) {
        global $CFG, $SITE, $PAGE;

        if (!empty($student->deleted)) {
            debugging('Can not send email to deleted user: '.$student->id, DEBUG_DEVELOPER);

            return false;
        }

        if (!validate_email($student->email)) {
            // We can not send emails to invalid addresses - it might create security issue or confuse the mailer.
            debugging("email_to_user: User $student->id (".fullname($student).") email ($student->email) is invalid! Not sending.");

            return false;
        }

        if (over_bounce_threshold($student)) {
            debugging("email_to_user: User $student->id (".fullname($student).") is over bounce threshold! Not sending.");

            return false;
        }

        $mail = get_mailer();

        if (!empty($mail->SMTPDebug)) {
            echo '<pre>' . "\n";
        }

        $temprecipients = [];
        $tempreplyto = [];

        $noreplyaddressdefault = 'noreply@' . get_host_from_url($CFG->wwwroot);
        $noreplyaddress = empty($CFG->noreplyaddress) ? $noreplyaddressdefault : $CFG->noreplyaddress;

        // Make up an email address for handling bounces.
        if (!empty($CFG->handlebounces)) {
            $modargs = 'B'.base64_encode(pack('V', $student->id)).substr(md5($student->email), 0, 16);
            $mail->Sender = generate_email_processing_address(0, $modargs);
        } else {
            $mail->Sender = $noreplyaddress;
        }

        $admin = get_admin();

        $mail->From = $noreplyaddress;

        $fromdetails = new \stdClass();
        $fromdetails->name = fullname($admin);
        $fromdetails->url = preg_replace('#^https?://#', '', $CFG->wwwroot);
        $fromdetails->siteshortname = format_string($SITE->shortname);

        $fromstring = get_string('emailvia', 'core', $fromdetails);

        $mail->FromName = $fromstring;

        $tempreplyto[] = [$noreplyaddress, $fromstring];
        $temprecipients[] = [$student->email, fullname($student)];

        $finalbody = $this->replace_body_variables($student, $mailer->body);

        $renderer = $PAGE->get_renderer('core');

        $messagehtml = $renderer->render_from_template('local_engagementmailer/mail', [
            'content' => $finalbody,
            'sitefullname' => $SITE->fullname,
            'year' => date('Y')
        ]);

        $context = [
            'sitefullname' => $SITE->fullname,
            'siteshortname' => $SITE->shortname,
            'sitewwwroot' => $CFG->wwwroot,
            'subject' => $mailer->subject,
            'to' => $student->email,
            'toname' => fullname($student),
            'from' => $mail->From,
            'fromname' => $mail->FromName,
            'body' => $messagehtml
        ];

        $mail->Subject = $renderer->render_from_template('core/email_subject', $context);
        $mail->FromName = $renderer->render_from_template('core/email_fromname', $context);
        $messagetext = $renderer->render_from_template('core/email_text', $context);

        // Autogenerate a MessageID if it's missing.
        if (empty($mail->MessageID)) {
            $mail->MessageID = generate_email_messageid();
        }

        $mail->isHTML(true);
        $mail->Encoding = 'quoted-printable';
        $mail->Body = $messagehtml;
        $mail->AltBody = "\n$messagetext\n";

        // Check if the email should be sent in an other charset then the default UTF-8.
        if ((!empty($CFG->sitemailcharset) || !empty($CFG->allowusermailcharset))) {

            // Use the defined site mail charset or eventually the one preferred by the recipient.
            $charset = $CFG->sitemailcharset;
            if (!empty($CFG->allowusermailcharset)) {
                if ($studentemailcharset = get_user_preferences('mailcharset', '0', $student->id)) {
                    $charset = $studentemailcharset;
                }
            }

            // Convert all the necessary strings if the charset is supported.
            $charsets = get_list_of_charsets();
            unset($charsets['UTF-8']);
            if (in_array($charset, $charsets)) {
                $mail->CharSet  = $charset;
                $mail->FromName = \core_text::convert($mail->FromName, 'utf-8', strtolower($charset));
                $mail->Subject  = \core_text::convert($mail->Subject, 'utf-8', strtolower($charset));
                $mail->Body     = \core_text::convert($mail->Body, 'utf-8', strtolower($charset));
                $mail->AltBody  = \core_text::convert($mail->AltBody, 'utf-8', strtolower($charset));

                foreach ($temprecipients as $key => $values) {
                    $temprecipients[$key][1] = \core_text::convert($values[1], 'utf-8', strtolower($charset));
                }
                foreach ($tempreplyto as $key => $values) {
                    $tempreplyto[$key][1] = \core_text::convert($values[1], 'utf-8', strtolower($charset));
                }
            }
        }

        foreach ($temprecipients as $values) {
            $mail->addAddress($values[0], $values[1]);
        }

        foreach ($tempreplyto as $values) {
            $mail->addReplyTo($values[0], $values[1]);
        }

        if ($mail->send()) {
            set_send_count($student);

            if (!empty($mail->SMTPDebug)) {
                echo '</pre>';
            }

            return true;
        } else {
            // Trigger event for failing to send email.
            $event = \core\event\email_failed::create(array(
                'context' => \context_system::instance(),
                'userid' => $admin->id,
                'relateduserid' => $student->id,
                'other' => array(
                    'subject' => $mailer->subject,
                    'message' => $messagetext,
                    'errorinfo' => $mail->ErrorInfo
                )
            ));
            $event->trigger();

            if (CLI_SCRIPT) {
                mtrace('Error: lib/moodlelib.php email_to_user(): '.$mail->ErrorInfo);
            }

            if (!empty($mail->SMTPDebug)) {
                echo '</pre>';
            }

            return false;
        }
    }
}
