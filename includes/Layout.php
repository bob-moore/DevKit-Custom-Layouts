<?php
/**
 * Layout Object Class
 *
 * @class Timber
 * @package CustomLayouts\Classes
 */

namespace DevKit\Layouts;

defined('ABSPATH') || exit;

class Layout
{
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
	 * Template part to include (maybe)
	 *
	 * @var string
	 */
	public string $partial = '';
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
	/**
	 * raw meta
	 *
	 * @var string
	 * @access protected
	 */
	protected array $meta = [];

	/**
	 * @param int $id
	 */
	public function __construct( int $id )
	{
		$this->id = $id;

		$this->create();

		return $this;
	}
	/**
	 * Create new layout, with fields ready to be rendered on the frontend
	 *
	 * @return void
	 */
	public function create() : void
	{
		if ( get_post_type( $this->id ) !== DEVKIT_TEMPLATES_POST_TYPE || get_post_status( $this->id ) !== 'publish')
		{
			return;
		}

		$this->meta = Subscriber::getInstance('Meta')->get($this->id);

		foreach ( $this->meta as $key => $value )
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
	protected function setStyles( array $styles )
	{
		$this->styles = $styles['compiled'];
	}
	protected function setScripts( array $scripts )
	{
		$this->scripts = $scripts['compiled'];
	}
}
