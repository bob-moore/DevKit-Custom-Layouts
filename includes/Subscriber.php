<?php
/**
 * Subscriber class
 *
 * Used to keep and return instances and wrap subscription functionality that
 * relies on having access to a particular instance
 *
 * @class Subscriber
 * @package CustomLayouts\Classes
 */

namespace DevKit\Layouts;

use \DevKit\Layouts\Plugin;

defined( 'ABSPATH' ) || exit;

class Subscriber {
	/**
	 * Instances
	 *
	 * @access protected
	 * @var (array) $instances : Collection of instantiated classes
	 */
	protected static array $instances = [];
	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct() {}
	/**
	 * Get registered instance of classes
	 *
	 * @param string|object $class name or instance of class to retrieve instance of
	 * @return object|false Instance of called class || false
	 */
	public static function getInstance( $class = '' )
	{
		/**
		 * If class is empty, return an instance of ourself
		 */
		if ( empty( $class ) )
		{
			if ( ! isset( self::$instances[ __CLASS__ ] ) )
			{
				self::$instances[ __CLASS__ ] = new self();
			}
			$instance = self::$instances[ __CLASS__ ];
		}
		/**
		 * Else search for an instance
		 */
		else
		{
			$classname = is_object( $class ) ? get_class( $class ) : $class;
			/**
			 * Check and see if we have an instance of that class to return
			 */
			if ( isset( self::$instances[$classname] ) )
			{
				$instance = self::$instances[$classname];
			}
			/**
			 * Else check if we have to add the namespace, and return the instance
			 */
			elseif ( isset( self::$instances[ __NAMESPACE__ . "\\" . $classname ] ) )
			{
				$instance = self::$instances[ __NAMESPACE__ . "\\" . $classname ];
			}
			/**
			 * Else see if we were passed on object to add to instances,
			 * & return it as the instance
			 */
			elseif ( is_object( $class ) )
			{
				self::$instances[ $classname ] = $class;
				$instance = self::$instances[ $classname ];
			}
			/**
			 * Check for existing classes on string name
			 */
			elseif ( is_string( $classname ) )
			{
				if ( str_contains( $classname, __NAMESPACE__ ) && class_exists( $classname ) )
				{
					self::$instances[ $classname ] = new $classname();
					$instance = self::$instances[ $classname ];
				}
				elseif ( class_exists( __NAMESPACE__ . "\\" . $classname ) )
				{
					$ns_classname = __NAMESPACE__ . "\\" . $classname;
					self::$instances[ $classname ] = new $ns_classname();
					$instance = self::$instances[ $classname ];
				}
			}
		}
		return $instance ?? $class;
	}
	/**
	 * Hooks a function on to a specific action.
	 *
	 * Exactly like wordpress native add_action, which calls add_filter,
	 * this is just a wrapper for addFilter
	 *
	 * @param string $hook : The name of the filter to hook the $function_to_add callback to.
	 * @param callable $callback : The callback to be run when the filter is applied.
	 * @param int $priority  :Optional. Used to specify the order in which the functions
	 * @param int $argument_count   Optional. The number of arguments the function accepts. Default 1.
	 *
	 * @see  https://developer.wordpress.org/reference/functions/add_action/
	 */
	public static function addAction( string $hook = '', $callback = '', int $priority = 10, int $argument_count = 1 ) : void
	{
		self::addFilter( $hook, $callback, $priority, $argument_count );
	}
	/**
	 * Removes a function from a specified action hook.
	 *
	 * Exactly like wordpress native remove_action, which calls remove_filter,
	 * this is just a wrapper for removeFilter
	 *
	 * @param string $hook : The action hook to which the function to be removed is hooked.
	 * @param callable|string $calback : The name of the function which should be removed.
	 * @param int $priority : Optional. The priority of the function. Default 10.
	 * @return (bool) Whether the function is removed.
	 *
	 * @see  https://developer.wordpress.org/reference/functions/remove_action/
	 */
	public static function removeAction( string $hook = '',$callback = '', int $priority = 10 ) : bool
	{
		return self::removeFilter( $hook, $callback, $priority );
	}
	/**
	 * Checks if a specific action has been registered for this hook.
	 *
	 * Wrapper for hasFilter
	 *
	 * @since 1.0.0
	 *
	 * @param string hook The name of the filter hook. Default empty.
	 * @param callable|string|false $function Optional. The callback to check for. Default false.
	 * @return bool|int The priority of that hook is returned, or false if the function is not attached.
	 *
	 * @see  https://developer.wordpress.org/reference/functions/has_action/
	 */
	public static function hasAction( string $hook = '', $callback = false )
	{
		return self::hasFilter( $hook, $callback );
	}
	/**
	 * Hook a function or method to a specific filter action.
	 *
	 * @param string $hook : The name of the filter to hook the $function_to_add callback to.
	 * @param callable $callback : The callback to be run when the filter is applied.
	 * @param int $priority  :Optional. Used to specify the order in which the functions
	 * @param int $argument_count   Optional. The number of arguments the function accepts. Default 1.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_filter/
	 */
	public static function addFilter( string $hook = '', $callback = '', int $priority = 10, int $argument_count = 1 ) : void
	{
		if ( is_array( $callback ) )
		{
			add_filter( $hook, [ self::getInstance( $callback[0] ), $callback[1] ], $priority, $argument_count );
		}
		else
		{
			add_filter( $hook, $callback, $priority, $argument_count );
		}
	}
	/**
	 * Removes a function from a specified filter hook.
	 *
	 * This function removes a function attached to a specified filter hook. This
	 * method can be used to remove default functions attached to a specific filter
	 * hook and possibly replace them with a substitute.
	 *
	 * @param string $hook : The action hook to which the function to be removed is hooked.
	 * @param callable|string $callback : The name of the function which should be removed.
	 * @param int $priority : Optional. The priority of the function. Default 10.
	 * @return (bool) Whether the function is removed.
	 *
	 * @see  https://developer.wordpress.org/reference/functions/remove_action/
	 */
	public static function removeFilter( string $hook = '', $callback = '', int $priority = 10 ) : bool
	{
		if ( is_array( $callback ) )
		{
			return remove_filter( $hook, [ self::getInstance( $callback[0] ), $callback[1] ], $priority );
		}
		else
		{
			return remove_filter( $hook, $callback, $priority );
		}
	}
	/**
	 * Checks if a specific action has been registered for this hook.
	 *
	 * @param string hook The name of the filter hook. Default empty.
	 * @param callable|string|false $callback Optional. The callback to check for. Default false.
	 * @return bool|int The priority of that hook is returned, or false if the function is not attached.
	 *
	 * @see  https://developer.wordpress.org/reference/functions/has_filter/
	 */
	public static function hasFilter( string $hook = '', $callback = false )
	{
		if ( is_array( $callback ) )
		{
			return has_filter( $hook, [self::getInstance( $callback[0] ), $callback[1] ] );
		}
		else
		{
			return has_filter( $hook, $callback );
		}
	}
	/**
	 * Adds a new shortcode.
	 *
	 * Care should be taken through prefixing or other means to ensure that the
	 * shortcode tag being added is unique and will not conflict with other,
	 * already-added shortcode tags. In the event of a duplicated tag, the tag
	 * loaded last will take precedence.
	 *
	 * @param string $tag : Shortcode tag to be searched in post content.
	 * @param callable|string $callback : The callback function to run when the shortcode is found.
	 *
	 * @see https://developer.wordpress.org/reference/functions/add_shortcode/
	 */
	public static function addShortcode( string $hook = '', $callback = '' ) : void
	{
		if ( is_array( $callback ) )
		{
			add_shortcode( $hook, [ self::getInstance( $callback[0] ), $callback[1] ] );
		}
		else {
			add_shortcode( $hook, $callback );
		}
	}
}