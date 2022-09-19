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

class Authorbox extends Base
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
				'author-box' => 'Author Box',
			]
		);
	}
}