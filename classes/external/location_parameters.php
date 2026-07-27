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

namespace filter_dixeo_imageeditor\external;

use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use filter_dixeo_imageeditor\adapter\eligibility;
use filter_dixeo_imageeditor\adapter\feature_gate;
use filter_dixeo_imageeditor\adapter\file_replacer;
use local_dixeo\repository\image\job_repository;
use local_dixeo\service\image\job_orchestrator;
use local_dixeo\service\image\content\location;
use local_dixeo\service\image\content_target;

/**
 * Shared validation for filter externals.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait location_parameters {

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    protected static function location_parameters_definition(): external_function_parameters {
        return new external_function_parameters([
            'contextid' => new external_value(PARAM_INT, 'File context id', VALUE_REQUIRED),
            'component' => new external_value(PARAM_COMPONENT, 'File component', VALUE_REQUIRED),
            'filearea' => new external_value(PARAM_AREA, 'File area', VALUE_REQUIRED),
            'itemid' => new external_value(PARAM_INT, 'File item id', VALUE_REQUIRED),
            'filepath' => new external_value(PARAM_PATH, 'File path', VALUE_DEFAULT, '/'),
            'filename' => new external_value(PARAM_FILE, 'File name', VALUE_REQUIRED),
            'courseid' => new external_value(PARAM_INT, 'Course id', VALUE_REQUIRED),
        ]);
    }

    /**
     * Validate the file coordinates and authorise the user against the file's own course.
     *
     * The course id is derived server-side from the stored file's context; the client
     * supplied courseid is never used for authorisation.
     *
     * @param array $params
     * @return location
     */
    protected static function validate_location(array $params): location {
        $definition = self::location_parameters_definition();
        $params = self::validate_parameters($definition, array_intersect_key($params, $definition->keys));

        $file = location::from_params($params)->get_stored_file();
        if (!$file || !eligibility::is_eligible_stored_file($file)) {
            throw new \moodle_exception('error_not_eligible', 'filter_dixeo_imageeditor');
        }

        $location = location::from_stored_file($file);
        if ($location->courseid < 1) {
            throw new \moodle_exception('error_not_eligible', 'filter_dixeo_imageeditor');
        }

        self::validate_context(\context_course::instance($location->courseid));
        feature_gate::require_filter_edit($location->courseid);

        return $location;
    }

    /**
     * Standard response after an in-place image replacement.
     *
     * @param location $location
     * @return array<string, mixed>
     */
    protected static function image_apply_returns(location $location): array {
        return [
            'imageurl' => file_replacer::get_current_image_url($location),
            'current_contenthash' => file_replacer::get_current_contenthash($location),
            'history' => file_replacer::get_history_for_location($location),
        ];
    }

    /**
     * Queue lock and adhoc poll task after a remote job was accepted.
     *
     * @param location $location
     * @param string $jobid
     * @param int $userid
     * @param string $source
     * @param string|null $prompt
     * @param string|null $quality
     * @param string|null $mode
     * @return array{jobid: string, status: string}
     */
    protected static function queue_content_image_job(
        location $location,
        string $jobid,
        int $userid,
        string $source,
        ?string $prompt = null,
        ?string $quality = null,
        ?string $mode = null
    ): array {
        job_orchestrator::submit_and_queue(
            content_target::from_location($location),
            $jobid,
            $userid,
            [
                'placeholderid' => null,
                'targettable' => null,
                'targetfield' => null,
                'targetid' => null,
                'cmid' => null,
                'origin' => job_repository::ORIGIN_MODAL,
                'prompt' => $prompt,
                'quality' => $quality,
                'mode' => $mode,
            ],
            $source
        );

        return ['jobid' => $jobid, 'status' => job_repository::STATUS_PENDING];
    }

    /**
     * Return external structure for version history rows.
     *
     * @return external_multiple_structure
     */
    protected static function history_returns(): external_multiple_structure {
        return new external_multiple_structure(new external_single_structure([
            'id' => new external_value(PARAM_INT, 'Version id'),
            'source' => new external_value(PARAM_ALPHA, 'Source'),
            'contenthash' => new external_value(PARAM_ALPHANUMEXT, 'Archived contenthash'),
            'timecreated' => new external_value(PARAM_INT, 'Created time'),
            'usermodified' => new external_value(PARAM_INT, 'User id'),
            'previewurl' => new external_value(PARAM_URL, 'Preview URL'),
        ]));
    }

    /**
     * Returns description of method results.
     *
     * @return external_single_structure
     */
    protected static function location_status_returns(): external_single_structure {
        return new external_single_structure([
            'status' => new external_value(PARAM_ALPHA, 'idle|pending|processing|applied|failed'),
            'imageurl' => new external_value(PARAM_URL, 'Updated image URL', VALUE_OPTIONAL),
            'current_contenthash' => new external_value(PARAM_ALPHANUMEXT, 'Current file contenthash', VALUE_OPTIONAL),
            'errormessage' => new external_value(PARAM_RAW, 'Error message', VALUE_OPTIONAL),
            'lockid' => new external_value(PARAM_INT, 'Lock id', VALUE_OPTIONAL),
            'prefill_prompt' => new external_value(PARAM_RAW, 'Prefill prompt', VALUE_OPTIONAL),
            'prefill_quality' => new external_value(PARAM_ALPHA, 'Prefill quality', VALUE_OPTIONAL),
            'prefill_mode' => new external_value(PARAM_ALPHA, 'Prefill mode', VALUE_OPTIONAL),
        ]);
    }
}
