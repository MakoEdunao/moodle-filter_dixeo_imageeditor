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

namespace filter_dixeo_imageeditor\task;

use filter_dixeo_imageeditor\adapter\file_replacer;
use local_dixeo\service\image\content\location;

/**
 * Delete version history rows and archived files whose target image no longer exists.
 *
 * Version rows reference course content files by location; when the file, module,
 * or course is deleted the history would otherwise accumulate forever in the
 * system-context file storage.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_version_history extends \core\task\scheduled_task {
    /**
     * Return the scheduled task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_cleanup_version_history', 'filter_dixeo_imageeditor');
    }

    /**
     * Remove orphaned version history rows and files.
     *
     * @return void
     */
    public function execute(): void {
        global $DB;

        $fs = get_file_storage();
        $systemcontextid = \context_system::instance()->id;

        // Cache per locationhash: does the target file still exist?
        $targetexists = [];

        $rs = $DB->get_recordset('filter_dixeo_imageeditor_version', null, 'id ASC');
        foreach ($rs as $version) {
            $hash = (string) $version->locationhash;
            if (!array_key_exists($hash, $targetexists)) {
                $location = location::from_job_record($version);
                $targetexists[$hash] = $location->get_stored_file() !== null;
            }
            if ($targetexists[$hash]) {
                continue;
            }

            $fs->delete_area_files(
                $systemcontextid,
                'filter_dixeo_imageeditor',
                file_replacer::FILEAREA_HISTORY,
                (int) $version->id
            );
            $DB->delete_records('filter_dixeo_imageeditor_version', ['id' => $version->id]);
        }
        $rs->close();
    }
}
