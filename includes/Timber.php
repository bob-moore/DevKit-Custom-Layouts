<?php
/**
 * Wrapper for Timber
 *
 * @class Timber
 * @package CustomLayouts\Classes
 */

namespace DevKit\Layouts;

defined( 'ABSPATH' ) || exit;

class Timber extends Base
{
	/**
	 * Scope (context)
	 *
	 * @var array
	 * @access protected
	 */
	protected array $_scope = [];
	/**
	 * Timber instance
	 *
	 * @var \Timber\Timber
	 * @access protected
	 */
	protected $timber;
	/**
	 * Locations to look for template files
	 *
	 * @var array
	 * @access protected
	 */
	protected array $_locations = [];
	/**
	 * Constructor
	 *
	 * Instantiate timber
	 */
	public function __construct()
	{
		parent::__construct();

        $this->timber = new \Timber\Timber();

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
		Subscriber::addFilter('timber/locations', [$this, 'timberLocations'] );
	}
	/**
	 * Add plugin location to timber
	 *
	 * @param  array  $locations Location of template parts from this plugin
	 */
	public function timberLocations( array $locations ) : array
	{
        if (empty($this->_locations))
        {
            $this->_locations =  wp_parse_args(
                [
                    DEVKIT_TEMPLATES_PATH . 'template-parts/'
                ],
                $locations
            );
        }

		return wp_parse_args($this->_locations, $locations);
	}
	/**
	 * Get the scope, either from cache or from Timber directly
	 */
	public function scope() : array
	{
		return apply_filters( 'devkit/layouts/scope', $this->timber::context() );
	}
	/**
	 * Render a frontend template
	 *
	 * @param  array  $template name of template to render
	 * @param  array  $data     data to merge with $_scope
	 */
	public function render( string $template, array $data = [] ) : void
	{

		$templates = apply_filters( "devkit/layouts/templates/{$template}", [ $template ] );

		$found = '';

		$include = false;

        if ( empty($this->_locations ) )
        {
            \Timber\LocationManager::get_locations();
        }

		foreach ( $this->_locations as $location )
		{
			foreach ( $templates as $template )
			{
				$extension = pathinfo( $template, PATHINFO_EXTENSION );
				/**
				 * If already has a file extension
				 */
				if ( ! empty( $extension ) && is_file( $location . '/' . $template ) )
				{
					$found = $location . '/' . $template;
					break 2;
				}
				/**
				 * Else search for twig and php files
				 */
				else {

					foreach( [ '.twig', '.php' ] as $extension )
					{
						if ( is_file( $location . '/' . $template . $extension ) ) {
							$found = $location . '/' . $template . $extension;
							break 3;
						}
					}
				}
			}
		}

		/**
		 * Bail if no file found
		 */
		if ( empty( $found ) )
		{
			return;
		}

		$scope = $this->scope();
		/**
		 * Merge data
		 */
		if ( ! empty( $data ) )
		{
			$scope = wp_parse_args( $data, $scope );
		}
		/**
		 * Maybe render with twig
		 */
		if ( in_array( pathinfo( $found, PATHINFO_EXTENSION ), [ 'twig', 'html' ] ) )
		{
			$this->timber::render( $found, $scope );
		}
		/**
		 * Maybe include PHP
		 */
		else {
			include $found;
		}
	}
	/**
	 * Render a string using timber
	 *
	 * @param string $string HTML/TWIG string to be rendered by timber
	 * @return void
	 */
	public function renderString( string $string )
	{

		$_scope = $this->scope();

		if ( ! empty( $data ) )
		{
			$_scope = wp_parse_args( $data, $_scope );
		}

		$this->timber::render_string( $string, $_scope );
	}

	public function getPosts( $args = [] )
	{
		return new \Timber\PostQuery( $args );
	}

	public function getPost( int $id = 0 )
	{
		return $this->timber::get_post( $id );
	}
}