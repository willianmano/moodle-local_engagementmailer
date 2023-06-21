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

namespace local_engagementmailer\table;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/tablelib.php');

use table_sql;
use moodle_url;
use html_writer;

/**
 * Logs table class.
 *
 * @package    local_engagementmailer
 * @copyright  2023 We Are Boq
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logs extends table_sql {

    protected $context;
    protected $mailer;

    public function __construct($uniqueid, $context, $mailer) {
        parent::__construct($uniqueid);

        $this->context = $context;
        $this->mailer = $mailer;

        $this->define_columns(['id', 'fullname', 'email', 'timesent']);

        $this->define_headers(['ID', get_string('fullname'), 'E-mail', get_string('timesent', 'local_engagementmailer')]);

        $this->define_baseurl(new moodle_url('/local/engagementmailer/logs.php', ['id' => $mailer->id]));

        $this->sortable(true, 'id', SORT_DESC);

        $this->base_sql();

        $this->set_attribute('class', 'table table-bordered table-entries');
    }

    public function base_sql() {
        $fields = 'l.id, l.timecreated as timesent, u.firstname, u.lastname, u.email';

        $from = '{engagementmailer_logs} l INNER JOIN {user} u ON u.id = l.userid';

        $where = 'l.mailerid = :mailerid';

        $params = ['mailerid' => $this->mailer->id];

        $this->set_sql($fields, $from, $where, $params);
    }

    public function col_fullname($user) {
        return $user->firstname . ' ' . $user->lastname;
    }

    public function col_timesent($user) {
        return userdate($user->timesent);
    }
}
