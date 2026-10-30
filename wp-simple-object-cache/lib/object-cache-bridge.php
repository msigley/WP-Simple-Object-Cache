<?php
$object_cache_base_path = dirname(__FILE__);
$preferred_object_cache = defined( 'WP_OBJECT_CACHE' ) ? WP_OBJECT_CACHE : false;

if( $preferred_object_cache === false ) {
	if ( class_exists( 'Redis' ) )
		$preferred_object_cache = 'REDIS';
	else if( version_compare( PHP_VERSION, '7.2.0', '>=' ) && function_exists( 'dba_open' ) )
		$preferred_object_cache = 'PHPLMDB';
}

switch( $preferred_object_cache ) {
	case 'REDIS':
		require_once( $object_cache_base_path . '/redis/object-cache.php' );
		break;
	case 'PHPLMDB':
		require_once( $object_cache_base_path . '/php-dba-lmdb/object-cache.php' );
		break;
	default:
		// No cache
		require_once( ABSPATH . WPINC . '/cache.php' );
		break;
}