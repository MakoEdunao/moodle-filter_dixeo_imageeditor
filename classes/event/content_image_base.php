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

namespace filter_dixeo_imageeditor\event;

use local_dixeo\service\image\content\location;

/**
 * Base event for embedded content image audit records.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class content_image_base extends \core\event\base {

    /**
     * Init method.
     */
    protected function init(): void {
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'files';
    }

    /**
     * Return a course view URL for the event.
     *
     * @return \moodle_url
     */
    public function get_url() {
        $courseid = (int) ($this->other['courseid'] ?? 0);
        if ($courseid > 0) {
            return new \moodle_url('/course/view.php', ['id' => $courseid]);
        }

        return new \moodle_url('/');
    }

    /**
     * Build event data for a content image location.
     *
     * @param location $location
     * @param int $userid
     * @param array $extraother
     * @return array
     */
    protected static function build_location_data(location $location, int $userid, array $extraother = []): array {
        $context = \context_course::instance($location->courseid);

        $other = array_merge([
            'courseid' => $location->courseid,
            'locationhash' => $location->hash(),
            'component' => $location->component,
            'filearea' => $location->filearea,
            'filename' => $location->filename,
        ], $extraother);

        $file = $location->get_stored_file();

        return [
            'context' => $context,
            'objectid' => $file ? (int) $file->get_id() : 0,
            'userid' => $userid,
            'other' => $other,
        ];
    }
}
