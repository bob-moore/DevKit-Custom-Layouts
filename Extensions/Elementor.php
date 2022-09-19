<?php
/**
 * Elementor control class
 *
 * @class Elementor
 * @package CustomLayouts\Plugins
 */
namespace DevKit\Layouts\Extensions;

use DevKit\Layouts\Base;
use DevKit\Layouts\Subscriber;
use DevKit\Layouts\PostTypes\CustomLayout;

defined( 'ABSPATH' ) || exit;

class Elementor extends Base {
	/**
	 * Construct new instance
	 *
	 * @return object/bool $this or false
	 */
	public function __construct()
	{
		if ( ! Plugin::isPluginActive( 'elementor/elementor.php' ) )
		{
			return false;
		}
		return parent::__construct();
	}
	/**
	 * Register filters
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @return void
	 */
	public function addFilters()
	{
		Subscriber::addFilter( 'elementor/settings/controls/checkbox_list_cpt/post_type_objects', [$this, 'addPostTypeSupport'] );
		Subscriber::addFilter( 'devkit/layouts/content', [$this, 'render'], 8, 2 );
	}
	/**
	 * Add post type support to elementor
	 *
	 * Manually adds dk-custom-layouts as a supported post type. Necessary because
	 * it is not a public post type
	 *
	 * @param array $post_types Array of default supported post types
	 * @return array $post_types
	 */
	public function addPostTypeSupport( array $post_types ) : array
	{
		global $wp_post_types;

		if ( isset( $wp_post_types[CustomLayout::NAME] ) ) {
			$post_types[CustomLayout::NAME] = $wp_post_types[CustomLayout::NAME];
		}
		return $post_types;
	}
	/**
	 * Render elementor content
	 *
	 * If the layout uses elementor, use that to render instead of default content
	 * filters
	 *
	 * @param string $content default blank string
	 * @param object $layout post type object
	 * @return string maybe rendered content
	 */
	public function render( string $content, object $layout ) : string
	{
		/**
		 * Don't render on single edit screen
		 */
		if ( is_singular( CustomLayout::NAME ) && get_the_id() === $layout->id )
		{
			return $content;
		}
		/**
		 * Don't render if not a Beaver Builder document
		 */
		if ( ! class_exists( '\\Elementor\\Plugin' ) || ! \Elementor\Plugin::instance()->db->is_built_with_elementor( $layout->id ) )
		{
			return $content;
		}

		return \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $layout->id, true );
	}
}