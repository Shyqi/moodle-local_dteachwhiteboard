# Moodle Marketplace listing

Source text for the plugin's public page on [marketplace.moodle.com](https://marketplace.moodle.com).
Nothing here is read by Moodle — the listing is filled in by hand in the Marketplace
submission form, as the last step before publication. Keep this file and
[README.md](../README.md) saying the same thing: the contribution checklist asks for the
plugin record and the README to carry the same description.

## Short description

> A persistent, collaborative whiteboard in your Moodle activities. Teachers add it from
> the activity chooser, everyone draws on the same canvas live, and the drawing is still
> there on the next visit.

## Full description

> **Collaborative Whiteboard** puts a live, shared canvas inside any Moodle course.
>
> A teacher adds a whiteboard from the activity chooser like any other activity. Everyone
> enrolled opens the same canvas and draws on it together, in real time — sketching a
> proof, annotating a diagram, running a group brainstorm. The board is persistent: close
> it, come back next week, and the work is exactly where it was left.
>
> **Features**
>
> - Whiteboard available from the activity chooser of every course, no per-course setup.
> - Real-time multi-user drawing: shapes, freehand, text, arrows, images.
> - Boards persist between sessions and stay tied to their course activity.
> - Launched through Moodle's External tool (LTI 1.3) module, so enrolment and roles come
>   straight from Moodle.
>
> **Installation**
>
> Install the plugin, then go to *Site administration → Plugins → Local plugins →
> Collaborative Whiteboard → Your subscription* and paste the licence key that came with
> your order. The page runs LTI 1.3 Dynamic Registration for you and activates the tool in
> the activity chooser. There is nothing else to configure.
>
> **Requirements**
>
> Moodle 4.2 or later, with LTI 1.3 Dynamic Registration enabled.
>
> **Subscription**
>
> The plugin is GPL v3, but the whiteboard itself is a hosted service run by dteach. Your
> purchase here sends a licence key, which activates one site from the *Your subscription*
> page. When the subscription ends, teachers can no longer open a whiteboard and the tool
> leaves the activity chooser; the boards already drawn are kept.
>
> **Privacy**
>
> The plugin stores no personal data and sends the service nothing but the address of your
> site. Whiteboards are opened by Moodle's External tool (LTI) module, which declares
> separately what it transmits to the tool.

## Screenshots to produce

The Marketplace shows these in order, so shoot them in this order. Use a demo site with a
real-looking course name, and no test data visible.

1. **Whiteboard open in a course** — the hero shot. A board with actual content on it
   (diagram, handwriting, a couple of shapes) and at least two cursors visible, so the
   collaboration reads at a glance.
2. **Activity chooser** — the modal with the Whiteboard entry visible among the core
   activities. Shows an admin exactly what their teachers will see after install.
3. **Your subscription, active plan** — the admin page with the Active badge, plan name,
   end date, and "Whiteboard is active and available in the activity chooser."
4. **Your subscription, not connected** — the first-run state with the licence key field
   and the "What happens when you activate" steps. Answers "what do I have to do to
   install this?".

## Listing fields

| Field | Value |
|---|---|
| Plugin name | Collaborative Whiteboard |
| Component | `local_dteachwhiteboard` |
| Plugin type | Local plugin |
| Moodle versions | 4.2 – 5.0 |
| Licence | GNU GPL v3 or later |
| Source control | https://github.com/dteach/moodle-local_dteachwhiteboard |
| Bug tracker | https://github.com/dteach/moodle-local_dteachwhiteboard/issues |
| Documentation | https://docs.moodle.org/en/Collaborative_Whiteboard *(to create)* |
| Contact | contact@dteach.net |

## Still missing before submitting

- `pix/icon.svg` — the plugin icon, used in the admin tree and as the listing logo.
- The four screenshots above.
- The `docs.moodle.org` page, if the extended documentation link is kept.
