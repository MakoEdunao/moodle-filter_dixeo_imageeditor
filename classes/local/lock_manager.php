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

/**
 * Per-location job lock storage.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lock_manager {

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_APPLIED = 'applied';
    public const STATUS_FAILED = 'failed';

    /** @var int Seconds before a lock is marked failed (~1h). */
    public const TIMEOUT_SECONDS = 3600;

    /**
     * @param location_key $location
     * @return \stdClass|null
     */
    public static function get_active_lock(location_key $location): ?\stdClass {
        global $DB;

        $record = $DB->get_record('filter_dixeo_imageeditor_lock', ['locationhash' => $location->hash()], '*', IGNORE_MISSING);
        if (!$record) {
            return null;
        }

        if (in_array($record->status, [self::STATUS_APPLIED, self::STATUS_FAILED], true)) {
            return $record;
        }

        if ((time() - (int) $record->timecreated) > self::TIMEOUT_SECONDS) {
            self::mark_failed($record->id, get_string('error_job_failed', 'filter_dixeo_imageeditor'));
            return $DB->get_record('filter_dixeo_imageeditor_lock', ['id' => $record->id], '*', MUST_EXIST);
        }

        return $record;
    }

    /**
     * @param location_key $location
     * @return bool
     */
    public static function has_blocking_lock(location_key $location): bool {
        $lock = self::get_active_lock($location);
        if (!$lock) {
            return false;
        }
        return in_array($lock->status, [self::STATUS_PENDING, self::STATUS_PROCESSING], true);
    }

    /**
     * @param location_key $location
     * @param string $jobid
     * @param int $userid
     * @return \stdClass
     */
    public static function create_lock(location_key $location, string $jobid, int $userid): \stdClass {
        global $DB;

        if (self::has_blocking_lock($location)) {
            throw new \moodle_exception('error_locked', 'filter_dixeo_imageeditor');
        }

        $now = time();
        $record = (object) array_merge($location->to_record_fields(), [
            'jobid' => $jobid,
            'userid' => $userid,
            'status' => self::STATUS_PENDING,
            'errormessage' => null,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
        $record->id = $DB->insert_record('filter_dixeo_imageeditor_lock', $record);
        return $record;
    }

    /**
     * @param int $lockid
     * @param string $status
     * @param string|null $errormessage
     * @return void
     */
    public static function update_status(int $lockid, string $status, ?string $errormessage = null): void {
        global $DB;

        $DB->update_record('filter_dixeo_imageeditor_lock', (object) [
            'id' => $lockid,
            'status' => $status,
            'errormessage' => $errormessage,
            'timemodified' => time(),
        ]);
    }

    /**
     * @param int $lockid
     * @param string $message
     * @return void
     */
    public static function mark_failed(int $lockid, string $message): void {
        self::update_status($lockid, self::STATUS_FAILED, $message);
    }

    /**
     * @param int $lockid
     * @return void
     */
    public static function delete_lock(int $lockid): void {
        global $DB;
        $DB->delete_records('filter_dixeo_imageeditor_lock', ['id' => $lockid]);
    }

    /**
     * @param location_key $location
     * @param string $jobid
     * @param int $userid
     * @param int $chainseq
     * @return void
     */
    public static function queue_poll_task(
        location_key $location,
        string $jobid,
        int $userid,
        int $chainseq = 0,
        string $source = file_replacer::SOURCE_GENERATED
    ): void {
        $task = new \filter_dixeo_imageeditor\task\poll_content_image_job();
        $task->set_custom_data((object) array_merge($location->to_record_fields(), [
            'jobid' => $jobid,
            'userid' => $userid,
            'chainseq' => $chainseq,
            'source' => $source,
        ]));
        \core\task\manager::queue_adhoc_task($task);
    }

    /**
     * UX-facing status derived from lock row.
     *
     * @param location_key $location
     * @param bool $acknowledged When true, terminal locks are deleted after read.
     * @return array{status: string, imageurl?: string, errormessage?: string, lockid?: int}
     */
    public static function get_location_status(location_key $location, bool $acknowledged = false): array {
        $lock = self::get_active_lock($location);
        if (!$lock) {
            return ['status' => 'idle'];
        }

        $payload = [
            'status' => (string) $lock->status,
            'lockid' => (int) $lock->id,
        ];
        if (!empty($lock->errormessage)) {
            $payload['errormessage'] = (string) $lock->errormessage;
        }
        if ($lock->status === self::STATUS_APPLIED) {
            $payload['imageurl'] = file_replacer::get_current_image_url($location);
            $payload['current_contenthash'] = file_replacer::get_current_contenthash($location);
        }

        if ($acknowledged && in_array($lock->status, [self::STATUS_APPLIED, self::STATUS_FAILED], true)) {
            self::delete_lock((int) $lock->id);
        }

        return $payload;
    }
}
