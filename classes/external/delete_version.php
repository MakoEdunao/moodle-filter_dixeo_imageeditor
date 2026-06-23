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

namespace filter_dixeo_imageeditor\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use filter_dixeo_imageeditor\local\file_replacer;
use filter_dixeo_imageeditor\local\lock_manager;

/**
 * Delete one archived version from history.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class delete_version extends external_api {
    use location_parameters;

    /**
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        $location = self::location_parameters_definition();
        return new external_function_parameters(array_merge($location->keys, [
            'versionid' => new external_value(PARAM_INT, 'Version row id', VALUE_REQUIRED),
        ]));
    }

    /**
     * @param int $contextid
     * @param string $component
     * @param string $filearea
     * @param int $itemid
     * @param string $filepath
     * @param string $filename
     * @param int $courseid
     * @param int $versionid
     * @return array
     */
    public static function execute(
        int $contextid,
        string $component,
        string $filearea,
        int $itemid,
        string $filepath,
        string $filename,
        int $courseid,
        int $versionid
    ): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'component' => $component,
            'filearea' => $filearea,
            'itemid' => $itemid,
            'filepath' => $filepath,
            'filename' => $filename,
            'courseid' => $courseid,
            'versionid' => $versionid,
        ]);

        $location = self::validate_location($params);

        if (lock_manager::has_blocking_lock($location)) {
            throw new \moodle_exception('error_delete_blocked', 'filter_dixeo_imageeditor');
        }

        file_replacer::delete_version((int) $params['versionid'], $location);

        return [
            'current_contenthash' => file_replacer::get_current_contenthash($location),
            'history' => file_replacer::get_history_for_location($location),
        ];
    }

    /**
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'current_contenthash' => new external_value(PARAM_ALPHANUMEXT, 'Current file contenthash'),
            'history' => self::history_returns(),
        ]);
    }
}
