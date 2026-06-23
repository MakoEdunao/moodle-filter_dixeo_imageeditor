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
 * Tests for lock_manager.
 *
 * @package    filter_dixeo_imageeditor
 * @category   test
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_dixeo_imageeditor;

use filter_dixeo_imageeditor\local\lock_manager;
use filter_dixeo_imageeditor\local\location_key;

defined('MOODLE_INTERNAL') || die();

/**
 * @covers \filter_dixeo_imageeditor\local\lock_manager
 */
final class lock_manager_test extends \advanced_testcase {

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    public function test_create_and_acknowledge_terminal_lock(): void {
        global $USER;

        $this->setAdminUser();
        $location = new location_key(3, 'mod_page', 'content', 0, '/', 'pic.png', 2);

        lock_manager::create_lock($location, 'job-123', (int) $USER->id);
        $this->assertTrue(lock_manager::has_blocking_lock($location));

        lock_manager::update_status(
            (int) lock_manager::get_active_lock($location)->id,
            lock_manager::STATUS_APPLIED
        );

        $status = lock_manager::get_location_status($location, true);
        $this->assertSame(lock_manager::STATUS_APPLIED, $status['status']);
        $this->assertFalse(lock_manager::has_blocking_lock($location));
    }

    public function test_second_lock_is_rejected_while_pending(): void {
        global $USER;

        $this->setAdminUser();
        $location = new location_key(3, 'mod_page', 'content', 0, '/', 'pic.png', 2);

        lock_manager::create_lock($location, 'job-123', (int) $USER->id);

        $this->expectException(\moodle_exception::class);
        lock_manager::create_lock($location, 'job-456', (int) $USER->id);
    }
}
