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
 * Subscription state of this site: the licence key, the running plan, or the plan that ended.
 *
 * @package    local_dteachwhiteboard
 * @copyright  2026 dteach <contact@dteach.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_dteachwhiteboard\client;
use local_dteachwhiteboard\lti_tool;
use local_dteachwhiteboard\service_exception;

/** Component name, as every get_config() and get_string() call on this page needs it. */
const LOCAL_DTEACHWHITEBOARD = 'local_dteachwhiteboard';

/** Where the "Contact us" button writes to. */
const LOCAL_DTEACHWHITEBOARD_CONTACT = 'contact@dteach.net';

/** Marketplace listing the whiteboard is bought from. Placeholder until the listing is published. */
const LOCAL_DTEACHWHITEBOARD_LISTING = 'https://marketplace.moodle.com/';

admin_externalpage_setup('local_dteachwhiteboard_subscription');

$action = optional_param('action', '', PARAM_ALPHA);
$pageurl = new moodle_url('/local/dteachwhiteboard/subscription.php');
$client = new client();
$token = (string) get_config(LOCAL_DTEACHWHITEBOARD, 'token');
$licencekey = (string) get_config(LOCAL_DTEACHWHITEBOARD, 'licencekey');
$pendingkey = '';
$error = '';
$warning = '';

if ($action !== '') {
    require_sesskey();
}

if ($action === 'register') {
    $stored = (string) get_config(LOCAL_DTEACHWHITEBOARD, 'registrationurl');
    if ($stored !== '') {
        redirect(new moodle_url('/mod/lti/startltiadvregistration.php', [
            'url' => $stored,
            'sesskey' => sesskey(),
        ]));
    }
}

if ($action === 'activate') {
    $pendingkey = optional_param('licencekey', '', PARAM_ALPHANUM);
    if ($pendingkey === '') {
        $error = get_string('licencekeyrequired', LOCAL_DTEACHWHITEBOARD);
    }
}

$status = null;
if ($pendingkey === '' && $token !== '') {
    try {
        $status = $client->status($token);
    } catch (service_exception $e) {
        if ($e->servicecode !== 'invalid_token') {
            throw $e;
        }
        // A token issued but never registered is purged after a day. The key belongs to this
        // site for good, so spend it again rather than ask the admin to paste it a second time.
        unset_config('token', LOCAL_DTEACHWHITEBOARD);
        unset_config('registrationurl', LOCAL_DTEACHWHITEBOARD);
        $warning = get_string('tokenrejected', LOCAL_DTEACHWHITEBOARD);
        $pendingkey = $licencekey;
    }
}

if ($pendingkey !== '') {
    try {
        $invite = $client->issue_token($CFG->wwwroot, $pendingkey);
        set_config('licencekey', $pendingkey, LOCAL_DTEACHWHITEBOARD);
        set_config('token', $invite['token'], LOCAL_DTEACHWHITEBOARD);
        set_config('registrationurl', $invite['registration_url'], LOCAL_DTEACHWHITEBOARD);
        if ($action === 'activate') {
            redirect(new moodle_url('/mod/lti/startltiadvregistration.php', [
                'url' => $invite['registration_url'],
                'sesskey' => sesskey(),
            ]));
        }
    } catch (service_exception $e) {
        $error = $e->getMessage();
    }
}

$registrationurl = (string) get_config(LOCAL_DTEACHWHITEBOARD, 'registrationurl');

if ($status !== null && $status['connected']) {
    lti_tool::activate((string) $status['client_id'], $status['state'] !== 'expired');
}

$state = $status === null ? 'not_connected' : $status['state'];
// A plan with no end date never ends on its own, so it has no day count to show.
$endsat = $status === null ? null : ($status['plan']['ends_at'] ?? null);
$end = empty($endsat) ? false : strtotime($endsat);
$daysleft = $end === false ? null : max(0, (int) ceil(($end - time()) / DAYSECS));

if ($state === 'paid') {
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
    $summary = $registrationurl === ''
        ? get_string('notconnected', LOCAL_DTEACHWHITEBOARD)
        : get_string('registrationpending', LOCAL_DTEACHWHITEBOARD);
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
if ($warning !== '') {
    echo $OUTPUT->notification($warning, \core\output\notification::NOTIFY_WARNING);
}
if ($error !== '') {
    echo $OUTPUT->notification(s($error), \core\output\notification::NOTIFY_ERROR);
}

// Both spacing sets are emitted so the row breathes on Bootstrap 4 (4.2) and 5 (5.0) alike.
$actions = '';
if ($state === 'not_connected' && $registrationurl !== '') {
    $actions .= html_writer::div($OUTPUT->render(new single_button(
        new moodle_url($pageurl, ['action' => 'register', 'sesskey' => sesskey()]),
        get_string('finishsetup', LOCAL_DTEACHWHITEBOARD),
        'post',
        single_button::BUTTON_PRIMARY
    )), 'mr-2 me-2');
}
$actions .= html_writer::link(
    LOCAL_DTEACHWHITEBOARD_LISTING,
    get_string('viewlisting', LOCAL_DTEACHWHITEBOARD),
    [
        'class' => 'btn ' . ($state === 'expired' ? 'btn-primary' : 'btn-link'),
        'target' => '_blank',
        'rel' => 'noopener noreferrer',
    ]
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
    'toolready' => $state === 'paid' ? get_string('toolready', LOCAL_DTEACHWHITEBOARD) : '',
    'keyform' => $state === 'not_connected' && $registrationurl === '' ? [
        'formurl' => $pageurl->out(false),
        'sesskey' => sesskey(),
        'label' => get_string('licencekey', LOCAL_DTEACHWHITEBOARD),
        'help' => get_string('licencekeyhelp', LOCAL_DTEACHWHITEBOARD),
        'submit' => get_string('activate', LOCAL_DTEACHWHITEBOARD),
    ] : false,
    'connectsteps' => $steps === [] ? '' : get_string('connectsteps', LOCAL_DTEACHWHITEBOARD),
    'steps' => $steps,
    'actions' => $actions,
]);
echo $OUTPUT->footer();
