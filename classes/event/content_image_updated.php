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
 * Fired when an embedded content image is replaced in place.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content_image_updated extends content_image_base {
    /**
     * Init method.
     */
    protected function init(): void {
        parent::init();
        $this->data['crud'] = 'u';
    }

    /**
     * Return the localised event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('eventcontentimageupdated', 'filter_dixeo_imageeditor');
    }

    /**
     * Return the localised event description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string('eventcontentimageupdateddesc', 'filter_dixeo_imageeditor', (object) [
            'userid' => $this->userid,
            'courseid' => (int) ($this->other['courseid'] ?? 0),
            'source' => clean_param((string) ($this->other['source'] ?? ''), PARAM_ALPHA),
            'filename' => clean_param((string) ($this->other['filename'] ?? ''), PARAM_FILE),
        ]);
    }

    /**
     * Create an event instance for this content image action.
     *
     * @param location $location
     * @param int $userid
     * @param string $source
     * @return self
     */
    public static function create_from_location(location $location, int $userid, string $source): self {
        return self::create(self::build_location_data($location, $userid, [
            'source' => $source,
        ]));
    }
}
