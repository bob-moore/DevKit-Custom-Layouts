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
	public function addFilters() : void
	{
		Subscriber::addFilter( 'devkit/layouts/template_scope', [$this, 'setThis'] );
	}

	public function setThis( $_scope )
	{
		$_scope['functions'] = $this;
		return $_scope;
	}
	public static function compileCss( string $scss = '') : string
	{
		if ( empty( $scss ) ) {
			return '';
		}

		try
		{
			$compiler = new Compiler();
			$css = $compiler->compileString(apply_filters( 'devkit/layouts/scss', $scss ))->getCss();
			$autoprefixer = new Autoprefixer($css);
			$css = $autoprefixer->compile();
		}
		catch ( \Exception $e )
		{
//			return '';
		}
		finally
		{
			return $css ?? '';
		}
	}
	/**
	 * Format field data into usable json values
	 *
	 * @param array $data
	 * @return array
	 */
	public static function formatJsonSelect( array $data ) : array
	{
		foreach ( $data as $index => $field )
		{
			/**
			 * If not a select field, we can bail
			 */
			if ( ! isset( $field['type'] ) || $field['type'] !== 'select' )
			{
				continue;
			}
			/**
			 * Format each option
			 */
			$options = [];

			foreach ( $field['options'] as $optval => $optlabel ) {
				/**
				 * If an optgroup, we need to drill down further
				 */
				if ( is_array( $optlabel ) )
				{
					$optgroup = [];

					foreach ( $optlabel as $optgroup_value => $optgroup_value )
					{
						$option = [
							'value' => $optgroup_value,
							'label' => $optgroup_value
						];
						$optgroup[] = $option;
					}

					$options[] = [
						'label' => $optval,
						'options' => $optgroup
					];
				}
				else
				{
					$option = [
						'value' => $optval,
						'label' => $optlabel
					];
					$options[] = $option;
				}
			}
			$data[$index]['options'] = $options;
		}
		return $data;
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

}