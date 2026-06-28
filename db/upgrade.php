<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade steps for filter_dixeo_imageeditor.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @param int $oldversion
 * @return bool
 */
function xmldb_filter_dixeo_imageeditor_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026062000) {
        if ($dbman->table_exists('filter_dixeo_imageeditor_lock')
            && $dbman->table_exists('local_dixeo_content_image_job')
        ) {
            $locks = $DB->get_records('filter_dixeo_imageeditor_lock');
            foreach ($locks as $lock) {
                if ($DB->record_exists('local_dixeo_content_image_job', ['locationhash' => $lock->locationhash])) {
                    continue;
                }
                $record = (object) [
                    'placeholderid' => null,
                    'contextid' => $lock->contextid,
                    'component' => $lock->component,
                    'filearea' => $lock->filearea,
                    'itemid' => $lock->itemid,
                    'filepath' => $lock->filepath,
                    'filename' => $lock->filename,
                    'locationhash' => $lock->locationhash,
                    'courseid' => $lock->courseid,
                    'targettable' => null,
                    'targetfield' => null,
                    'targetid' => null,
                    'cmid' => null,
                    'origin' => 'modal',
                    'prompt' => null,
                    'quality' => null,
                    'mode' => null,
                    'jobid' => $lock->jobid,
                    'status' => $lock->status,
                    'errormessage' => $lock->errormessage,
                    'userid' => $lock->userid,
                    'timecreated' => $lock->timecreated,
                    'timemodified' => $lock->timemodified,
                ];
                $DB->insert_record('local_dixeo_content_image_job', $record);
            }

            $table = new xmldb_table('filter_dixeo_imageeditor_lock');
            if ($dbman->table_exists($table)) {
                $dbman->drop_table($table);
            }
        }

        upgrade_plugin_savepoint(true, 2026062000, 'filter', 'dixeo_imageeditor');
    }

    return true;
}
