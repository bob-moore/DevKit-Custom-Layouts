<?php
/**
 * Register block(s)
 *
 * @class Blocks
 * @package CustomLayouts\Classes
 */

namespace DevKit\Layouts\Blocks\TemplateParts;

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
		Subscriber::addAction('rest_api_init', [$this, 'restEndpoints']);

	}
	public function register()
	{
		$r = register_block_type( __DIR__,
			[
				'render_callback' => [$this, 'render'],
				'attributes' => [
					'template_part' => [
						'type' => 'object',
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
		$template = $attr['template_part']['value'] ?? false;

		if ( empty( $template ) )
		{
			return $this->defaultContent( '' );
		}

		$scope = apply_filters( "devkit/layouts/template_scope/{$template}", [] );

		ob_start();
			Subscriber::getInstance( 'Timber' )->render( $template, $scope );
		$content = ob_get_clean();

		if ( $attr['preview'] && empty( $content ) )
		{
			$content = $this->defaultContent( $template );
		}
		return $content;
	}

	public function defaultContent( string $template ) : string
	{
		return sprintf( '<div class="template-part-default">%s %s</div>',
			__( 'Preview Not Available', 'devkit_layouts' ),
			get_the_id()
		);
	}
	public function restEndpoints()
	{
		register_rest_route( 'devkit/layouts/v2', '/block/template-parts', [
			'methods' => 'GET',
			'callback' => [$this, 'getFields'],
		] );
	}
	public function getFields( \WP_REST_Request $request )
	{

		$data = [
			'options' => apply_filters( 'devkit/layouts/template_parts',
				[
					[
						'label' => 'Author Box',
						'value' => 'author-box'
					],
					[
						'label' => 'Post Navigation',
						'value' => 'post-navigation'
					]
				]
			)
		];

		return rest_ensure_response( $data );
	}
}