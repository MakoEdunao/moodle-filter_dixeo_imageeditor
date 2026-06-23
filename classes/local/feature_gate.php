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

namespace filter_dixeo_imageeditor\local;

defined('MOODLE_INTERNAL') || die();

use local_dixeo\service\image_generation_policy;

/**
 * Feature gate checks for the filter and its externals.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class feature_gate {

    /**
     * Whether the filter should run at all (excludes per-user caps).
     *
     * @return bool
     */
    public static function is_globally_enabled(): bool {
        return (bool) get_config('filter_dixeo_imageeditor', 'enabled');
    }

    /**
     * @param int $courseid
     * @return void
     */
    public static function require_filter_edit(int $courseid): void {
        require_capability('filter/dixeo_imageeditor:edit', \context_course::instance($courseid));
    }

    /**
     * Policy flags for UI.
     *
     * @return array{can_generate: bool, can_edit: bool}
     */
    public static function policy_flags(): array {
        return [
            'can_generate' => image_generation_policy::is_enabled(
                image_generation_policy::ENTITY_CONTENT,
                image_generation_policy::ACTION_GENERATE
            ),
            'can_edit' => image_generation_policy::is_enabled(
                image_generation_policy::ENTITY_CONTENT,
                image_generation_policy::ACTION_EDIT
            ),
        ];
    }

    /**
     * @param int $courseid
     * @return array{can_generate: bool, can_edit: bool}
     */
    public static function capability_flags(int $courseid): array {
        $context = \context_course::instance($courseid);
        return [
            'can_generate' => has_capability('local/dixeo:contentimagegenerate', $context),
            'can_edit' => has_capability('local/dixeo:contentimageedit', $context),
        ];
    }
}
