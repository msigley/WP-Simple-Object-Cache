=== XCache Object Cache Backend ===
Contributors: pierreschmitz
Donate link: https://pierre-schmitz.com
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.3.0
Tested up to: 3.4
Tags: xcache, backend, cache, object cache, batcache, performance, speed

An object-cache implementation using the XCache extension.

== Description ==

Using this Plugin WordPress is able to store certain regular used elements into a persistent cache. Instead of computing complex operations over and over again on every single page request, its result is stored in memory once and from then on fetched directly from the cache on future page requests.

Such an object cache will reduce access to your database and speed up page loading.

This implementation uses [XCache](http://xcache.lighttpd.net/)'s variable cache as backend.

== Installation ==

1. You need to install and configure the [XCache PHP extension](http://xcache.lighttpd.net/).
1. Make sure to set a size for the xcache.var_size directive (e.g. `xcache.var_size=64M`)
1. Copy the file object-cache.php of this Plugin into your `/wp-content/` directory. Note that this file needs to be stored directly into your content directory and not under the plugins directory.

== Changelog ==

= 1.0.3 =
* Compatibility with XCache < 1.3 which does not have the xcache_unset_by_prefix function

= 1.0.2 =
* Compatibility with WordPress 3.4 API

= 1.0.1 =
* Compatibility with [Batcache](http://wordpress.org/extend/plugins/batcache/)

= 1.0.0 =
* initial version
