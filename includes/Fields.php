<?php
/**
 * Metabox Form Fields
 *
 * @class Blocks
 * @package CustomLayouts\Classes
 */

namespace DevKit\Layouts;

defined( 'ABSPATH' ) || exit;

class Fields extends Base
{
	/**
	 * Register actions
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @return void
	 */
	public function addActions() : void {
		Subscriber::addFilter( 'devkit/layouts/metabox/json', [$this, 'formatFieldsForJS'], 999 );
		Subscriber::addFilter( 'devkit/layouts/metabox/json', [$this, 'insertValuesForJS'], 9999 );
		Subscriber::addFilter( 'devkit/layouts/fields/locations', [$this, 'themeSupport'], 8 );

	}

	public function get()
	{
		$fields =
			[
			'type' =>
			[
				'type' => 'select',
				'label' => __('Type', 'devkit_layouts'),
				'default' => '',
				'options' =>
				[
					'' => __('Default Editor', 'devkit_layouts'),
					'snippet' => __('Snippet', 'devkit_layouts'),
					'partial' => __('Template Part', 'devkit_layouts')
				]
			],
			'container' =>
			[
				'type' => 'select',
				'label' => __('Container', 'devkit_layouts'),
				'default' => 'div',
				'options' =>
				[
					'div' => 'div',
					'section' => 'section',
					'aside' => 'aside',
					'header' => 'header',
					'footer' => 'footer',
				]
			],
			'class' =>
				[
					'type' => 'text',
					'label' => __('CSS Class', 'devkit_layouts'),
					'default' => '',
				],
			'snippet' =>
				[
					'type' => 'twig',
					'label' => __('Custom Code Snippet', 'devkit_layouts'),
					'default' => '',
				],
			'comparison' =>
				[
					'type' => 'select',
					'label' => __('Comparison', 'devkit_layouts'),
					'default' => '=',
					'options' => [
						'=' => __('IS', 'devkit_layouts'),
						'!=' => __('IS NOT', 'devkit_layouts')
					]
				],
			'base' =>
				[
					'type' => 'select',
					'label' => __('Condition', 'devkit_layouts'),
					'options' =>
						[
							'__return_true' => __('Entire Site', 'devkit_layouts'),
							'view' => __('View', 'devkit_layouts'),
							'post_type' => __('Post Type', 'devkit_layouts'),
							'term' => __('Term', 'devkit_layouts'),
							'user' => __('User', 'devkit_layouts'),
							'author' => __('Author', 'devkit_layouts'),
							'schedule' => __('Schedule', 'devkit_layouts'),
							'posts' => __('Post', 'devkit_layouts'),
							'custom' => __('Custom', 'devkit_layouts'),
						]
				],
			'view' =>
				[
					'type' => 'select',
					'label' => __('View', 'devkit_layouts'),
					'multiple' => true,
					'options' =>
						[
							'is_singular' => __('Singular', 'devkit_layouts'),
							'is_archive' => __('Archive', 'devkit_layouts'),
							'is_search' => __('Search', 'devkit_layouts'),
							'is_404' => __('404', 'devkit_layouts'),
							'is_home' => __('Blog', 'devkit_layouts'),
						]
				],
			'post_type' =>
				[
					'type' => 'select',
					'label' => __('Post Type', 'devkit_layouts'),
					'multiple' => true,
					'options' => $this->postTypes()
				],
			'taxonomy' =>
				[
					'type' => 'select',
					'label' => __('Taxonomies', 'devkit_layouts'),
					'multiple' => true,
					'options' => $this->taxonomies()
				],
			'user' =>
				[
					'type' => 'select',
					'label' => __('User', 'devkit_layouts'),
					'multiple' => true,
					'options' => $this->users()
				],
			'term' =>
				[
					'type' => 'select',
					'label' => __('Terms', 'devkit_layouts'),
					'multiple' => true,
					'options' => $this->terms()
				],
			'posts' =>
				[
					'type' => 'select',
					'label' => __('Posts', 'devkit_layouts'),
					'multiple' => true,
					'options' => $this->posts()
				],
			'author' =>
				[
					'type' => 'select',
					'label' => __('Author', 'devkit_layouts'),
					'multiple' => true,
					'options' => $this->authors()
				],
			'schedule' =>
				[
					'type' => 'select',
					'label' => __('Schedule', 'devkit_layouts'),
					'default' => 'start',
					'options' =>
						[
							'start' => __('Start', 'devkit_layouts'),
							'end' => __('End', 'devkit_layouts'),
						]
				],
			'date' =>
				[
					'type' => 'date',
					'label' => __('Date', 'devkit_layouts'),
				],
			'hook' =>
				[
					'label' => __('Hook', 'devkit_layouts'),
					'type' => 'select',
					'options' => apply_filters( 'devkit/layouts/fields/locations', [
						'core' => [
							'wp_head' => __('WP Head', 'devkit_layouts'),
							'wp_footer' => __('WP Footer', 'devkit_layouts'),
							'the_content' => __('The Content', 'devkit_layouts'),
							'custom' => __( 'Custom' )
						]
					] ),
				],
			'priority' =>
				[
					'type' => 'number',
					'label' => __('Priority', 'devkit_layouts')
				],
			'display' =>
			[
				'type' => 'select',
				'label' => __('Show/Hide', 'devkit_layouts'),
				'options' =>
				[
					1 => __('Show', 'devkit_layouts'),
					0 => __('Hide', 'devkit_layouts')
				]
			],
		];
		return apply_filters( 'devkit/layouts/metabox/fields', $fields );
	}
	/**
	 * Format field data for our JS to use
	 *
	 * @param array $data
	 * @return array
	 */
	public function formatFieldsForJS( array $fields ) : array
	{
		foreach ( $fields as $index => $field )
		{
			if ( isset($field['type']) && $field['type'] === 'select' )
			{
				$options = [];

				foreach ( $field['options'] as $value => $label )
				{
					/**
					 * Format option groups required for React Select
					 */
					if ( is_array( $label ) )
					{
						$subgroup = [];
						foreach ($label as $subvalue => $sublabel )
						{
							$option = [
								'value' => $subvalue,
								'label' => $sublabel
							];
							$subgroup[] = $option;
						}
						$options[] = [
							'label' => $value,
							'options' => $subgroup
						];
					}
					/**
					 * Format single arrays
					 */
					else
					{
						$option = [
							'value' => $value,
							'label' => $label
						];
						if ( isset($field['value'] ) && $value === $field['value'])
						{
							$data['fields'][$index]['value'] = $option;
						}
						$options[] = $option;
					}
				}
				$fields[$index]['options'] = $options;
			}
		}
		return $fields;
	}
	public function insertValuesForJS( array $fields ) : array
	{
		foreach ( $fields as $key => $args )
		{
			if ( $args['type'] !== 'select' )
			{
				continue;
			}
			$temp = [
				'default' => '',
				'values' => []
			];
			foreach ( $args['options'] as $index => $option )
			{
				if ( ! isset( $option['value'] ) && isset( $option['options'] ) )
				{
					foreach ( $option['options']  as $suboption ) {
						$temp['values'][$suboption['value']] = $suboption;
					}
				}
				else {
					$temp['values'][$option['value']] = $option;
				}

				if ( isset( $args['default'] ) && $temp['values'][$args['default']] )
				{
					$temp['default'] = $temp['values'][$args['default']];
				}

			}
			$fields[$key] = wp_parse_args( $temp, $args );
		}

		return $fields;
	}

	/**
	 * Get all user roles for user view option
	 *
	 * @return void
	 */
	public function users()
	{
		global $wp_roles;
		$options = [
			'is_user_logged_in' => 'Logged In',
		];
		foreach ($wp_roles->roles as $value => $role) {
			$options[$value] = $role['name'];
		}
		return $options;
	}
	public function taxonomies() : array
	{
		$options = [];

		$tax_objects = get_taxonomies(['public' => true], 'objects');

		foreach ($tax_objects as $tax)
		{
			$post_types = is_array($tax->object_type) ? $tax->object_type[0] : (string)$tax->object_type;

			if ( ! isset( $options[$post_types] ) )
			{
				$options[$post_types] = [];
			}

			$options[$post_types][] = $tax->label;
		}

		return $options;
	}
	public function terms() : array
	{
		$options = [];

		$tax_objects = get_taxonomies(['public' => true], 'objects');

		foreach ($tax_objects as $tax) {

			if (in_array($tax->name, apply_filters( 'devkit/layouts/term_blacklist', ['fl-builder-template-category'] ))) {
				continue;
			}

			$temp = [];

			$terms = get_terms($tax->name, ['hide_empty' => false]);

			foreach ($terms as $term)
			{
				$temp[$term->term_taxonomy_id] = $term->name;
			}
			if ( ! empty ( $temp ) )
			{
				$options[$tax->name] = $temp;
			}
		}
		return $options;
	}
	public function postTypes() : array
	{
		$options = [];

		$post_types = get_post_types(['public' => true], 'objects');

		$post_types = apply_filters('devkit/layouts/fields/post_types', $post_types);

		foreach ($post_types as $post_type) {

			if (in_array($post_type->name, apply_filters( 'devkit/layouts/post_type_blacklist', ['fl-builder-template'] )))
			{
				continue;
			}
			$options[$post_type->name] = $post_type->label;
		}
		return $options;
	}
	public function posts() : array
	{
		$options = [];

		$post_types = get_post_types(['public' => true], 'objects');

		$post_types = apply_filters('devkit/layouts/fields/post_types', $post_types);

		foreach ($post_types as $post_type) {

			if ( in_array($post_type->name, apply_filters('devkit/layouts/post_type_blacklist', ['fl-builder-template']))) {
				continue;
			}

			$args = [
				'posts_per_page' => -1,
				'post_type' => [$post_type->name],
				'post_status' => 'publish',
				'perm' => 'readable',
				'fields' => 'ids'
			];

			$posts = get_posts( $args );

			$suboptions = [];

			foreach ( $posts as $post_id )
			{
				$suboptions[$post_id] = get_the_title( $post_id );
			}

			$options[$post_type->label] = $suboptions;
		}
		return $options;
	}
	/**
	 * Get available authors for metabox options
	 *
	 * @return array
	 */
	public function authors() : array
	{
		$options = [];

		$users = get_users(['who' => 'authors']);

		foreach ($users as $user)
		{
			$options[$user->ID] = $user->data->display_name;
		}
		return $options;
	}
	/**
	 * Get theme support for custom locations
	 *
	 * @param array $locations
	 * @return array
	 */
	public function themeSupport( array $locations ) : array
	{
		$theme_support = get_theme_support('devkit-layout-locations');

		if ( is_array( $theme_support ) )
		{
			$locations = array_merge( $theme_support[0], $locations );
		}

		return $locations;
	}
}