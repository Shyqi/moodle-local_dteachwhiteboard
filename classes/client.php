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
 * Client over the whiteboard service subscription endpoints.
 *
 * @package    local_dteachwhiteboard
 * @copyright  2026 dteach <contact@dteach.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class client {

    /** @var int Seconds to wait for the service before giving up. */
    private const TIMEOUT = 10;

    /** @var string Service base URL, without its trailing slash. */
    private $serviceurl;

    /**
     * Build a client from the plugin settings.
     */
    public function __construct() {
        $this->serviceurl = rtrim((string) get_config('local_dteachwhiteboard', 'serviceurl'), '/');
    }

    /**
     * Claim the token this site keeps for every later call.
     *
     * @param string $siteurl wwwroot of this Moodle
     * @return array the issued invite, including its registration_url
     */
    public function issue_token(string $siteurl): array {
        return $this->request('POST', '/api/lti/draw/invites/', ['site_url' => $siteurl], null);
    }

    /**
     * Connection and plan state behind a token.
     *
     * @param string $token
     * @return array
     */
    public function status(string $token): array {
        return $this->request('GET', '/api/lti/draw/status/', null, $token);
    }

    /**
     * Stripe Checkout for the monthly plan.
     *
     * @param string $token
     * @param string $successurl where Stripe returns on success
     * @param string $cancelurl where Stripe returns on cancellation
     * @return string the URL to send the admin to
     */
    public function checkout_url(string $token, string $successurl, string $cancelurl): string {
        $response = $this->request('POST', '/api/lti/draw/checkout-session/', [
            'success_url' => $successurl,
            'cancel_url' => $cancelurl,
        ], $token);
        return $response['url'];
    }

    /**
     * Stripe Customer Portal for a subscription paid by card.
     *
     * @param string $token
     * @param string $returnurl where Stripe returns when the admin is done
     * @return string the URL to send the admin to
     */
    public function portal_url(string $token, string $returnurl): string {
        $response = $this->request('POST', '/api/lti/draw/portal-session/', [
            'return_url' => $returnurl,
        ], $token);
        return $response['url'];
    }

    /**
     * Issue one call and decode its JSON body.
     *
     * @param string $method
     * @param string $path
     * @param array|null $body encoded as JSON when present
     * @param string|null $token sent as a bearer token when present
     * @return array
     * @throws service_exception when the service answers anything but a 2xx JSON object
     */
    private function request(string $method, string $path, ?array $body, ?string $token): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $headers = ['Accept: application/json'];
        if ($token !== null) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
        }

        $curl = new \curl();
        $curl->setHeader($headers);
        $options = ['CURLOPT_TIMEOUT' => self::TIMEOUT, 'CURLOPT_CONNECTTIMEOUT' => self::TIMEOUT];
        $url = $this->serviceurl . $path;
        if ($method === 'GET') {
            $response = $curl->get($url, [], $options);
        } else {
            $response = $curl->post($url, json_encode($body), $options);
        }

        $code = (int) ($curl->get_info()['http_code'] ?? 0);
        $decoded = json_decode((string) $response, true);
        if ($code < 200 || $code >= 300 || !is_array($decoded)) {
            $servicecode = is_array($decoded) ? (string) ($decoded['code'] ?? '') : '';
            $detail = is_array($decoded) ? ($decoded['detail'] ?? '') : $curl->error;
            throw new service_exception($servicecode, (string) ($detail ?: $code));
        }
        return $decoded;
    }
}
