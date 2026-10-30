<?php
/**
 * Plugin Name: WP Simple PHP DBA LMDB Object Cache
 * Author:      Matthew Sigley
 * Version:     1.0.0
 */

if( !is_dir( WP_CONTENT_DIR . '/object-cache/' ) ) {
	@mkdir( WP_CONTENT_DIR . '/object-cache/', 0655 );
}

/**
 * Determines whether the object cache implementation supports a particular feature.
 *
 * @param string $feature Name of the feature to check for. Possible values include:
 *                        'add_multiple', 'set_multiple', 'get_multiple', 'delete_multiple',
 *                        'flush_runtime', 'flush_group'.
 * @return bool True if the feature is supported, false otherwise.
 */
function wp_cache_supports( $feature ) {
    switch ( $feature ) {
		case 'add_multiple':
		case 'set_multiple':
		case 'get_multiple':
		case 'delete_multiple':
		case 'flush_runtime':
        case 'flush_group':
            return true;

        default:
            return false;
    }
}

/**
 * Adds a value to cache.
 *
 * If the specified key already exists, the value is not stored and the function
 * returns false.
 *
 * @param string $key        The key under which to store the value.
 * @param mixed  $value      The value to store.
 * @param string $group      The group value appended to the $key.
 * @param int    $expiration The expiration time, defaults to 0.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool              Returns TRUE on success or FALSE on failure.
 */
function wp_cache_add( $key, $value, $group = '', $expiration = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->add( $key, $value, $group, $expiration );
}

/**
 * Adds multiple values to the cache in one call.
 *
 * @since 6.0.0
 *
 * @see WP_Object_Cache::add_multiple()
 * @global WP_Object_Cache $wp_object_cache Object cache global instance.
 *
 * @param array  $data   Array of keys and values to be set.
 * @param string $group  Optional. Where the cache contents are grouped. Default empty.
 * @param int    $expire Optional. When to expire the cache contents, in seconds.
 *                       Default 0 (no expiration).
 * @return bool[] Array of return values, grouped by key. Each value is either
 *                true on success, or false if cache key and group already exist.
 */
function wp_cache_add_multiple( array $data, $group = '', $expire = 0 ) {
	global $wp_object_cache;

	return $wp_object_cache->add_multiple( $data, $group, (int) $expire );
}

/**
 * Closes the cache. Runs as a PHP shutdown handler.
 *
 * @return  bool    Always returns True
 */
function wp_cache_close() {
	global $wp_object_cache;
	return $wp_object_cache->close();
}

/**
 * Decrement a numeric item's value.
 *
 * @param string $key    The key under which to store the value.
 * @param int    $offset The amount by which to decrement the item's value.
 * @param string $group  The group value appended to the $key.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return int|bool      Returns item's new value on success or FALSE on failure.
 */
function wp_cache_decr( $key, $offset = 1, $group = '' ) {
	global $wp_object_cache;
	return $wp_object_cache->decr( $key, $offset, $group );
}

/**
 * Remove the item from the cache.
 *
 * @param string $key    The key under which to store the value.
 * @param string $group  The group value appended to the $key.
 * @param int    $time   The amount of time the server will wait to delete the item in seconds.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool           Returns TRUE on success or FALSE on failure.
 */
function wp_cache_delete( $key, $group = '', $time = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->delete( $key, $group, $time );
}

/**
 * Deletes multiple values from the cache in one call.
 *
 * @since 6.0.0
 *
 * @see WP_Object_Cache::delete_multiple()
 * @global WP_Object_Cache $wp_object_cache Object cache global instance.
 *
 * @param array  $keys  Array of keys under which the cache to deleted.
 * @param string $group Optional. Where the cache contents are grouped. Default empty.
 * @return bool[] Array of return values, grouped by key. Each value is either
 *                true on success, or false if the contents were not deleted.
 */
function wp_cache_delete_multiple( array $keys, $group = '' ) {
	global $wp_object_cache;

	return $wp_object_cache->delete_multiple( $keys, $group );
}

/**
 * Invalidate all items in the cache.
 *
 * @param int $delay  Number of seconds to wait before invalidating the items.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool             Returns TRUE on success or FALSE on failure.
 */
function wp_cache_flush() {
	global $wp_object_cache;
	return $wp_object_cache->flush();
}

/**
 * Removes all cache items from the in-memory runtime cache.
 *
 * @since 6.0.0
 *
 * @see WP_Object_Cache::flush()
 *
 * @return bool True on success, false on failure.
 */
function wp_cache_flush_runtime() {
	global $wp_object_cache;
	return $wp_object_cache->flush_runtime();
}

/**
 * Removes cache contents for a given group.
 *
 * @param string $group Where the cache contents are grouped
 * 
 * @global WP_Object_Cache $wp_object_cache
 * 
 * @return bool True on successful removal, false on failure
 */
function wp_cache_flush_group( $group ) {
	global $wp_object_cache;
	return $wp_object_cache->flush_group( $group );
}

/**
 * Retrieve object from cache.
 *
 * Gets an object from cache based on $key and $group.
 *
 * @param string      $key        The key under which to store the value.
 * @param string      $group      The group value appended to the $key.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool|mixed             Cached object value.
 */
function wp_cache_get( $key, $group = '', $force = false, &$found = null ) {
	global $wp_object_cache;

	return $wp_object_cache->get( $key, $group, $force, $found );
}

/**
 * Gets multiple values from cache in one call.
 *
 * @since 5.5.0
 * @see WP_Object_Cache::get_multiple()
 *
 * @param array       $keys   Array of keys to get from group.
 * @param string      $group  Optional. Where the cache contents are grouped. Default empty.
 * @param bool        $force  Optional. Whether to force an update of the local cache from the persistent
 *                            cache. Default false.
 * @return array|bool Array of values.
 */
function wp_cache_get_multiple( $keys, $group = '', $force = false ) {
	global $wp_object_cache;

	return $wp_object_cache->get_multiple( $keys, $group, $force );
}

/**
 * Increment a numeric item's value.
 *
 * @param string $key    The key under which to store the value.
 * @param int    $offset The amount by which to increment the item's value.
 * @param string $group  The group value appended to the $key.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return int|bool      Returns item's new value on success or FALSE on failure.
 */
function wp_cache_incr( $key, $offset = 1, $group = '' ) {
	global $wp_object_cache;
	return $wp_object_cache->incr( $key, $offset, $group );
}

/**
 * Sets up Object Cache Global and assigns it.
 *
 * @global  WP_Object_Cache $wp_object_cache    WordPress Object Cache
 *
 * @return  void
 */
function wp_cache_init() {
	global $wp_object_cache;
	$wp_object_cache = new WP_Object_Cache();
}

/**
 * Replaces a value in cache.
 *
 * This method is similar to "add"; however, is does not successfully set a value if
 * the object's key is not already set in cache.
 *
 * @param string $key        The key under which to store the value.
 * @param mixed  $value      The value to store.
 * @param string $group      The group value appended to the $key.
 * @param int    $expiration The expiration time, defaults to 0.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool              Returns TRUE on success or FALSE on failure.
 */
function wp_cache_replace( $key, $value, $group = '', $expiration = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->replace( $key, $value, $group, $expiration );
}

/**
 * Sets a value in cache.
 *
 * @param string $key        The key under which to store the value.
 * @param mixed  $value      The value to store.
 * @param string $group      The group value appended to the $key.
 * @param int    $expiration The expiration time, defaults to 0.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool              Returns TRUE on success or FALSE on failure.
 */
function wp_cache_set( $key, $value, $group = '', $expiration = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->set( $key, $value, $group, $expiration );
}

/**
 * Sets multiple values to the cache in one call.
 *
 * @since 6.0.0
 *
 * @see WP_Object_Cache::set_multiple()
 * @global WP_Object_Cache $wp_object_cache Object cache global instance.
 *
 * @param array  $data   Array of keys and values to be set.
 * @param string $group  Optional. Where the cache contents are grouped. Default empty.
 * @param int    $expire Optional. When to expire the cache contents, in seconds.
 *                       Default 0 (no expiration).
 * @return bool[] Array of return values, grouped by key. Each value is either
 *                true on success, or false on failure.
 */
function wp_cache_set_multiple( array $data, $group = '', $expire = 0 ) {
	global $wp_object_cache;

	return $wp_object_cache->set_multiple( $data, $group, $expire );
}

/**
 * Switch the interal blog id.
 *
 * This changes the blog id used to create keys in blog specific groups.
 *
 * @param  int $_blog_id Blog ID
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return bool
 */
function wp_cache_switch_to_blog( $_blog_id ) {
	global $wp_object_cache;
	return $wp_object_cache->switch_to_blog( $_blog_id );
}

/**
 * Adds a group or set of groups to the list of DBA groups.
 *
 * @param   string|array $groups     A group or an array of groups to add.
 *
 * @global WP_Object_Cache $wp_object_cache
 *
 * @return  void
 */
function wp_cache_add_global_groups( $groups ) {
	global $wp_object_cache;
	$wp_object_cache->add_global_groups( $groups );
}

/**
 * Adds a group or set of groups to the list of non-persistent groups.
 *
 * @since 2.6.0
 *
 * @param string|array $groups A group or an array of groups to add.
 */
function wp_cache_add_non_persistent_groups( $groups ) {
	global $wp_object_cache;
	$wp_object_cache->add_non_persistent_groups( $groups );
}

class WP_Object_Cache {
	// Core properties from /wp-includes/class-wp-object-cache.php
	/**
	 * Holds the cached objects.
	 *
	 * @since 2.0.0
	 * @var array
	 */
	public $cache = array();

	/**
	 * The amount of times the cache data was already stored in the memory cache.
	 *
	 * @since 2.5.0
	 * @var int
	 */
	public $cache_hits = 0;

	/**
	 * Amount of times the cache did not have the request in the memory cache.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	public $cache_misses = 0;

	/**
	 * The amount of times the cache data was already stored in the external cache.
	 *
	 * @since 2.5.0
	 * @var int
	 */
	public $external_cache_hits = 0;

	/**
	 * Amount of times the cache did not have the request in the external cache.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	public $external_cache_misses = 0;

	/**
	 * List of global cache groups.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	protected $global_groups = array();

	/**
	 * The blog prefix to prepend to keys in non-global groups.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	private $blog_prefix;

	/**
	 * Holds the value of is_multisite().
	 *
	 * @since 3.5.0
	 * @var bool
	 */
	private $multisite;

	/**
	 * List of global cache groups.
	 *
	 * @since 3.0.0
	 * @var array
	 */
	protected $non_persistent_groups = array();

	// Implementation specific properties
	/**
	 * Number of times a cache client has connected this request
	 */
	private static $num_instances = 0;

	/**
	 * Holds the DBA Connection.
	 */
	private static $dba = null;

	/**
	 * Track if DBA is available.
	 *
	 * @var bool
	 */
	private static $dba_connected = false;

	// The marker characters below work because compressed text will always start with \78 and these characters never appear in normal text.
	/**
	 * Character used at the beginning of strings to mark the string is compressed.
	 * SOT is our compression marker. This character is never used in actual text.
	 * 
	 * @var char
	 */
	private $compression_marker = "\2";

	/**
	 * Character used at the beginning of strings to mark the value expires.
	 * EOT is our expiration marker. This character is never used in actual text.
	 * 
	 * @var char
	 */
	private $expiration_marker = "\3";

	public function __construct() {
		// Check if php-dba extension is enabled and the LMDB hander is available (PHP > 7.2.0).
		if ( function_exists( 'dba_open' ) && version_compare( PHP_VERSION, '7.2.0', '>=' ) ) {
			if( !self::$dba_connected || empty( self::$dba ) ) {
				try {
					self::$dba = dba_open( WP_CONTENT_DIR . '/object-cache/object-cache.lmdb', 'c', 'lmdb', 0644, 0, null );
					if( false === self::$dba )
						throw new Exception("Failed to initialize lmdb database handler.");

					self::$dba_connected = true;
				} catch ( Exception $e ) {
					$this->close();
				}
			}
		}

		$this->multisite = is_multisite();
		$this->blog_prefix = $this->multisite ? get_current_blog_id() . ':' : '';

		self::$num_instances++;
	}

	/**
	 * Is DBA available and is this group persistent?
	 *
	 * @return bool
	 */
	public function can_dba( $group = '' ) {
		if( !empty( $group ) && isset( $this->non_persistent_groups[ $group ] ) )
			return false;

		return self::$dba_connected;
	}


	/**
	 * Wrapper for dba_replace().
	 * Handle object serialization and value compression.
	 *
	 * @return bool
	 */
	public function set_dba( $key, $value, $expire = 0 ) {
		$value = @serialize( $value );
		$compressed_value = @gzcompress( $value, 1, FORCE_DEFLATE );
		if( false !== $compressed_value )
			$value = "$this->compression_marker$compressed_value";
		if( $expire > 0 )
			$value = "$this->expiration_marker" . ( (int) ( time() + $expire ) ) . "$this->expiration_marker$value";
		
		dba_replace( $key, $value, self::$dba );
	}

	/**
	 * Wrapper for dba_fetch().
	 * Handle object unserialization, value decompression, and value expiration.
	 *
	 * @return bool
	 */
	public function get_dba( $key ) {
		$value = dba_fetch( $key, self::$dba );
		if( is_string( $value ) && substr( $value, 0, 1 ) == $this->expiration_marker ) {
			$expiration_marker_pos = strpos( $value, $this->expiration_marker, 1 );
			$expiration = (int) substr( $value, 1, $expiration_marker_pos - 1 );
			if( $expiration > time() ) {
				$value = substr( $value, $expiration_marker_pos + 1 );
			} else {
				return false; // Treat expired keys as if they do not exist.
			}
		} else {
			// Values missing an expiration marker do not expire
		}

		return $this->decompress( $value );
	}

	/**
	 * Wrapper for dba_delete().
	 * Handle object unserialization, value decompression, and value expiration.
	 *
	 * @return bool
	 */
	public function del_dba( ...$keys ) {
		$return = true;
		foreach( $keys as &$key ){
			$return &= dba_delete( $key, self::$dba );
		} unset( $key );
		return $return;
	}

	public function keys_dba( $key_prefix ) {
		$keys = array();
		$key = dba_firstkey( self::$dba );
		while( $key !== false ) {
			if( strpos( $key, $prefix ) === 0 )
				$keys[] = $key;
			$key = dba_nextkey( self::$dba );
		}
		return $keys;
	}

	public function flush_dba() {
		$keys = array();
		$key = dba_firstkey( self::$dba );
		while( $key !== false ) {
			$keys[] = $key;
			$key = dba_nextkey( self::$dba );
		}
		return $this->del_dba( ...$keys );
	}

	private function ttl( $key ) {
		$value = dba_fetch( $key, self::$dba );
		$expiration_marker_pos = strrpos( $value, $this->expiration_marker );
		if( !is_string( $value ) || false === $expiration_marker_pos )
			return -1; // No expiration

		$expiration = (int) substr( $value, $expiration_marker_pos );
		$ttl = time() - $expiration;
		if( $ttl < 1 )
			return 0;

		return $ttl;
	}

	private function decompress( $value ) {
		$decompressed_value = @gzuncompress( substr( $value, 1 ) );
		if( false !== $decompressed_value )
			$value = $decompressed_value;
		return @unserialize( $value );
	}

	/**
	 * Makes private properties readable for backward compatibility.
	 *
	 * @since 4.0.0
	 *
	 * @param string $name Property to get.
	 * @return mixed Property.
	 */
	public function __get( $name ) {
		return $this->$name;
	}

	/**
	 * Makes private properties settable for backward compatibility.
	 *
	 * @since 4.0.0
	 *
	 * @param string $name  Property to set.
	 * @param mixed  $value Property value.
	 * @return mixed Newly-set property.
	 */
	public function __set( $name, $value ) {
		return $this->$name = $value;
	}

	/**
	 * Makes private properties checkable for backward compatibility.
	 *
	 * @since 4.0.0
	 *
	 * @param string $name Property to check if set.
	 * @return bool Whether the property is set.
	 */
	public function __isset( $name ) {
		return isset( $this->$name );
	}

	/**
	 * Makes private properties un-settable for backward compatibility.
	 *
	 * @since 4.0.0
	 *
	 * @param string $name Property to unset.
	 */
	public function __unset( $name ) {
		unset( $this->$name );
	}

	/**
	 * Adds data to the cache if it doesn't already exist.
	 *
	 * @since 2.0.0
	 *
	 * @uses WP_Object_Cache::_exists() Checks to see if the cache already has data.
	 * @uses WP_Object_Cache::set()     Sets the data after the checking the cache
	 *                                  contents existence.
	 *
	 * @param int|string $key    What to call the contents in the cache.
	 * @param mixed      $data   The contents to store in the cache.
	 * @param string     $group  Optional. Where to group the cache contents. Default 'default'.
	 * @param int        $expire Optional. When to expire the cache contents. Default 0 (no expiration).
	 * @return bool True on success, false if cache key and group already exist.
	 */
	public function add( $key, $data, $group = 'default', $expire = 0 ) {
		if ( wp_suspend_cache_addition() ) {
			return false;
		}

		if ( empty( $group ) ) {
			$group = 'default';
		}

		$id = $key;
		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) ) {
			$id = $this->blog_prefix . $key;
		}

		if ( $this->_exists( $id, $group ) ) {
			return false;
		}

		return $this->set( $key, $data, $group, (int) $expire );
	}

	/**
	 * Adds multiple values to the cache in one call.
	 *
	 * @since 6.0.0
	 *
	 * @param array  $data   Array of keys and values to be added.
	 * @param string $group  Optional. Where the cache contents are grouped. Default empty.
	 * @param int    $expire Optional. When to expire the cache contents, in seconds.
	 *                       Default 0 (no expiration).
	 * @return bool[] Array of return values, grouped by key. Each value is either
	 *                true on success, or false if cache key and group already exist.
	 */
	public function add_multiple( array $data, $group = '', $expire = 0 ) {
		$values = array();

		foreach ( $data as $key => $value ) {
			$values[ $key ] = $this->add( $key, $value, $group, $expire );
		}

		return $values;
	}

	/**
	 * Sets the list of global cache groups.
	 *
	 * @since 3.0.0
	 *
	 * @param array $groups List of groups that are global.
	 */
	public function add_global_groups( $groups ) {
		$groups = (array) $groups;

		$groups              = array_fill_keys( $groups, true );
		$this->global_groups = array_merge( $this->global_groups, $groups );
	}

	/**
	 * Sets the list of global cache groups.
	 *
	 * @param array $groups List of groups that are global.
	 */
	public function add_non_persistent_groups( $groups ) {
		$groups = (array) $groups;

		$groups              = array_fill_keys( $groups, true );
		$this->non_persistent_groups = array_merge( $this->non_persistent_groups, $groups );
	}

	/**
	 * Wrapper for dba_close().
	 *
	 * @return bool
	 */
	public function close() {
		if( !empty( self::$dba ) ) {
			dba_close( self::$dba );
			self::$dba = null;
		}
		self::$dba_connected = false;
	}

	/**
	 * Decrements numeric cache item's value.
	 *
	 * @since 3.3.0
	 *
	 * @param int|string $key    The cache key to decrement.
	 * @param int        $offset Optional. The amount by which to decrement the item's value. Default 1.
	 * @param string     $group  Optional. The group the key is in. Default 'default'.
	 * @return int|false The item's new value on success, false on failure.
	 */
	public function decr( $key, $offset = 1, $group = 'default' ) {
		return $this->incr( $key, $offset * -1, $group );
	}

	/**
	 * Removes the contents of the cache key in the group.
	 *
	 * If the cache key does not exist in the group, then nothing will happen.
	 *
	 * @since 2.0.0
	 *
	 * @param int|string $key        What the contents in the cache are called.
	 * @param string     $group      Optional. Where the cache contents are grouped. Default 'default'.
	 * @param bool       $deprecated Optional. Unused. Default false.
	 * @return bool False if the contents weren't deleted and true on success.
	 */
	public function delete( $key, $group = 'default', $deprecated = false ) {
		if ( empty( $group ) ) {
			$group = 'default';
		}

		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) ) {
			$key = $this->blog_prefix . $key;
		}

		if ( ! $this->_exists( $key, $group ) ) {
			return false;
		}

		unset( $this->cache[ $group ][ $key ] );

		if( $this->can_dba( $group ) )
			return (bool) $this->del_dba( "$group:$key" );
		return true;
	}

	/**
	 * Deletes multiple values from the cache in one call.
	 *
	 * @since 6.0.0
	 *
	 * @param array  $keys  Array of keys to be deleted.
	 * @param string $group Optional. Where the cache contents are grouped. Default empty.
	 * @return bool[] Array of return values, grouped by key. Each value is either
	 *                true on success, or false if the contents were not deleted.
	 */
	public function delete_multiple( array $keys, $group = '' ) {
		$values = array();

		foreach ( $keys as $key ) {
			$values[ $key ] = $this->delete( $key, $group );
		}

		return $values;
	}

	/**
	 * Remove the contents of all cache keys in the group.
	 *
	 * @param string $group Where the cache contents are grouped.
	 * @return boolean True on success, false on failure.
	 */
	public function flush_group( $group = 'default' ) {
		if ( empty( $group ) ) {
			$group = 'default';
		}

		$key_prefix = '';
		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) ) {
			$key_prefix = $this->blog_prefix;
			$len_blog_prefix = strlen( $this->blog_prefix );
			foreach( $this->cache[ $group ] as $key => &$value ) { // &$value prevents looping over copy of array saving memory
				if( $this->blog_prefix === substr( $key, 0, $len_blog_prefix ) )
					unset( $this->cache[ $group ][ $key ] );
			}
			unset( $value );
		} else {
			unset( $this->cache[ $group ] );
		}

		if( $this->can_dba( $group ) ) {
			$keys = $this->keys_dba( "$group:$key_prefix*" );
			if( empty( $keys ) )
				return false;
			return (bool) $this->del_dba( ...$keys );
		}
		return true;
	}

	/**
	 * Clears the object cache of all data.
	 *
	 * @since 2.0.0
	 *
	 * @return true Always returns true.
	 */
	public function flush() {
		$this->cache = array();

		if ( $this->can_dba() )
			$this->flush_dba();

		return true;
	}

	/**
	 * Clears the in-memory cache of all data leaving the external cache untouched.
	 *
	 * @return bool Always returns true.
	 */
	public function flush_runtime() {
		$this->cache = array();

		return true;
	}

	
	/**
	 * Retrieves the cache contents, if it exists.
	 *
	 * The contents will be first attempted to be retrieved by searching by the
	 * key in the cache group. If the cache is hit (success) then the contents
	 * are returned.
	 *
	 * On failure, the number of cache misses will be incremented.
	 *
	 * @since 2.0.0
	 *
	 * @param int|string $key    What the contents in the cache are called.
	 * @param string     $group  Optional. Where the cache contents are grouped. Default 'default'.
	 * @param bool       $force  Optional. Unused. Whether to force a refetch rather than relying on the local
	 *                           cache. Default false.
	 * @param bool       $found  Optional. Whether the key was found in the cache (passed by reference).
	 *                           Disambiguates a return of false, a storable value. Default null.
	 * @return mixed|false The cache contents on success, false on failure to retrieve contents.
	 */
	public function get( $key, $group = 'default', $force = false, &$found = null ) {
		$external_cache_queried = false;
		
		if ( empty( $group ) ) {
			$group = 'default';
		}

		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) ) {
			$key = $this->blog_prefix . $key;
		}

		if ( $this->_exists( $key, $group ) ) {
			if ( ( $force || !isset( $this->cache[ $group ][ $key ] ) ) && $this->can_dba( $group ) ) {
				$external_cache_queried = true;

				$this->cache[ $group ][ $key ] = $this->get_dba( "$group:$key" );
			}

			if ( isset( $this->cache[ $group ][ $key ] ) ) {
				$found = true;
				if( $external_cache_queried ) {
					$this->external_cache_hits += 1;
				} else {
					$this->cache_hits += 1;
				}

				if ( is_object( $this->cache[ $group ][ $key ] ) ) {
					return clone $this->cache[ $group ][ $key ];
				} else {
					return $this->cache[ $group ][ $key ];
				}
			}
		}

		$found = false;
		if( $external_cache_queried ) {
			$this->external_cache_misses += 1;
		} else {
			$this->cache_misses += 1;
		}
		return false;
	}

	/**
	 * Retrieves multiple values from the cache.
	 *
	 * @since  5.5.0
	 *
	 * @param array $keys        Array of keys to fetch.
	 * @param bool  $force       Optional. Unused. Whether to force a refetch rather than relying on the local
	 *                           cache. Default false.
	 *
	 * @return array Array of values organized into groups.
	 */
	public function get_multiple( $keys, $group = 'default', $force = false ) {
		$values = array();

		foreach ( $keys as $key )
			$values[ $key ] = $this->get( $key, $group, $force );

		return $values;
	}

	/**
	 * Increments numeric cache item's value.
	 *
	 * @since 3.3.0
	 *
	 * @param int|string $key    The cache key to increment
	 * @param int        $offset Optional. The amount by which to increment the item's value. Default 1.
	 * @param string     $group  Optional. The group the key is in. Default 'default'.
	 * @return int|false The item's new value on success, false on failure.
	 */
	public function incr( $key, $offset = 1, $group = 'default' ) {
		if ( empty( $group ) ) {
			$group = 'default';
		}

		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) ) {
			$key = $this->blog_prefix . $key;
		}

		if ( ! $this->_exists( $key, $group ) ) {
			return false;
		}

		if ( !isset( $this->cache[ $group ][ $key ] ) && $this->can_dba( $group ) )
			$this->cache[ $group ][ $key ] = $this->get_dba( "$group:$key" );

		if ( ! is_numeric( $this->cache[ $group ][ $key ] ) ) {
			$this->cache[ $group ][ $key ] = 0;
		}

		$offset = (int) $offset;

		$this->cache[ $group ][ $key ] += $offset;

		if ( $this->cache[ $group ][ $key ] < 0 ) {
			$this->cache[ $group ][ $key ] = 0;
		}

		if( $this->can_dba( $group ) ) {
			$ttl = $this->ttl( "$group:$key" );
			if( $ttl < 0 ) // -1 indicates the key has no ttl
				$this->set_dba( "$group:$key", $this->cache[ $group ][ $key ] );
			else if( $ttl == 0 )
				$this->delete( $key, $group );
			else
				$this->set_dba( "$group:$key", $this->cache[ $group ][ $key ], $ttl );
		}

		return $this->cache[ $group ][ $key ];
	}

	/**
	 * Replaces the contents in the cache, if contents already exist.
	 *
	 * @since 2.0.0
	 *
	 * @see WP_Object_Cache::set()
	 *
	 * @param int|string $key    What to call the contents in the cache.
	 * @param mixed      $data   The contents to store in the cache.
	 * @param string     $group  Optional. Where to group the cache contents. Default 'default'.
	 * @param int        $expire Optional. When to expire the cache contents. Default 0 (no expiration).
	 * @return bool False if not exists, true if contents were replaced.
	 */
	public function replace( $key, $data, $group = 'default', $expire = 0 ) {
		if ( empty( $group ) ) {
			$group = 'default';
		}

		$id = $key;
		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) ) {
			$id = $this->blog_prefix . $key;
		}

		if ( ! $this->_exists( $id, $group ) ) {
			return false;
		}

		return $this->set( $key, $data, $group, (int) $expire );
	}

	/**
	 * Sets the data contents into the cache.
	 *
	 * The cache contents are grouped by the $group parameter followed by the
	 * $key. This allows for duplicate ids in unique groups. Therefore, naming of
	 * the group should be used with care and should follow normal function
	 * naming guidelines outside of core WordPress usage.
	 *
	 * The $expire parameter is not used, because the cache will automatically
	 * expire for each time a page is accessed and PHP finishes. The method is
	 * more for cache plugins which use files.
	 *
	 * @since 2.0.0
	 *
	 * @param int|string $key    What to call the contents in the cache.
	 * @param mixed      $data   The contents to store in the cache.
	 * @param string     $group  Optional. Where to group the cache contents. Default 'default'.
	 * @param int        $expire Not Used.
	 * @return true Always returns true.
	 */
	public function set( $key, $data, $group = 'default', $expire = 0 ) {
		if ( empty( $group ) ) {
			$group = 'default';
		}

		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) ) {
			$key = $this->blog_prefix . $key;
		}


		if ( is_object( $data ) ) {
			$data = clone $data;
		}

		$this->cache[ $group ][ $key ] = $data;

		if( $this->can_dba( $group ) )
			$this->set_dba( "$group:$key", $this->cache[ $group ][ $key ], $expire );

		return true;
	}

	/**
	 * Sets multiple values to the cache in one call.
	 *
	 * @since 6.0.0
	 *
	 * @param array  $data   Array of key and value to be set.
	 * @param string $group  Optional. Where the cache contents are grouped. Default empty.
	 * @param int    $expire Optional. When to expire the cache contents, in seconds.
	 *                       Default 0 (no expiration).
	 * @return bool[] Array of return values, grouped by key. Each value is always true.
	 */
	public function set_multiple( array $data, $group = '', $expire = 0 ) {
		$values = array();

		foreach ( $data as $key => $value ) {
			$values[ $key ] = $this->set( $key, $value, $group, $expire );
		}

		return $values;
	}

	/**
	 * Echoes the stats of the caching.
	 *
	 * Gives the cache hits, and cache misses. Also prints every cached group,
	 * key and the data.
	 *
	 * @since 2.0.0
	 */
	public function stats() {
		echo '<p>';
		echo "<strong>In Memory Cache Hits:</strong> {$this->cache_hits}<br />";
		echo "<strong>In Memory Cache Misses:</strong> {$this->cache_misses}<br />";
		echo "<strong>LMDB Cache Hits:</strong> {$this->external_cache_hits}<br />";
		echo "<strong>LMDB Cache Misses:</strong> {$this->external_cache_misses}<br />";
		echo '</p>';
		echo '<ul>';
		foreach ( $this->cache as $group => $cache ) {
			echo "<li><strong>Group:</strong> $group - ( " . number_format( strlen( serialize( $cache ) ) / 1024, 2 ) . 'k )</li>';
		}
		echo '</ul>';
	}

	public function cache_stats() {
		echo '<p><strong>Using Database Abstraction Layer with LMDB database handler (via php-dba extension).</strong></p>';
		echo '<p>';
		if( self::$dba_connected ) {
			echo '<strong>Connected to database.</strong><br />';
		} else {
			echo '<strong>Unable to connect to database.</strong><br />';
		}
		echo 'Database files are stored at:<br />';
		echo '<code>' . WP_CONTENT_DIR . '/object-cache/' . '</code><br />';
		echo 'If the database is unable to connect, please ensure the folder above exists and is writable.';
		echo '</p>';
	}

	/**
	 * Switches the internal blog ID.
	 *
	 * This changes the blog ID used to create keys in blog specific groups.
	 *
	 * @since 3.5.0
	 *
	 * @param int $blog_id Blog ID.
	 */
	public function switch_to_blog( $blog_id ) {
		$blog_id           = (int) $blog_id;
		$this->blog_prefix = $this->multisite ? $blog_id . ':' : '';
	}

	/**
	 * Serves as a utility function to determine whether a key exists in the cache.
	 *
	 * @since 3.4.0
	 *
	 * @param int|string $key   Cache key to check for existence.
	 * @param string     $group Cache group for the key existence check.
	 * @return bool Whether the key exists in the cache for the given group.
	 */
	protected function _exists( $key, $group ) {
		return ( isset( $this->cache[ $group ] ) && ( isset( $this->cache[ $group ][ $key ] ) || array_key_exists( $key, $this->cache[ $group ] ) ) )
			|| ( $this->can_dba( $group ) && dba_exists( "$group:$key", self::$dba ) );
	}
}