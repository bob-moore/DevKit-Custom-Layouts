<?php
/**
 * Beaver Builder compatibility class
 *
 * @class flbuilder
 * @package CustomLayouts\Plugins
 */

namespace DevKit\Layouts\Compatibility;

use DevKit\Layouts\Base;
use DevKit\Layouts\Subscriber;
use DevKit\Layouts\Plugin;
use DevKit\Layouts\Layout;

use FLBuilderModel;

defined('ABSPATH') || exit;

class FLBuilder extends Base
{
	/**
	 * Construct new instance
	 *
	 * @return object/bool $this or false
	 */
	public function __construct()
	{
		if ( ! Plugin::isPluginActive('beaver-builder-lite-version/fl-builder.php') && ! Plugin::isPluginActive('bb-plugin/fl-builder.php') )
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
	public function addFilters() : void
	{
		Subscriber::addFilter('fl_builder_admin_settings_post_types', [$this, 'addPostTypeSupport']);
		Subscriber::addFilter('devkit/layouts/content', [$this, 'render'], 8, 2);
	}
	/**
	 * Add post type support to beaver builder
	 *
	 * Manually adds dk-custom-layouts as a supported post type. Necessary because
	 * it is not a public post type
	 *
	 * @param array $post_types Array of default supported post types
	 * @return array $post_types
	 */
	public function addPostTypeSupport(array $post_types): array
	{
		global $wp_post_types;

		if (isset($wp_post_types[DEVKIT_TEMPLATES_POST_TYPE]))
		{
			$post_types[DEVKIT_TEMPLATES_POST_TYPE] = $wp_post_types[DEVKIT_TEMPLATES_POST_TYPE];
		}
		return $post_types;
	}
	public function editLink($layout): void
	{
		$layout_id = \FLBuilderModel::get_post_id();
		/**
		 * Ensure that a) beaver builder is open and b) we aren't editing the layout directly
		 */
		if (\FLBuilderModel::is_builder_active() )
		{
			$edit_link = get_post_permalink($layout_id) . '?fl_builder';
			printf('<a href="%s" class="devkit-layout-edit" target="_blank"><span class="edit-label"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z"></path></svg>%s</span></a>', $edit_link, __('Edit Layout', 'devkit_layouts'));
		}
	}
	public function returnEditClass(string $classes): string
	{
		return $classes . ' layout-edit-enabled';
	}
	/**
	 * Render beaver builder content
	 *
	 * If the layout uses beaver builder, use that to render instead of default content
	 * filters
	 *
	 * @param string $content default blank string
	 * @param object $layout post type object
	 * @return string maybe rendered content
	 */
	public function render(string $content, object $layout): string
	{
		/**
		 * Don't render if not a Beaver Builder document
		 */
		if ( !class_exists('FLBuilderModel') || ! \FLBuilderModel::is_builder_enabled( $layout->id ) )
		{
			return $content;
		}

		ob_start();

		Subscriber::addAction('fl_builder_after_render_nodes', [$this, 'editLink']);
		Subscriber::addFilter('fl_builder_content_classes', [$this, 'returnEditClass']);
		/**
		 * Render differently if currently in `the_content` action
		 */
		if ( doing_action( 'the_content' ) )
		{
			\FLBuilder::enqueue_layout_styles_scripts_by_id($layout->id);
			\FLBuilder::render_content_by_id($layout->id);
		}
		/**
		 * Render all else normally
		 */
		else
		{
			\FLBuilder::render_query([
				'post_type' => DEVKIT_TEMPLATES_POST_TYPE,
				'p' => $layout->id,
			]);
		}

		Subscriber::removeAction('fl_builder_after_render_nodes', [$this, 'editLink']);
		Subscriber::removeFilter('fl_builder_content_classes', [$this, 'returnEditClass']);

		return ob_get_clean();
	}
}
