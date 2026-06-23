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
use local_dixeo\external\service_factory;
use local_dixeo\local\content_image_capability;
use local_dixeo\service\image_generation_service;
use local_dixeo\service\pluginfile_image_helper;

/**
 * Start a content image edit job.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class start_edit extends external_api {
    use location_parameters;

    /**
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        $location = self::location_parameters_definition();
        return new external_function_parameters(array_merge($location->keys, [
            'instructions' => new external_value(PARAM_RAW, 'Edit instructions', VALUE_REQUIRED),
            'size' => new external_value(PARAM_TEXT, 'Image size', VALUE_DEFAULT, image_generation_service::DEFAULT_SIZE),
            'quality' => new external_value(PARAM_ALPHA, 'Quality', VALUE_DEFAULT, image_generation_service::DEFAULT_QUALITY),
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
     * @param string $instructions
     * @param string $size
     * @param string $quality
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
        string $instructions,
        string $size = image_generation_service::DEFAULT_SIZE,
        string $quality = image_generation_service::DEFAULT_QUALITY
    ): array {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'contextid' => $contextid,
            'component' => $component,
            'filearea' => $filearea,
            'itemid' => $itemid,
            'filepath' => $filepath,
            'filename' => $filename,
            'courseid' => $courseid,
            'instructions' => $instructions,
            'size' => $size,
            'quality' => $quality,
        ]);

        $location = self::validate_location($params);
        content_image_capability::require_edit($location->courseid);

        if (trim($params['instructions']) === '') {
            throw new \moodle_exception('instructions_required', 'filter_dixeo_imageeditor');
        }

        if (lock_manager::has_blocking_lock($location)) {
            throw new \moodle_exception('error_locked', 'filter_dixeo_imageeditor');
        }

        $imageurl = $location->get_pluginfile_url();
        $b64 = pluginfile_image_helper::image_url_to_base64($imageurl);

        $imageservice = service_factory::get_image_generation_service();
        $result = $imageservice->submit_content_image_edit_job(
            $location->courseid,
            [$b64],
            trim($params['instructions']),
            $params['size'],
            $params['quality']
        );

        $jobid = trim((string) $result->jobid);
        if ($jobid === '') {
            throw new \moodle_exception('dixeo_image_job_empty_result', 'local_dixeo');
        }

        return self::queue_content_image_job(
            $location,
            $jobid,
            (int) $USER->id,
            file_replacer::SOURCE_EDITED
        );
    }

    /**
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'jobid' => new external_value(PARAM_RAW, 'Remote job id'),
            'status' => new external_value(PARAM_ALPHA, 'Lock status'),
        ]);
    }
}
