<?php
/**
 * Register block(s)
 *
 * @class Blocks
 * @package CustomLayouts\Classes
 */

namespace DevKit\Layouts;

defined( 'ABSPATH' ) || exit;

class Blocks extends Base
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
//		Subscriber::addAction('init', [$this, 'registerBlocks']);
//		Subscriber::addAction('rest_api_init', [$this, 'registerRestEndpoints']);

	}
	public function registerBlocks()
	{
//		register_block_type( DEVKIT_TEMPLATES_PATH . 'Blocks/twig',
//			[
//				'render_callback' => [$this, 'renderCodeBlock'],
//				'attributes' => [
//					'value' => [
//						'type' => 'string',
//						'default' => ''
//					],
//					'preview' => [
//						'type' => 'bool',
//						'default' => false
//					]
//				]
//			]
//		);
		$reg = register_block_type( DEVKIT_TEMPLATES_PATH . 'Blocks/template-parts',
			[
				'render_callback' => [$this, 'renderTemplateBlock'],
				'attributes' => [
					'value' => [
						'type' => 'string',
						'default' => ''
					],
					'preview' => [
						'type' => 'bool',
						'default' => false
					],
					'options' => [
						'type' => 'array',
						'default' => [
							[
								'label' => 'Post Navigation',
								'value' => 'post-navigation'
							],
							[
								'label' => 'Related Posts',
								'value' => 'related-posts'
							]
						]
					]
				]
			]
		);
	}
	public function renderCodeBlock( $attr, $content )
	{
		ob_start();
		if ( $attr['preview'] )
		{
			echo $attr['value'];
		}
		else {
			Subscriber::getInstance( 'Timber' )->renderString( $attr['value'] );
		}
		return ob_get_clean();
	}
	public function renderTemplateBlock( $attr, $content )
	{
		return '';
	}
}