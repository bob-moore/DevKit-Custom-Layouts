<?php
/**
 * Support for the divi
 *
 * @class astra
 * @package CustomLayouts\ThemeSupport
 */

namespace DevKit\Layouts\Compatibility;

use DevKit\Layouts\Base;
use DevKit\Layouts\Subscriber;

defined('ABSPATH') || exit;

class Divi extends Base
{
	/**
	 * Constructor
	 *
	 * Check if Astra is activated and construct new instance
	 *
	 * @return $this
	 */
	public function __construct()
	{
		$theme = wp_get_theme();
//
		if ( is_object( $theme ) && strtolower( $theme->__get( 'template' ) ) === 'divi' )
		{
			parent::__construct();
		}

		return $this;
	}
	/**
	 * Register filters
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @return void
	 */
	public function addFilters() : void
	{
		Subscriber::addFilter('devkit/layouts/fields/locations', [$this, 'locations'] );
	}
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
		Subscriber::addFilter('devkit/layouts/content', [$this, 'render'], 8, 2);
//		Subscriber::addAction( 'save_post_custom-layout', [$this, 'clearCache'] );
//		Subscriber::addAction( 'wp_enqueue_scripts', [$this, 'enqueueEditStyles'] );
	}


	/**
	 * Get all the action hooks for this specific theme
	 *
	 * @param  array $locations Action hooks that the theme can display - default is empty
	 * @return array $hooks
	 */
	public function locations( array $locations ) : array
	{
		$astra_locations =
			[
				'Divi' => [
					'et_before_main_content' => 'Before main Content',
					'et_after_main_content' => 'After main Content',
					'et_header_top' => 'Header Top',
					'et_before_post' => 'Before Post',
					'et_before_content' => 'Before Content',
					'et_after_post' => 'After Post',
					'et_fb_before_comments_template' => 'Before Comments Template',
					'et_fb_after_comments_template' => 'After Comments Template',
					'et_block_template_canvas_main_content' => 'Canvas Main Content',
				]
			];
		return array_merge_recursive( $locations, $astra_locations );
	}
	/**
	 * Render divi content
	 *
	 * If the layout uses the divi builder, use that to render instead of default content
	 * filters
	 *
	 * @param string $content default blank string
	 * @param object $layout post type object
	 * @return string maybe rendered content
	 */
	public function render( string $content, object $layout ) : string
	{
		/**
		 * Bail if divi
		 */
		if ( ! function_exists( 'et_pb_is_pagebuilder_used' ) || ! et_pb_is_pagebuilder_used( $layout->id ) ) {
			return $content;
		}
//		/**
//		 * See if we are currently editing a page
//		 */
		if ( isset( $_GET['et_fb'] ) && $_GET['et_fb'] == 1 && get_the_id() !== $layout->id )
		{

			$edit_link = get_post_permalink( $layout->id ) . '?et_fb=1&PageSpeed=off';

			return sprintf( '<a href="%s" class="devkit-layout-edit divi" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z"></path></svg><span class="placeholder">Edit Custom Layout</a></a>', $edit_link );
		}
		else
		{
			return et_builder_render_layout( get_the_content( null, true, $layout->id ) );
		}
	}
}