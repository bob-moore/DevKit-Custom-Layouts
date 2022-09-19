<?php

/**
 * Elementor control class
 *
 * @class Elementor
 * @package CustomLayouts\Plugins
 */

namespace DevKit\Layouts\Compatibility;

use DevKit\Layouts\Base;
use DevKit\Layouts\Subscriber;
use DevKit\Layouts\Plugin;
use DevKit\Layouts\Layout;

defined('ABSPATH') || exit;

class Elementor extends Base
{
	/**
	 * Construct new instance
	 *
	 * @return object/bool $this or false
	 */
	public function __construct()
	{
		if (!Plugin::isPluginActive('elementor/elementor.php')) {
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
		Subscriber::addFilter('elementor/settings/controls/checkbox_list_cpt/post_type_objects', [$this, 'addPostTypeSupport']);
		Subscriber::addFilter('devkit/layouts/content', [$this, 'render'], 8, 2);
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
	public function addPostTypeSupport(array $post_types): array
	{
		global $wp_post_types;

		if (isset($wp_post_types[Layout::LAYOUT_POST_TYPE_NAME])) {
			$post_types[Layout::LAYOUT_POST_TYPE_NAME] = $wp_post_types[Layout::LAYOUT_POST_TYPE_NAME];
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
	public function render(string $content, object $layout): string
	{
		if (!class_exists('\\Elementor\\Plugin') || !\Elementor\Plugin::instance()->db->is_built_with_elementor($layout->id)) {
			return $content;
		}

		$content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display($layout->id, true);

		if ( \Elementor\Plugin::$instance->preview->is_preview_mode() )
		{
			$edit_link = str_ireplace('action=edit', 'action=elementor', get_edit_post_link($layout->id));
			$content = sprintf(
				'<div class="layout-edit-enabled"><a href="%s" class="devkit-layout-edit" target="_blank"><span class="edit-label"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M13.89 3.39l2.71 2.72c.46.46.42 1.24.03 1.64l-8.01 8.02-5.56 1.16 1.16-5.58s7.6-7.63 7.99-8.03c.39-.39 1.22-.39 1.68.07zm-2.73 2.79l-5.59 5.61 1.11 1.11 5.54-5.65zm-2.97 8.23l5.58-5.6-1.07-1.08-5.59 5.6z"></path></svg>%s</span></a>',
				$edit_link,
				__('Edit Layout', 'devkit_layouts')
			) . $content . '</div>';
		}

		return $content;
	}
}
