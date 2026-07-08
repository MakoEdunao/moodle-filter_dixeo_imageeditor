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
 * Language strings.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['filtername'] = 'Dixeo image editor';
$string['pluginname'] = 'Dixeo image editor';
$string['privacy:metadata'] = 'The Dixeo image editor filter stores version history metadata and archived image bytes for embedded content images.';
$string['dixeo_imageeditor:edit'] = 'Edit embedded content images with AI';
$string['setting_enabled'] = 'Enable Dixeo image editor';
$string['setting_enabled_desc'] = 'When disabled, the filter does not inject edit controls.';
$string['editimage'] = 'Edit image';
$string['modal_title'] = 'Dixeo Image Editor';
$string['mode_new_image'] = 'New image';
$string['mode_edit_current_image'] = 'Edit current';
$string['prompt_label_generate'] = 'Describe the image you want AI to create';
$string['prompt_placeholder_generate'] = 'Try \'Mountain landscape\'';
$string['prompt_label_edit'] = 'Describe the changes to make to the image';
$string['prompt_placeholder_edit'] = 'e.g. Remove the laptop from the desk, zoom in slightly, and keep the same lighting.';
$string['prompt_required'] = 'Please enter a description before continuing.';
$string['instructions_required'] = 'Please describe the changes to apply.';
$string['shape_label'] = 'Image shape';
$string['shape_landscape'] = 'Landscape';
$string['shape_square'] = 'Square';
$string['shape_portrait'] = 'Portrait';
$string['quality_label'] = 'Quality';
$string['quality_low'] = 'Low';
$string['quality_medium'] = 'Medium';
$string['quality_high'] = 'High';
$string['history_label'] = 'Version history';
$string['history_set_current'] = 'Set as current image';
$string['history_delete'] = 'Delete from history';
$string['history_actions'] = 'Version actions';
$string['submit_generate'] = 'Generate';
$string['submit_edit'] = 'Edit';
$string['generating_status'] = 'Generating image...';
$string['error_job_failed'] = 'Image generation failed. Please try again.';
$string['error_locked'] = 'An image job is already in progress for this image.';
$string['error_not_eligible'] = 'This image cannot be edited.';
$string['error_revert_blocked'] = 'Cannot revert while a job is in progress.';
$string['error_delete_blocked'] = 'Cannot delete from history while a job is in progress.';
$string['error_delete_current'] = 'Cannot delete the history entry that matches the current image.';
$string['privacy:metadata:usermodified'] = 'The user who created a version history entry.';
$string['privacy:metadata:timecreated'] = 'When the version history entry was created.';
$string['privacy:metadata:versiontable'] = 'Stores metadata about archived image versions for embedded content.';
$string['privacy:metadata:filename'] = 'The embedded image filename.';
$string['privacy:metadata:source'] = 'How the archived version was created.';
$string['privacy:metadata:historyfiles'] = 'Archived copies of previous image versions are stored in the file system.';
$string['privacy:pathversions'] = 'Image version history';
$string['task_cleanup_version_history'] = 'Clean up Dixeo image version history';
$string['close'] = 'Close';
$string['history_preview_title'] = 'Version preview';
$string['history_preview_prev'] = 'Previous version';
$string['history_preview_next'] = 'Next version';
$string['upload_image'] = 'Upload';
$string['upload_replace_title'] = 'Replace current image?';
$string['upload_replace_body'] = 'The selected file will replace the current image. The previous version will be saved in version history.';
$string['upload_replace_confirm'] = 'Replace image';
$string['upload_invalid_type'] = 'Please choose a supported image file ({$a}).';
$string['error_upload_blocked'] = 'Cannot upload while an AI image job is in progress.';
$string['error_upload_invalid_image'] = 'The uploaded file is not a supported image.';
$string['manual_edit_start'] = 'Edit image manually';
$string['manual_save'] = 'Save';
$string['manual_discard'] = 'Discard';
$string['manual_download'] = 'Download';
$string['manual_rotate_clockwise'] = 'Rotate';
$string['manual_apply_crop'] = 'Crop';
$string['manual_flip_horizontal'] = 'Flip horizontal';
$string['manual_flip_vertical'] = 'Flip vertical';
$string['manual_zoom_in'] = 'Zoom in';
$string['manual_zoom_out'] = 'Zoom out';
$string['manual_zoom_reset'] = 'Reset zoom';
$string['manual_filter_grayscale'] = 'Black & white';
$string['manual_filter_sepia'] = 'Sepia';
$string['manual_brightness'] = 'Brightness';
$string['manual_contrast'] = 'Contrast';
$string['manual_toolbar_history'] = 'History and export';
$string['manual_toolbar_adjust'] = 'Adjust image';
$string['manual_toolbar_zoom'] = 'Zoom';
$string['manual_undo'] = 'Undo';
$string['manual_redo'] = 'Redo';
$string['manual_unsaved_changes_title'] = 'Discard unsaved changes?';
$string['manual_unsaved_changes_body'] = 'You have unsaved manual edits. If you leave now, those changes will be lost.';
$string['error_manual_blocked'] = 'Cannot save manual edits while an AI image job is in progress.';
$string['error_manual_invalid_image'] = 'The edited image could not be processed.';
