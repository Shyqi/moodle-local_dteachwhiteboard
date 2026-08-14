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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Subscription state of this site, and the buttons that move it along.
 *
 * @package    local_dteachwhiteboard
 * @copyright  2026 dteach <contact@dteach.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_dteachwhiteboard\client;
use local_dteachwhiteboard\lti_tool;

const LOCAL_DTEACHWHITEBOARD = 'local_dteachwhiteboard';
const LOCAL_DTEACHWHITEBOARD_CONTACT = 'contact@dteach.net';

admin_externalpage_setup('local_dteachwhiteboard_subscription');

$action = optional_param('action', '', PARAM_ALPHA);
$pageurl = new moodle_url('/local/dteachwhiteboard/subscription.php');
$client = new client();
$token = (string) get_config(LOCAL_DTEACHWHITEBOARD, 'token');

/**
 * Whole days left before a plan ends, or null for a plan that never ends on its own.
 *
 * @param string|null $endsat ISO 8601 timestamp
 * @return int|null
 */
function local_dteachwhiteboard_days_left(?string $endsat): ?int {
    if (empty($endsat) || ($end = strtotime($endsat)) === false) {
        return null;
    }
    return max(0, (int) ceil(($end - time()) / DAYSECS));
}

if ($action !== '') {
    require_sesskey();

    if ($action === 'connect' || $action === 'connectpaid') {
        if ($token === '') {
            $invite = $client->issue_token($CFG->wwwroot);
            $token = $invite['token'];
            set_config('token', $token, LOCAL_DTEACHWHITEBOARD);
            set_config('registrationurl', $invite['registration_url'], LOCAL_DTEACHWHITEBOARD);
        }
        // Checkout needs a registration to bill, so the paid path registers first and is
        // resumed on the next page load.
        if ($action === 'connectpaid') {
            set_config('pendingcheckout', 1, LOCAL_DTEACHWHITEBOARD);
        }
        redirect(new moodle_url('/mod/lti/startltiadvregistration.php', [
            'url' => get_config(LOCAL_DTEACHWHITEBOARD, 'registrationurl'),
            'sesskey' => sesskey(),
        ]));
    }

    if ($action === 'checkout') {
        redirect($client->checkout_url($token, $pageurl->out(false), $pageurl->out(false)));
    }

    if ($action === 'billing') {
        redirect($client->portal_url($token, $pageurl->out(false)));
    }
}

try {
    $status = $token === '' ? null : $client->status($token);
} catch (\local_dteachwhiteboard\service_exception $e) {
    // A token issued but never registered is purged after a day; drop it rather than
    // leave the page stuck on an error with no way to ask for another one.
    if ($e->servicecode !== 'invalid_token') {
        throw $e;
    }
    unset_config('token', LOCAL_DTEACHWHITEBOARD);
    unset_config('registrationurl', LOCAL_DTEACHWHITEBOARD);
    unset_config('pendingcheckout', LOCAL_DTEACHWHITEBOARD);
    redirect($pageurl, get_string('tokenrejected', LOCAL_DTEACHWHITEBOARD), null,
        \core\output\notification::NOTIFY_WARNING);
}

if ($status !== null && $status['connected']) {
    lti_tool::activate((string) $status['client_id']);
    if (get_config(LOCAL_DTEACHWHITEBOARD, 'pendingcheckout')) {
        unset_config('pendingcheckout', LOCAL_DTEACHWHITEBOARD);
        redirect($client->checkout_url($token, $pageurl->out(false), $pageurl->out(false)));
    }
}

$state = $status === null ? 'not_connected' : $status['state'];
$daysleft = $status === null ? null : local_dteachwhiteboard_days_left($status['plan']['ends_at'] ?? null);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('subscription', LOCAL_DTEACHWHITEBOARD));

if ($state === 'trial') {
    $summary = $daysleft === null
        ? get_string('trialrunning', LOCAL_DTEACHWHITEBOARD)
        : get_string('trialdaysleft', LOCAL_DTEACHWHITEBOARD, $daysleft);
} else if ($state === 'paid') {
    $summary = $daysleft === null
        ? get_string('paidrunning', LOCAL_DTEACHWHITEBOARD)
        : get_string('paiddaysleft', LOCAL_DTEACHWHITEBOARD, $daysleft);
} else if ($state === 'expired') {
    $summary = get_string('expired', LOCAL_DTEACHWHITEBOARD);
} else {
    $summary = get_string('notconnected', LOCAL_DTEACHWHITEBOARD);
}
echo $OUTPUT->notification($summary, $state === 'paid' || $state === 'trial'
    ? \core\output\notification::NOTIFY_SUCCESS
    : \core\output\notification::NOTIFY_INFO);

$buttons = [];
if ($state === 'not_connected') {
    $buttons[] = new single_button(
        new moodle_url($pageurl, ['action' => 'connect', 'sesskey' => sesskey()]),
        get_string('starttrial', LOCAL_DTEACHWHITEBOARD),
        'post',
        single_button::BUTTON_PRIMARY
    );
    $buttons[] = new single_button(
        new moodle_url($pageurl, ['action' => 'connectpaid', 'sesskey' => sesskey()]),
        get_string('startpaid', LOCAL_DTEACHWHITEBOARD)
    );
} else if ($state === 'paid' && $status['has_billing_account']) {
    $buttons[] = new single_button(
        new moodle_url($pageurl, ['action' => 'billing', 'sesskey' => sesskey()]),
        get_string('billing', LOCAL_DTEACHWHITEBOARD)
    );
} else if ($state !== 'paid') {
    $buttons[] = new single_button(
        new moodle_url($pageurl, ['action' => 'checkout', 'sesskey' => sesskey()]),
        get_string('upgrade', LOCAL_DTEACHWHITEBOARD),
        'post',
        single_button::BUTTON_PRIMARY
    );
}

echo html_writer::start_div('d-flex gap-2 mb-3');
foreach ($buttons as $button) {
    echo $OUTPUT->render($button);
}
echo html_writer::link(
    'mailto:' . LOCAL_DTEACHWHITEBOARD_CONTACT,
    get_string('contactus', LOCAL_DTEACHWHITEBOARD),
    ['class' => 'btn btn-secondary']
);
echo html_writer::end_div();

if ($state !== 'not_connected') {
    echo $OUTPUT->notification(get_string('toolready', LOCAL_DTEACHWHITEBOARD), \core\output\notification::NOTIFY_INFO);
}

echo $OUTPUT->footer();
