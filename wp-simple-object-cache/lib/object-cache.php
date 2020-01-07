<?php
/**
 * Load which ever type of object cache is appropriate for the system.
 *
 * @author Peter Chester
 * @copyright Modern Tribe, Inc.
 */

$tribe_object_cache_base_path =  WP_CONTENT_DIR . '/plugins/wp-simple-object-cache/lib';

// Use Memcached
if ( isset( $memcached_servers ) ) {
	require_once( $tribe_object_cache_base_path . '/memcached/object-cache.php' );
}

// USE APC Cache
elseif ( function_exists( 'apc_add' ) ) {
	require_once( $tribe_object_cache_base_path . '/apc/object-cache.php' );
}

// Use XCACHE
elseif ( function_exists( 'xcache_get' ) ) {
	require_once( $tribe_object_cache_base_path . '/xcache/object-cache.php' );
}

// Use WinCache
elseif ( function_exists( 'wincache_ucache_get' ) ) {
	require_once( $tribe_object_cache_base_path . '/wincache/object-cache.php' );
}

// No Cache
else {
	require_once( ABSPATH . WPINC . '/cache.php' );
}

?>