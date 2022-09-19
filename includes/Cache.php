<?php
/**
 * Cache controller class
 *
 * @class Cache
 * @package CustomLayouts\Classes
 */

namespace DevKit\Layouts;

defined( 'ABSPATH' ) || exit;

class Cache extends Base
{
	/**
	 * Default cache timeout, 24 hours
	 *
	 * @var int
	 */
	const CACHE_TIMEOUT = 86400;
	/**
	 * Specific cache key
	 *
	 * Generated based on url & user role
	 *
	 * @var string
	 * @access protected
	 */
	protected $_key = '';
	/**
	 * Get cache
	 *
	 * @return array/false Collection of cached TempaltePart objects
	 */
	public function get()
	{
		/**
		 * Try to get from object cache first
		 * @var [type]
		 */
		$cache = wp_cache_get( $this->key(), __NAMESPACE__ );
		/**
		 * Fallback to checking transient
		 */
		if ( $cache === false )
		{
			$transient = get_transient( __NAMESPACE__ );
			/**
			 * Check that this specific page/user is cached
			 */
			if ( $transient !== false && isset( $transient[$this->key()] ) )
			{
				/**
				 * if has no expiration, retrieve as cache
				 */
				if ( empty( $transient[$this->key()]['expiration'] ) )
				{
					$cache = $transient[$this->key()]['data'];
				}
				/**
				 * Check if expired, remove from cache and revalidate
				 */
				elseif ( $transient[$this->key()]['expiration'] <= strtotime( wp_date( 'm/d/Y h:i:s a' ) ) )
				{
					unset( $transient[$this->key()] );
					set_transient( __NAMESPACE__, $transient, self::CACHE_TIMEOUT );
				}
				/**
				 * Not expired, retrieve as cache
				 */
				else
				{
					$cache = $transient[$this->key()]['data'];
				}
			}
			return $cache;
		}
	}
	/**
	 * Set cache
	 *
	 * @param array $data Collection TempaltePart objects to be cached
	 * @param int $cache_until Linux datetime string of date/time to recheck cache
	 */
	public function set( $data, $cache_until )
	{
		/**
		 * Linux time for right now
		 */
		$now = strtotime( wp_date( 'm/d/Y h:i:s a' ) );
		/**
		 * Maybe calculate cache timeout, if datetime provided
		 */
		if ( empty ( $cache_until ) )
		{
			/**
			 * Difference between now and cache_until
			 */
			$cache_difference = $cache_until - $now;
			/**
			 * Actual
			 */
			$cache_in_seconds = $cache_difference > 0 && $cache_difference < self::CACHE_TIMEOUT ? : self::CACHE_TIMEOUT;
		}
		else
		{
			$cache_in_seconds = self::CACHE_TIMEOUT;
		}
		/**
		 * Setup object cache
		 */
		wp_cache_set( $this->key(), $data, __NAMESPACE__, $cache_in_seconds );
		/**
		 * Transient cache
		 * @var array/false
		 */
		$transient = get_transient( __NAMESPACE__ );
		/**
		 * Ensure we have an array
		 */
		$transient = is_array( $transient ) ? $transient : [];
		/**
		 * Set new key
		 */
		$transient[ $this->key() ] = [
			'data' => $data,
			'expiration' => $cache_until
		];
		/**
		 * Set the new transient
		 */
		set_transient( __NAMESPACE__, $transient, self::CACHE_TIMEOUT );
	}
	/**
	 * Delete cache
	 *
	 * @todo add 'wp_cache_delete_group' to only flush our own group, once WP adds support for it
	 * @return [type] [description]
	 */
	public function delete()
	{
		/**
		 * Delete the transient
		 */
		delete_transient( __NAMESPACE__ );
		/**
		 * Delete the object cache
		 *
		 * @todo add 'wp_cache_delete_group' to only flush our own group, once WP
		 * add support for it
		 */
		wp_cache_flush();
	}
	/**
	 * Return or generate cache key
	 *
	 * Generates key based on WP->request & user role
	 *
	 * @access protected
	 * @return string Generated cache key or $this->_key, if it exists
	 */
	protected function key()
	{
		/**
		 * If we've already set the key, we can return it now
		 */
		if ( ! empty( $this->_key ) )
		{
			return $this->_key;
		}

		$keys = [ 'cl_cache_'];

		/**
		 * Set user role(s) variable
		 */
		if ( ! is_user_logged_in() )
		{
			$keys[] = 'logout';
		}
		/**
		 * Smush together and set user roles
		 */
		else
		{
			$user = wp_get_current_user();
			$keys[] = implode( '', $user->roles );
		}
		/**
		 * Check if homepage
		 */
		if ( is_front_page() && ! is_home() )
		{
			$keys[] = 'front';
		}
		/**
		 * Check for search
		 */
		elseif ( is_search() )
		{
			$keys[] = 'search';
		}
		/**
		 * Check for 404
		 */
		elseif ( is_404() )
		{
			$keys[] = '404';
		}
		/**
		 * Use actual request
		 */
		else
		{
			global $wp;

			$keys[] = $this->removePagination( $wp->request );
		}

		$this->_key = implode( '_', $keys );

		return $this->_key;
	}
	/**
	 * Remove pagination from cache key
	 *
	 * Archive pagination does not need a seperate cache, so they can be removed from the key
	 * The wp->request includes pagination, which would balloon the cache unecessarily and
	 * prevent cache hits we want
	 *
	 * @param  string $request WP Generated request string
	 * @access protected
	 * @return string altered request string without pagination
	 */
	protected function removePagination( $request )
	{
		/**
		 * position of string '/page/' in page request parameter
		 * @var [type]
		 */
		$pos = strpos( $request, "/page/" );
		/**
		 * If not paginated, don't bother
		 * @var [type]
		 */
		if ( $pos === false ) {
			return $request;
		}
		/**
		 * Get the starting position
		 * @var [type]
		 */
		$start = $pos + strlen( "/page/" );
		/**
		 * Get the end position, the position to the next slash
		 */
		$pos = strpos( $request, "/", $start );
		$end = $pos === false ? strlen( $request ) : $pos;
		/**
		 * Remove the numbers
		 */
		$request = substr_replace($request, '', $start, $end - $start);
		/**
		 * Finally remove the pagination
		 */
		return str_replace( "/page/", '', $request );
	}
}