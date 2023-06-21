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
 * Index file
 *
 * @package    local_engagementmailer
 * @copyright  2023 We Are Boq
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');

// Course id.
$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);

$context = context_course::instance($course->id);

require_capability('moodle/course:update', $context);

$PAGE->set_context($context);
$PAGE->set_url('/local/engagementmailer/index.php', ['id' => $course->id]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_pagelayout('standard');

$PAGE->navbar->add(format_string($course->fullname), new moodle_url('/course/view.php', ['id' => $id]));
$PAGE->navbar->add(get_string('pluginname', 'local_engagementmailer'));

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('local_engagementmailer');

$contentrenderable = new \local_engagementmailer\output\index($course, $context);

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();
