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

namespace filter_dixeo_imageeditor\task;

defined('MOODLE_INTERNAL') || die();

use filter_dixeo_imageeditor\local\file_replacer;
use filter_dixeo_imageeditor\local\location_key;
use filter_dixeo_imageeditor\local\lock_manager;
use local_dixeo\external\service_factory;
use local_dixeo\service\image_poll_manager;

/**
 * Polls a remote Dixeo content-image job and applies the result in-place.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class poll_content_image_job extends \core\task\adhoc_task {

    private const POLL_WINDOW_SECONDS = 60;
    private const POLL_INTERVAL_SECONDS = 4;

    public function get_component(): string {
        return 'filter_dixeo_imageeditor';
    }

    public function execute(): void {
        $data = $this->get_custom_data();
        if (!is_object($data)) {
            return;
        }

        $location = location_key::from_params((array) $data);
        $jobid = trim((string) ($data->jobid ?? ''));
        $userid = (int) ($data->userid ?? 0);
        $chainseq = (int) ($data->chainseq ?? 0);
        $source = trim((string) ($data->source ?? file_replacer::SOURCE_GENERATED));
        if ($source === '') {
            $source = file_replacer::SOURCE_GENERATED;
        }

        if ($location->courseid < 1 || $jobid === '' || $userid < 1) {
            return;
        }

        $lockfields = ['locationhash' => $location->hash()];
        global $DB;
        $lock = $DB->get_record('filter_dixeo_imageeditor_lock', $lockfields, '*', IGNORE_MISSING);
        if (!$lock) {
            return;
        }

        if ($lock->status === lock_manager::STATUS_APPLIED) {
            return;
        }

        if ($lock->status === lock_manager::STATUS_FAILED) {
            return;
        }

        lock_manager::update_status((int) $lock->id, lock_manager::STATUS_PROCESSING);

        $jobservice = service_factory::get_job_service();
        $deadline = time() + self::POLL_WINDOW_SECONDS;

        while (time() < $deadline) {
            $jobstatus = $jobservice->get_job_status($jobid);

            if ($jobstatus->is_completed()) {
                $result = $jobstatus->result;
                if (is_string($result)) {
                    $decoded = json_decode($result, true);
                    $result = is_array($decoded) ? $decoded : [];
                } else if (!is_array($result)) {
                    $result = $result !== null ? (array) $result : [];
                }

                try {
                    $freshlock = $DB->get_record('filter_dixeo_imageeditor_lock', ['id' => $lock->id], '*', MUST_EXIST);
                    if ($freshlock->status === lock_manager::STATUS_APPLIED) {
                        return;
                    }
                    file_replacer::apply_job_result(
                        $location,
                        $result,
                        $userid,
                        $source
                    );
                    lock_manager::update_status((int) $lock->id, lock_manager::STATUS_APPLIED);
                } catch (\Throwable $e) {
                    lock_manager::mark_failed((int) $lock->id, $e->getMessage());
                }
                return;
            }

            if ($jobstatus->is_failed()) {
                $message = (string) ($jobstatus->errormessage ?? get_string('error_job_failed', 'filter_dixeo_imageeditor'));
                lock_manager::mark_failed((int) $lock->id, $message);
                return;
            }

            sleep(self::POLL_INTERVAL_SECONDS);
        }

        if ($chainseq + 1 >= image_poll_manager::MAX_CHAIN_SEGMENTS) {
            lock_manager::mark_failed((int) $lock->id, get_string('error_job_failed', 'filter_dixeo_imageeditor'));
            return;
        }

        lock_manager::queue_poll_task($location, $jobid, $userid, $chainseq + 1, $source);
    }

    public function get_name(): string {
        return get_string('task_poll_content_image', 'filter_dixeo_imageeditor');
    }
}
