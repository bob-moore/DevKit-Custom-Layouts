<?php
/**
 * Register block(s)
 *
 * @class Blocks
 * @package CustomLayouts\Classes
 */

namespace DevKit\Layouts;

defined( 'ABSPATH' ) || exit;

class Rest extends Base
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
		Subscriber::addAction('rest_api_init', [$this, 'registerRestEndpoints']);
	}
	public function registerRestEndpoints()
	{
		register_rest_route( 'devkit/layouts/v2', '/metabox/(?P<id>[a-zA-Z0-9-]+)', [
			'methods' => 'GET',
			'callback' => [$this, 'getMetaboxData'],
		] );
	}

	public function getMetaboxData( \WP_REST_Request $request )
	{
		$id = $request->get_param( 'id' );

		$meta = array_merge( Subscriber::getInstance( 'Meta' )->get( $id ), [ 'key' => DEVKIT_TEMPLATES_META_KEY ] );

		$fields = apply_filters( 'devkit/layouts/metabox/json', Subscriber::getInstance( 'Fields' )->get() );

		$response = [
			'meta' => $meta,
			'fields' => $fields,
		];

		return rest_ensure_response( $response );
	}

}