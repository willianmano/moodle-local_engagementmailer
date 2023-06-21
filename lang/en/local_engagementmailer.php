<?php
// This file is part of eMailTest plugin for Moodle - http://moodle.org/
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
// along with eMailTest.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'local_engagementmailer', language 'en', branch 'MOODLE_410_STABLE'
 *
 * @package    local_engagementmailer
 * @copyright  2023 We Are Boq
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Engagement mailer';
$string['createanewmailer'] = 'Create a new mailer';
$string['editamailer'] = 'Edit mailer';

$string['chooseanoption'] = 'Choose an option';

$string['moment'] = 'Moment to ship';
$string['mindays'] = 'Min days';
$string['maxdays'] = 'Max days';
$string['name'] = 'Name';
$string['subject'] = 'Subject';
$string['enabled'] = 'Enabled';
$string['body'] = "Body";
$string['body_help'] = <<<BODY
<p>A personalized welcome message can be added as plain text or Moodle-auto format, including HTML tags and multi-lang tags.</p>
<p>The following placeholders can be included in the message:</p>
<ul>
<li>Username [[username]]</li>
<li>Full user name [[fullname]]</li>
<li>First user name [[firstname]]</li>
<li>Last user name [[lastname]]</li>
<li>User email [[email]]</li>
<li>User address [[address]]</li>
<li>User country [[country]]</li>
<li>User city [[city]]</li>
<li>User phone [[phone]]</li>
<li>User cellphone [[cellphone]]</li>
<li>Site url [[url]]</li>
<li>Site fullname [[sitename]]</li>
</ul>
BODY;

$string['totalemailssent'] = 'Total emails sent';
$string['disabled'] = 'Disabled';
$string['actions'] = 'Actions';
$string['timesent'] = 'Time sent';
$string['emailssubmissionslogs'] = 'Emails submissions logs';

$string['validation:minlen'] = 'This field must have at least {$a} digits.';
$string['createsuccess'] = 'Item created with success';
$string['editsuccess'] = 'Item edited with success';
$string['deletesuccess'] = 'Item deleted with success';

$string['confirm_yes'] = 'Yes, I want it!';
$string['confirm_no'] = 'Cancel';
$string['confirm_title'] = 'Are you sure?';
$string['confirm_msg'] = 'Once deleted, this item cannot be recovered!';

$string['course_not_finished_in_a_period'] = 'Course not finished in a period';
$string['global_not_access_platform_in_a_period'] = 'Not access platform in a period';
$string['global_completed_course_did_not_enroll_new_course'] = 'Completed a course but did not make a new enrolment in a period';
