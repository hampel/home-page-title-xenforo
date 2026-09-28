# Custom home page title for XenForo

A XenForo add-on that gives the forum home page its own title, instead of the board title. The
custom title is used for the page heading, the browser title and, optionally, the `og:title` and
`twitter:title` metadata that link previews read.

By [Simon Hampel](https://xenforo.com/community/members/sim.4264/).

## Requirements

- XenForo 2.2.0 or later

## Configuration

**The options are under *Setup > Options > Home Page Title*** in the admin control panel.

- **Home Page Title** — the title to use. Leave it empty and the add-on changes nothing.
- **Replace metadata** — also use it for `og:title` and `twitter:title`. On by default.

**It applies when the forum list or new posts is the home page** — that is, when *Forums default
page* names that page and the *Index page route* is empty or `forums/`. With the index route set to
something else, such as *What's new* or a page node, the home page keeps its usual title and so do
the forum pages.

## Upgrading from XenForo 1

The XenForo 1 version of this add-on stored the title in a differently named option. From 2.0.3,
installing this add-on over it carries the title across; earlier 2.x releases did not, so a forum
upgraded with one of those needs the title entered again.

## Support

- [Add-on: Home Page Title](https://xenforo.com/community/resources/home-page-title.7819/)
- [Discussion and support: Home Page Title](https://xenforo.com/community/threads/home-page-title.180858/)
