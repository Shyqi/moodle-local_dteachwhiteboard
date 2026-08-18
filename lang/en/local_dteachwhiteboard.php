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

$string['activate'] = 'Activate';
$string['connectstep1'] = 'Your licence key and the address of this site are sent to the whiteboard service, which answers with a registration link. Nothing else is sent.';
$string['connectstep2'] = 'Moodle registers with the whiteboard service, and the whiteboard is added to the activity chooser of every course.';
$string['connectstep3'] = 'Teachers open a whiteboard from any course, and everyone in it draws on the same canvas.';
$string['connectsteps'] = 'What happens when you activate';
$string['contactus'] = 'Contact us';
$string['endson'] = 'Ends on';
$string['errorlicencealreadyclaimed'] = 'This licence key already activates another site. One key opens one site.';
$string['errorlicencenotactive'] = 'This order no longer pays for the whiteboard. Renew it from the Moodle Marketplace listing, then activate this site again.';
$string['errorsitealreadyconnected'] = 'This site is already connected to the whiteboard service.';
$string['errorunknownlicencekey'] = 'This licence key is not recognised. Check the key that came with your order.';
$string['expired'] = 'Your plan has ended, and teachers can no longer open a whiteboard. Renew it from the Moodle Marketplace listing to reopen it. This site keeps its licence key.';
$string['finishsetup'] = 'Finish setup';
$string['licencekey'] = 'Licence key';
$string['licencekeyhelp'] = 'The key you were sent when you bought the whiteboard on Moodle Marketplace. It activates this site, and no other.';
$string['licencekeyrequired'] = 'Enter the licence key that came with your order.';
$string['notconnected'] = 'This site is not connected yet. Paste your licence key to add the whiteboard to the activity chooser.';
$string['paiddaysleft'] = 'Plan active: {$a} days left';
$string['paidrunning'] = 'Plan active';
$string['plan'] = 'Plan';
$string['pluginname'] = 'Collaborative Whiteboard';
$string['privacy:metadata:dteachwhiteboard'] = 'This plugin sends the address of this site and its licence key to the whiteboard service, to register the site and read its plan. It sends no personal data: whiteboard activities are launched by the External tool (LTI) module, which declares what it transmits.';
$string['privacy:metadata:dteachwhiteboard:licencekey'] = 'The licence key bought for this site, used to identify the subscription it holds.';
$string['privacy:metadata:dteachwhiteboard:siteurl'] = 'The address of this Moodle site, used to identify the subscription it holds.';
$string['privacy:metadata:nullproviderreason'] = 'This plugin stores only site-level settings: the licence key bought for this site, the token identifying it to the whiteboard service, and its registration address. It stores no data about any user.';
$string['registrationpending'] = 'Your licence key is activated. Finish setup to register this site and put the whiteboard in the activity chooser.';
$string['servicefailed'] = 'The whiteboard service could not be reached: {$a}';
$string['serviceurl'] = 'Service URL';
$string['serviceurl_desc'] = 'Base URL of the whiteboard service. Leave the default unless you are testing against another environment.';
$string['settings'] = 'Settings';
$string['site'] = 'Site';
$string['stateexpired'] = 'Expired';
$string['statenotconnected'] = 'Not connected';
$string['statepaid'] = 'Active';
$string['subscription'] = 'Your subscription';
$string['tokenrejected'] = 'This site\'s connection to the whiteboard service is no longer valid. Activate it again below.';
$string['toolready'] = 'Whiteboard is active and available in the activity chooser.';
$string['viewlisting'] = 'View on Moodle Marketplace';
