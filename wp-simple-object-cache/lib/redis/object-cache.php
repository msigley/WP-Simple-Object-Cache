<?php
/**
 * Plugin Name: WP Simple Redis Object Cache
 * Author:      Matthew Sigley
 * Version:     1.0.1
 */

// Check if Redis class is installed
if ( ! class_exists( 'Redis' ) ) {
	return;
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
function wp_cache_add( $key, $value, $group = 'default', $expiration = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->add( $key, $value, $group, $expiration );
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
function wp_cache_decr( $key, $offset = 1, $group = 'default' ) {
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
function wp_cache_delete( $key, $group = 'default', $time = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->delete( $key, $group, $time );
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
function wp_cache_delete_group( $group ) {
	global $wp_object_cache;
	return $wp_object_cache->delete_group( $group );
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
function wp_cache_flush( $delay = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->flush( $delay );
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
function wp_cache_get( $key, $group = 'default', $force = false, &$found = null ) {
	global $wp_object_cache;

	$is_alloptions = $key == 'alloptions' && $group = 'options';
	if( $is_alloptions && $wp_object_cache->cached_alloptions )
		return array( 0 );

	$return = $wp_object_cache->get( $key, $group, $force, $found );

	if( $is_alloptions && !$wp_object_cache->cached_alloptions && false !== $return  ) {
		$wp_object_cache->cached_alloptions = true;

		$options_keys = array_keys( $return );
		$options_values = $wp_object_cache->mget_redis( $return );
		unset( $return ); // Free up RAM. This is a very large array.
		$num_options_keys = count( $options_keys );
		if( empty( $wp_object_cache->cache['options'] ) )
			$wp_object_cache->cache['options'] = array();
		for( $i = 0; $i < $num_options_keys; $i++ ) {
			if( false === $options_values[$i] )
				continue;
			
			$wp_object_cache->cache['options'][$options_keys[$i]] = $options_values[$i];
		}

		return array( 0 );
	}
	
	return $return;
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
function wp_cache_incr( $key, $offset = 1, $group = 'default' ) {
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
function wp_cache_replace( $key, $value, $group = 'default', $expiration = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->replace( $key, $value, $group, $expiration );
}

/**
 * Sets a value in cache.
 *
 * The value is set whether or not this key already exists in Redis.
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
function wp_cache_set( $key, $value, $group = 'default', $expiration = 0 ) {
	global $wp_object_cache;
	return $wp_object_cache->set( $key, $value, $group, $expiration );
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
 * Adds a group or set of groups to the list of Redis groups.
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
	 * The amount of times the cache data was already stored in the cache.
	 *
	 * @since 2.5.0
	 * @var int
	 */
	public $cache_hits = 0;

	/**
	 * Amount of times the cache did not have the request in cache.
	 *
	 * @since 2.0.0
	 * @var int
	 */
	public $cache_misses = 0;

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
	 * Holds the Redis client.
	 *
	 * @var Redis
	 */
	private static $num_instances = 0;

	/**
	 * Holds the Redis client.
	 *
	 * @var Redis
	 */
	private static $redis = null;

	/**
	 * Track if Redis is available.
	 *
	 * @var bool
	 */
	private static $redis_connected = false;

	/**
	 * Character used at the being of strings to mark the string is compressed.
	 * SOT is our compression marker. This character is never used in actual text.
	 * 
	 * @var char
	 */
	private $compression_marker = "\2";

	/**
	 * If the alloptions cache has been processed.
	 *
	 * @var bool
	 */
	public $cached_alloptions = false;

	/**
	 * Instantiate the Redis class.
	 *
	 * @param   null $redis_instance
	 */
	public function __construct() {
		// General Redis settings
		$redis_settings = array(
			'host' => '127.0.0.1',
			'port' => 6379,
			'auth' => false,
			'database' => 0
		);

		if ( defined( 'WP_REDIS_BACKEND_HOST' ) && WP_REDIS_BACKEND_HOST ) {
			$redis_settings['host'] = WP_REDIS_BACKEND_HOST;
		}
		if ( defined( 'WP_REDIS_BACKEND_PORT' ) && WP_REDIS_BACKEND_PORT ) {
			$redis_settings['port'] = WP_REDIS_BACKEND_PORT;
		}
		if ( defined( 'WP_REDIS_BACKEND_AUTH' ) && WP_REDIS_BACKEND_AUTH ) {
			$redis_settings['auth'] = WP_REDIS_BACKEND_AUTH;
		}
		if ( defined( 'WP_REDIS_BACKEND_DB' ) && WP_REDIS_BACKEND_DB ) {
			$redis_settings['database'] = WP_REDIS_BACKEND_DB;
		}

		/**
		 * This approach is borrowed from Sivel and Boren. Use the salt for easy cache invalidation and for
		 * multi single WP installs on the same server.
		 * Note this approach works but makes most memory analysis tools useless. 
		 * Best practice should be to define 'WP_CACHE_KEY_SALT' as an empty string and set each site to use its own database in Redis if possible.
		 */
		if ( ! defined( 'WP_CACHE_KEY_SALT' ) ) {
			define( 'WP_CACHE_KEY_SALT', hash('crc32b', ABSPATH ) ); // Use crc32b to shorten key size
		}


		// Use Redis PECL library.
		if( !self::$redis_connected || empty( self::$redis ) ) {
			try {
				// Calulate Redis timeout based on the PHP max_execution_time setting.
				$timeout = ini_get( 'max_execution_time' );
				if( false === $timeout )
					$timeout = 30; // Default max_execution_time is 30 seconds.
				if( empty( $timeout ) )
					$timeout = 300; // We want all connections to have a timeout.
				$timeout += 2; // Add a little leeway so we don't have Redis timeout issues with long running scripts.

				self::$redis = new Redis();
				self::$redis->connect( $redis_settings['host'], $redis_settings['port'], $timeout );
				self::$redis->client( 'setname', uniqid( 'PHP_' . $redis_settings['database'] . '_' . WP_CACHE_KEY_SALT . '_' . $_SERVER['REMOTE_ADDR'] . '_' . $_SERVER['REMOTE_PORT'] . '_', true ) ); // This allows connections to be identified in RedisInsight

				self::$redis->setOption( Redis::OPT_SERIALIZER, Redis::SERIALIZER_NONE );
				if( !empty( WP_CACHE_KEY_SALT ) )
					self::$redis->setOption( Redis::OPT_PREFIX, WP_CACHE_KEY_SALT . ':' );

				if ( false !== $redis_settings['auth'] )
					self::$redis->auth( $redis_settings['auth'] );

				self::$redis->select( $redis_settings['database'] );

				self::$redis_connected = true;

			} catch ( RedisException $e ) {
				$this->close();
			}
		}

		$this->multisite = is_multisite();
		$this->blog_prefix = $this->multisite ? get_current_blog_id() . ':' : '';

		self::$num_instances++;
	}

	/**
	 * Is Redis available and is this group persistent?
	 *
	 * @return bool
	 */
	public function can_redis( $group = '' ) {
		if( !empty( $group ) && isset( $this->non_persistent_groups[ $group ] ) )
			return false;

		return self::$redis_connected;
	}


	/**
	 * Wrapper for Redis::set.
	 * Handle object serialization and value compression.
	 *
	 * @return bool
	 */
	public function set_redis( $key, $value, $expire = 0 ) {
		$value = @serialize( $value );
		$compressed_value = @gzcompress( $value, 1, FORCE_DEFLATE );
		if( false !== $compressed_value )
			$value = "$this->compression_marker$compressed_value";
		if( $expire > 0 )
			self::$redis->set( $key, $value, $expire );
		else
			self::$redis->set( $key, $value );
	}

	/**
	 * Wrapper for Redis::get.
	 * Handle object unserialization and value decompression.
	 *
	 * @return bool
	 */
	public function get_redis( $key ) {
		return $this->decompress( self::$redis->get( $key ) );
	}

	/**
	 * Wrapper for Redis::mget.
	 * Handle object unserialization and value decompression.
	 *
	 * @return bool
	 */
	public function mget_redis( $keys ) {
		return self::$redis->mget( $keys );
	}

	private function needs_decompressed( $value ) {
		return is_string( $value ) && substr( $value, 0, 1 ) == $this->compression_marker;
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
	 * Wrapper for Redis::close.
	 *
	 * @return bool
	 */
	public function close() {
		if( !empty( self::$redis ) ) {
			self::$redis->close();
		}
		self::$redis_connected = false;
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

		if( $this->can_redis( $group ) )
			return (bool) self::$redis->del( "$group:$key" );
		return true;
	}

	/**
	 * Remove the contents of all cache keys in the group.
	 *
	 * @param string $group Where the cache contents are grouped.
	 * @return boolean True on success, false on failure.
	 */
	public function delete_group( $group = 'default' ) {
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

		if( $this->can_redis( $group ) ) {
			$keys = self::$redis->keys( "$group:$key_prefix*" );
			if( empty( $keys ) )
				return false;
			return (bool) self::$redis->del( ...$keys );
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

		if ( $this->can_redis() )
			self::$redis->flushDb();

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
		if ( empty( $group ) ) {
			$group = 'default';
		}

		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) ) {
			$key = $this->blog_prefix . $key;
		}

		if ( $this->_exists( $key, $group ) ) {
			if ( ( $force || !isset( $this->cache[ $group ][ $key ] ) ) && $this->can_redis( $group ) )
				$this->cache[ $group ][ $key ] = $this->get_redis( "$group:$key" );

			if ( isset( $this->cache[ $group ][ $key ] ) ) {
				$found             = true;
				$this->cache_hits += 1;

				if( 'options' == $group && $this->needs_decompressed( $this->cache[ $group ][ $key ] ) )
					$this->cache[ $group ][ $key ] = $this->decompress( $this->cache[ $group ][ $key ] );

				if ( is_object( $this->cache[ $group ][ $key ] ) ) {
					return clone $this->cache[ $group ][ $key ];
				} else {
					return $this->cache[ $group ][ $key ];
				}
			}
		}

		$found               = false;
		$this->cache_misses += 1;
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
	 * Don't use Redis::incrBy to emulate WP core's behavior
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

		if ( !isset( $this->cache[ $group ][ $key ] ) && $this->can_redis( $group ) )
			$this->cache[ $group ][ $key ] = $this->get_redis( "$group:$key" );

		if ( ! is_numeric( $this->cache[ $group ][ $key ] ) ) {
			$this->cache[ $group ][ $key ] = 0;
		}

		$offset = (int) $offset;

		$this->cache[ $group ][ $key ] += $offset;

		if ( $this->cache[ $group ][ $key ] < 0 ) {
			$this->cache[ $group ][ $key ] = 0;
		}

		if( $this->can_redis( $group ) ) {
			$ttl = self::$redis->ttl( "$group:$key" );
			if( $ttl < 0 ) // -1 indicates the key has no ttl
				$this->set_redis( "$group:$key", $this->cache[ $group ][ $key ] );
			else if( $ttl == 0 )
				$this->delete( $key, $group );
			else
				$this->set_redis( "$group:$key", $this->cache[ $group ][ $key ], $ttl );
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

		$this->override_cache_value( $key, $data, $group );

		if ( $this->multisite && ! isset( $this->global_groups[ $group ] ) ) {
			$key = $this->blog_prefix . $key;
		}


		if ( is_object( $data ) ) {
			$data = clone $data;
		}

		$this->cache[ $group ][ $key ] = $data;

		if( $this->can_redis( $group ) )
			$this->set_redis( "$group:$key", $this->cache[ $group ][ $key ], $expire );

		return true;
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
		echo "<strong>Cache Hits:</strong> {$this->cache_hits}<br />";
		echo "<strong>Cache Misses:</strong> {$this->cache_misses}<br />";
		if( !empty( $this->cache['options'] ) ) {
			$compressed_percent = ( count( array_filter( $this->cache['options'], array( $this, 'needs_decompressed' ) ) ) / count( $this->cache['options'] ) ) * 100;
			$decompressed_percent = 100 - $compressed_percent;
			echo "<strong>Options Cache Compressed:</strong> " . number_format( $compressed_percent, 2 ) . "%<br />";
			echo "<strong>Options Cache Decompressed:</strong> " . number_format( $decompressed_percent, 2 ) . "%<br />";
		}
		echo '</p>';
		echo '<ul>';
		foreach ( $this->cache as $group => $cache ) {
			echo "<li><strong>Group:</strong> $group - ( " . number_format( strlen( serialize( $cache ) ) / 1024, 2 ) . 'k )</li>';
		}
		echo '</ul>';
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
			|| ( $this->can_redis( $group ) && self::$redis->exists( "$group:$key" ) );
	}

	public function override_cache_value( $key, &$value, $group ) {
		if( empty( $value ) )
			return;
		if( $key == 'alloptions' && $group = 'options' ) {
			array_walk( $value, function( &$item, $item_key ) {
				$item = "options:$item_key";
			} );
		}
	}
}