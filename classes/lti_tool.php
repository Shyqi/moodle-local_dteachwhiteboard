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

namespace local_dteachwhiteboard;

/**
 * Finishes what Dynamic Registration leaves undone on the created tool.
 *
 * @package    local_dteachwhiteboard
 * @copyright  2026 dteach <contact@dteach.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lti_tool {

    /**
     * Activate the registered tool and put it in the activity chooser.
     *
     * Dynamic Registration always leaves the tool pending, hardcodes `coursevisible` to
     * preconfigured and the launch container to an embed, ignoring what the tool asked
     * for (see mod/lti/classes/local/ltiopenid/registration_helper.php). Idempotent, so
     * the subscription page can call it on every load.
     *
     * The client id is what names our tool: dteach serves several products from one
     * launch URL, so a site running more than one of them has several tools sharing a
     * base URL, told apart only by the client id the platform minted per registration.
     *
     * @param string $clientid client id of the registration this site owns
     * @return bool whether the tool exists and is now ready to use
     */
    public static function activate(string $clientid): bool {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/lti/locallib.php');

        $record = $DB->get_record('lti_types', ['clientid' => $clientid]);
        if ($record === false) {
            return false;
        }

        $typeconfig = lti_get_type_config($record->id);
        $ready = (int) $record->state === LTI_TOOL_STATE_CONFIGURED
            && (int) $record->coursevisible === LTI_COURSEVISIBLE_ACTIVITYCHOOSER
            && (int) ($typeconfig['launchcontainer'] ?? 0) === LTI_LAUNCH_CONTAINER_WINDOW;
        if ($ready) {
            return true;
        }

        $type = lti_get_type($record->id);
        // No config key maps onto `state`, so the column is set here; the two others are
        // applied by lti_prepare_type_for_save().
        $type->state = LTI_TOOL_STATE_CONFIGURED;
        $config = new \stdClass();
        $config->lti_coursevisible = LTI_COURSEVISIBLE_ACTIVITYCHOOSER;
        $config->lti_launchcontainer = LTI_LAUNCH_CONTAINER_WINDOW;
        lti_update_type($type, $config);

        return true;
    }
}
