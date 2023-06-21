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

namespace local_engagementmailer\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;

/**
 * Logs renderable class.
 *
 * @package    local_engagementmailer
 * @copyright  2023 We Are Boq
 * @author     Willian Mano <willianmanoaraujo@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logs implements renderable, templatable {
    protected $context;
    protected $mailer;

    public function __construct($context, $mailer) {
        $this->context = $context;
        $this->mailer = $mailer;
    }

    public function export_for_template(renderer_base $output) {
        $table = new \local_engagementmailer\table\logs(
            'local-engagementmailer-logs-table',
            $this->context,
            $this->mailer,
        );

        $table->collapsible(false);

        ob_start();
        $table->out(30, true);
        $logstable = ob_get_contents();
        ob_end_clean();

        return [
            'logstable' => $logstable,
            'mailer' => $this->mailer
        ];
    }
}
