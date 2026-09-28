# Testing Home Page Title

What this add-on touches, what breaks quietly, and how to check it. The add-on is two options and
four template modifications, so testing it means confirming each modification still applies and
that the custom title appears on the home page and nowhere else.

## Surfaces

**Four template modifications and two options** — no class extensions, code event listeners or
templates. `Setup.php` has no install steps. On an upgrade from the XF1 1.x add-on it copies the
old `homepageTitle` option's value into `hampelHomePageTitle`, and on XF 2.3 and later its
`postUpgrade()` queues the standard post-upgrade file clean-up.

| modification | template | replaces |
|---|---|---|
| `hampelHomePageTitleForumList` | `forum_list` | the `<xf:h1>` shown when the forum list is the home page |
| `hampelHomePageTitleForumNewPosts` | `forum_new_posts` | the `<xf:h1>` shown when new posts is the home page |
| `hampelHomePageTitlePageContainer` | `PAGE_CONTAINER` | the `<title>` tag, on the home page only |
| `hampelHomePageTitlePageContainerMetaData` | `PAGE_CONTAINER` | the `og:title` / `twitter:title` macro, on the home page only, when *Replace metadata* is on |

| option | default | effect |
|---|---|---|
| `hampelHomePageTitle` | empty | the custom title; empty disables every modification |
| `hampelHomePageTitleReplaceMetadata` | on | also replace the metadata title |

## Fragile points

- **"Is this the home page" is decided by core, and the `PAGE_CONTAINER` modifications restate
  core's test.** A page is the home page when `forumsDefaultPage` names it *and* `indexRoute` is
  empty or `forums/`. The `<h1>` modifications inherit that test from the core templates; the
  `<title>` and metadata modifications carry their own copy. If a XenForo release changes the test
  in `forum_list` or `forum_new_posts`, the copy has to change with it. Before 2.0.3 the copy
  omitted `indexRoute`, and the forum list was given the home page title whenever the index route
  pointed elsewhere.
- **The `<h1>` and `<title>` modifications are exact `str_replace` finds.** A release that
  rewrites `<xf:h1>{$xf.options.boardTitle}</xf:h1>` or the `PAGE_CONTAINER` `<title>` line leaves
  the modification matching nothing, with no error, and the page quietly shows the board title.
- **The metadata modification is a regex matching both macro syntaxes** — XF 2.3's
  `id="metadata_macros::title"` and XF 2.2's `template="metadata_macros" name="title"`. Dropping
  either branch stops the metadata swap on that version.
- **A style with a customised copy of `PAGE_CONTAINER`, `forum_list` or `forum_new_posts` can stop
  matching in that style only.**
- **A home page that is neither the forum list nor new posts is not handled** — a page node or
  *What's new* as the index route gets no custom title.

## Automated

- **Every modification applies.** In the admin control panel, *Appearance > Template
  modifications*, each of the four should show no error and a non-zero apply count. In SQL, each
  row should be `ok` with an `apply_count` of 1 for the master style:

  ```sql
  SELECT m.modification_key, t.style_id, l.status, l.apply_count
  FROM xf_template_modification m
  JOIN xf_template_modification_log l USING (modification_id)
  JOIN xf_template t ON t.template_id = l.template_id
  WHERE m.addon_id = 'Hampel/HomePageTitle'
  ORDER BY m.modification_key, t.style_id;
  ```

  **A modification that matched nothing still logs `ok`, with an `apply_count` of 0** — so read
  the count, not the status.
- **`_output/` and the database agree.** Run from the XenForo root; the working tree should be
  unchanged afterwards:

  ```bash
  php cmd.php xf-dev:import --addon=Hampel/HomePageTitle
  php cmd.php xf-addon:export Hampel/HomePageTitle
  git status --short
  ```

- **The title lands on the home page and only there.** Set the options for each row, fetch the
  page as a guest, and read `<title>` and `og:title` from the HTML. Record `indexRoute`,
  `forumsDefaultPage`, `hampelHomePageTitle` and `hampelHomePageTitleReplaceMetadata` first, and
  restore them afterwards — verified against their `default_value` in `xf_option`, not only
  against what was recorded. Request the home page as `/`: when the forum list is the index,
  `forums/` redirects there.

  | `indexRoute` | `forumsDefaultPage` | metadata | fetch | expect |
  |---|---|---|---|---|
  | `forums/` | `forums` | on | `/` | custom `<title>` and `og:title` |
  | `whats-new/` | `forums` | on | `forums/` | core's `Forum list \| <board>`, not the custom title |
  | `whats-new/` | `forums` | on | `/` | core's `What's new \| <board>` |
  | `forums/` | `new_posts` | on | `/` | custom `<title>` and `og:title` |
  | `forums/` | `forums` | off | `/` | custom `<title>`, core's `og:title` |

  Options can be set from a script booted with `XF::setupApp('XF\Cli\App')`, via
  `$app->repository('XF:Option')->updateOptions([...])`. Run the whole probe as one script with the
  restore in an exit trap, so a failed run cannot leave the options changed. The second row is the
  regression check for the 2.0.3 fix; it showed the custom title before it.

- **The upgrade path, in a disposable forum with development mode off.** Install the last published
  release from its zip, set a title, then upgrade to the new zip. The upgrade must report
  *Importing add-on data*, not *All data imported* — the second is the development-mode shortcut and
  proves nothing. Then run the job queue (`xf:run-jobs`), and check that the title is kept, each
  modification applies once, the home page renders it, and `xf_error_log` gained no rows. Run the
  probe table above on the same forum, on each supported XenForo line.
- **The XenForo 2 half of an upgrade from the XF1 add-on.** On a disposable forum, uninstall this
  add-on, then create the state XenForo's own 2.0 upgrade leaves: an `xf_addon` row for
  `HomepageTitle` with `version_id` 3, `active` 0 and `is_legacy` 1, and an `xf_option` row
  `homepageTitle` owned by `HomepageTitle` holding a title. Upgrade from the new zip: the title
  must arrive in `hampelHomePageTitle`, the old row must be gone, and no
  `hampelHomePageTitleLegacy` key may be left in `xf_data_registry`. **Run the same with the
  previous release as the control** — it must lose the title, or the test is not exercising the
  migration. An empty old value must set nothing.

## Needs a human

- **Look at the home page in a browser** with a custom title set, in each of the two supported
  configurations (forum list, new posts): the heading, the browser tab title, and a link preview
  if one can be generated, for the metadata.
- **Upgrading from the XF1 1.x add-on needs a real XF1 forum.** The automated check above builds
  the state XenForo's 2.0 upgrade is expected to leave; only a forum that actually came through
  that upgrade shows the old `homepageTitle` row survives it. After the core upgrade, install this
  add-on over the legacy one and confirm the home page title set under 1.x is now the value of
  *Home Page Title*.
