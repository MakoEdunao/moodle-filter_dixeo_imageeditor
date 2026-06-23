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
 * Canonical file location identity for version history and locks.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class location_key {

    public const FILEAREA_HISTORY = 'history';

    /** @var int */
    public int $contextid;
    /** @var string */
    public string $component;
    /** @var string */
    public string $filearea;
    /** @var int */
    public int $itemid;
    /** @var string */
    public string $filepath;
    /** @var string */
    public string $filename;
    /** @var int */
    public int $courseid;

    /**
     * @param int $contextid
     * @param string $component
     * @param string $filearea
     * @param int $itemid
     * @param string $filepath
     * @param string $filename
     * @param int $courseid
     */
    public function __construct(
        int $contextid,
        string $component,
        string $filearea,
        int $itemid,
        string $filepath,
        string $filename,
        int $courseid
    ) {
        $this->contextid = $contextid;
        $this->component = $component;
        $this->filearea = $filearea;
        $this->itemid = $itemid;
        $this->filepath = $filepath === '' ? '/' : $filepath;
        $this->filename = $filename;
        $this->courseid = $courseid;
    }

    /**
     * @param \stored_file $file
     * @return self
     */
    public static function from_stored_file(\stored_file $file): self {
        $courseid = \local_dixeo\service\pluginfile_image_helper::resolve_course_id_for_file($file);
        return new self(
            (int) $file->get_contextid(),
            (string) $file->get_component(),
            (string) $file->get_filearea(),
            (int) $file->get_itemid(),
            (string) $file->get_filepath(),
            (string) $file->get_filename(),
            $courseid
        );
    }

    /**
     * @param array $params
     * @return self
     */
    public static function from_params(array $params): self {
        return new self(
            (int) ($params['contextid'] ?? 0),
            (string) ($params['component'] ?? ''),
            (string) ($params['filearea'] ?? ''),
            (int) ($params['itemid'] ?? 0),
            (string) ($params['filepath'] ?? '/'),
            (string) ($params['filename'] ?? ''),
            (int) ($params['courseid'] ?? 0)
        );
    }

    /**
     * Stable hash for history file paths.
     *
     * @return string
     */
    public function hash(): string {
        return sha1(implode('|', [
            $this->contextid,
            $this->component,
            $this->filearea,
            $this->itemid,
            $this->filepath,
            $this->filename,
        ]));
    }

    /**
     * @return array<string, int|string>
     */
    public function to_record_fields(): array {
        return [
            'contextid' => $this->contextid,
            'component' => $this->component,
            'filearea' => $this->filearea,
            'itemid' => $this->itemid,
            'filepath' => $this->filepath,
            'filename' => $this->filename,
            'locationhash' => $this->hash(),
            'courseid' => $this->courseid,
        ];
    }

    /**
     * @return \stored_file|null
     */
    public function get_stored_file(): ?\stored_file {
        $fs = get_file_storage();
        $file = $fs->get_file(
            $this->contextid,
            $this->component,
            $this->filearea,
            $this->itemid,
            $this->filepath,
            $this->filename
        );
        if (!$file || $file->is_directory()) {
            return null;
        }
        return $file;
    }

    /**
     * @return string Pluginfile URL for the target image.
     */
    public function get_pluginfile_url(): string {
        return \moodle_url::make_pluginfile_url(
            $this->contextid,
            $this->component,
            $this->filearea,
            $this->itemid,
            $this->filepath,
            $this->filename
        )->out(false);
    }
}
