=== WinCache Object Cache Backend ===
Contributors: ruslany
Donate link: http://ruslany.net/
Tags: WinCache, object cache, backend, cache, performance, speed
Requires at least: 2.9.2
Tested up to: 3.1.2
Stable tag: 1.1

WinCache Object Cache provides a persistent memory-based backend for the WordPress object cache. WinCache must be available on your PHP install.

== Description ==

This plugin is based on the code written by markjaquith for APC Object Cache Backend (http://wordpress.org/extend/plugins/apc/).

WinCache Object Cache provides a persistent memory-based backend for the WordPress object cache. WinCache must be available on your PHP install.

An object cache is a place for WordPress and WordPress extensions to store the results of complex operations. On subsequent loads, 
this data can be fetched from the cache, which will be must faster than dynamically generating it on every page load.

Be sure to read the installation instructions, as this is **not** a traditional plugin, and needs to be installed in a specific location.

== Installation ==

1. Verify that you have PHP 5.2+ and a compatible WinCache version installed.
2. Copy `object-cache.php` to your WordPress content directory (`wp-content/` by default).
3. Done!

== Frequently Asked Questions ==

= What is WinCache? =

WinCache is a Windows-optimized extension for PHP which significantly improves performance of PHP applications by using byte code, file and object caches. More information about using WinCache can be found in PHP documentation: http://www.php.net/wincache.

= Where can I download latest version of WinCache from? =

WinCache extension for PHP can be downloaded from http://www.iis.net/download/WinCacheForPhp.

= I share `wp-config.php` among multiple WordPress installs. How can I guarantee key uniqueness? =

Define `WP_APC_KEY_SALT` to something that is unique for each install (like an md5 of the MySQL host, database, and table prefix).

== Changelog ==

= 1.1 =
* Fixed a typo bug when calling Flush() function

= 1.0 =
* First version in SVN based on markjaquith's APC Object Cache plugin.