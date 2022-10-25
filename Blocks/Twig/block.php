<?php
/**
 * Register block(s)
 *
 * @class Blocks
 * @package CustomLayouts\Classes
 */

namespace DevKit\Layouts\Blocks\Twig;

use DevKit\Layouts\Base;
use DevKit\Layouts\Plugin;
use DevKit\Layouts\Subscriber;

defined( 'ABSPATH' ) || exit;

class Block extends Base
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
		Subscriber::addAction('init', [$this, 'register']);
	}
	public function register()
	{
		$r = register_block_type( __DIR__,
			[
				'render_callback' => [$this, 'render'],
				'attributes' => [
					'attributes' => [
						'type' => 'string',
						'default' => ''
					],
					'preview' => [
						'type' => 'bool',
						'default' => false
					],
				]
			]
		);
	}
	public function render( $attr, $content ) : string
	{
		ob_start();

		if ( ! $attr['preview'] )
		{
			$scope = [];
//			$scope = apply_filters( "devkit/layouts/template_scope/{$template}", [] );
			Subscriber::getInstance( 'Timber' )->renderString( $attr['value'] ?? '', $scope );

		}
		/**
		 * During the preview, we echo the content directly without interpretting it
		 */
		else
		{
			echo $attr['value'] ?? '';
		}
		return ob_get_clean();
	}
}