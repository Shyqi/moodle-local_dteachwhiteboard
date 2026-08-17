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

/** Component name, as every get_config() and get_string() call on this page needs it. */
const LOCAL_DTEACHWHITEBOARD = 'local_dteachwhiteboard';

/** Where the "Contact us" button writes to. */
const LOCAL_DTEACHWHITEBOARD_CONTACT = 'contact@dteach.net';

/** Public page describing the whiteboard, for admins who want more than this page shows. */
const LOCAL_DTEACHWHITEBOARD_HOMEPAGE = 'https://draw.dteach.net';

admin_externalpage_setup('local_dteachwhiteboard_subscription');

$action = optional_param('action', '', PARAM_ALPHA);
$pageurl = new moodle_url('/local/dteachwhiteboard/subscription.php');
$client = new client();
$token = (string) get_config(LOCAL_DTEACHWHITEBOARD, 'token');

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
    redirect(
        $pageurl,
        get_string('tokenrejected', LOCAL_DTEACHWHITEBOARD),
        null,
        \core\output\notification::NOTIFY_WARNING
    );
}

if ($status !== null && $status['connected']) {
    lti_tool::activate((string) $status['client_id'], $status['state'] !== 'expired');
    if (get_config(LOCAL_DTEACHWHITEBOARD, 'pendingcheckout')) {
        unset_config('pendingcheckout', LOCAL_DTEACHWHITEBOARD);
        redirect($client->checkout_url($token, $pageurl->out(false), $pageurl->out(false)));
    }
}

$state = $status === null ? 'not_connected' : $status['state'];
// A plan with no end date never ends on its own, so it has no day count to show.
$endsat = $status === null ? null : ($status['plan']['ends_at'] ?? null);
$end = empty($endsat) ? false : strtotime($endsat);
$daysleft = $end === false ? null : max(0, (int) ceil(($end - time()) / DAYSECS));

if ($state === 'trial') {
    $statelabel = get_string('statetrial', LOCAL_DTEACHWHITEBOARD);
    $statevariant = 'info';
    $summary = $daysleft === null
        ? get_string('trialrunning', LOCAL_DTEACHWHITEBOARD)
        : get_string('trialdaysleft', LOCAL_DTEACHWHITEBOARD, $daysleft);
} else if ($state === 'paid') {
    $statelabel = get_string('statepaid', LOCAL_DTEACHWHITEBOARD);
    $statevariant = 'success';
    $summary = $daysleft === null
        ? get_string('paidrunning', LOCAL_DTEACHWHITEBOARD)
        : get_string('paiddaysleft', LOCAL_DTEACHWHITEBOARD, $daysleft);
} else if ($state === 'expired') {
    $statelabel = get_string('stateexpired', LOCAL_DTEACHWHITEBOARD);
    $statevariant = 'warning';
    $summary = get_string('expired', LOCAL_DTEACHWHITEBOARD);
} else {
    $statelabel = get_string('statenotconnected', LOCAL_DTEACHWHITEBOARD);
    $statevariant = 'secondary';
    $summary = get_string('notconnected', LOCAL_DTEACHWHITEBOARD);
}

$details = [];
if (!empty($status['plan']['name'])) {
    $details[] = [
        'label' => get_string('plan', LOCAL_DTEACHWHITEBOARD),
        'value' => $status['plan']['name'],
    ];
}
if ($end !== false) {
    $details[] = [
        'label' => get_string('endson', LOCAL_DTEACHWHITEBOARD),
        'value' => userdate($end, get_string('strftimedaydate')),
    ];
}
$details[] = [
    'label' => get_string('site', LOCAL_DTEACHWHITEBOARD),
    'value' => $status['site_url'] ?? $CFG->wwwroot,
];

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('subscription', LOCAL_DTEACHWHITEBOARD));

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

// Both spacing sets are emitted so the row breathes on Bootstrap 4 (4.2) and 5 (5.0) alike.
$actions = '';
foreach ($buttons as $button) {
    $actions .= html_writer::div($OUTPUT->render($button), 'mr-2 me-2');
}
$actions .= html_writer::link(
    LOCAL_DTEACHWHITEBOARD_HOMEPAGE,
    get_string('learnmore', LOCAL_DTEACHWHITEBOARD),
    ['class' => 'btn btn-link', 'target' => '_blank', 'rel' => 'noopener noreferrer']
);
$actions .= html_writer::link(
    'mailto:' . LOCAL_DTEACHWHITEBOARD_CONTACT,
    get_string('contactus', LOCAL_DTEACHWHITEBOARD),
    ['class' => 'btn btn-link']
);

$steps = [];
if ($state === 'not_connected') {
    $steps = [
        get_string('connectstep1', LOCAL_DTEACHWHITEBOARD),
        get_string('connectstep2', LOCAL_DTEACHWHITEBOARD),
        get_string('connectstep3', LOCAL_DTEACHWHITEBOARD),
    ];
}

echo $OUTPUT->render_from_template('local_dteachwhiteboard/subscription', [
    'statelabel' => $statelabel,
    'statevariant' => $statevariant,
    'summary' => $summary,
    'details' => $details,
    'toolready' => $state === 'trial' || $state === 'paid'
        ? get_string('toolready', LOCAL_DTEACHWHITEBOARD)
        : '',
    'connectsteps' => $steps === [] ? '' : get_string('connectsteps', LOCAL_DTEACHWHITEBOARD),
    'steps' => $steps,
    'actions' => $actions,
]);
echo $OUTPUT->footer();
