<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace filter_dixeo_imageeditor\adapter;

use local_dixeo\service\image\content\apply_handler;
use local_dixeo\service\image\content\location;

/**
 * Applies modal poll results via file_replacer (version history).
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class modal_apply_handler implements apply_handler {
    /**
     * Apply a modal poll job result via file_replacer.
     *
     * @param location $location
     * @param array $jobresult
     * @param int $userid
     * @param string $source
     * @return void
     */
    public function apply_job_result(
        location $location,
        array $jobresult,
        int $userid,
        string $source
    ): void {
        file_replacer::apply_job_result($location, $jobresult, $userid, $source);
    }
}
