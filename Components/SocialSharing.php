<?php
/**
 * Authorbox component
 *
 * @class Authorbox
 * @package CustomLayouts\Components
 */

namespace DevKit\Layouts\Components;

use DevKit\Layouts\Base;
use DevKit\Layouts\Subscriber;
use DevKit\Layouts\Plugin;

class SocialSharing extends Base
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
		Subscriber::addFilter( 'devkit/layouts/template_parts', [$this, 'addTemplates'] );
		Subscriber::addFilter( 'timber/context', [$this, 'context'] );
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
				'social-sharing-buttons' => 'Social Sharing Buttons',
			]
		);
	}
	public function context( array $scope ) : array
	{
		if ( ! isset( $scope['devkit']['sharing'] ) )
		{
			$scope['devkit']['sharing'] = $this;
		}
		return $scope;
	}
	public function buttons() : string
	{
		ob_start();

		if (Plugin::isPluginActive('social-warfare/social-warfare.php') && function_exists( 'social_warfare' ) )
		{
			social_warfare();
		}
		elseif ( Plugin::isPluginActive( 'jetpack/jetpack.php' ) )
		{
			if ( function_exists( 'sharing_display' ) )
			{
				sharing_display( '', true );
			}

			if ( class_exists( 'Jetpack_Likes' ) )
			{
				$custom_likes = new \Jetpack_Likes;
				echo $custom_likes->post_likes( '' );
			}
		}
		elseif ( current_user_can( 'edit_posts' ) )
		{
			echo __( 'No social sharing plugin enabled. Install social warfare or jetpack sharing' );
		}

		$content = ob_get_clean();

		return apply_filters( 'devkit/layouts/sharing_buttons_display', $content );
	}
}