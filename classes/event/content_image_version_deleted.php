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
 * Fired when an archived image version is deleted from history.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content_image_version_deleted extends content_image_base {
    /**
     * Init method.
     */
    protected function init(): void {
        parent::init();
        $this->data['crud'] = 'd';
        $this->data['objecttable'] = 'filter_dixeo_imageeditor_version';
    }

    /**
     * Return the localised event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('eventcontentimageversiondeleted', 'filter_dixeo_imageeditor');
    }

    /**
     * Return the localised event description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string('eventcontentimageversiondeleteddesc', 'filter_dixeo_imageeditor', (object) [
            'userid' => $this->userid,
            'courseid' => (int) ($this->other['courseid'] ?? 0),
            'versionid' => (int) ($this->other['versionid'] ?? 0),
        ]);
    }

    /**
     * Create an event instance for this content image action.
     *
     * @param location $location
     * @param int $userid
     * @param int $versionid
     * @return self
     */
    public static function create_from_location(location $location, int $userid, int $versionid): self {
        return self::create(self::build_location_data($location, $userid, [
            'versionid' => $versionid,
        ]));
    }
}
