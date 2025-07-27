=== Signalfire Reading Estimator ===
Contributors: signalfire
Donate link: https://signalfire.co.uk
Tags: reading time, estimate, posts, content, analytics
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Calculates and displays estimated reading time for posts with configurable speed and flexible display options.

== Description ==

Signalfire Reading Estimator automatically calculates and displays the estimated reading time for your posts and pages. This helps visitors gauge how long content will take to read before they begin.

= Key Features =

* Automatic reading time calculation based on configurable words per minute
* Flexible display options (top or bottom of content)
* Support for multiple post types
* Customizable display text with placeholder support
* Shortcode support for manual placement
* Clean, responsive styling
* Translation ready

= Customization Options =

* Set custom reading speed (words per minute)
* Choose which post types to display reading time on
* Position reading time at top or bottom of content
* Customize the display text format
* Enable/disable automatic display

= Shortcode Usage =

Use `[reading_time]` to display reading time for the current post, or `[reading_time post_id="123"]` for a specific post.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/signalfire-reading-estimator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->Reading Estimator screen to configure the plugin.

== Frequently Asked Questions ==

= How is reading time calculated? =

Reading time is calculated by counting the words in your content and dividing by the configured words per minute (default: 200 WPM). The result is rounded up to ensure a minimum of 1 minute.

= Can I customize the display text? =

Yes! Go to Settings -> Reading Estimator and modify the "Display Text" field. Use %s as a placeholder for the calculated time value.

= Which post types are supported? =

By default, the plugin works with posts. You can configure it to work with pages, custom post types, or any combination through the settings page.

= Can I place the reading time manually? =

Yes, use the `[reading_time]` shortcode to place reading time anywhere in your content or theme files.

= Does this work with page builders? =

Yes, the plugin works with most page builders and themes since it hooks into WordPress's standard content filters.

== Screenshots ==

1. Reading time displayed at the top of a post
2. Plugin settings page with all configuration options
3. Shortcode usage example in the WordPress editor

== Changelog ==

= 1.0.0 =
* Initial release
* Automatic reading time calculation and display
* Configurable settings for speed, position, and post types
* Shortcode support
* Translation ready
* Clean styling with responsive design

== Upgrade Notice ==

= 1.0.0 =
Initial release of Signalfire Reading Estimator.