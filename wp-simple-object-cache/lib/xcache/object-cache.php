<?php

/**
 * Plugin Name: XCache Object Cache Backend
 * Description: XCache backend for the WordPress Object Cache.
 * Version: 1.0.3
 * Author: Pierre Schmitz
 * Author URI: https://pierre-schmitz.com/
 * Plugin URI: http://wordpress.org/extend/plugins/xcache/
 */

function wp_cache_add($key, $data, $group = '', $expire = 0) {
	global $wp_object_cache;

	return $wp_object_cache->add($key, $data, $group, $expire);
}

function wp_cache_close() {
	return true;
}

function wp_cache_decr( $key, $offset = 1, $group = '' ) {
	global $wp_object_cache;

	return $wp_object_cache->decr( $key, $offset, $group );
}

function wp_cache_delete($key, $group = '') {
	global $wp_object_cache;

	return $wp_object_cache->delete($key, $group);
}

function wp_cache_flush() {
	global $wp_object_cache;

	return $wp_object_cache->flush();
}

function wp_cache_get( $key, $group = '', $force = false, &$found = null ) {
	global $wp_object_cache;

	return $wp_object_cache->get( $key, $group, $force, $found );
}

function wp_cache_incr( $key, $offset = 1, $group = '' ) {
	global $wp_object_cache;

	return $wp_object_cache->incr( $key, $offset, $group );
}


function wp_cache_init() {
	$GLOBALS['wp_object_cache'] = new XCache_Object_Cache();
}

function wp_cache_replace($key, $data, $group = '', $expire = 0) {
	global $wp_object_cache;

	return $wp_object_cache->replace($key, $data, $group, $expire);
}

function wp_cache_set($key, $data, $group = '', $expire = 0) {
	global $wp_object_cache;

	return $wp_object_cache->set($key, $data, $group, $expire);
}

function wp_cache_add_global_groups( $groups ) {
	global $wp_object_cache;

	return $wp_object_cache->add_global_groups($groups);
}

function wp_cache_add_non_persistent_groups( $groups ) {
	global $wp_object_cache;

	return $wp_object_cache->wp_cache_add_non_persistent_groups($groups);
}

function wp_cache_reset() {
	global $wp_object_cache;

	return $wp_object_cache->reset();
}

class XCache_Object_Cache {

	private $prefix = '';
	private $local_cache = array();
	private $global_groups = array();
	private $non_persistent_groups = array();

	public function __construct() {
		global $table_prefix;

		if ( !function_exists( 'xcache_get' ) ) {
			wp_die( 'You do not have XCache installed, so you cannot use the XCache object cache backend. Please remove the <code>object-cache.php</code> file from your content directory.' );
		}

		$this->prefix = DB_HOST.'.'.DB_NAME.'.'.$table_prefix;
	}


	private function get_key($group, $key) {
		if (empty($group)) {
			$group = 'default';
		}
		return $this->prefix.'.'.$group.'.'.$key;
	}

	public function add( $key, $data, $group = 'default', $expire = '' ) {
		if (function_exists('wp_suspend_cache_addition') && wp_suspend_cache_addition()) {
			return false;
		}
		if (isset($this->local_cache[$group][$key])) {
			return false;
		}
		if (!isset($this->non_persistent_groups[$group]) && xcache_isset($this->get_key($group, $key))) {
			return false;
		}

		if (is_object($data)) {
			$this->local_cache[$group][$key] = clone $data;
		} else {
			$this->local_cache[$group][$key] = $data;
		}

		if (!isset($this->non_persistent_groups[$group])) {
			return xcache_set($this->get_key($group, $key), serialize($data), $expire);
		}

		return true;
	}

	public function add_global_groups( $groups ) {
		if (is_array($groups)) {
			foreach ($groups as $group) {
				$this->global_groups[$group] = 1;
			}
		} else {
			$this->global_groups[$groups] = 1;
		}
	}

	public function wp_cache_add_non_persistent_groups( $groups ) {
		if (is_array($groups)) {
			foreach ($groups as $group) {
				$this->non_persistent_groups[$group] = 1;
			}
		} else {
			$this->non_persistent_groups[$groups] = 1;
		}
	}

	public function decr( $key, $offset = 1, $group = 'default' ) {
		if (isset($this->non_persistent_groups[$group])) {
			if (isset($this->local_cache[$group][$key]) && $this->local_cache[$group][$key] - $offset >= 0) {
				$this->local_cache[$group][$key] -= $offset;
			} else {
				$this->local_cache[$group][$key] = 0;
			}
			return $this->local_cache[$group][$key];
		} else {
			return xcache_dec($this->get_key($group, $key), $offset);
		}
	}

	public function delete($key, $group = 'default', $force = false) {
		unset($this->local_cache[$group][$key]);
		if (!isset($this->non_persistent_groups[$group])) {
			return xcache_unset($this->get_key($group, $key));
		}
		return true;
	}

	public function flush() {
		$this->local_cache = array ();
		// xcache_unset_by_prefix is only available since XCache 1.3
		if (!function_exists('xcache_unset_by_prefix')) {
			return xcache_unset_by_prefix($this->prefix);
		} else {
			xcache_clear_cache(XC_TYPE_VAR, 0);
			return true;
		}
	}

	public function get( $key, $group = 'default', $force = false, &$found = null) {
		if (isset($this->local_cache[$group][$key])) {
			$found = true;
			if (is_object($this->local_cache[$group][$key])) {
				return clone $this->local_cache[$group][$key];
			} else {
				return $this->local_cache[$group][$key];
			}
		} elseif (isset($this->non_persistent_groups[$group])) {
			$found = false;
			return false;
		} else {
			$value = unserialize(xcache_get($this->get_key($group, $key)));
			if ($value !== false) {
				$found = true;
				if (is_object($value)) {
					$this->local_cache[$group][$key] = clone $value;
				} else {
					$this->local_cache[$group][$key] = $value;
				}
			} else {
				$found = false;
			}
			return $value;
		}
	}

	public function incr( $key, $offset = 1, $group = 'default' ) {
		if (isset($this->non_persistent_groups[$group])) {
			if (isset($this->local_cache[$group][$key]) && $this->local_cache[$group][$key] + $offset >= 0) {
				$this->local_cache[$group][$key] += $offset;
			} else {
				$this->local_cache[$group][$key] = 0;
			}
			return $this->local_cache[$group][$key];
		} else {
			return xcache_inc($this->get_key($group, $key), $offset);
		}
	}

	public function replace($key, $data, $group = 'default', $expire = '') {
		if (isset($this->non_persistent_groups[$group])) {
			if (!isset($this->local_cache[$group][$key])) {
				return false;
			}
		} else {
			if (!isset($this->local_cache[$group][$key]) && !xcache_isset($this->get_key($group, $key))) {
				return false;
			}
			xcache_set($this->get_key($group, $key), serialize($data), $expire);
		}

		if (is_object($data)) {
			$this->local_cache[$group][$key] = clone $data;
		} else {
			$this->local_cache[$group][$key] = $data;
		}

		return true;
	}

	public function reset() {
		// TODO: remove non-global groups
	}

	public function set($key, $data, $group = 'default', $expire = '') {
		if (is_object($data)) {
			$this->local_cache[$group][$key] = clone $data;
		} else {
			$this->local_cache[$group][$key] = $data;
		}

		if (!isset($this->non_persistent_groups[$group])) {
			return xcache_set($this->get_key($group, $key), serialize($data), $expire);
		}

		return true;
	}

	public function stats() {
		// TODO: print some stats
		echo '';
	}
}

?>
