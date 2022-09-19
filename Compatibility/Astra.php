<?php

/**
 * Support for the astra theme
 *
 * @class astra
 * @package CustomLayouts\ThemeSupport
 */

namespace DevKit\Layouts\Compatibility;

use DevKit\Layouts\Base;
use DevKit\Layouts\Subscriber;

defined('ABSPATH') || exit;

class Astra extends Base
{
	/**
	 * Constructer
	 *
	 * Check if Astra is activated and construct new instance
	 *
	 * @return bool/obj False when not activated, $this otherwise
	 */
	public function __construct()
	{

		// $theme = wp_get_theme();

		// if (!is_object($theme) || !isset($theme->template) || $theme->template !== 'astra') {
		// 	return false;
		// }

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
		Subscriber::addFilter('devkit/layouts/fields/locations', [$this, 'locations'] );
	}
	/**
	 * Get all the action hooks for this specific theme
	 *
	 * @param  array $hooks Action hooks that the theme can display - default is empty
	 * @return array $hooks
	 */
	public function locations( array $locations ) : array
	{
		$astra_locations =
		[
			'head' =>
			[
				'astra_html_before' => 'astra_html_before',
				'astra_head_top' => 'astra_head_top',
				'astra_head_bottom' => 'astra_head_bottom',
			],
			'header' =>
			[
				'astra_body_top' => 'astra_body_top',
				'astra_header_above_container_before' => 'astra_header_above_container_before',
				'astra_header_above_container_after' => 'astra_header_above_container_after',
				'astra_header_primary_container_before' => 'astra_header_primary_container_before',
				'astra_header_primary_container_after' => 'astra_header_primary_container_after',
				'astra_header_below_container_before' => 'astra_header_below_container_before',
				'astra_header_below_container_after' => 'astra_header_below_container_after',
				'astra_header_before' => 'astra_header_before',
				'astra_masthead_top' => 'astra_masthead_top',
				'astra_main_header_bar_top' => 'astra_main_header_bar_top',
				'astra_masthead_content' => 'astra_masthead_content',
				'astra_masthead_toggle_buttons_before' => 'astra_masthead_toggle_buttons_before',
				'astra_masthead_toggle_buttons_after' => 'astra_masthead_toggle_buttons_after',
				'astra_main_header_bar_bottom' => 'astra_main_header_bar_bottom',
				'astra_masthead_bottom' => 'astra_masthead_bottom',
				'astra_header_after' => 'astra_header_after',
			],
			'content' =>
			[
				'astra_content_before' => 'astra_content_before',
				'astra_content_top' => 'astra_content_top',
				'astra_primary_content_top' => 'astra_primary_content_top',
				'astra_content_loop' => 'astra_content_loop',
				'astra_template_parts_content_none' => 'astra_template_parts_content_none',
				'astra_content_while_before' => 'astra_content_while_before',
				'astra_template_parts_content_top' => 'astra_template_parts_content_top',
				'astra_template_parts_content' => 'astra_template_parts_content',
				'astra_entry_before' => 'astra_entry_before',
				'astra_entry_top' => 'astra_entry_top',
				'astra_single_header_before' => 'astra_single_header_before',
				'astra_single_header_top' => 'astra_single_header_top',
				'astra_single_post_title_after' => 'astra_single_post_title_after',
				'astra_single_header_bottom' => 'astra_single_header_bottom',
				'astra_single_header_after' => 'astra_single_header_after',
				'astra_entry_content_before' => 'astra_entry_content_before',
				'astra_entry_content_404_page' => 'astra_entry_content_404_page',
				'astra_entry_content_after' => 'astra_entry_content_after',
				'astra_entry_bottom' => 'astra_entry_bottom',
				'astra_entry_after' => 'astra_entry_after',
				'astra_template_parts_content_bottom' => 'astra_template_parts_content_bottom',
				'astra_primary_content_bottom' => 'astra_primary_content_bottom',
				'astra_content_while_after' => 'astra_content_while_after',
				'astra_content_bottom' => 'astra_content_bottom',
				'astra_content_after' => 'astra_content_after',
			],
			'comment' =>
			[
				'astra_comments_before' => 'astra_comments_before',
				'astra_comments_after' => 'astra_comments_after',
			],
			'sidebar' =>
			[
				'astra_sidebars_before' => 'astra_sidebars_before',
				'astra_sidebars_after' => 'astra_sidebars_after',
			],
			'footer' =>
			[
				'astra_footer_above_container_before' => 'astra_footer_above_container_before',
				'astra_footer_above_container_after' => 'astra_footer_above_container_after',
				'astra_footer_primary_container_before' => 'astra_footer_primary_container_before',
				'astra_footer_primary_container_after' => 'astra_footer_primary_container_after',
				'astra_footer_below_container_before' => 'astra_footer_below_container_before',
				'astra_footer_below_container_after' => 'astra_footer_below_container_after',
				'astra_footer_before' => 'astra_footer_before',
				'astra_footer_content_top' => 'astra_footer_content_top',
				'astra_footer_inside_container_top' => 'astra_footer_inside_container_top',
				'astra_footer_inside_container_bottom' => 'astra_footer_inside_container_bottom',
				'astra_footer_content_bottom' => 'astra_footer_content_bottom',
				'astra_footer_after' => 'astra_footer_after',
				'astra_body_bottom' => 'Astra Body Bottom',
			],
			'not found' => [
				'astra_404_content_template' => 'astra_404_content_template',
			],
		];
		return array_merge_recursive( $locations, $astra_locations );
	}
}
