=== Easy Installer ===
Contributors: stingray82, reallyusefulplugins.com
Tags: plugins, installer, download
Requires at least: 6.3
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple plugin to download and activate WordPress plugins from a URL, including handling redirects.

== Description ==

Easy Installer simplifies the process of installing plugins by allowing you to paste a URL to a plugin ZIP file. The plugin will download, extract, and activate the plugin automatically.

Key Features:
* Install plugins from any valid ZIP file URL.
* Automatically handles redirecting URLs.
* Easy-to-use admin interface.

This plugin is ideal for developers or site admins who need a quick way to install plugins from custom sources.

== Installation ==

1. Download the plugin ZIP file.
2. Upload the `easy-installer` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Navigate to "Plugin Installer" in the WordPress admin menu and enter the plugin URL.

== Frequently Asked Questions ==

= Does it support protected URLs? =
Yes, if the authentication information (e.g., API key) is included in the URL.

= Is it safe to use this plugin? =
Ensure that the URL you provide points to a trusted source. The plugin does not validate the content of the ZIP file.

= What happens if the plugin cannot determine the main file? =
If the plugin fails to identify the main plugin file, it will show an error message and not activate the plugin.

== Screenshots ==

1. The interface for entering a plugin URL.
2. A successful plugin installation confirmation.

== Changelog ==

= 1.2 =
* Handle redirected URLs gracefully.
* Improved error handling for plugin installation.

= 1.1 =
* Added support for token-protected URLs.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.2 =
Support for redirect URLs and improved error handling.

== License ==

This plugin is licensed under the GPLv2 or later. For details, see [License URI](https://www.gnu.org/licenses/gpl-2.0.html).
