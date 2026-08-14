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
 * English strings.
 *
 * @package    local_dteachwhiteboard
 * @copyright  2026 dteach <contact@dteach.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['billing'] = 'Billing';
$string['contactus'] = 'Contact us';
$string['expired'] = 'Your plan has ended. Teachers can no longer open a whiteboard.';
$string['notconnected'] = 'This site is not connected yet. Start the trial to add the whiteboard to your activity chooser.';
$string['paiddaysleft'] = 'Paid plan: {$a} days left';
$string['paidrunning'] = 'Paid plan active';
$string['pluginname'] = 'Collaborative Whiteboard';
$string['privacy:metadata:dteachwhiteboard'] = 'This plugin sends the address of this site to the whiteboard service to register it and read its plan. It sends no personal data: whiteboard activities are launched by the External tool (LTI) module, which declares what it transmits.';
$string['privacy:metadata:dteachwhiteboard:siteurl'] = 'The address of this Moodle site, used to identify the subscription it holds.';
$string['privacy:metadata:nullproviderreason'] = 'This plugin stores only site-level settings: the token identifying this site to the whiteboard service, and its registration address. It stores no data about any user.';
$string['servicefailed'] = 'The whiteboard service could not be reached: {$a}';
$string['serviceurl'] = 'Service URL';
$string['serviceurl_desc'] = 'Base URL of the whiteboard service. Leave the default unless you are testing against another environment.';
$string['settings'] = 'Settings';
$string['startpaid'] = 'Start with paid plan';
$string['starttrial'] = 'Start trial';
$string['subscription'] = 'Your subscription';
$string['tokenrejected'] = 'This site\'s token is no longer valid. Start again to get a new one.';
$string['toolready'] = 'Whiteboard is active and available in the activity chooser.';
$string['trialdaysleft'] = 'Trial: {$a} days left';
$string['trialrunning'] = 'Trial running';
$string['upgrade'] = 'Upgrade to paid plan';
