# CHANGELOG

## 2.0.3 (2026-09-28)

- only use the home page title for the page `<title>` and `og:title` when the forum list or new posts really is the home page — with the *Index page route* set elsewhere, such as *What's new*, the forum list at `forums/` was given the home page title
- upgrading from the XenForo 1 version of this add-on now carries the home page title across from its `homepageTitle` option
- refer to "the board title" rather than `boardTitle` in the add-on description and option text, and fix a typo in the option group description

## 2.0.2 (2025-12-11)

- run `enqueuePostUpgradeCleanUp()` during upgrades on XenForo 2.3 and later

## 2.0.1 (2024-07-12)

- update template modifications to work with XF 2.3

## 2.0.0 (2020-05-26)

- migrate to XF 2.x
- option to replace home page title in og:title and twitter:title metadata tags as well

## 1.0.1 (2016-09-02)

- add homepageTitle to og:title as well

## 1.0.0 (2016-08-26)

- first working version

