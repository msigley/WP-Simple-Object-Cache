<?php
$object_cache_base_path = dirname(__FILE__);

if ( isset( $memcached_servers ) ) { // Use Memcached
	require_once( $object_cache_base_path . '/memcached/object-cache.php' );
} elseif ( function_exists( 'apc_add' ) ) { // USE APC Cache
	require_once( $object_cache_base_path . '/apc/object-cache.php' );
} elseif ( function_exists( 'xcache_get' ) ) { // Use XCACHE
	require_once( $object_cache_base_path . '/xcache/object-cache.php' );
} elseif ( class_exists( 'Redis' ) ) { // Use Redis
	require_once( $object_cache_base_path . '/redis/object-cache.php' );
} elseif ( function_exists( 'wincache_ucache_get' ) ) { // Use WinCache
	require_once( $object_cache_base_path . '/wincache/object-cache.php' );
} else { // No Cache
	require_once( ABSPATH . WPINC . '/cache.php' );
}