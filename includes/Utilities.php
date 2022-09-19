<?php
/**
 * Generic helper utilities
 *
 * @class Utilities
 * @package CustomLayouts\Classes
 */

namespace DevKit\Layouts;

use Padaliyajay\PHPAutoprefixer\Autoprefixer;
use ScssPhp\ScssPhp\Compiler;

defined( 'ABSPATH' ) || exit;

class Utilities extends Base
{
	/**
	 * Register filters
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @return void
	 */
	public function addFilters() {
		Subscriber::addFilter( 'devkit/layouts/template_scope', [$this, 'setThis'] );
		// Subscriber::addFilter( 'template_include', [$this, 'filterTemplate'] );
	}

	public function setThis( $_scope )
	{
		$_scope['functions'] = $this;
		return $_scope;
	}
	public function compileCss( string $scss = '', $node = '' ) : string
	{
		if ( empty( $scss ) ) {
			return '';
		}

		$css = '';

		if ( ! empty( $node ) )
		{
			$scss = str_ireplace( '$SELECTOR', '#custom-layout-' . $node, $scss );

			$scss = apply_filters( "devkit/layouts/scss/{$node}", $scss );
		}

		$scss = apply_filters( 'devkit/layouts/scss', $scss );

		try
		{
			$compiler = new Compiler();
			$css = $compiler->compile( $scss );
			$autoprefixer = new Autoprefixer( $css );
			$css = $autoprefixer->compile();
		}
		catch ( \Exception $e )
		{
			return '';
		}

		return $css;
	}
	/**
	 * Global callback to run wp functions
	 */
	public function __call( $function, $args )
	{
		if ( function_exists( $function ) ) {
			ob_start();

			$output = call_user_func_array( $function, $args );

			$output = ob_get_length() ? ob_get_clean() : $output;

			return $output;

		}
	}
	// public function filterTemplate($template) {
	// 	return $template;
	// }
}