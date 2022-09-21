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
		Subscriber::addAction("add_meta_boxes", [$this, 'metabox'] );
        Subscriber::addAction('save_post_' . Layout::LAYOUT_POST_TYPE_NAME, [$this, 'saveMeta'], 10, 3 );
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
        Subscriber::addFilter('devkit/layouts/metabox_json', [$this, 'formatJsonFields'], 999);
        Subscriber::addFilter('devkit/layouts/metabox_json', [$this, 'formatJsonMeta'], 999);
        Subscriber::addFilter('devkit/layouts/meta', [$this, 'setupMeta'], 0);
        Subscriber::addFilter('devkit/layouts/fields/locations', [$this, 'themeSupport'], 5);
        Subscriber::addFilter('admin_body_class', [$this, 'adminBodyClass'] );
    }
	/**
	 * Enqueue all necessary JS and CSS for the admin interface
	 */
	public function enqueueAssets() : void
	{
		if ( $this->currentScreen() === 'devkit-layout' )
		{
			wp_enqueue_script( 'devkit-layouts-admin', DEVKIT_TEMPLATES_URL . 'dist/scripts/admin' . DEVKIT_TEMPLATES_ASSET_PREFIX . '.js', ['wp-i18n', 'wp-element'], DEVKIT_TEMPLATES_VERSION, true );
			wp_enqueue_style( 'devkit-layouts-admin', DEVKIT_TEMPLATES_URL . 'dist/styles/admin' . DEVKIT_TEMPLATES_ASSET_PREFIX . '.css', [], DEVKIT_TEMPLATES_VERSION, 'all' );
			wp_set_script_translations('devkit-layouts-admin', 'my-devkit_layouts');

			$json = sprintf( 'const devkit_metabox_data = %s;', json_encode(apply_filters('devkit/layouts/metabox_json', $this->getMetaboxJson()) ) );
			wp_add_inline_script('devkit-layouts-admin', $json, 'before');
		}
	}
    public function adminBodyClass( string $classes ) : string
    {
        if ($this->currentScreen() === 'devkit-layout' )
        {
            $meta = $this->getMeta( $this->postId() );

            if ( $meta && in_array( $meta['type'], ['partial', 'snippet'] ) )
            {
                $classes .= ' devkit-editor-hidden';
            }
        }
        return $classes;
    }
    /**
     * Get all user roles for user view option
     *
     * @return void
     */
    public function userOptions()
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
    public function taxonomyOption() : array
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
    public function termOptions()
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

            $options[$tax->label] = $temp;
        }

        return $options;
    }
    public function postTypeOptions()
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
    public function postOptions()
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
    public function authorOptions() : array
    {
        $options = [];

        $users = get_users(['who' => 'authors']);

        foreach ($users as $user)
        {
            $options[$user->ID] = $user->data->display_name;
        }
        return $options;
    }

	public function currentScreen()
	{
		if ( ! function_exists( 'get_current_screen' ) )
		{
			return '';
		}

		$screen = get_current_screen();

		return isset( $screen->id ) ? $screen->id : false;
	}

    public function postId()
    {
        global $post;

        if ( is_a($post, 'WP_Post' ) )
        {
            return $post->ID;
        }
        return 0;
    }

	public function metabox() : void
	{
        // Plugin::log( get_option('save_post_debug' ) );
        // Plugin::log(DEVKIT_TEMPLATES_DATBASE_KEY );
		add_meta_box( 'devkit_layouts_options', __( 'Options', 'devkit_layouts' ), [$this, 'renderMetabox'], [Layout::NAME], 'advanced', 'high' );
		add_meta_box( 'devkit_layouts_locations', __( 'Location', 'devkit_layouts' ), [$this, 'renderMetabox'], [Layout::NAME], 'advanced', 'high' );
		add_meta_box( 'devkit_layouts_conditions', __( 'Conditions', 'devkit_layouts' ), [$this, 'renderMetabox'], [Layout::NAME], 'advanced', 'high' );
		add_meta_box( 'devkit_layouts_scripts', __( 'Scripts & Styles', 'devkit_layouts' ), [$this, 'renderMetabox'], [Layout::NAME], 'advanced', 'high' );
	}

	public function renderMetabox( \WP_Post $post, array $args ) : void
	{
		$id = str_replace('devkit_layouts_', '', $args['id'] );

		if ( is_file( DEVKIT_TEMPLATES_PATH . 'template-parts/admin/metaboxes/' . $id . '.twig' ) )
		{
			$content = 'this is the content';
		}

		printf( '<div id="devkit-layouts-metabox-%s" class="devkit-layouts-metabox"></div>', $id, $content ?? '' );
	}

    public function getMetaboxJson()
    {
        $metabox_data =
            [
            'key' => DEVKIT_TEMPLATES_META_KEY,
            'strings' =>
            [
                'add' => __('add', 'devkit_layouts'),
                'remove' => __('remove', 'devkit_layouts'),
                'add group' => __('add group', 'devkit_layouts'),
                'remove group' => __('remove group', 'devkit_layouts'),
                'add condition' => __('add condition', 'devkit_layouts'),
                'remove condition' => __('remove condition', 'devkit_layouts'),
                'add location' => __('add location', 'devkit_layouts'),
                'remove location' => __('remove location', 'devkit_layouts'),
                'shortcode' => sprintf('[devkit_layout id="%s"]', $this->postId() ),
            ],
            'shortcode' => sprintf('[devkit_layout id="%s"]', $this->postId()),
            'fields' =>
            [
                'type' =>
                [
                    'label' => __('Editor Type', 'devkit_layouts'),
                    'type' => 'select',
                    'options' =>
                    [
                        '' => __('Default Editor', 'devkit_layouts'),
                        'snippet' => __('Code Snippet', 'devkit_layouts'),
                        'partial' => __('Template Part', 'devkit_layouts')
                    ]
                ],
                'container' =>
                [
                    'label' => __('Container', 'devkit_layouts'),
                    'type' => 'select',
                    'options' => [
                        'div' => 'div',
                        'section' => 'section',
                        'aside' => 'aside',
                        'header' => 'header',
                        'footer' => 'footer',
                        'span' => 'span',
                        '' => __('None', 'devkit_layouts')
                    ]
                ],
                'partials' =>
                [
                    'label' => __('Template Parts', 'devkit_layouts'),
                    'type' => 'select',
                    'options' => [
                        'author-box' => __( 'Author Box', 'devkit_layouts' ),
                    ]
                ],
                'class' =>
                [
                    'label' => __('Container Class', 'devkit_layouts'),
                    'type' => 'text',
                ],
                'snippet' =>
                [
                    'label' => __('Custom Code Snippet', 'devkit_layouts'),
                    'type' => 'ace',
                ],
                'styles' =>
                [
                    'label' => __('Container', 'devkit_layouts'),
                    'type' => 'ace',
                ],
                'scripts' =>
                [
                    'label' => __('Container', 'devkit_layouts'),
                    'type' => 'ace',
                ],
                'hook' =>
                [
                    'label' => __('Hook', 'devkit_layouts'),
                    'type' => 'select',
                    'options' => apply_filters( 'devkit/layouts/fields/locations', [
                        'core' => [
                            'wp_head' => __('WP Head', 'devkit_layouts'),
                            'wp_footer' => __('WP Footer', 'devkit_layouts'),
                            'the_content' => __('The Content', 'devkit_layouts')
                        ]
                    ] ),
                ],
                'priority' =>
                [
                    'type' => 'number',
                    'label' => __('Priority', 'devkit_layouts')
                ],
                'view' =>
                [
                    'type' => 'select',
                    'label' => __('Views', 'devkit_layouts'),
                    'options' =>
                    [
                        '__return_true' => __('Entire Site', 'devkit_layouts'),
                        'is_front_page' => __('Front Page', 'devkit_layouts'),
                        'is_home' => __('Blog Page', 'devkit_layouts'),
                        'is_404' => __('404', 'devkit_layouts'),
                        'is_search' => __('Search Results', 'devkit_layouts'),
                        'singular' => __('Single', 'devkit_layouts'),
                        'archive' => __('Archives', 'devkit_layouts'),
                        'user' => __('User', 'devkit_layouts'),
                        'custom' => __('Custom', 'devkit_layouts'),
                    ]
                ],
                'singular' =>
                [
                    'type' => 'select',
                    'label' => __('Singular', 'devkit_layouts'),
                    'options' =>
                    [
                        '' => __('All', 'devkit_layouts'),
                        'post_type' => __('Post Type', 'devkit_layouts'),
                        'term' => __('Term', 'devkit_layouts'),
                        'author' => __('Author', 'devkit_layouts'),
                        'posts' => __('Select', 'devkit_layouts'),
                    ]
                ],
                'archive' =>
                [
                    'type' => 'select',
                    'label' => __('Archives', 'devkit_layouts'),
                    'options' =>
                    [
                        '' => __('All', 'devkit_layouts'),
                        'post_type' => __('Post Type', 'devkit_layouts'),
                        'taxonomy' => __( 'Taxonomy', 'devkit_layouts' ),
                        'term' => __('Term', 'devkit_layouts'),
                        'author' => __('Author', 'devkit_layouts'),
                    ]
                ],
                'taxonomy' =>
                [
                    'type' => 'select',
                    'label' => __('Taxonomies', 'devkit_layouts'),
                    'options' => $this->taxonomyOption()
                ],
                'user' =>
                [
                    'type' => 'select',
                    'label' => __('User', 'devkit_layouts'),
                    'options' => $this->userOptions()
                ],
                'term' =>
                [
                    'type' => 'select',
                    'label' => __('Terms', 'devkit_layouts'),
                    'options' => $this->termOptions()
                ],
                'post_type' =>
                [
                    'type' => 'select',
                    'label' => __('Post Type', 'devkit_layouts'),
                    'options' => $this->postTypeOptions()
                ],
                'posts' =>
                [
                    'type' => 'select',
                    'label' => __('Posts', 'devkit_layouts'),
                    'options' => $this->postOptions()
                ],
                'author' =>
                [
                    'type' => 'select',
                    'label' => __('Author', 'devkit_layouts'),
                    'options' => $this->authorOptions()
                ],
                'display' =>
                [
                    'type' => 'select',
                    'label' => __('Show/Hide', 'devkit_layouts'),
                    'options' => [
                        1 => __('Show', 'devkit_layouts'),
                        0 => __('Hide', 'devkit_layouts')
                    ]
                ],
                'comparison' =>
                [
                    'type' => 'select',
                    'label' => '',
                    'options' => [
                        '=' => __('IS', 'devkit_layouts'),
                        '!=' => __('IS NOT', 'devkit_layouts')
                    ]
                ]
            ],
            'meta' => $this->getMeta( $this->postId() )
        ];

        return apply_filters( 'devkit/layouts/metaboxinputs', $metabox_data );
    }
    /**
     * Format field data for our JS to use
     *
     * @param array $data
     * @return array
     */
    public function formatJsonFields( array $data ) : array
    {
        foreach ( $data['fields'] as $index => $field )
        {
            if ( isset($field['type']) && $field['type'] === 'select' )
            {
                $options = [];

                foreach ( $field['options'] as $value => $label )
                {

                    /**
                     * Format optgroups
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
                $data['fields'][$index]['options'] = $options;
            }
        }
        return $data;
    }
    /**
     * Format meta data for our JS to use
     *
     * @param array $data
     * @return array
     */
    public function formatJsonMeta( array $data ) : array
    {
        if ( empty($data['meta']['locations'] ) )
        {
            $data['meta']['locations'][] = [ 'hook' => '', 'priority' => 10 ];
        }
        return $data;
    }
    /**
     * Make sure post_meta has all the necessary indexes
     *
     * @param array $meta
     * @return array
     */
    public function setupMeta(array $meta): array
    {
        /**
         * If empty, we can just return a default meta set
         */
        if ( empty($meta ) )
        {
            return
            [
                'container' => 'div',
                'class' => '',
                'snippet' => '',
                'type' => '',
                'styles' => '',
                'scripts' => '',
                'partial' => '',
                'locations' =>
                [
                    [
                        'hook' => '',
                        'priority' => 10
                    ]
                ],
                'conditions' =>
                [
                    [
                        'display' => 1,
                        'conditions' =>
                        [
                            [
                                'view' => '',
                                'comparison' => '!=',
                                'subtype' => '',
                                'deps' => [],
                                'condition_id' => ''
                            ]
                        ]
                    ]
                ],
            ];
        }
        /**
         * Default base structure
         */
        $default_base = [
            'conditions' => [],
            'container' => 'div',
            'class' => '',
            'locations' => [],
            'snippet' => '',
            'type' => '',
            'partial' => '',
            'styles' => '',
            'scripts' => ''
        ];
        /**
         * Default structure for a single location
         */
        $default_location = [
            'hook' => '',
            'priority' => 10
        ];
        /**
         * Default structure for a group of conditions
         */
        $default_condition_group = [
            'display' => 1,
            'conditions' => []
        ];
        /**
         * Default structure for a single condition
         */
        $default_conditions = [
            'view' => '',
            'comparison' => '!=',
            'subtype' => '',
            'deps' => [],
            'condition_id' => ''
        ];
        /**
         * Merge base
         */
        $meta = wp_parse_args($meta, $default_base);
        /**
         * Merge locations
         */
        foreach ($meta['locations'] as $index => $location) {
            $meta['locations'][$index] = wp_parse_args($location, $default_location);
        }
        /**
         * Merge Conditions
         */
        foreach ($meta['conditions'] as $group => $condition_group) {
            $meta['conditions'][$group] = wp_parse_args($condition_group, $default_condition_group);

            foreach ($meta['conditions'][$group]['conditions'] as $index => $conditions) {
                $meta['conditions'][$group]['conditions'][$index] = wp_parse_args($meta['conditions'][$group]['conditions'][$index], $default_conditions);
            }
        }
        return $meta;
    }
    /**
     * Retrieve meta data for a layout post type
     *
     * @param integer $id
     * @return array
     */
    public function getMeta( int $id ) : array
    {
        $meta = get_post_meta($id, DEVKIT_TEMPLATES_META_KEY, true );
        return apply_filters( 'devkit/layouts/meta', $meta ?: [] );
    }
    /**
     * Save post meta when post is saved
     *
     * @param integer $post_ID
     * @param \WP_Post $post
     * @param boolean $update
     * @return void
     */
    public function saveMeta(int $post_ID, \WP_Post $post, bool $update ) : void
    {
        /**
         * Check user permissions
         */
        $post_type = get_post_type_object( $post->post_type );

        if ( ! current_user_can( $post_type->cap->edit_post, $post_ID ) )
        {
            return;
        }
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
         * Update the post meta
         */
        $meta = $this->setupMeta($_POST[DEVKIT_TEMPLATES_META_KEY] );
        /**
         * Compile SCSS
         */
        $meta['styles'] = $this->compileCss( $meta['styles'], $post_ID );

        update_post_meta($post_ID, DEVKIT_TEMPLATES_META_KEY, $meta );
    }
    /**
     * Compile SCSS into usable CSS
     *
     * @param string $scss
     * @param string $node
     * @return string
     */
    public function compileCss(string $scss = '', $node = '') : string
    {
        if (empty($scss)) {
            return '';
        }

        $css = '';

        if (!empty($node))
        {
            $scss = str_ireplace('$SELECTOR', '#devkit-layout-' . $node, $scss);
            $scss = apply_filters("devkit/layouts/scss/{$node}", $scss);
        }

        $scss = apply_filters('devkit/layouts/scss', $scss);
        try
        {
            $compiler = new Compiler();
            $css = $compiler->compileString($scss)->getCss();
            $autoprefixer = new Autoprefixer($css);
            $css = $autoprefixer->compile();

        } catch (\Exception $e)
        {
            error_log($e->getMessage());
        }
        finally {
            return $css;
        }

        return $css;
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
            $locations = array_merge($locations, $theme_support[0] );
        }

        return $locations;
    }
}