<?php
/**
 * Admin controller class
 *
 * @class admin
 * @package CustomLayouts\Classes
 */

namespace DevKit\Layouts;

use \Padaliyajay\PHPAutoprefixer\Autoprefixer;
use \ScssPhp\ScssPhp\Compiler;
use \ScssPhp\ScssPhp\Exception\SassException;

defined( 'ABSPATH' ) || exit;

class Admin extends Base
{
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
		Subscriber::addAction( 'admin_enqueue_scripts', [$this, 'enqueueAssets'] );
		Subscriber::addAction("add_meta_boxes", [$this, 'metabox'], 5 );
		Subscriber::addAction( 'admin_menu', [$this, 'submenuPage'], 99 );
		Subscriber::addAction( 'menu_order', [$this, 'reorderAdminMenu'], 999 );
		Subscriber::addAction('manage_' . DEVKIT_TEMPLATES_POST_TYPE . '_posts_custom_column', [$this, 'adminColumnContent'], 10, 2 );
		Subscriber::addAction('save_post_' . DEVKIT_TEMPLATES_POST_TYPE, [$this, 'savePostMeta'], 10, 3 );
	}
	/**
	 * Register filters
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @return void
	 */
	public function addFilters(): void
	{
		Subscriber::addFilter('admin_body_class', [$this, 'adminBodyClass'] );
		Subscriber::addFilter('manage_' . DEVKIT_TEMPLATES_POST_TYPE . '_posts_columns', [$this, 'addAdminColumns'] );
		Subscriber::addFilter('manage_edit-' . DEVKIT_TEMPLATES_POST_TYPE . '_sortable_columns', [$this, 'adminSortableColumns'] );
		Subscriber::addFilter('parent_file', [$this, 'subMenuParent'] );
	}
	/**
	 *  Enqueue all necessary JS and CSS for the admin interface
	 *
	 * @return void
	 */
	public function enqueueAssets() : void
	{
		wp_enqueue_style(
			'devkit-layouts-admin',
			DEVKIT_TEMPLATES_URL . 'dist/styles/admin.css',
			[],
			DEVKIT_TEMPLATES_VERSION,
			'all'
		);

		add_theme_support('editor-styles');
		add_editor_style( DEVKIT_TEMPLATES_URL . 'dist/styles/editor.css' );

		if ( $this->currentScreen() === 'devkit-layout' )
		{
			$assets = include DEVKIT_TEMPLATES_PATH . '/dist/scripts/admin.asset.php';
			wp_enqueue_script(
				'devkit-layouts-admin',
				DEVKIT_TEMPLATES_URL . 'dist/scripts/admin.js',
				$assets['dependencies'],
				$assets['version'],
				true
			);
			wp_set_script_translations(
				'devkit-layouts-admin',
				'devkit_layouts'
			);
			$json_data = sprintf( 'const devkit_post_id = %s;',
				$this->postId()
			);
			wp_add_inline_script('devkit-layouts-admin', $json_data, 'before');
		}
	}

	/**
	 * Add body classes to the post editor screen to show/hide block editor
	 *
	 * @param string $classes
	 * @return string
	 */
	public function adminBodyClass( string $classes ) : string
	{
		if ( $this->currentScreen() === 'devkit-layout' )
		{
			$meta = Subscriber::getInstance('Meta')->get( $this->postId() );

			if ( $meta && in_array( $meta['type'], ['partial', 'snippet'] ) )
			{
				$classes .= ' devkit-editor-hidden';
			}
		}
		return $classes;
	}
	/**
	 * Add submenu page to themes menu for groups taxonomy
	 *
	 * @return void
	 */
	function submenuPage() : void
	{
		add_submenu_page(
			'themes.php',
			__( 'Layout Groups', 'devkit_layouts' ),
			__( 'Layout Groups', 'devkit_layouts' ),
			'manage_categories',
			'edit-tags.php?taxonomy=' . DEVKIT_TEMPLATES_GROUP_TAX,
			null,
			99
		);
	}
	/**
	 * Corrects the parent page of the Layout Group taxonomy
	 *
	 * @param string $parent_file
	 * @return string
	 */
	function subMenuParent(string $parent_file ) : string
	{
		if ( function_exists('get_current_screen') && get_current_screen()->id === 'edit-' . DEVKIT_TEMPLATES_GROUP_TAX )
		{
			return 'themes.php';
		}
		return $parent_file;
	}
	/**
	 * Ensure custom layouts and layout groups are last, and next to one another
	 *
	 * @param  array $menu_items Array of menu items
	 * @return array $menu_items
	 */
	public function reorderAdminMenu( array $menu_items ) : array
	{
		global $submenu;

		$post_type_item = null;

		$tax_item = null;

		foreach ( $submenu['themes.php'] as $index => $sub_menu_item )
		{
			if ( $sub_menu_item[0] === 'DevKit Layouts' )
			{
				$post_type_item = $sub_menu_item;
				unset( $submenu['themes.php'][$index] );
			}
			elseif ( $sub_menu_item[0] === 'Layout Groups' )
			{
				$tax_item = $sub_menu_item;
				unset( $submenu['themes.php'][$index] );
			}
		}

		if ( ! empty( $post_type_item ) )
		{
			$submenu['themes.php'][] = $post_type_item;
		}
		if ( ! empty( $tax_item ) )
		{
			$submenu['themes.php'][] = $tax_item;
		}

		return $menu_items;
	}

	public function currentScreen()
	{
		if ( ! function_exists( 'get_current_screen' ) )
		{
			return '';
		}

		$screen = get_current_screen();

		return $screen->id ?? false;
	}

	public function postId() : int
	{
		global $post;

		if ( is_a( $post, 'WP_Post' ) )
		{
			return $post->ID;
		}
		return 0;
	}

	public function metabox() : void
	{
		add_meta_box( 'devkit_layouts_options', __( 'Container', 'devkit_layouts' ), [$this, 'renderMetabox'], [DEVKIT_TEMPLATES_POST_TYPE], 'side', 'high' );
		add_meta_box( 'devkit_layouts_locations', __( 'Location', 'devkit_layouts' ), [$this, 'renderMetabox'], [DEVKIT_TEMPLATES_POST_TYPE], 'advanced', 'high' );
		add_meta_box( 'devkit_layouts_conditions', __( 'Conditions', 'devkit_layouts' ), [$this, 'renderMetabox'], [DEVKIT_TEMPLATES_POST_TYPE], 'advanced', 'high' );
		add_meta_box( 'devkit_layouts_scripts', __( 'Scripts & Styles', 'devkit_layouts' ), [$this, 'renderMetabox'], [DEVKIT_TEMPLATES_POST_TYPE], 'side', 'high' );
	}

	public function renderMetabox( \WP_Post $post, array $args ) : void
	{
		$id = str_replace('devkit_layouts_', '', $args['id'] );

		if ( is_file( DEVKIT_TEMPLATES_PATH . 'template-parts/admin/metaboxes/' . $id . '.twig' ) )
		{
//			$content = 'this is the content';
		}

		printf( '<div id="devkit-layouts-metabox-%s" class="devkit-layouts-metabox" data-post="%s">%s</div>',
			$id,
			$post->ID,
			$content ?? ''
		);
	}
	/**
	 * Add admin columns to post type screen
	 *
	 * @param array $columns
	 * @return array
	 */
	public function addAdminColumns( array $columns ) : array
	{
		$new_columns = [];
		/**
		 * Reset checkbox
		 */
		if ( isset( $columns['cb'] ) )
		{
			$new_columns['cb'] = $columns['cb'];
		}
		/**
		 * Reset title
		 */
		if ( isset( $columns['title'] ) )
		{
			$new_columns['title'] = $columns['title'];
		}
		/**
		 * Add Locations
		 */
		$new_columns['devkit_locations'] = __( 'Locations', 'devkit_layouts' );
		$new_columns['devkit_type'] = __( 'Type', 'devkit_layouts' );
		/**
		 * Reset Taxonomy Group
		 */
		if ( isset( $columns['taxonomy-devkit-layout-group'] ) )
		{
			$new_columns['taxonomy-devkit-layout-group'] = $columns['taxonomy-devkit-layout-group'];
		}
		/**
		 * Add Shortcode
		 */
		$new_columns['devkit_shortcode'] = __( 'Shortcode', 'devkit_layouts' );

		return array_merge( $new_columns, $columns );
	}

	/**
	 * Make our custom columns sortable
	 * Using placeholder 'default' as `orderby` parameter, since we don't want to use a real one
	 *
	 * @param array $columns
	 * @return array
	 */
	public function adminSortableColumns( array $columns ) : array
	{
		$columns['devkit_locations'] = 'default';
		$columns['devkit_type'] = 'default';
		$columns['taxonomy-devkit-layout-group'] = 'default';
		return $columns;
	}

	/**
	 * Add content to our custom columns
	 *
	 * @param string $column_name
	 * @param int $post_id
	 * @return void
	 */
	public function adminColumnContent( string $column_name, int $post_id ) : void
	{
		if ( $column_name === 'devkit_shortcode' )
		{
			printf( '[devkit_layout id="%d"]', $post_id );
		}
		elseif ( $column_name === 'devkit_locations' )
		{
			$meta = get_post_meta( $post_id, DEVKIT_TEMPLATES_META_KEY, true );

			if ( ! is_array( $meta ) || ! isset( $meta['locations'] ) )
			{
				return;
			}

			$locations = [];

			foreach ( $meta['locations'] as $location )
			{
				$locations[] = $location['hook'];
			}

			echo implode( ', ', $locations );
		}
		if ( $column_name === 'devkit_type' )
		{
			$meta = get_post_meta( $post_id, DEVKIT_TEMPLATES_META_KEY, true );

			if ( ! is_array( $meta ) || ! isset( $meta['type'] ) )
			{
				return;
			}
			switch ($meta['type']) {
				case '' :
					echo __( 'Editor', 'devkit_layouts' );
					break;
				case 'partial' :
					echo __( 'Template-Part', 'devkit_layouts' );
					break;
				case 'snippet' :
					echo __( 'Code Snippet', 'devkit_layouts' );
					break;
				default :
					break;
			}
		}
	}

	public function savePostMeta( int $post_ID, \WP_Post $post, bool $update ) : void
	{
		/**
		 * Check user permissions
		 */
//		$post_type = get_post_type_object( $post->post_type );
//
//		if ( ! current_user_can( $post_type->cap->edit_post, $post_ID ) )
//		{
//			return;
//		}
		/**
		 * Do not save the data if autosave
		 */
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
		{
			return;
		}
		/**
		 * Do not save if we don't have our key
		 */
		if ( ! isset( $_POST[DEVKIT_TEMPLATES_META_KEY] ) )
		{
			return;
		}
		/**
		 * Merge with defaults
		 */
		$meta = Subscriber::getInstance('Meta')->merge( $_POST[DEVKIT_TEMPLATES_META_KEY] );
		/**
		 * Compile SCSS
		 */
		if ( ! empty( $meta['styles']['raw'] ) )
		{
			$scss = str_ireplace('$SELECTOR', '#devkit-layout-' . $post_ID, $meta['styles']['raw'] );
			$scss = apply_filters("devkit/layouts/scss/{$node}", $scss);
			$meta['styles']['compiled'] = Utilities::compileCss( $scss );
		}
		/**
		 * Compile javascript
		 */
		if ( ! empty( $meta['scripts']['raw'] ) )
		{
			$meta['scripts']['compiled'] = str_ireplace('SELECTOR', '"devkit-layout-' . $post_ID . '"', $js);;
		}
		/**
		 * Save post meta
		 */
		update_post_meta($post_ID, DEVKIT_TEMPLATES_META_KEY, $meta );
	}
	public function compileJs(string $js, string $node) : string
	{
		return str_ireplace('SELECTOR', '"devkit-layout-' . $node . '"', $js);
	}
	/**
	 * Compile SCSS into usable CSS
	 *
	 * @param string $scss
	 * @param string $node
	 * @return string
	 */
	public function compileCss(string $scss, string $node) : string
	{
		if (empty($scss)) {
			return '';
		}

		$scss = str_ireplace('$SELECTOR', '#devkit-layout-' . $node, $scss);
		$scss = apply_filters("devkit/layouts/scss/{$node}", $scss);

		return Utilities::compileCss( $scss );
	}
}