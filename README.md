# Collaborative Whiteboard (`local_dteachwhiteboard`)

A persistent, collaborative whiteboard for Moodle activities. Teachers add a
whiteboard from the activity chooser; everyone in the activity draws on the same
canvas, live, and the drawing is still there on the next visit.

## Install

Drop this repository into `local/dteachwhiteboard/` of your Moodle install, then
visit *Site administration → Notifications*.

## Set up

*Site administration → Plugins → Local plugins → Collaborative Whiteboard → Your
subscription*, then start the 15-day free trial with your email address, or paste
the licence key that came with your Moodle Marketplace order. The page walks the
LMS through registration and shows the plan afterwards.

Nothing else to configure. LTI Dynamic Registration always leaves a tool pending,
shown only as a preconfigured tool and launched in an embed; the plugin activates
it, puts it in the activity chooser and switches it to a new window afterwards.

## Requirements

Moodle 4.2 or later, with LTI 1.3 Dynamic Registration.

## Subscription

The plugin is GPL v3, but the whiteboard itself is a hosted service run by dteach.
Every site opens a 15-day free trial from the subscription page, once: the days
belong to the site, so reinstalling never restarts the count. After that, access is
bought on the [Moodle Marketplace listing](https://marketplace.moodle.com/plugins/4045), which sends a licence key. One key opens
one site, and a key pasted during the trial takes over from it without registering
again. When the subscription ends, teachers can no longer open a whiteboard and the
tool leaves the activity chooser; the boards already drawn are kept.

## Privacy

The plugin itself stores no personal data. It sends the service the address of your
site, with the licence key it was given or, for a trial, the email address typed on
the subscription page — which it sends but never stores. Whiteboards are opened by Moodle's External tool (LTI) module,
which declares separately what it transmits to the tool.

## Support

Bugs and feature requests: open an issue on this repository. Anything else:
<contact@dteach.net>.

## License

GNU GPL v3 or later. See [LICENSE](LICENSE).
