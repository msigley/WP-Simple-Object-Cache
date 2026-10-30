<?php
/*
Plugin Name:	WP Simple Object Cache
Description:	Simple object cache plugin that supports Redis. Also provides frontend caching for widgets and wp_nav_menu.
Version:		4.0.0
Requires at least: 6.9
Requires PHP: 7.4
Author: Matthew Sigley
Author URI: http://github.com/msigley/
License: GPL2
Update URI: false
*/

// Block direct requests
if ( !defined('ABSPATH') )
	die( 'Sorry Chuck!' );

class WPSimple_Object_Cache {
    private $path = '';

    public function __construct() { }

    public function init() {
        //Plugin path
		$this->path = plugin_dir_path( __FILE__ );

        //Plugin activation/deactivation
        register_activation_hook( __FILE__, array( $this, 'activation' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

        if ($this->_do_flush()) {
            //Admin area actions
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'filter_plugin_actions' ), 10, 2 );
            add_action( 'admin_init', array( $this, 'handle_flush_object_cache_request' ) );

            //widget cache options
            add_action('in_widget_form', array( $this, 'in_widget_form' ), 10, 3);
            add_filter('widget_update_callback', array( $this, 'widget_update_callback' ), 10, 4);

            //Post cache flush
            global $wp_version;
            if (version_compare($wp_version,'3.5', '>=')) {
                add_action('clean_post_cache', array( &$this, 'on_post_change' ), 0, 2);
            } else {
                add_action('wp_trash_post', array( &$this, 'on_post_change' ), 0);
                add_action('save_post', array( &$this, 'on_post_change' ), 0);
                add_action('delete_post', array( &$this, 'on_post_change' ), 0);
                add_action('publish_phone', array( &$this, 'on_post_change' ), 0);
            }

            //Comment cache flush
            add_action('comment_post', array( &$this, 'on_comment_change' ), 0);
            add_action('edit_comment', array( &$this, 'on_comment_change' ), 0);
            add_action('delete_comment', array( &$this, 'on_comment_change' ), 0);
            add_action('wp_set_comment_status', array( &$this, 'on_comment_status' ), 0, 2);
            add_action('trackback_post', array( &$this, 'on_comment_change' ), 0);
            add_action('pingback_post', array( &$this, 'on_comment_change' ), 0);

            //Option cache flush
            add_action('updated_option', array( &$this, 'on_change_option' ), 0, 1);
            add_action('added_option', array( &$this, 'on_change_option' ), 0, 1);
            add_action('delete_option', array( &$this, 'on_change_option' ), 0, 1);

            //wp_nav_menu cache flush
            add_action('wp_delete_nav_menu', array( &$this, 'on_change' ), 0);
            add_action('wp_create_nav_menu', array( &$this, 'on_change' ), 0);
            add_action('wp_update_nav_menu', array( &$this, 'on_change' ), 0);
            add_action('wp_update_nav_menu_item', array( &$this, 'on_change' ), 0);

            //widget cache flush
            add_action('update_widget', array( &$this, 'on_change' ), 0);
        } else {
            //Frontend actions
            //wp_nav_menu cache
            add_filter('wp_nav_menu_args', array( $this, 'wp_nav_menu_args' ), 20);
            add_filter('wp_nav_menu_objects', array( $this, 'wp_nav_menu_objects' ));

            //widget cache
            add_filter('widget_display_callback', array( $this, 'widget_display_callback' ), 10, 3);
        }

        //Theme switch cache flush
        add_action('switch_theme', array( &$this, 'on_change' ), 0);

        //Edit user profile cache flush
        add_action('edit_user_profile_update', array( &$this, 'on_change_profile' ), 0);

        //Cache Stats in Admin
        add_action( 'after_plugin_row_meta', array( &$this, 'after_plugin_row_meta' ), 10, 2 );

        if( defined( 'WP_OBJECT_CACHE_DEBUG' ) && WP_OBJECT_CACHE_DEBUG ) {
            add_action( 'shutdown', array( &$this, 'print_page_cache_stats' ) );
        }
    }
    
    public function activation() {
        @mkdir( WP_CONTENT_DIR . '/object-cache/', 0655 );

        $this->install_object_cache_drop_in();
    }

    public function deactivation() {
        wp_cache_flush();
        wp_cache_close();

        $cache_files = glob( WP_CONTENT_DIR . '/object-cache/*' );
        foreach( $cache_files as $cache_file ) {
            @unlink( $cache_file );
        }

        $this->uninstall_object_cache_drop_in();
    }

    public function install_object_cache_drop_in() {
        $from = $this->path . '/lib/object-cache.php';
        $to = WP_CONTENT_DIR . '/object-cache.php';
        copy( $from, $to ); // Will overwrite an existing file
    }

    public function uninstall_object_cache_drop_in() {
        $file = WP_CONTENT_DIR . '/object-cache.php';
        @unlink( $file );
    }
        
    public function filter_plugin_actions($links, $file) {
        $links['flush_cache'] = '<a href="' . admin_url( '?flush_object_cache=1', 'https' ) . '">Flush Object Cache</a>';
        return $links;
    }

    public function handle_flush_object_cache_request() {
		if( empty( $_GET['flush_object_cache'] ) )
			return;

        wp_cache_flush();
        wp_cache_close();

        $cache_files = glob( WP_CONTENT_DIR . '/object-cache/*' );
        foreach( $cache_files as $cache_file ) {
            @unlink( $cache_file );
        }

		wp_redirect( admin_url( 'plugins.php#wp-simple-object-cache', 'https'), 307 );
		die();
	}

	/**
	 * Checks if post should be flushed or not. Returns true if it should not be flushed
	 * @param $post
	 * @return bool
	 */
	function _is_flushable_post($post) {
	    if (is_numeric($post))
	        $post = get_post($post);
	    $post_status = array('publish');
	    // dont flush when we have post "attachment"
	    // its child of the post and is flushed always when post is published, while not changed in fact
	    $post_type = array('revision', 'attachment');
	    $flushable = !in_array($post->post_type, $post_type) && in_array($post->post_status, $post_status);
	    return apply_filters('wpsimple_flushable_post', $flushable, $post);
	}
	
    /**
     * Change action
     */
    function on_change() {
        static $flushed = false;

        if (!$flushed) {
            wp_cache_flush();
            $flushed = true;
        }
    }

    /**
     * Change post action
     */
    function on_post_change($post_id = 0, $post = null) {
        static $flushed = false;
		
        if (!$flushed) {
            if (is_null($post))
                $post = $post_id;
			
            if ($post_id> 0 && $this->_is_flushable_post($post)) {
                return;
            }

            wp_cache_flush();
            $flushed = true;
        }
    }

    /**
     * Change action
     */
    function on_change_option($option) {
        if ($option != 'cron') {
            wp_cache_flush();
            $flushed = true;
        }
    }

    /**
     * Flush cache when user profile is updated
     * @param int $user_id
    */
    function on_change_profile($user_id) {
        static $flushed = false;

        if (!$flushed) {
            wp_cache_flush();
            $flushed = true;
        }
    }


    /**
     * Comment change action
     *
     * @param integer $comment_id
     */
    function on_comment_change($comment_id) {
        $post_id = 0;

        if ($comment_id) {
            $comment = get_comment($comment_id, ARRAY_A);
            $post_id = !empty($comment['comment_post_ID']) ? (int) $comment['comment_post_ID'] : 0;
        }

        $this->on_post_change($post_id);
    }

    /**
     * Comment status action
     *
     * @param integer $comment_id
     * @param string $status
     */
    function on_comment_status($comment_id, $status) {
        if ($status === 'approve' || $status === '1') {
            $this->on_comment_change($comment_id);
        }
    }

    /**
     * Strip current* classes from menu items, since manus are cached once, not per page.
     *
     * @param array $menu_items Array of menu item objects.
     * @return array
     */
    public function wp_nav_menu_objects( $menu_items ) {
        foreach ( $menu_items as &$item ) {
            //Use array_filter to avoid a foreach inside of a foreach for PHP < 7 support
            $item->classes = array_filter( $item->classes, function( $value, $key ) {
                return 0 !== stripos( $value, 'current' );
            }, ARRAY_FILTER_USE_BOTH ); 
        }

        return $menu_items;
    }

    /**
     * Fake no menu matches to force menu run custom callback.
     *
     * @deprecated
     *
     * @param array $args Menu arguments.
     * @return array
     */
    public function wp_nav_menu_args( $args ) {
        if ( empty( $args['doing_menu_cache'] ) ) {
            add_filter( 'wp_get_nav_menus', '__return_empty_array' ); //
            $args = array(
                'menu'           => '',
                'theme_location' => '',
                'fallback_cb'    => array( $this, 'wp_nav_menu_fallback_cb' ),
                'original_args'  => $args,
            );
        }
        return $args;
    }

    /**
     * Restore arguments and fetch cached menu content.
     *
     * @deprecated
     *
     * @param array $args Arguments.
     * @return string
     */
    public function wp_nav_menu_fallback_cb( $args ) {
        remove_filter( 'wp_get_nav_menus', '__return_empty_array' );
        $args = $args['original_args'];
        unset( $args['original_args'] );
        $echo = $args['echo'];
        $args['echo'] = false;
        $args['doing_menu_cache'] = true;
        $cache_key = hash( 'sha256', maybe_serialize($args) );
        $output = wp_cache_get( $cache_key, 'wp_nav_menu' );
        if( false === $output || '' === $output ) {
            $name = is_object( $args['menu'] ) ? $args['menu']->slug : $args['menu'];
            if ( empty( $name ) && ! empty( $args['theme_location'] ) )
                $name = $args['theme_location'];
            $output = wp_nav_menu( $args ) . $this->get_comment( $name, 'wp_nav_menu' );
            if( false !== $output && '' !== $output )
                wp_cache_set( $cache_key, $output, 'wp_nav_menu' );
        }
        if ( $echo )
            echo $output;
        return $output;
    }

    public function widget_display_callback( $instance, $widget, $args ) {
        if( !empty( $instance['exclude_from_widget_cache'] ) )
            return $instance;

        $cache_key = $widget->id;
        $output = wp_cache_get( $cache_key, 'widget' );
        if( false === $output || !is_string( $output ) || !preg_match( '/^<!--.+-->\s*\S/', $output ) ) {
            ob_start();
            $success = call_user_func_array( array( $widget, 'widget' ), array( $args, $instance ) );
            $output = $this->get_comment( $args['name'], 'widget' ) . "\n\n" . ob_get_clean();
            if( false !== $output && '' !== $output )
                wp_cache_set( $cache_key, $output, 'widget' );
        }
        echo $output;

        return false;
    }

    public function in_widget_form( $widget, $return, $instance ) {
        ?>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['exclude_from_widget_cache'], 1 ); ?> id="<?php echo $widget->get_field_id( 'exclude_from_widget_cache' ); ?>" name="<?php echo $widget->get_field_name( 'exclude_from_widget_cache' ); ?>" value="1" /> 
            <label for="<?php echo $widget->get_field_id( 'exclude_from_widget_cache' ); ?>"> Exclude from widget cache</label>
        </p>
        <?php
    }

    public function widget_update_callback( $instance, $new_instance, $old_instance, $widget ) {
        $instance['exclude_from_widget_cache'] = !empty( $new_instance['exclude_from_widget_cache'] );
        return $instance;
    }

    /**
     * Get human-readable HTML comment with timestamp to append to cached frontend content.
     *
     * @param string $name Fragment name.
     *
     * @return string
     */
    public function get_comment( $name, $type ) {
        return '<!-- ' . esc_html( $name ) . ' ' . esc_html( $type ) . ' cached on ' . date_i18n( DATE_RSS ) . ' -->';
    }

    public function after_plugin_row_meta( $plugin_file, $plugin_data ) {
        if( $plugin_file !== 'object-cache.php' )
            return;

        global $wp_object_cache;
        if( method_exists( $wp_object_cache, 'cache_stats' ) ) {
            $wp_object_cache->cache_stats();
        } else {
            echo '<p><strong>No supported object cache backend available.</strong></p>';
        }

        echo '<p>';
        echo '<strong>Supported object cache backends in order of preference:</strong><br />';
        echo '<ul>';
        echo '<li>Redis (via php_redis extension). <code>REDIS</code></li>';
        echo '<li>Database Abstraction Layer with LMDB database handler (via php-dba extension). <code>PHPLMDB</code><br />'
            .'Requires PHP 7.2.0+.</li>';
        echo '</ul>';
        echo 'A preferred object cache backend can be configured using the values above using the <code>WP_OBJECT_CACHE</code> setting in the wp-config.php file.';
        echo '</p>';
    }

    public function print_page_cache_stats() {
        global $wp_object_cache;
        echo '<div style="clear: both; margin-left: 160px; padding-left: 20px;">';
        $wp_object_cache->stats();
        echo '</div>';
    }

    /**
     * @return bool
     */
    private function _do_flush() {
        //TODO: Requires admin flush until OC can make changes in Admin backend
        //TODO: Verify this with AJAX changes in newer WP
        return is_admin() || defined('WP_ADMIN');
    }
}

$WPSimple_Object_Cache = new WPSimple_Object_Cache;
$WPSimple_Object_Cache->init();