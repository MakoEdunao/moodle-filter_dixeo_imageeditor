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
 * Client-side polling for async image job status.
 *
 * @module     filter_dixeo_imageeditor/polling
 * @copyright  2026 Dixeo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'core/ajax',
    'core/notification',
    'core/str',
    'filter_dixeo_imageeditor/image_sync',
], function(Ajax, Notification, Str, imageSync) {
    'use strict';

    const GENERATING_CLASS = 'is-generating';
    const POLL_INTERVAL_MS = 4000;
    /** Align with job_repository::TIMEOUT_SECONDS (1 hour). */
    const POLL_TIMEOUT_MS = 3600000;

    /** @type {Map<string, number>} */
    const pollTimers = new Map();

    /** @type {Map<string, number>} */
    const pollStartedAt = new Map();

    /**
     * Toggle generating overlay on a wrapper.
     *
     * @param {HTMLElement} wrap
     * @param {boolean} active
     * @param {string} label
     */
    const setGeneratingOverlay = (wrap, active, label = '') => {
        if (active) {
            wrap.classList.add(GENERATING_CLASS);
            wrap.dataset.dixeoImageGeneratingLabel = label;
            return;
        }
        wrap.classList.remove(GENERATING_CLASS);
        delete wrap.dataset.dixeoImageGeneratingLabel;
    };

    /**
     * Stop polling for one location.
     *
     * @param {string} key
     */
    const stopPolling = (key) => {
        const timer = pollTimers.get(key);
        if (timer) {
            window.clearInterval(timer);
            pollTimers.delete(key);
        }
        pollStartedAt.delete(key);
    };

    /**
     * Poll lock status for UX overlay only.
     *
     * @param {HTMLElement} wrap
     * @param {Object} [callbacks]
     * @param {Function} [callbacks.onApplied]
     * @param {Function} [callbacks.onFailed]
     * @param {Function} [callbacks.onTimeout]
     */
    const startStatusPolling = (wrap, callbacks = {}) => {
        const key = imageSync.getLocationKey(wrap);
        stopPolling(key);
        pollStartedAt.set(key, Date.now());

        const poll = () => {
            const started = pollStartedAt.get(key) || Date.now();
            if (Date.now() - started > POLL_TIMEOUT_MS) {
                stopPolling(key);
                setGeneratingOverlay(wrap, false);
                if (callbacks.onTimeout) {
                    callbacks.onTimeout();
                }
                Str.getString('error_job_failed', 'filter_dixeo_imageeditor').then((message) => {
                    Notification.addNotification({message, type: 'error'});
                }).catch(Notification.exception);
                return;
            }

            Ajax.call([{
                methodname: 'filter_dixeo_imageeditor_get_location_status',
                args: imageSync.getLocationArgs(wrap),
            }])[0].then(async(status) => {
                if (!status || !status.status) {
                    return;
                }

                if (status.status === 'pending' || status.status === 'processing') {
                    return;
                }

                stopPolling(key);

                if (status.status === 'applied') {
                    imageSync.applyImageToWrap(wrap, status.imageurl || '', status.current_contenthash || '');
                    setGeneratingOverlay(wrap, false);
                    if (callbacks.onApplied) {
                        callbacks.onApplied(status);
                    }
                    Ajax.call([{
                        methodname: 'filter_dixeo_imageeditor_get_location_status',
                        args: Object.assign({}, imageSync.getLocationArgs(wrap), {acknowledge: true}),
                    }])[0].catch(() => {
                        // Acknowledge is best-effort cleanup.
                    });
                    return;
                }

                if (status.status === 'failed') {
                    setGeneratingOverlay(wrap, false);
                    if (callbacks.onFailed) {
                        callbacks.onFailed(status);
                    }
                    let message = status.errormessage || '';
                    if (!message) {
                        message = await Str.getString('error_job_failed', 'filter_dixeo_imageeditor');
                    }
                    Notification.addNotification({message, type: 'error'});
                    Ajax.call([{
                        methodname: 'filter_dixeo_imageeditor_get_location_status',
                        args: Object.assign({}, imageSync.getLocationArgs(wrap), {acknowledge: true}),
                    }])[0].catch(() => {
                        // Acknowledge is best-effort cleanup.
                    });
                }
            }).catch(Notification.exception);
        };

        pollTimers.set(key, window.setInterval(poll, POLL_INTERVAL_MS));
        poll();
    };

    /**
     * Resume overlays for in-flight jobs after page load.
     *
     * Only wrappers flagged server-side (data-dixeo-pending="1") are checked, so
     * pages full of idle images make no status requests at all.
     *
     * @param {string} wrapSelector
     */
    const resumePendingOverlays = (wrapSelector) => {
        document.querySelectorAll(wrapSelector).forEach((wrap) => {
            if (!(wrap instanceof HTMLElement)) {
                return;
            }
            if (wrap.dataset.dixeoPending !== '1') {
                return;
            }
            Ajax.call([{
                methodname: 'filter_dixeo_imageeditor_get_location_status',
                args: imageSync.getLocationArgs(wrap),
            }])[0].then((status) => {
                if (!status) {
                    return;
                }
                if (status.status === 'pending' || status.status === 'processing') {
                    Str.getString('generating_status', 'filter_dixeo_imageeditor').then((label) => {
                        setGeneratingOverlay(wrap, true, label);
                        startStatusPolling(wrap);
                    });
                }
            }).catch(() => {
                // Ignore resume failures on pages without webservice access.
            });
        });
    };

    return {
        GENERATING_CLASS,
        setGeneratingOverlay,
        stopPolling,
        startStatusPolling,
        resumePendingOverlays,
    };
});
