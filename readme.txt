=== Page Protection ===
Contributors: mortenf
Donate link: http://www.mfd-consult.dk/paypal/
Tags: security, privacy, access, protection
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: trunk

Protect pages and their subpages with user name/password, and keep protected pages from showing up in menus, search results and page lists.

== Description ==

This plugin adds optional per-page user name and password protection, implemented using standard HTTP protocol authorization headers, thus triggering the standard user/password dialog of the browser, and making it possible to make the browser store the credentials.

Subpages of a protected page are protected with the same user name and password as their parent. 

Protected pages and their subpages do not show up in menus, search results and page lists.

= Usage =

1. When editing a page, locate the section titled "Page Protection" (probably located near the bottom of the right sidebar).
1. Check the box "Protect page and subpages", and provide a user name and password combination.
1. Optionally, check the box "Make page and subpages searchable", if you want the page and its subpages turn up in search results, but only with their title, not their content.
1. Save your page.

== Installation ==

1. Download plugin .zip-file.
1. Unzip and upload to the plugin directory, usually at `wp-content/plugins/`.
1. Activate the plugin from the WordPress "Plugin" administration screen.

See also the [usage instructions](http://wordpress.org/extend/plugins/page-protection/).

== Screenshots ==

1. Page edit control

== Frequently Asked Questions ==

= Is it secure? =

Not really, no. It depends on WordPress working as intended, and the user name and password is sent in clear text across the wire.

= Can I protect posts? =

No, only pages and their subpages can be protected by this plugin.

= My protected page shows up in the page list? =

Yes, that's actually considered a feature (at least for now). The point being, that a hidden page really doesn't need to be protected, and that it makes it work cleaner for e.g. members-only pages. Don't worry, as you have probably discovered, the contents of the page are not visible without the correct user name and password.

= Why doesn't it work for me? =

You might be running WordPress on PHP using CGI, and that often makes it impossible to handle the authorization features of HTTP - the appropriate headers simply don't get passed on to PHP/WordPress by the web server.

If you wan't to make sure that's the problem, try creating a protected post with a user name and password of debug/debug.

= I have translated the plugin into another language, now what? =

Great, thanks! Please do leave a comment on the plugin's homepage
[www.mfd-consult.dk/page-protection](http://www.mfd-consult.dk/page-protection/) or send an e-mail with details; I'll
make sure it's included in the next version.

= Another question? =

If your question isn't answered here, please do leave a comment in the forum or on the plugin's homepage:
[www.mfd-consult.dk/page-protection](http://www.mfd-consult.dk/page-protection/)

== Changelog ==

= 1.2 =
* Added debugging functionality.

= 1.1 =
* Added Danish translation.
* Prepared for translations.

= 1.0 =
* Initial release.

== License ==

Copyright (c) 2009 Morten Høybye Frederiksen <morten@wasab.dk>

Permission to use, copy, modify, and distribute this software for any
purpose with or without fee is hereby granted, provided that the above
copyright notice and this permission notice appear in all copies.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
