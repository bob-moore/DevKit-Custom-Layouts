<?php
/**
 * Post Navigation component
 *
 * @class PostNavigation
 * @package CustomLayouts\Components
 */

namespace DevKit\Layouts\Components;

use DevKit\Layouts\Base;
use DevKit\Layouts\Subscriber;

use \Carbon_Fields\Field;

class PostNavigation extends Base {
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
		Subscriber::addFilter( 'devkit/layouts/template_parts', [$this, 'addTemplates'] );
		Subscriber::addFilter( 'timber/context', [ $this, 'context' ], 14 );
	}
	/**
	 * Add template partials to select field
	 *
	 * @param array $templates List of template parts
	 * @return $templates
	 */
	public function addTemplates( array $templates ) : array
	{
		return array_merge(
			$templates,
			[
				'post-navigation' => 'Post Navigation',
			]
		);
	}

	public function context( array $scope ) : array
	{
		if ( ! is_singular() )
		{
			return $scope;
		}

		if ( ! isset( $scope['post'] ) )
		{
			$scope['post'] = Subscriber::getInstance( 'Timber' )->getPost( get_the_id() );
		}

		$default_args = [
			'in_same_term' => false,
			'excluded_terms' => '',
			'taxonomy' => 'category',
		];

		$args = wp_parse_args(
			apply_filters( 'devkit/layouts/adjacent_posts/args', $default_args, $scope['post'] ),
			$default_args
		);

		$next = apply_filters( 'devkit/layouts/next_post', get_next_post( $args['in_same_term'], $args['excluded_terms'], $args['taxonomy'] ), $scope['post'], $args );

		$prev = apply_filters( 'devkit/layouts/prev_post', get_previous_post( $args['in_same_term'], $args['excluded_terms'], $args['taxonomy'] ), $scope['post'], $args );

		$adj = [];

		if ( $prev )
		{
			$adj['prev'] = Subscriber::getInstance( 'Timber' )->getPost( $prev->ID );

			$atts = apply_filters( 'devkit/layouts/atts/prev_post_link', [
				'icon' => 'wpcl-icon wpcl-icon-arrow_back',
				'prefix' => __( 'Previous', 'devkit_layouts' ),
			] );

			foreach ( $atts as $name => $value )
			{
				$adj['prev']->{$name} = $value;
			}
		}

		if ( $next )
		{
			$adj['next'] = Subscriber::getInstance( 'Timber' )->getPost( $next->ID );

			$atts = apply_filters( 'devkit/layouts/atts/next_post_link', [
				'icon' => 'wpcl-icon wpcl-icon-arrow_forward',
				'prefix' => __( 'Next', 'devkit_layouts' ),
			] );

			foreach ( $atts as $name => $value )
			{
				$adj['next']->{$name} = $value;
			}
		}

		$scope['devkit']['adjacent'] = $adj;

		return $scope;
	}
}
