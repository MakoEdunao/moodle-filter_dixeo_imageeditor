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

/**
 * Tests for revert_version external.
 *
 * @package    filter_dixeo_imageeditor
 * @category   test
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_dixeo_imageeditor;

use context_module;
use filter_dixeo_imageeditor\external\revert_version;
use filter_dixeo_imageeditor\adapter\file_replacer;
use local_dixeo\service\image\content\location;
use local_dixeo\repository\image\job_repository;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/page/lib.php');

/**
 * Tests for the revert_version external function.
 *
 * @covers \filter_dixeo_imageeditor\external\revert_version
 */
final class revert_version_test extends \advanced_testcase {
    /**
     * Return PNG bytes from core filestorage fixtures.
     */
    private static function fixture_png_bytes(): string {
        global $CFG;
        return (string) file_get_contents($CFG->dirroot . '/lib/filestorage/tests/fixtures/testimage.png');
    }

    /**
     * Return JPEG bytes from core filestorage fixtures.
     */
    private static function fixture_jpeg_bytes(): string {
        global $CFG;
        return (string) file_get_contents($CFG->dirroot . '/lib/filestorage/tests/fixtures/testimage.jpg');
    }

    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
    }

    /**
     * Create a page module with an embedded PNG for tests.
     *
     * @return array{0: location, 1: int}
     */
    private function create_page_image_location(): array {
        global $USER;

        $this->setAdminUser();
        $gen = $this->getDataGenerator();
        $course = $gen->create_course();
        $page = $gen->create_module('page', ['course' => $course->id]);
        $context = context_module::instance($page->cmid);

        $draftitemid = file_get_unused_draft_itemid();
        $fs = get_file_storage();
        $fs->create_file_from_string([
            'contextid' => \context_user::instance($USER->id)->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $draftitemid,
            'filepath' => '/',
            'filename' => 'embedded.png',
        ], self::fixture_png_bytes());

        file_save_draft_area_files(
            $draftitemid,
            $context->id,
            'mod_page',
            'content',
            0,
            ['subdirs' => 0, 'maxfiles' => 1]
        );

        $file = $fs->get_file($context->id, 'mod_page', 'content', 0, '/', 'embedded.png');
        $this->assertNotFalse($file);

        return [location::from_stored_file($file), (int) $course->id];
    }

    public function test_revert_version_restores_archived_image(): void {
        global $USER;

        [$location, $courseid] = $this->create_page_image_location();
        $originalhash = $location->get_stored_file()->get_contenthash();

        file_replacer::apply_binary(
            $location,
            self::fixture_jpeg_bytes(),
            (int) $USER->id,
            file_replacer::SOURCE_UPLOAD
        );

        $history = file_replacer::get_history_for_location($location);
        $versionid = (int) $history[0]['id'];

        $result = revert_version::execute(
            $location->contextid,
            $location->component,
            $location->filearea,
            $location->itemid,
            $location->filepath,
            $location->filename,
            $courseid,
            $versionid
        );

        $this->assertNotEmpty($result['imageurl']);
        $this->assertSame($originalhash, $result['current_contenthash']);
        $this->assertSame($originalhash, $location->get_stored_file()->get_contenthash());
    }

    public function test_revert_version_rejects_foreign_versionid(): void {
        global $USER;

        [$location, $courseid] = $this->create_page_image_location();

        file_replacer::apply_binary(
            $location,
            self::fixture_jpeg_bytes(),
            (int) $USER->id,
            file_replacer::SOURCE_UPLOAD
        );

        $history = file_replacer::get_history_for_location($location);
        $versionid = (int) $history[0]['id'];

        [$otherlocation, $othercourseid] = $this->create_page_image_location();

        try {
            revert_version::execute(
                $otherlocation->contextid,
                $otherlocation->component,
                $otherlocation->filearea,
                $otherlocation->itemid,
                $otherlocation->filepath,
                $otherlocation->filename,
                $othercourseid,
                $versionid
            );
            $this->fail('Expected moodle_exception');
        } catch (\moodle_exception $e) {
            $this->assertSame('error_not_eligible', $e->errorcode);
        }
    }

    public function test_revert_version_rejects_when_locked(): void {
        global $USER;

        [$location, $courseid] = $this->create_page_image_location();

        file_replacer::apply_binary(
            $location,
            self::fixture_jpeg_bytes(),
            (int) $USER->id,
            file_replacer::SOURCE_UPLOAD
        );

        $history = file_replacer::get_history_for_location($location);
        $versionid = (int) $history[0]['id'];

        job_repository::create_job($location, 'job-locked', (int) $USER->id);
        job_repository::update_status(
            (int) job_repository::get_active_job_for_location($location)->id,
            job_repository::STATUS_PROCESSING
        );

        try {
            revert_version::execute(
                $location->contextid,
                $location->component,
                $location->filearea,
                $location->itemid,
                $location->filepath,
                $location->filename,
                $courseid,
                $versionid
            );
            $this->fail('Expected moodle_exception');
        } catch (\moodle_exception $e) {
            $this->assertSame('error_revert_blocked', $e->errorcode);
        }
    }
}
