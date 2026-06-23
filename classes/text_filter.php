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

namespace filter_dixeo_imageeditor;

defined('MOODLE_INTERNAL') || die();

use filter_dixeo_imageeditor\local\eligibility;
use filter_dixeo_imageeditor\local\feature_gate;
use filter_dixeo_imageeditor\local\file_replacer;
use filter_dixeo_imageeditor\local\location_key;

/**
 * Injects AI image edit controls on eligible embedded images.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_filter extends \core_filters\text_filter {

    /** @var bool */
    private static bool $amdsloaded = false;

    #[\Override]
    public function setup($page, $context): void {
        if (self::$amdsloaded) {
            return;
        }
        if (!feature_gate::is_globally_enabled()) {
            return;
        }
        $page->requires->js_call_amd('filter_dixeo_imageeditor/editor', 'init');
        $page->requires->css('/filter/dixeo_imageeditor/styles.css');
        $page->requires->css('/filter/dixeo_imageeditor/cropper.css');
        self::$amdsloaded = true;
    }

    #[\Override]
    public function filter($text, array $options = []): string {
        if (!feature_gate::is_globally_enabled()) {
            return $text;
        }
        if ($text === '' || stripos($text, '<img') === false) {
            return $text;
        }
        if (!empty($options['stage']) && $options['stage'] !== 'post_clean') {
            return $text;
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="dixeo-imageeditor-root">' . $text . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return $text;
        }

        $xpath = new \DOMXPath($dom);
        $images = $xpath->query('//img');
        if (!$images || $images->length === 0) {
            return $text;
        }

        $modified = false;
        foreach ($images as $img) {
            if (!($img instanceof \DOMElement)) {
                continue;
            }
            $src = $img->getAttribute('src');
            if ($src === '' || stripos($src, 'pluginfile.php') === false) {
                continue;
            }

            $file = eligibility::resolve_eligible_file_from_url($src);
            if (!$file) {
                continue;
            }

            $location = location_key::from_stored_file($file);
            if ($location->courseid < 1) {
                continue;
            }

            $coursecontext = \context_course::instance($location->courseid, IGNORE_MISSING);
            if (!$coursecontext || !has_capability('filter/dixeo_imageeditor:edit', $coursecontext)) {
                continue;
            }

            $contenthash = $file->get_contenthash();
            $img->setAttribute('src', file_replacer::append_image_rev($src, $contenthash));

            $wrapper = $dom->createElement('span');
            $wrapper->setAttribute('class', 'dixeo-imageeditor-wrap');
            $wrapper->setAttribute('data-dixeo-imageeditor', '1');
            $wrapper->setAttribute('data-contextid', (string) $location->contextid);
            $wrapper->setAttribute('data-component', $location->component);
            $wrapper->setAttribute('data-filearea', $location->filearea);
            $wrapper->setAttribute('data-itemid', (string) $location->itemid);
            $wrapper->setAttribute('data-filepath', $location->filepath);
            $wrapper->setAttribute('data-filename', $location->filename);
            $wrapper->setAttribute('data-courseid', (string) $location->courseid);
            $wrapper->setAttribute('data-contenthash', $contenthash);

            $parent = $img->parentNode;
            if (!$parent) {
                continue;
            }
            $parent->replaceChild($wrapper, $img);
            $wrapper->appendChild($img);

            $button = $dom->createElement('button');
            $button->setAttribute('type', 'button');
            $button->setAttribute('class', 'dixeo-imageeditor-editbtn');
            $button->setAttribute('data-action', 'open-editor');
            $button->setAttribute('aria-label', get_string('editimage', 'filter_dixeo_imageeditor'));
            $button->textContent = get_string('editimage', 'filter_dixeo_imageeditor');
            $wrapper->appendChild($button);

            $modified = true;
        }

        if (!$modified) {
            return $text;
        }

        $root = $dom->getElementById('dixeo-imageeditor-root');
        if (!$root) {
            return $text;
        }

        $html = '';
        foreach ($root->childNodes as $child) {
            $html .= $dom->saveHTML($child);
        }
        return $html;
    }
}
