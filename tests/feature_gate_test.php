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
 * Tests for feature_gate.
 *
 * @package    filter_dixeo_imageeditor
 * @category   test
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_dixeo_imageeditor;

use filter_dixeo_imageeditor\local\feature_gate;
use local_dixeo\service\image_generation_policy;

defined('MOODLE_INTERNAL') || die();

/**
 * @covers \filter_dixeo_imageeditor\local\feature_gate
 */
final class feature_gate_test extends \advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    public function test_is_globally_enabled_when_filter_disabled(): void {
        set_config('enabled', 0, 'filter_dixeo_imageeditor');
        set_config('image_generation_enabled', 1, 'local_dixeo');
        set_config('image_generation_content_mode', image_generation_policy::MODE_GENERATE_EDIT, 'local_dixeo');

        $this->assertFalse(feature_gate::is_globally_enabled());
    }

    public function test_is_globally_enabled_when_content_mode_disabled(): void {
        set_config('enabled', 1, 'filter_dixeo_imageeditor');
        set_config('image_generation_enabled', 1, 'local_dixeo');
        set_config('image_generation_content_mode', image_generation_policy::MODE_DISABLED, 'local_dixeo');

        $this->assertTrue(feature_gate::is_globally_enabled());
    }

    public function test_is_globally_enabled_when_global_dixeo_image_off(): void {
        set_config('enabled', 1, 'filter_dixeo_imageeditor');
        set_config('image_generation_enabled', 0, 'local_dixeo');
        set_config('image_generation_content_mode', image_generation_policy::MODE_DISABLED, 'local_dixeo');

        $this->assertTrue(feature_gate::is_globally_enabled());
    }
}
