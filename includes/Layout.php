<?php
/**
 * Layout Object Class
 *
 * @class Timber
 * @package CustomLayouts\Classes
 */

namespace DevKit\Layouts;

use DevKit\Layouts\Plugin;

defined('ABSPATH') || exit;

class Layout extends Base
{
	/**
	 * Name of the custom post type
	 */
	const NAME = 'devkit-layout';
	/**
	 * Name of the custom post type
	 */
	const LAYOUT_POST_TYPE_NAME = 'devkit-layout';
	/**
	 * Name of the custom taxonomy
	 */
	const GROUP_TAXONOMY_NAME = 'devkit-layout-group';
	/**
	 * Post ID
	 *
	 * @var int
	 */
	public int $id = 0;
	/**
	 * Type of container (div, section, etc)
	 *
	 * @var string
	 */
	public string $container = 'div';
	/**
	 * Class to wrap layout with
	 *
	 * @var string
	 */
	public string $class = '';
	/**
	 * Array of hook/priority locations to set
	 *
	 * @var array
	 */
	public array $locations = [];
	/**
	 * Array of conditions to display/hide the layout
	 *
	 * @var array
	 */
	public array $conditions = [];
	/**
	 * Custom code snippet to include
	 *
	 * @var string
	 */
	public string $snippet = '';
	/**
	 * The type of layout
	 *
	 * @var string
	 */
	public string $type = '';
	/**
	 * Extra javascript to include
	 *
	 * @var string
	 */
	public string $scripts = '';
	/**
	 * Extra css to include
	 *
	 * @var string
	 */
	public string $styles = '';

	public function __construct( int $id = 0 )
	{
		if ( ! $id )
		{
			parent::__construct();
		}
		else {
			$this->id = $id;
			$this->create();
		}
		return $this;
	}
	/**
	 * Register actions
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @return void
	 */
	public function addActions(): void
	{
		Subscriber::addAction( 'init', [$this, 'registerPostType'] );
		Subscriber::addAction('init', [$this, 'registerTaxonomy']);
	}
	/**
	 * Create new layout, with fields ready to be rendered on the frontend
	 *
	 * @return void
	 */
	public function create() : void
	{
		if ( get_post_type( $this->id ) !== self::LAYOUT_POST_TYPE_NAME || get_post_status( $this->id ) !== 'publish')
		{
			return;
		}
		$meta = Subscriber::getInstance('Admin')->getMeta($this->id);

		foreach ( $meta as $key => $value )
		{
			/**
			 * Check property of this matches meta key
			 */
			if ( ! property_exists( $this, $key ) )
			{
				continue;
			}
			$methodKey = 'set' . ucwords( $key );
			/**
			 * Check if method exists to set property
			 */
			if ( method_exists( $this, $methodKey ) )
			{
				$this->{$methodKey}( $value );
			}
			else {
				$this->{$key} = $value;
			}
		}
	}

	protected function setLocations( $locations )
	{
		foreach ( $locations as $location )
		{
			$this->locations[ $location['hook'] ] = $location['priority'];
		}
	}
	protected function setClass( string $class )
	{
		$this->class = esc_attr( trim( $class) );
	}
	/**
	 * Register custom post type
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_post_type/
	 */
	public function registerPostType(): void
	{
		$labels =
		[
			'name'                  => _x('DevKit Layouts', 'Post Type General Name', 'devkit_layouts'),
			'singular_name'         => _x('DevKit Layout', 'Post Type Singular Name', 'devkit_layouts'),
			'menu_name'             => __('DevKit Layouts', 'devkit_layouts'),
			'name_admin_bar'        => __('DevKit Layouts', 'devkit_layouts'),
			'parent_item_colon'     => __('Parent Layout:', 'devkit_layouts'),
			'all_items'             => __('DevKit Layouts', 'devkit_layouts'),
			'add_new_item'          => __('Add New Layout', 'devkit_layouts'),
			'add_new'               => __('Add New', 'devkit_layouts'),
			'new_item'              => __('New Layout', 'devkit_layouts'),
			'edit_item'             => __('Edit Layout', 'devkit_layouts'),
			'update_item'           => __('Update Layout', 'devkit_layouts'),
			'view_item'             => __('View Layout', 'devkit_layouts'),
			'search_items'          => __('Search Layouts', 'devkit_layouts'),
			'not_found'             => __('Not found', 'devkit_layouts'),
			'not_found_in_trash'    => __('Not found in Trash', 'devkit_layouts'),
			'items_list'            => __('Layout list', 'devkit_layouts'),
			'items_list_navigation' => __('Layout list navigation', 'devkit_layouts'),
			'filter_items_list'     => __('Filter block list', 'devkit_layouts'),
		];
		$rewrite =
		[
			'slug'                  => 'devkit-layout',
			'with_front'            => false,
			'pages'                 => false,
			'feeds'                 => false,
		];
		$args =
		[
			'label'                 => __('DevKit Layout', 'devkit_layouts'),
			'description'           => __('DevKit Layouts', 'devkit_layouts'),
			'taxonomies'            => [self::GROUP_TAXONOMY_NAME],
			'labels'                => $labels,
			'supports'              => ['title', 'editor', 'revisions'],
			'hierarchical'          => true,
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => 'themes.php',
			'menu_position'         => 99999,
			'menu_icon'             => 'dashicons-text',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => is_user_logged_in(),
			'capability_type'       => 'page',
			'show_in_rest'          => true,
			'rewrite'               => $rewrite,
		];
		register_post_type(self::LAYOUT_POST_TYPE_NAME, $args);
	}
	/**
	 * Register custom taxonomy
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_taxonomy/
	 * @see https://developer.wordpress.org/reference/functions/register_taxonomy_for_object_type/
	 */
	public function registerTaxonomy(): void
	{
		$labels =
		[
			'name'                       => _x('Layout Groups', 'Taxonomy General Name', 'devkit_layouts'),
			'singular_name'              => _x('Layout Group', 'Taxonomy Singular Name', 'devkit_layouts'),
			'menu_name'                  => __('Layout Groups', 'devkit_layouts'),
			'all_items'                  => __('All Items', 'devkit_layouts'),
			'parent_item'                => __('Parent Item', 'devkit_layouts'),
			'parent_item_colon'          => __('Parent Item:', 'devkit_layouts'),
			'new_item_name'              => __('New Item Name', 'devkit_layouts'),
			'add_new_item'               => __('Add New Item', 'devkit_layouts'),
			'edit_item'                  => __('Edit Item', 'devkit_layouts'),
			'update_item'                => __('Update Item', 'devkit_layouts'),
			'view_item'                  => __('View Item', 'devkit_layouts'),
			'separate_items_with_commas' => __('Separate items with commas', 'devkit_layouts'),
			'add_or_remove_items'        => __('Add or remove items', 'devkit_layouts'),
			'choose_from_most_used'      => __('Choose from the most used', 'devkit_layouts'),
			'popular_items'              => __('Popular Items', 'devkit_layouts'),
			'search_items'               => __('Search Items', 'devkit_layouts'),
			'not_found'                  => __('Not Found', 'devkit_layouts'),
			'no_terms'                   => __('No items', 'devkit_layouts'),
			'items_list'                 => __('Items list', 'devkit_layouts'),
			'items_list_navigation'      => __('Items list navigation', 'devkit_layouts'),
		];
		$args =
		[
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => false,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			'show_in_rest'               => true,
			'post_types'                 => [self::LAYOUT_POST_TYPE_NAME],
		];

		register_taxonomy(self::GROUP_TAXONOMY_NAME, self::LAYOUT_POST_TYPE_NAME, $args);
		register_taxonomy_for_object_type(self::GROUP_TAXONOMY_NAME, self::LAYOUT_POST_TYPE_NAME);
	}
}
