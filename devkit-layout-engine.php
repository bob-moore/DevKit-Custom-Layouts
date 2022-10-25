<?php
/**
 * @wordpress-plugin
 * Plugin Name: DevKit Layout Engine
 * Plugin URI:  https://github.com/bob-moore/DevKit-Custom-Layouts
 * Description: Custom layouts for (almost) any site
 * Version:     0.1.0
 * Author:      Bob Moore
 * Author URI:  https://www.bobmoore.dev
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: devkit_layouts
 */

namespace DevKit\Layouts;

use \wpcl\wpconsole\Console;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\\DevKit\\Layouts\\Plugin' ) )
{
	require_once __DIR__ . '/vendor/autoload.php';

	class Plugin extends Base
	{
		/**
		 * Construct new instance
		 */
		public function __construct()
		{
			/**
			 * Create plugin constants
			 */
			define( 'DEVKIT_TEMPLATES_VERSION', '0.1.0' );
			define( 'DEVKIT_TEMPLATES_ASSET_PREFIX', $this->isDev() ? '' : '.min' );
			define( 'DEVKIT_TEMPLATES_URL', plugin_dir_url( __FILE__ ) );
			define( 'DEVKIT_TEMPLATES_PATH', plugin_dir_path( __FILE__ ) );
            define( 'DEVKIT_TEMPLATES_META_KEY', substr( md5('devkit_layouts' ), 0, 8));
			define( 'DEVKIT_TEMPLATES_POST_TYPE', 'devkit-layout');
			define( 'DEVKIT_TEMPLATES_GROUP_TAX', 'devkit-layout-group');
			/**
			 * Register the text domain
			 */
			load_plugin_textdomain( 'devkit_layouts', false, basename( dirname( __FILE__ ) ) . '/languages' );
			/**
			 * Register activation hook
			 */
			register_activation_hook( __FILE__, [$this, 'activate'] );
			/**
			 * Register deactivation hook
			 */
			register_deactivation_hook( __FILE__, [$this, 'deactivate'] );
			/**
			 * Construct parent
			 */
			parent::__construct();
			/**
			 * Kickoff the plugin
			 */
			$this->burnBabyBurn();
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
		 * Register filters
		 *
		 * Uses the subscriber class to ensure only actions of this instance are added
		 * and the instance can be referenced via subscriber
		 */
		public function addFilters() : void
		{
			Subscriber::addFilter( 'timber/context', [ $this, 'context' ], 2 );
		}

		/**
		 * Set up an empty array for classes to add context to
		 *
		 * @param array $context
		 * @return array
		 */
		public function context( array $context ) : array
		{
			$context['devkit'] = [];
			return $context;
		}

		/**
		 * Kickoff activation of all plugin classes
		 *
		 * @return void
		 */
		private function burnBabyBurn() : void
		{
			$classes = [
				// Core
				__NAMESPACE__ . '\\Admin',
				__NAMESPACE__ . '\\FrontEnd',
				__NAMESPACE__ . '\\Timber',
				__NAMESPACE__ . '\\Locations',
				__NAMESPACE__ . '\\Rest',
				__NAMESPACE__ . '\\Meta',
				__NAMESPACE__ . '\\Fields',
				// Blocks
				__NAMESPACE__ . '\\Blocks\\TemplateParts\\Block',
				__NAMESPACE__ . '\\Blocks\\Twig\\Block',
				// Components
//				__NAMESPACE__ . '\\Components\\Authorbox',
				__NAMESPACE__ . '\\Components\\PostNavigation',
//				__NAMESPACE__ . '\\Components\\RecentPosts',
//				__NAMESPACE__ . '\\Components\\RelatedPosts',
//				__NAMESPACE__ . '\\Components\\SocialSharing',
				// Compatibility
				__NAMESPACE__ . '\\Compatibility\\Astra',
				__NAMESPACE__ . '\\Compatibility\\FLBuilder',
				__NAMESPACE__ . '\\Compatibility\\Elementor',
				__NAMESPACE__ . '\\Compatibility\\Divi',
			];

			foreach ( $classes as $class )
			{
				new $class();
			}
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
					'taxonomies'            => [DEVKIT_TEMPLATES_GROUP_TAX],
					'labels'                => $labels,
					'supports'              => ['title', 'editor', 'revisions', 'custom-fields'],
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
			register_post_type(DEVKIT_TEMPLATES_POST_TYPE, $args);
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
					'post_types'                 => [DEVKIT_TEMPLATES_POST_TYPE],
				];

			register_taxonomy(DEVKIT_TEMPLATES_GROUP_TAX, DEVKIT_TEMPLATES_POST_TYPE, $args);
			register_taxonomy_for_object_type(DEVKIT_TEMPLATES_GROUP_TAX, DEVKIT_TEMPLATES_POST_TYPE);
		}
		/**
		 * Activate Plugin
		 *
		 * Flush permalinks after first registration of post type/taxonomy
		 */
		public function activate() : void
		{
			add_action( 'init', function()
			{
				global $wp_rewrite;

				$wp_rewrite->init();

				$wp_rewrite->flush_rules();
			}, 99 );
		}
		/**
		 * Deactivate Plugin
		 *
		 */
		public function deactivate() : void
		{

		}
		/**
		 * Helper function to determine if this is a dev environment or not
		 *
		 * @return bool
		 */
		public static function isDev() : bool
		{
			if ( function_exists('wp_get_environment_type') )
			{
				return in_array( wp_get_environment_type(), ['staging', 'development', 'local'] ) || WP_DEBUG === true;
			}
			else
			{
				return WP_DEBUG;
			}
		}
		/**
		 * Helper function to expose errors and objects and stuff
		 *
		 * Prints PHP objects, errors, etc. to the browser console using either the
		 * 'wp_footer', or 'admin_footer' hooks. Which are the final hooks that run reliably.
		 *
		 * @param mixed $object anything to be logged to console
		 * @param bool $include_stack flag to include/exclude the backtrace, so you know where
		 * @return void
		 */
		public static function log( $object, bool $include_stack = true ) : void
		{
			if ( $include_stack )
			{
				$backtrace = debug_backtrace();
				$object = [
					'stack' => [
						'class' => $backtrace[1]['class'] ?? '',
						'file' => $backtrace[0]['file'],
						'line' => $backtrace[0]['line']
					],
					'object' => $object
				];
			}
			Console::log( $object );
		}
		/**
		 * Helper function to determine if plugin is active or not
		 * Wrapper function for is_plugin_active core WP function
		 *
		 * @see https://developer.wordpress.org/reference/functions/is_plugin_active/
		 * @param string  $plugin Path to the plugin file relative to the plugins directory
		 * @return boolean True, if in the active plugins list. False, not in the list.
		 */
		public static function isPluginActive( string $plugin = '' ) : bool
		{
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			return is_plugin_active( $plugin );
		}
	}
	new \DevKit\Layouts\Plugin();
}
