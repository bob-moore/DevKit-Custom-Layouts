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
		public function __construct() {
			/**
			 * Create plugin constants
			 */
			define( 'DEVKIT_TEMPLATES_VERSION', '0.1.0' );
			define( 'DEVKIT_TEMPLATES_ASSET_PREFIX', $this->isDev() ? '' : '.min' );
			define( 'DEVKIT_TEMPLATES_URL', plugin_dir_url( __FILE__ ) );
			define( 'DEVKIT_TEMPLATES_PATH', plugin_dir_path( __FILE__ ) );
            define('DEVKIT_TEMPLATES_META_KEY', substr(md5('devkit_layouts'), 0, 8));
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
			 * Kickoff the plugin
			 */
			$this->burnBabyBurn();
			/**
			 * Construct parent
			 */
			parent::__construct();
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

		public function context( array $context ) : array
		{
			$context['devkit'] = [];

			return $context;
		}
		/**
		 * Kickoff activation of all plugin classes
		 */
		private function burnBabyBurn() : void
		{
			$classes = [
				__NAMESPACE__ . '\\Admin',
				__NAMESPACE__ . '\\FrontEnd',
				__NAMESPACE__ . '\\Timber',
				__NAMESPACE__ . '\\Layout',
				__NAMESPACE__ . '\\Controllers\\Locations',
				// Post Types

				// Taxonomies
				// __NAMESPACE__ . '\\Taxonomies\\Group',
				// Components
				// __NAMESPACE__ . '\\Components\\Authorbox',
				// __NAMESPACE__ . '\\Components\\PostNavigation',
				// __NAMESPACE__ . '\\Components\\RecentPosts',
				// __NAMESPACE__ . '\\Components\\RelatedPosts',
				// __NAMESPACE__ . '\\Components\\SocialSharing',
				// Compatibility
				__NAMESPACE__ . '\\Compatibility\\Astra',
				__NAMESPACE__ . '\\Compatibility\\FLBuilder',
				__NAMESPACE__ . '\\Compatibility\\Elementor',
				// __NAMESPACE__ . '\\Themes\\Divi',
				// __NAMESPACE__ . '\\Themes\\Genesis',
				// // Plugin Compatibility
				// __NAMESPACE__ . '\\Plugins\\Elementor',
				// __NAMESPACE__ . '\\Plugins\\FLBuilder',
			];

			foreach ( $classes as $class )
			{
				new $class();
			}
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
		 * Prints PHP objects, errors, etc to the browswer console using either the
		 * 'wp_footer', or 'admin_footer' hooks. Which are the final hooks that run reliably.
		 *
		 * @param mixed $object : anything to be logged to console
		 * @param bool $include_stack : flag to include/exclude the backtrace so you know where
		 * @return void
		 */
		public static function log( $object, $include_stack = true )
		{
			if ( $include_stack )
			{
				$backtrace = debug_backtrace();
				$object = [
					'stack' => [
						'class' => isset( $backtrace[1]['class'] ) ? $backtrace[1]['class'] : '',
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
		 * @param string  $plugin : Path to the plugin file relative to the plugins directory
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
