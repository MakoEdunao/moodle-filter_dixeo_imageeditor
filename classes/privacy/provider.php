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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Privacy API implementation for filter_dixeo_imageeditor.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_dixeo_imageeditor\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy provider for version history and job lock metadata.
 *
 * @package    filter_dixeo_imageeditor
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\core_userlist_provider,
        \core_privacy\local\request\plugin\provider {

    /**
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'filter_dixeo_imageeditor_version',
            [
                'filename' => 'privacy:metadata:filename',
                'source' => 'privacy:metadata:source',
                'usermodified' => 'privacy:metadata:usermodified',
                'timecreated' => 'privacy:metadata:timecreated',
            ],
            'privacy:metadata:versiontable'
        );

        $collection->add_database_table(
            'filter_dixeo_imageeditor_lock',
            [
                'filename' => 'privacy:metadata:filename',
                'jobid' => 'privacy:metadata:jobid',
                'userid' => 'privacy:metadata:userid',
                'status' => 'privacy:metadata:status',
                'errormessage' => 'privacy:metadata:errormessage',
                'timecreated' => 'privacy:metadata:timecreated',
                'timemodified' => 'privacy:metadata:timemodified',
            ],
            'privacy:metadata:locktable'
        );

        return $collection;
    }

    /**
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                 WHERE ctx.id IN (
                       SELECT DISTINCT v.contextid
                         FROM {filter_dixeo_imageeditor_version} v
                        WHERE v.usermodified = :useridversion
                       UNION
                       SELECT DISTINCT l.contextid
                         FROM {filter_dixeo_imageeditor_lock} l
                        WHERE l.userid = :useridlock
                 )";

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, [
            'useridversion' => $userid,
            'useridlock' => $userid,
        ]);

        return $contextlist;
    }

    /**
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        $contextid = (int) $context->id;

        $sql = "SELECT DISTINCT v.usermodified AS userid
                  FROM {filter_dixeo_imageeditor_version} v
                 WHERE v.contextid = :contextidversion
                 UNION
                SELECT DISTINCT l.userid
                  FROM {filter_dixeo_imageeditor_lock} l
                 WHERE l.contextid = :contextidlock";

        $userlist->add_from_sql('userid', $sql, [
            'contextidversion' => $contextid,
            'contextidlock' => $contextid,
        ]);
    }

    /**
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist as $context) {
            $versions = $DB->get_records('filter_dixeo_imageeditor_version', [
                'contextid' => $context->id,
                'usermodified' => $userid,
            ], 'timecreated ASC');

            if (!empty($versions)) {
                $exportversions = [];
                foreach ($versions as $version) {
                    $exportversions[] = (object) [
                        'filename' => $version->filename,
                        'source' => $version->source,
                        'timecreated' => transform::datetime($version->timecreated),
                    ];
                }
                writer::with_context($context)->export_data(
                    [get_string('privacy:pathversions', 'filter_dixeo_imageeditor')],
                    (object) ['versions' => $exportversions]
                );
            }

            $locks = $DB->get_records('filter_dixeo_imageeditor_lock', [
                'contextid' => $context->id,
                'userid' => $userid,
            ], 'timecreated ASC');

            if (!empty($locks)) {
                $exportlocks = [];
                foreach ($locks as $lock) {
                    $exportlocks[] = (object) [
                        'filename' => $lock->filename,
                        'jobid' => $lock->jobid,
                        'status' => $lock->status,
                        'errormessage' => $lock->errormessage,
                        'timecreated' => transform::datetime($lock->timecreated),
                        'timemodified' => transform::datetime($lock->timemodified),
                    ];
                }
                writer::with_context($context)->export_data(
                    [get_string('privacy:pathlocks', 'filter_dixeo_imageeditor')],
                    (object) ['locks' => $exportlocks]
                );
            }
        }
    }

    /**
     * @param \context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        $DB->delete_records('filter_dixeo_imageeditor_version', ['contextid' => $context->id]);
        $DB->delete_records('filter_dixeo_imageeditor_lock', ['contextid' => $context->id]);
    }

    /**
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist as $context) {
            $DB->delete_records('filter_dixeo_imageeditor_version', [
                'contextid' => $context->id,
                'usermodified' => $userid,
            ]);
            $DB->delete_records('filter_dixeo_imageeditor_lock', [
                'contextid' => $context->id,
                'userid' => $userid,
            ]);
        }
    }

    /**
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();

        foreach ($userlist->get_userids() as $userid) {
            $DB->delete_records('filter_dixeo_imageeditor_version', [
                'contextid' => $context->id,
                'usermodified' => $userid,
            ]);
            $DB->delete_records('filter_dixeo_imageeditor_lock', [
                'contextid' => $context->id,
                'userid' => $userid,
            ]);
        }
    }
}
