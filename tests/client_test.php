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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');
require_once(__DIR__ . '/fixtures/mock_curl.php');

/**
 * What the plugin sends to the whiteboard service, and what it makes of the answers.
 *
 * @package    local_dteachwhiteboard
 * @copyright  2026 dteach <contact@dteach.net>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_dteachwhiteboard\client
 */
final class client_test extends \advanced_testcase {
    /**
     * Point the client at a service that only exists in these tests.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        set_config('serviceurl', 'https://service.test/', 'local_dteachwhiteboard');
    }

    /**
     * The licence key is what buys the invite, so it travels with the site address.
     */
    public function test_issue_token_spends_the_licence_key(): void {
        $curl = new mock_curl(201, json_encode([
            'token' => 'tok',
            'registration_url' => 'https://service.test/api/lti/register/tok/',
        ]));

        $invite = (new client($curl))->issue_token('https://moodle.test', 'abcdef');

        $this->assertSame('tok', $invite['token']);
        $call = $curl->calls[0];
        $this->assertSame('POST', $call['method']);
        $this->assertSame('https://service.test/api/lti/draw/invites/', $call['url']);
        $this->assertSame(
            ['site_url' => 'https://moodle.test', 'licence_key' => 'abcdef'],
            json_decode($call['params'], true)
        );
        $this->assertSame(['Accept: application/json', 'Content-Type: application/json'], $call['header']);
    }

    /**
     * A trial buys its invite with an email address instead of a key.
     */
    public function test_start_trial_sends_the_contact_email(): void {
        $curl = new mock_curl(201, json_encode([
            'token' => 'tok',
            'registration_url' => 'https://service.test/api/lti/register/tok/',
        ]));

        $invite = (new client($curl))->start_trial('https://moodle.test', 'admin@moodle.test');

        $this->assertSame('tok', $invite['token']);
        $call = $curl->calls[0];
        $this->assertSame('POST', $call['method']);
        $this->assertSame('https://service.test/api/lti/draw/invites/', $call['url']);
        $this->assertSame(
            ['site_url' => 'https://moodle.test', 'contact_email' => 'admin@moodle.test'],
            json_decode($call['params'], true)
        );
    }

    /**
     * Buying during a trial spends the key on the token this site already holds.
     */
    public function test_claim_licence_spends_the_key_on_the_site_token(): void {
        $curl = new mock_curl(200, json_encode([
            'state' => 'paid',
            'plan' => ['slug' => 'lti_draw_paid_monthly'],
        ]));

        $status = (new client($curl))->claim_licence('tok', 'abcdef');

        $this->assertSame('paid', $status['state']);
        $call = $curl->calls[0];
        $this->assertSame('POST', $call['method']);
        $this->assertSame('https://service.test/api/lti/draw/licence/', $call['url']);
        $this->assertSame(['licence_key' => 'abcdef'], json_decode($call['params'], true));
        $this->assertContains('Authorization: Bearer tok', $call['header']);
    }

    /**
     * Every later call is authenticated by the token the invite left behind.
     */
    public function test_status_sends_the_site_token(): void {
        $curl = new mock_curl(200, json_encode(['connected' => true, 'state' => 'paid']));

        $status = (new client($curl))->status('tok');

        $this->assertSame('paid', $status['state']);
        $call = $curl->calls[0];
        $this->assertSame('GET', $call['method']);
        $this->assertSame('https://service.test/api/lti/draw/status/', $call['url']);
        $this->assertSame(['Accept: application/json', 'Authorization: Bearer tok'], $call['header']);
    }

    /**
     * A refusal the admin can act on comes back as its own sentence, not as a status code.
     */
    public function test_a_refused_licence_key_carries_its_code(): void {
        $curl = new mock_curl(400, json_encode([
            'code' => 'unknown_licence_key',
            'detail' => 'This licence key does not exist.',
        ]));

        try {
            (new client($curl))->issue_token('https://moodle.test', 'nope');
            $this->fail('The client should have refused an unknown licence key.');
        } catch (service_exception $e) {
            $this->assertSame('unknown_licence_key', $e->servicecode);
            $this->assertSame(
                get_string('errorunknownlicencekey', 'local_dteachwhiteboard'),
                $e->getMessage()
            );
        }
    }

    /**
     * A token the service no longer knows is what tells the page to ask for another invite.
     */
    public function test_an_unknown_token_is_told_apart(): void {
        $curl = new mock_curl(403, json_encode([
            'code' => 'invalid_token',
            'detail' => 'Unknown or revoked token.',
        ]));

        try {
            (new client($curl))->status('stale');
            $this->fail('The client should have refused a stale token.');
        } catch (service_exception $e) {
            $this->assertSame('invalid_token', $e->servicecode);
        }
    }

    /**
     * A trial asked for without an email comes back as its own sentence too.
     */
    public function test_a_trial_without_an_email_carries_its_code(): void {
        $curl = new mock_curl(400, json_encode([
            'code' => 'contact_email_required',
            'detail' => 'A trial needs an email address.',
        ]));

        try {
            (new client($curl))->start_trial('https://moodle.test', '');
            $this->fail('The client should have refused a trial without an email.');
        } catch (service_exception $e) {
            $this->assertSame('contact_email_required', $e->servicecode);
            $this->assertSame(
                get_string('errorcontactemailrequired', 'local_dteachwhiteboard'),
                $e->getMessage()
            );
        }
    }

    /**
     * A gateway answering anything but JSON must not be read as a plan.
     */
    public function test_an_answer_that_is_not_json_fails(): void {
        $curl = new mock_curl(502, 'Bad gateway');

        $this->expectException(service_exception::class);
        (new client($curl))->status('tok');
    }
}
