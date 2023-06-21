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

/**
 * Create file
 *
 * @package    local_engagementmailer
 * @copyright  2023 We Are Boq
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
// Course id.
$courseid = required_param('courseid', PARAM_INT);

$id = optional_param('id', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHANUMEXT);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

$context = context_course::instance($course->id);

require_capability('moodle/course:update', $context);

$params = [
    'courseid' => $courseid
];

if ($id) {
    $params['id'] = $id;
}

if ($action) {
    $params['action'] = $action;
}

$url = new moodle_url('/local/engagementmailer/mailer.php', $params);

$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_pagelayout('standard');

$mailer = null;
if ($action == 'update' || $action == 'delete') {
    $mailer = $DB->get_record('engagementmailer_mailers', ['id' => $id], '*', MUST_EXIST);
}

$redirecturl = new moodle_url('/local/engagementmailer/index.php', ['id' => $courseid]);

if ($action == 'delete') {
    try {
        if (!confirm_sesskey()) {
            redirect($redirecturl, get_string('invaliddata', 'error'), null, \core\output\notification::NOTIFY_ERROR);
        }

        $DB->delete_records('engagementmailer_mailers', ['id' => $mailer->id]);

        $DB->delete_records('engagementmailer_logs', ['mailerid' => $mailer->id]);

        redirect($redirecturl, get_string('deletesuccess', 'local_engagementmailer'), null, \core\output\notification::NOTIFY_ERROR);
    } catch (\Exception $e) {
        redirect($redirecturl, get_string('invaliddata', 'error'), null, \core\output\notification::NOTIFY_ERROR);
    }
}

$form = new \local_engagementmailer\form\mailer($url, ['courseid' => $courseid, 'data' => $mailer]);

if ($form->is_cancelled()) {
    redirect($redirecturl);
}

if ($formdata = $form->get_data()) {
    $mailerdata = new \stdClass();
    $mailerdata->moment = $formdata->moment;
    $mailerdata->mindays = $formdata->mindays;
    $mailerdata->maxdays = $formdata->maxdays;
    $mailerdata->name = $formdata->name;
    $mailerdata->subject = $formdata->subject;
    $mailerdata->body = $formdata->body['text'];
    $mailerdata->enabled = $formdata->enabled;
    $mailerdata->timemodified = time();

    if ($action == 'create') {
        $mailerdata->courseid = $formdata->courseid;
        $mailerdata->timecreated = time();

        $DB->insert_record('engagementmailer_mailers', $mailerdata);

        redirect($redirecturl, get_string('createsuccess', 'local_engagementmailer'), null, \core\output\notification::NOTIFY_SUCCESS);
    }

    if ($action == 'update') {
        $mailerdata->id = $mailer->id;

        $DB->update_record('engagementmailer_mailers', $mailerdata);

        redirect($redirecturl, get_string('editsuccess', 'local_engagementmailer'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

$PAGE->navbar->add(format_string($course->fullname), new moodle_url('/course/view.php', ['id' => $courseid]));
$PAGE->navbar->add(get_string('pluginname', 'local_engagementmailer'), $redirecturl);

echo $OUTPUT->header();

$form->display();

echo $OUTPUT->footer();
