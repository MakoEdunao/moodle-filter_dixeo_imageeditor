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

use local_dixeo\service\course_image_writer;

/**
 * Archives versions and performs in-place file replacement.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class file_replacer {

    public const SOURCE_GENERATED = 'generated';
    public const SOURCE_EDITED = 'edited';
    public const SOURCE_REVERTED = 'reverted';
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_UPLOAD = 'uploaded';

    /**
     * @param location_key $location
     * @return array<int, array<string, mixed>>
     */
    public static function get_history_for_location(location_key $location): array {
        global $DB;

        $fields = $location->to_record_fields();
        unset($fields['courseid']);

        $records = $DB->get_records('filter_dixeo_imageeditor_version', ['locationhash' => $location->hash()], 'timecreated DESC, id DESC');
        $history = [];
        foreach ($records as $record) {
            $history[] = self::format_version_record($record, $location);
        }
        return $history;
    }

    /**
     * @param \stdClass $record
     * @param location_key $location
     * @return array<string, mixed>
     */
    private static function format_version_record(\stdClass $record, location_key $location): array {
        $systemcontext = \context_system::instance();
        $filepath = self::history_filepath($location);
        $url = \moodle_url::make_pluginfile_url(
            $systemcontext->id,
            'filter_dixeo_imageeditor',
            location_key::FILEAREA_HISTORY,
            (int) $record->id,
            $filepath,
            self::history_filename($record)
        )->out(false);

        return [
            'id' => (int) $record->id,
            'source' => (string) $record->source,
            'contenthash' => (string) $record->contenthash,
            'timecreated' => (int) $record->timecreated,
            'usermodified' => (int) $record->usermodified,
            'previewurl' => $url,
        ];
    }

    /**
     * @param location_key $location
     * @return string
     */
    public static function get_current_contenthash(location_key $location): string {
        $file = $location->get_stored_file();
        return $file ? $file->get_contenthash() : '';
    }

    /**
     * Append a stable cache-busting query param derived from file contenthash.
     *
     * @param string $url Image src URL (may already include a rev param).
     * @param string $contenthash Stored file contenthash.
     * @return string
     */
    public static function append_image_rev(string $url, string $contenthash): string {
        if ($contenthash === '') {
            return $url;
        }

        $url = preg_replace('/([?&])rev=[^&]*/', '', $url) ?? $url;
        $url = rtrim($url, '?&');
        $separator = (strpos($url, '?') !== false) ? '&' : '?';

        return $url . $separator . 'rev=' . rawurlencode($contenthash);
    }

    /**
     * Pluginfile URL for the current image with a contenthash rev query param.
     *
     * @param location_key $location
     * @return string
     */
    public static function get_current_image_url(location_key $location): string {
        return self::append_image_rev(
            $location->get_pluginfile_url(),
            self::get_current_contenthash($location)
        );
    }

    /**
     * @param int $versionid
     * @param location_key $location
     * @return void
     */
    public static function delete_version(int $versionid, location_key $location): void {
        global $DB;

        $version = $DB->get_record('filter_dixeo_imageeditor_version', ['id' => $versionid], '*', MUST_EXIST);
        if ((string) $version->locationhash !== $location->hash()) {
            throw new \moodle_exception('error_not_eligible', 'filter_dixeo_imageeditor');
        }

        $currenthash = self::get_current_contenthash($location);
        if ($currenthash !== '' && (string) $version->contenthash === $currenthash) {
            throw new \moodle_exception('error_delete_current', 'filter_dixeo_imageeditor');
        }

        $systemcontext = \context_system::instance();
        $fs = get_file_storage();
        $historyfile = $fs->get_file(
            $systemcontext->id,
            'filter_dixeo_imageeditor',
            location_key::FILEAREA_HISTORY,
            $versionid,
            self::history_filepath($location),
            self::history_filename($version)
        );
        if ($historyfile) {
            $historyfile->delete();
        }

        $DB->delete_records('filter_dixeo_imageeditor_version', ['id' => $versionid]);
    }

    /**
     * @param location_key $location
     * @param string $source
     * @param int $userid
     * @return int|null Version row id, or null when content is already archived
     */
    public static function archive_current(location_key $location, string $source, int $userid): ?int {
        global $DB;

        $file = $location->get_stored_file();
        if (!$file) {
            throw new \moodle_exception('error_not_eligible', 'filter_dixeo_imageeditor');
        }

        $contenthash = $file->get_contenthash();
        if (self::has_history_contenthash($location, $contenthash)) {
            return null;
        }

        $now = time();
        $fields = $location->to_record_fields();
        unset($fields['courseid']);

        $versionid = $DB->insert_record('filter_dixeo_imageeditor_version', (object) array_merge($fields, [
            'contenthash' => $contenthash,
            'source' => $source,
            'usermodified' => $userid,
            'timecreated' => $now,
        ]));

        $systemcontext = \context_system::instance();
        $record = [
            'contextid' => $systemcontext->id,
            'component' => 'filter_dixeo_imageeditor',
            'filearea' => location_key::FILEAREA_HISTORY,
            'itemid' => $versionid,
            'filepath' => self::history_filepath($location),
            'filename' => self::history_filename((object) ['id' => $versionid, 'timecreated' => $now]),
            'userid' => $userid,
            'mimetype' => $file->get_mimetype(),
        ];

        $fs = get_file_storage();
        $fs->create_file_from_string($record, $file->get_content());

        return (int) $versionid;
    }

    /**
     * @param location_key $location
     * @param array $jobresult
     * @param int $userid
     * @param string $source
     * @return void
     */
    public static function apply_job_result(
        location_key $location,
        array $jobresult,
        int $userid,
        string $source = self::SOURCE_GENERATED
    ): void {
        $binary = course_image_writer::extract_image_binary_from_result($jobresult);
        if ($binary === '') {
            throw new \moodle_exception('dixeo_image_job_empty_result', 'local_dixeo');
        }
        self::apply_binary($location, $binary, $userid, $source);
    }

    /**
     * @param location_key $location
     * @param string $binary
     * @param int $userid
     * @param string $source
     * @return void
     */
    public static function apply_binary(location_key $location, string $binary, int $userid, string $source): void {
        $file = $location->get_stored_file();
        if (!$file) {
            throw new \moodle_exception('error_not_eligible', 'filter_dixeo_imageeditor');
        }

        self::archive_current($location, $source, $userid);
        self::replace_file_content($file, $binary, $userid);
    }

    /**
     * @param location_key $location
     * @param int $versionid
     * @param int $userid
     * @return string New pluginfile URL
     */
    public static function revert_to_version(location_key $location, int $versionid, int $userid): string {
        global $DB;

        $version = $DB->get_record('filter_dixeo_imageeditor_version', ['id' => $versionid], '*', MUST_EXIST);
        if ((string) $version->locationhash !== $location->hash()) {
            throw new \moodle_exception('error_not_eligible', 'filter_dixeo_imageeditor');
        }

        $target = $location->get_stored_file();
        if (!$target) {
            throw new \moodle_exception('error_not_eligible', 'filter_dixeo_imageeditor');
        }

        $systemcontext = \context_system::instance();
        $fs = get_file_storage();
        $historyfile = $fs->get_file(
            $systemcontext->id,
            'filter_dixeo_imageeditor',
            location_key::FILEAREA_HISTORY,
            $versionid,
            self::history_filepath($location),
            self::history_filename($version)
        );
        if (!$historyfile) {
            throw new \moodle_exception('error_not_eligible', 'filter_dixeo_imageeditor');
        }

        $currenthash = $target->get_contenthash();
        $versionhash = (string) $version->contenthash;
        if ($currenthash === $versionhash) {
            return self::get_current_image_url($location);
        }

        self::archive_current($location, self::SOURCE_REVERTED, $userid);

        self::replace_file_content($target, $historyfile->get_content(), $userid);

        return self::get_current_image_url($location);
    }

    /**
     * Whether an archived version already stores this file content.
     *
     * @param location_key $location
     * @param string $contenthash
     * @return bool
     */
    private static function has_history_contenthash(location_key $location, string $contenthash): bool {
        global $DB;

        return $DB->record_exists('filter_dixeo_imageeditor_version', [
            'locationhash' => $location->hash(),
            'contenthash' => $contenthash,
        ]);
    }

    /**
     * @param \stored_file $file
     * @param string $binary
     * @param int $userid
     * @return void
     */
    private static function replace_file_content(\stored_file $file, string $binary, int $userid): void {
        global $DB;

        $fs = get_file_storage();
        $draftrecord = [
            'contextid' => $file->get_contextid(),
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => file_get_unused_draft_itemid(),
            'filepath' => '/',
            'filename' => 'dixeo-replace-' . time() . '.bin',
            'userid' => $userid,
        ];
        $temp = $fs->create_file_from_string($draftrecord, $binary);
        $file->replace_file_with($temp);
        $temp->delete();

        $mimetype = self::detect_mimetype($binary, $file->get_filename());
        if ($mimetype !== $file->get_mimetype()) {
            $DB->set_field('files', 'mimetype', $mimetype, ['id' => $file->get_id()]);
        }
    }

    /**
     * @param string $binary
     * @param string $filename
     * @return string
     */
    private static function detect_mimetype(string $binary, string $filename): string {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $fromcontent = $finfo->buffer($binary);
        if (is_string($fromcontent) && $fromcontent !== '' && $fromcontent !== 'application/octet-stream') {
            return $fromcontent;
        }
        return \file_storage::mimetype('content://', $filename) ?: 'application/octet-stream';
    }

    /**
     * @param location_key $location
     * @return string
     */
    private static function history_filepath(location_key $location): string {
        return '/' . $location->contextid . '/' . $location->hash() . '/';
    }

    /**
     * @param \stdClass $record
     * @return string
     */
    private static function history_filename(\stdClass $record): string {
        return 'version-' . (int) $record->id . '-' . (int) $record->timecreated . '.bin';
    }
}
