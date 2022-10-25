<?php

/**
 * Related Posts
 *
 * @class RelatedPosts
 * @package CustomLayouts\Components
 */

namespace DevKit\Layouts\Components;

use DevKit\Layouts\Base;
use DevKit\Layouts\Subscriber;
use DevKit\Layouts\Plugin;

class RelatedPosts extends Base
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
		Subscriber::addFilter('devkit/layouts/template_parts', [$this, 'addTemplates']);
		Subscriber::addFilter('timber/context', [$this, 'context']);
		Subscriber::addFilter('devkit/layouts/related_posts', [$this, 'contextualRelatedPosts'], 12 );
		Subscriber::addFilter('devkit/layouts/related_posts', [$this, 'jetpack'], 12 );
	}
	/**
	 * Add template partials to select field
	 *
	 * @param array $templates List of template parts
	 * @return $templates
	 */
	public function addTemplates(array $templates) : array
	{
		return array_merge(
			$templates,
			[
				'related-posts-grid' => 'Related Posts - Grid',
				'related-posts-List' => 'Related Posts - List',
			]
		);
	}
	/**
	 * Set related posts in timber context
	 *
	 * @param array $scope
	 * @return $scope
	 */
	public function context(array $scope) : array
	{
		if (! is_singular()) {
			return $scope;
		}

		if (!isset($scope['post'])) {
			$scope['post'] = Subscriber::getInstance('Timber')->getPost(get_the_id());
		}

		if ( isset( $scope['devkit']['related'] ) )
		{
			return $scope;
		}

		$related_ids = apply_filters( 'devkit/layouts/related_posts', [] );

		if ( ! empty( $related_ids ) )
		{
			$query = apply_filters(
				'devkit/layouts/related_posts/query',
				[
					'ignore_sticky_posts' => true,
					'post__in' => $related_ids,
					'orderby' => 'post__in',
					'post_type' =>  'any'
				],
				true
			);
		}
		else
		{
			$query = apply_filters(
				'devkit/layouts/related_posts/query',
				[
				'posts_per_page' => apply_filters( 'devkit/layouts/related_posts/limit', 6 ),
				'ignore_sticky_posts' => true,
				'post_type' => get_post_type(),
				],
				false
			);
		}

		$posts = Subscriber::getInstance('Timber')->getPosts($query);

		$scope['devkit']['related'] = $posts;

		return $scope;
	}
	/**
	 * Add related posts from contextual related posts plugin
	 *
	 * @param array $ids
	 * @return array
	 */
	public function contextualRelatedPosts( array $ids ) : array
	{
		/**
		 * Bail if already been filled by another plugin/theme/user
		 */
		if ( ! empty( $ids ) )
		{
			return $ids;
		}
		/**
		 * Bail if contextual related posts not active
		 */
		if ( ! Plugin::isPluginActive('contextual-related-posts/contextual-related-posts.php') || ! function_exists('get_crp_posts_id' ) )
		{
			return $ids;
		}

		$related_raw = get_crp_posts_id();

		/**
		 * Bail if no found related posts
		 */
		if ( empty( $related_raw ) )
		{
			return $ids;
		}

		$related_ids = [];

		foreach ( $related_raw as $raw_post )
		{
			$related_ids[] = $raw_post->ID;
		}

		return $related_ids;
	}
	/**
	 * Add related posts from jetpack plugin
	 *
	 * @param array $ids
	 * @return array
	 */
	public function jetpack( array $ids ) : array
	{
		/**
		 * Bail if already been filled by another plugin/theme/user
		 */
		 if ( ! empty( $ids ) )
		 {
		 	return $ids;
		 }
		/**
		 * Bail if plugin not active
		 */
		if ( ! Plugin::isPluginActive( 'jetpack/jetpack.php' ) || ! class_exists( 'Jetpack_RelatedPosts' ) )
		{
			return $ids;
		}

		$related = \Jetpack_RelatedPosts::init_raw()
			->set_query_name( 'devkit-related-posts' ) // Optional, name can be anything.
			->get_for_post_id(
				get_the_ID(),
				[ 'size' => apply_filters( 'devkit/layouts/related_posts/limit', 6 ) ]
			);

		if ( ! empty( $related ) )
		{
			foreach ( $related as $rel )
			{
				$ids[] = $rel['id'];
			}
		}

		return $ids;
	}
}
