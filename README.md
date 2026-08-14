# Collaborative Whiteboard (`local_dteachwhiteboard`)

A persistent, collaborative whiteboard for Moodle activities. Teachers add a
whiteboard from the activity chooser; everyone in the activity draws on the same
canvas, live, and the drawing is still there on the next visit.

## Install

Drop this repository into `local/dteachwhiteboard/` of your Moodle install, then
visit *Site administration → Notifications*.

## Set up

*Site administration → Plugins → Local plugins → Collaborative Whiteboard → Your
subscription*, then start the free trial. The page walks the LMS through
registration and shows the plan afterwards.

Nothing else to configure. LTI Dynamic Registration always leaves a tool pending,
shown only as a preconfigured tool and launched in an embed; the plugin activates
it, puts it in the activity chooser and switches it to a new window afterwards.

## Requirements

Moodle 4.2 or later, with LTI 1.3 Dynamic Registration.

## Subscription

This plugin is free and GPL, but the whiteboard itself is a hosted service run by
dteach. Every site starts on a free trial; once it ends, a paid plan is needed for
teachers to keep opening whiteboards. Plans are bought from the *Your subscription*
page and billed by dteach, not through Moodle.

## Privacy

The plugin itself stores no personal data, and sends the service nothing but the
address of your site. Whiteboards are opened by Moodle's External tool (LTI) module,
which declares separately what it transmits to the tool.

## Support

Bugs and feature requests: open an issue on this repository. Anything else:
<contact@dteach.net>.

## License

GNU GPL v3 or later. See [LICENSE](LICENSE).
