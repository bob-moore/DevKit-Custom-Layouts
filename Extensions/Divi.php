<?php
/**
 * Support for the divi theme
 *
 * @class divi
 * @package CustomLayouts\ThemeSupport
 */

namespace DevKit\Layouts\Extensions;

use DevKit\Layouts\Base;
use DevKit\Layouts\Subscriber;
use DevKit\Layouts\PostTypes\CustomLayout;

defined( 'ABSPATH' ) || exit;

class Divi extends Base
{
	/**
	 * Constructer
	 *
	 * Check if Divi is activated and construct new instance
	 *
	 * @return bool/obj False when not activated, $this otherwise
	 */
	public function __construct()
	{

		$theme = wp_get_theme();

		if ( ! is_object( $theme ) || ! isset( $theme->template ) || strtolower( $theme->template ) !== 'divi' ) {
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
	 * @since 1.0.0
	 */
	public function addFilters() {
		Subscriber::addFilter( 'devkit/layouts/fields/hooks/theme', [$this, 'getHooks'] );
	}
	/**
	 * Register actions
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @since 1.0.0
	 * @see  https://developer.wordpress.org/reference/functions/add_filter/
	 */
	public function addActions() {
		Subscriber::addFilter( 'devkit/layouts/content', [$this, 'render'], 8, 2 );
		// Subscriber::addAction( 'devkit/layouts/before_render', [$this, 'maybeRender'] );
		Subscriber::addAction( 'save_post_custom-layout', [$this, 'clearCache'] );
	}
	/**
	 * Get all the action hooks for this specific theme
	 *
	 * @param  array $hooks Action hooks that the theme can display - default is empty
	 * @return array $hooks
	 */
	public function getHooks( $hooks ) {
		/**
		 * Add our known defaults
		 */
		$default_hooks = [
			'et_before_main_content' => 'Before main Content',
			'et_after_main_content' => 'After main Content',
			'et_header_top' => 'Header Top',
			'et_before_post' => 'Before Post',
			'et_before_content' => 'Before Content',
			'et_after_post' => 'After Post',
			'et_fb_before_comments_template' => 'Before Comments Template',
			'et_fb_after_comments_template' => 'After Comments Template',
			'et_block_template_canvas_main_content' => 'Canvas Main Content',
		];
		return wp_parse_args($hooks, $default_hooks);
	}

	public function maybeRender( $layout ) {
		if ( function_exists( 'et_pb_is_pagebuilder_used' ) && et_pb_is_pagebuilder_used( $layout->id ) ) {
			add_action( "custom_layout/render/{$layout->id}", [$this, 'render'] );
		}
	}

	public function clearCache() {
		if ( class_exists( 'ET_Core_PageResource' ) ) {
			\ET_Core_PageResource::remove_static_resources( 'all', 'all' );
		}
	}

	public function render( string $content, object $layout ) {
		/**
		 * Don't render on single edit screen
		 */
		if ( is_singular( CustomLayout::NAME ) && get_the_id() === $layout->id )
		{
			return $content;
		}


		if ( ! function_exists( 'et_pb_is_pagebuilder_used' ) || ! et_pb_is_pagebuilder_used( $layout->id ) ) {
			return $content;
		}
		// $result = \ET_Builder_Element::setup_advanced_styles_manager( $layout->id );
		// $advanced_styles_manager = $result['manager'];
		// if ( isset( $result['deferred'] ) ) {
		// 	$deferred_styles_manager = $result['deferred'];
		// }
		// $styles   = \ET_Builder_Element::get_style( true, $layout->id );
		// // self::log($result);
		// self::log( $advanced_styles_manager->has_file(), false );
		return apply_filters( 'the_content', get_the_content( null, true, $layout->id ) );
		// return et_builder_render_layout( get_the_content( null, true, $layout->id ) );
	}
}