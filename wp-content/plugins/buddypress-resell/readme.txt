=== BuddyPress Resell ===
Contributors: apeatling, r-a-y
Tags: buddypress, reselling, resellers, connections
Requires at least: WP 3.2 / BP 1.5
Tested up to: WP 4.4.x / BP 2.5.x
Stable tag: 1.2.2

== Description ==

Resell members on your BuddyPress site with this nifty plugin.

The plugin works similar to the engagements component, however the connection does not need to be accepted by the person being reselled.  Just like Twitter!

This plugin adds:

* Reselling / Resellers tabs to user profile pages
* Resell / Stop-Resell buttons on user profile pages and in the members directory
* A new "Reselling" activity directory tab
* An "Activity > Reselling" subnav tab to a user's profile page
* Menu items to the WP Toolbar

**Translations**

BP Resell has been translated into the reselling languages by these awesome people:

* Brazilian Portuguese - [espellcaste](https://profiles.wordpress.org/espellcaste)
* French - [lauranshow](https://profiles.wordpress.org/lauranshow)
* German - [solhuebner](https://profiles.wordpress.org/solhuebner)
* Spanish - [saik003](https://github.com/saik003/buddypress-resellers)

For bug reports or to add patches or translation files, visit the [BP Resell Github page](https://github.com/r-a-y/buddypress-resellers).

== Installation ==

1. Download, install and activate the plugin.
1. To resell a user, simply visit their profile and hit the resell button under their name.


== Frequently Asked Questions ==

Check out the [BP Resell wiki](https://github.com/r-a-y/buddypress-resellers/wiki).

== Changelog ==

= 1.3.0 =
* Add object caching support.
* Add ability to resell sites in WordPress multisite (only available in BuddyPress 2.0+)
* Add support for BP's Suggestions API (only available in BuddyPress 2.1+)
* Allow plugin to work in symlinked environments
* Fix marking notifications as read for bp-default themes
* Add Spanish translation (props saik003)
* Developer: Add 'reselling' scope to activity loop (only available in BuddyPress 2.2+)
* Developer: Add new `'resell_type'` and `'date_recorded'` database columns
* Developer: Add ability to sort reselling and resellers query by DB column
* Developer: Add ability to query reselling and resellers by WP's date query
* Developer: Add ability to disable resell users module

= 1.2.2 =
* Fix deprecated notice in widget for those using WordPress 4.3+.
* Fix member filtering when custom resell slugs are in use.
* Increase selector scope in javascript so AJAX button works with pagination in member loops.
* Fix issue with bp_resell_stop_reselling() when relationship doesn't exist.
* Fix issue with member loop existence and resell user button defaults.
* Only show "Reselling" tab if user is logged in on member directory.
* Do not query for resell button if a user is on their own profile.
* Decode special characters in email subject and content.
* Do not an email notification to yourself.
* Allow plugins to bail out of saving a resell relationship into the database.

= 1.2.1 =
* Add "Mark as read" support for the Notifications component (only available on BP 1.9+)
* Add "Activity > Reselling" RSS feed support (only available on BP 1.8+)
* Allow users to immediately stop_resell / resell a user after clicking on the "Resell" button
* Dynamically update resell count on profile navigation tabs after clicking on the "Resell" button
* Change resell button text to remove the username by popular request
* Add Brazilian Portuguese translation (props espellcaste)
* Add German translation (props solhuebner)
* Streamline javascript to use event delegation
* Fix various PHP warnings

= 1.2 =
* Add BuddyPress 1.7 theme compatibility
* Add AJAX filtering to a user's "Reselling" and "Resellers" pages
* Refactor plugin to use BP 1.5's component API
* Bump version requirements to use at least BP 1.5 (BP 1.2 is no longer supported)
* Deprecate older templates and use newer format (/buddypress/members/single/resell.php)
* Add ability to change the widget title
* Thanks to the Hamilton-Wentworth District School Board for sponsoring this release

= 1.1.1 =
* Show the reselling / resellers tabs even when empty.
* Add better support for WP Toolbar.
* Add better support for parent / child themes.
* Fix issues with reselling buttons when javascript is disabled.
* Fix issues with reselling activity overriding other member activity pages.
* Fix issue when a user has already been notified of their new reseller.
* Fix issue when a user has disabled new resell notifications.
* Adjust some hooks so 3rd-party plugins can properly run their code.

= 1.1 =
* Add BuddyPress 1.5 compatibility.
* Add WP Admin Bar support.
* Add localization support.
* Add AJAX functionality to all resell buttons.
* Add resell button to group members page.
* Fix reselling count when a user is deleted.
* Fix dropdown activity filter for reselling tabs.
* Fix member profile reselling pagination
* Fix BuddyBar issues when a logged-in user is on another member's page.
* Thanks to mrjarbenne for sponsoring this release.

= 1.0 =
* Initial release.
