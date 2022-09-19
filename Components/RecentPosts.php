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

class RecentPosts extends Base
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
				'recent-posts-grid' => 'Recent Posts - Grid',
				'recent-posts-list' => 'Recent Posts - List',
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
		if ( isset( $scope['devkit']['recent_posts'] ) )
		{
			return $scope;
		}

		$recent_ids = apply_filters( 'devkit/layouts/recent_posts', [] );

		if ( ! empty( $recent_ids ) )
		{
			$query = apply_filters(
				'devkit/layouts/recent_posts/query',
				[
					'ignore_sticky_posts' => true,
					'post__in' => $recent_ids,
					'orderby' => 'post__in',
					'post_type' =>  'any'
				],
				true
			);
		}
		else
		{
			$query = apply_filters(
				'devkit/layouts/recent_posts/query',
				[
				'posts_per_page' => apply_filters( 'devkit/layouts/recent_posts/limit', 5 ),
				'ignore_sticky_posts' => false,
				'post_type' => get_post_type(),
				],
				false
			);
		}

		if ( is_singular() )
		{
			$query['post__not_in'] = [ get_the_id() ];
		}

		$posts = Subscriber::getInstance('Timber')->getPosts($query);

		$scope['devkit']['recent_posts'] = $posts;

		return $scope;
	}
}
