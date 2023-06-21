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

namespace local_engagementmailer\form;

use local_engagementmailer\util\course;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir. '/formslib.php');

/**
 * Create form.
 *
 * @package    local_engagementmailer
 * @copyright  2023 We Are Boq
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mailer extends \moodleform {
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        if (isset($this->_customdata['courseid'])) {
            $mform->setDefault('courseid', $this->_customdata['courseid']);
        }

        $courseutil = new course();

        $mform->addElement('select', 'moment', get_string('moment', 'local_engagementmailer'), $courseutil->get_course_moments_options($this->_customdata['courseid']));
        $mform->addRule('moment', get_string('required'), 'required', '', 'client');

        $mform->addElement('text', 'mindays', get_string('mindays', 'local_engagementmailer'));
        $mform->setType('mindays', PARAM_INT);
        $mform->addRule('mindays', get_string('required'), 'required', '', 'client');
        $mform->addRule('mindays', null, 'numeric', null, 'client');

        $mform->addElement('text', 'maxdays', get_string('maxdays', 'local_engagementmailer'));
        $mform->setType('maxdays', PARAM_INT);
        $mform->addRule('maxdays', get_string('required'), 'required', '', 'client');
        $mform->addRule('maxdays', null, 'numeric', null, 'client');

        $mform->addElement('text', 'name', get_string('name', 'local_engagementmailer'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', '', 'client');

        $mform->addElement('text', 'subject', get_string('subject', 'local_engagementmailer'));
        $mform->addRule('subject', get_string('required'), 'required', '', 'client');
        $mform->setType('subject', PARAM_TEXT);

        $mform->addElement('editor', 'body', get_string('body', 'local_engagementmailer'));
        $mform->addHelpButton('body', 'body', 'local_engagementmailer');
        $mform->setType('body', PARAM_RAW);
        $mform->addRule('body', get_string('required'), 'required', '', 'client');

        $mform->addElement('selectyesno', 'enabled', get_string('enabled', 'local_engagementmailer'));
        $mform->setDefault('enabled', true);

        $this->add_action_buttons(true);

        if (isset($this->_customdata['data'])) {
            $mailer = $this->_customdata['data'];

            $body = $mailer->body;

            $mailer->body = [];
            $mailer->body['text'] = $body;

            $this->set_data($mailer);
        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['name']) || mb_strlen(strip_tags($data['name'])) < 3) {
            $errors['name'] = get_string('validation:minlen', 'local_engagementmailer', 3);
        }

        if (empty($data['subject']) || mb_strlen(strip_tags($data['subject'])) < 3) {
            $errors['subject'] = get_string('validation:minlen', 'local_engagementmailer');
        }

        if ($data['body'] && !empty($data['body']['text']) && mb_strlen(strip_tags($data['body']['text'])) < 10) {
            $errors['body'] = get_string('validation:minlen', 'local_engagementmailer', 10);
        }

        return $errors;
    }
}
