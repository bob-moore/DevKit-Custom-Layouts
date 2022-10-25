<?php
/**
 * Locations Controller
 *
 * Manage where layouts are displayed
 *
 * @class Locations
 * @package Layouts/Controllers
 */

namespace DevKit\Layouts;

defined( 'ABSPATH' ) || exit;

class Locations extends Base
{
	/**
	 * Queue of layouts to display on this page
	 *
	 * @var array
	 * @access protected
	 */
	protected array $_queue = [];
	/**
	 * Register actions
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @return void
	 */
	public function addActions() : void
	{
		if ( ! is_admin() )
		{
			Subscriber::addAction('wp', [$this, 'queue'] );
			Subscriber::addAction( 'template_redirect', [$this, 'registerLocations'], 10 );
		}
	}
	public function queue()
	{
		$layouts = get_posts(
			[
				'posts_per_page' => -1,
				'post_type' => [DEVKIT_TEMPLATES_POST_TYPE],
				'fields' => 'ids'
			]
		);

		foreach ( $layouts as $layout_id )
		{
			$layout = new Layout($layout_id);

			if ( Subscriber::getInstance('Conditions' )->shouldDisplay( $layout ) )
			{
				foreach ( $layout->locations as $location => $priority )
				{
					$this->_queue[ $location ][ $layout->id ] = $layout;
				}
			}
		}
	}
	/**
	 * Get object(s) from the queue
	 *
	 * @param string  $hook Action hook to look in
	 * @param  integer $id ID of post type object
	 * @return false/array/object Return group of layout objects, or single. False on failure
	 */
	public function getQueued( string $hook = '', int $id = 0 )
	{
		/**
		 * Don't waste time if queue is empty
		 */
		if ( empty( $this->_queue ) )
		{
			return false;
		}
		/**
		 * If no hook, return entire queue
		 */
		if ( empty( $hook ) )
		{
			return $this->_queue;
		}
		/**
		 * If hook not set in queue, bail
		 */
		if ( ! isset( $this->_queue[$hook] ) || empty( $this->_queue[$hook] ) )
		{
			return false;
		}
		/**
		 * If ID not set return entire hook
		 */
		if ( ! $id )
		{
			return $this->_queue[$hook];
		}
		/**
		 * Else return specific layout
		 */
		elseif ( isset( $this->_queue[$hook][$id] ) )
		{
			return $this->_queue[$hook][$id];
		}
		/**
		 * Default return
		 */
		return false;
	}
	/**
	 * Set action on hook
	 *
	 * Loops through the queue and adds_action for each
	 *
	 * @return void
	 */
	public function registerLocations() : void
	{
		if ( empty( $this->_queue ) )
		{
			return;
		}
		/**
		 * Iterate through each action in queue
		 */
		foreach ( $this->_queue as $action => $layouts )
		{
			/**
			 * For each action, iterate through each layout
			 */
			foreach ( $layouts as $layout )
			{
				/**
				 * And through each layout, iterate through each action
				 */
				foreach ( $layout->locations as $location => $priority )
				{
					$priority = $location === 'the_content' ? 10 : intval($priority);
					Subscriber::addAction($location, [Subscriber::getInstance('FrontEnd'), "devkit/layouts/render/{$layout->id}"], $priority);
				}
			}
		}
	}
}