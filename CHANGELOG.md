# Changelog

All notable changes to this plugin are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the plugin follows
[Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.2.0] - 2026-08-18

Access moved to the Moodle Marketplace listing. The plugin no longer sells anything.

### Added

- Licence key field on *Your subscription*: the key that comes with a Marketplace order activates this site, and is kept so the page can register again on its own if the service drops an invite that was never used.
- Readable messages for the three refusals the service can answer with: unknown key, key already spent on another site, site already connected.

### Changed

- *Your subscription* now has three states: waiting for a licence key, plan active, plan ended. The ended state links to the Marketplace listing.

### Removed

- Free trial, Stripe Checkout and the billing portal: buying and billing belong to the Marketplace.

## [0.1.0] - 2026-08-14

First release.

### Added

- *Your subscription* admin page: starts the free trial, registers the site with the whiteboard service through LTI 1.3 Dynamic Registration, and shows the plan afterwards.
- Upgrade to the paid plan and manage billing, both through Stripe.
- Automatic activation of the registered tool: Dynamic Registration always leaves it pending and hidden from the activity chooser, so the plugin finishes the job.
- The tool leaves the activity chooser when the plan ends, and returns when a plan is running again.

[0.2.0]: https://github.com/dteach/moodle-local_dteachwhiteboard/releases/tag/v0.2.0
[0.1.0]: https://github.com/dteach/moodle-local_dteachwhiteboard/releases/tag/v0.1.0
