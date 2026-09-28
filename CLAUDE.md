# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

Home Page Title is a XenForo add-on (`Hampel/HomePageTitle`, requires XF 2.2.0+) that lets an admin
set a title for the forum home page other than the board title. The repository root is the add-on
directory, `src/addons/Hampel/HomePageTitle/` inside a XenForo install. There are no Composer
dependencies and no tests, and the only PHP is `Setup.php`, which migrates the XF1 add-on's setting:
**the add-on is two options and four template modifications in `_output/`.**

## The title is swapped in three places, and each must agree with core on what "home" means

The options are `hampelHomePageTitle` (the text; empty means the add-on does nothing) and
`hampelHomePageTitleReplaceMetadata` (on by default), in their own option group.

| modification | template | what it replaces | condition |
|---|---|---|---|
| `...ForumList`, `...ForumNewPosts` | `forum_list`, `forum_new_posts` | the page `<xf:h1>` | the option is set |
| `...PageContainer` | `PAGE_CONTAINER` | the `<title>` tag | the option is set **and** the page is the home page |
| `...PageContainerMetaData` | `PAGE_CONTAINER` | the `metadata_macros` title macro (`og:title`, `twitter:title`) | as above, plus the metadata option |

**Core decides whether `forum_list` or `forum_new_posts` is the home page, with two options**:
`forumsDefaultPage` must name that page, *and* `indexRoute` must be empty or `forums/`. Both
templates wrap their `<xf:h1>` in exactly that test, so the `<h1>` modifications inherit it and
need no condition of their own.

**The `PAGE_CONTAINER` modifications sit outside that test and have to restate it** — `$template`,
`forumsDefaultPage` and `indexRoute`, in every branch. Before 2.0.3 they omitted `indexRoute`, so a
forum whose index route was *What's new* or a page node had its `forums/` list titled as the home
page. Keep the two conditions in step with core's if either template changes it. A home page that
is neither of these templates is not handled at all.

### The metadata modification is a regex so that one release covers XF 2.2 and 2.3

**`...PageContainerMetaData` is the only `preg_replace`, and its alternation matches both macro
syntaxes** — XF 2.3's `<xf:macro id="metadata_macros::title" .../>` and 2.2's
`template="metadata_macros" name="title"`. Its replacement uses the 2.2 form, which 2.3 still
compiles. Keep both branches for as long as `require` allows 2.2; dropping one silently stops the
metadata swap on that version.

2.0.1 existed to make the modifications match XF 2.3's templates, so treat an XF upgrade as a
reason to recheck them.

### A modification that stops matching fails silently

The other three are exact `str_replace` finds against core markup. **XF logs a non-matching
modification as `ok` with an apply count of 0**, so the page quietly reverts to the board title and
nothing errors. The apply count is the check: *Appearance > Template modifications* in the admin
control panel, or the `xf_template_modification_log` table. Each should apply exactly once.

## `Setup.php` carries the XF1 title across, and guards a 2.3-only call

**An upgrade from the XF1 1.x add-on is a real upgrade path**, through `legacy_addon_id`: XF treats
the XF1 add-on's record as the installed version. The 1.x add-on stored its title in an option named
`homepageTitle`, which the data import deletes because 2.x does not define it. So `upgrade()` —
which runs after `preUpgrade()` has moved the old option to this add-on's id and before the import —
reads the value and stashes it in the data registry, and `postUpgrade()`, which runs after the
import, sets `hampelHomePageTitle` from it unless that already holds a title. The registry is what
carries the value across: in the admin control panel the import runs as a job, so the two methods
are not in the same request.

It is keyed on the old option row existing, not on a version number, so an ordinary 2.x upgrade
reads nothing and does nothing. The XenForo 2 half is tested by planting the legacy rows in a
disposable forum — `TESTING.md` has the recipe and its control. **It has not been proven against a
forum that came through XenForo's own 1.x to 2.x upgrade**, which is the only thing that shows the
old row survives that far.

`postUpgrade()` calls `enqueuePostUpgradeCleanUp()` only when `\XF::$versionId >= 2030000`, because
that method does not exist on XF 2.2 and the add-on still installs there. Options and modifications
come from `_output/`.

## Commands

Run from the XenForo install root; `cmd.php` resolves the install from its own location, so an
absolute path to it works from this directory too.

```bash
php cmd.php xf-dev:import --addon=Hampel/HomePageTitle     # after hand-editing _output/
php cmd.php xf-addon:export Hampel/HomePageTitle           # DB -> _output/, this add-on only
php cmd.php xf:addon-upgrade Hampel/HomePageTitle          # after a version bump
php cmd.php xf-addon:build-release Hampel/HomePageTitle    # release zip into _releases/
```

**Never run `xf-dev:export`** — its `--addon` defaults to every add-on on the install, and it
overwrites `_output/` from the database. `xf-addon:export` above is the scoped equivalent.

**Prefer editing a modification in the admin control panel and exporting** over hand-editing its
JSON: the `replace` values carry escaped tabs and newlines, and the `_metadata.json` hashes must
agree with the files.

The install's `AGENTS.md` covers the `_output/` workflow, version bumps and releases; the
`xenforo-addon-release` and `xenforo-addon-audit` skills, where available, carry the release order
and the full checklist.

## Packaging

**Dev-only root files are kept out of two places by two separate mechanisms**, and adding a new one
means updating both:

- **The XenForo release zip** — `build.json`'s `rm` line deletes `TESTING.md` and the two Claude
  files. It must run before the `mv` that promotes every remaining root `*.md` to the zip root, or
  it matches nothing and the file ships.
- **`git archive`** (GitHub's "Download ZIP") — `export-ignore` entries in `.gitattributes`.

Machine-specific notes go in `CLAUDE.local.md`, which is gitignored. `.gitattributes` also forces LF
on commit and checkout, because a Windows git client can write CRLF into the working tree.
