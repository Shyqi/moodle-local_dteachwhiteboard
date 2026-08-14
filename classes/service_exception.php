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
 * A call the whiteboard service refused, carrying the code it answered with.
 *
 * @package    local_dteachwhiteboard
 * @copyright  2026 dteach <contact@dteach.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class service_exception extends \moodle_exception {

    /** @var string Machine-readable code from the service, empty when it sent none. */
    public $servicecode;

    /**
     * @param string $servicecode code the service answered with
     * @param string $detail human-readable reason, shown to the admin
     */
    public function __construct(string $servicecode, string $detail) {
        $this->servicecode = $servicecode;
        parent::__construct('servicefailed', 'local_dteachwhiteboard', '', $detail);
    }
}
