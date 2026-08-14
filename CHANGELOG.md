# Changelog

All notable changes to this plugin are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the plugin follows
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2026-08-14

First release.

### Added

- *Your subscription* admin page: starts the free trial, registers the site with
  the whiteboard service through LTI 1.3 Dynamic Registration, and shows the plan
  afterwards.
- Upgrade to the paid plan and manage billing, both through Stripe.
- Automatic activation of the registered tool: Dynamic Registration always leaves
  it pending and hidden from the activity chooser, so the plugin finishes the job.
- The tool leaves the activity chooser when the plan ends, and returns when a plan
  is running again.

[0.1.0]: https://github.com/dteach/moodle-local_dteachwhiteboard/releases/tag/v0.1.0
