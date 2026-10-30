# WP-Simple-Object-Cache
Object cache plugin that implements Redis (via php-redis extension) or Database Abstraction Layer with LMDB database handler (via php-dba extension) based on your Wordpress site's hosting environment. 

## Supported Object Cache Backends
* Redis (via php-redis extension)
  * Use in dedicated hosting environments where a Redis server can be installed and managed.
  * Requires php-redis extension to be enabled.
  * Supports bulk reads implementing an "alloptions" cache override.
* Database Abstraction Layer with LMDB database handler (via php-dba extension)
  * Use in shared hosting environments.
  * Requires PHP 7.2.0+.
  * Requires php-dba extension to be enabled.
  * Doesn't support bulk reads.

## Additional Caching Features
* Frontend caching for widgets.
* Frontend caching for navigation menus.

## Configuration and settings are documented in the "Plugins > Drop-ins" area of the WP Admin when the plugin is active.
