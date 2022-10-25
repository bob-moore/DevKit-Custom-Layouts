<?php
/**
 * Post Meta Function
 *
 * @class Blocks
 * @package CustomLayouts\Classes
 */

namespace DevKit\Layouts;

defined( 'ABSPATH' ) || exit;

class Meta extends Base
{
	protected const DEFAULT =
	[
		'container' => 'div',
		'class' => '',
		'snippet' => '',
		'type' => '',
		'partial' => '',
		'styles' =>
		[
			'raw' => '',
			'compiled' => '',
		],
		'scripts' =>
		[
			'raw' => '',
			'compiled' => '',
		],
		'locations' =>
		[
			'hook' => '',
			'priority' => 10
		],
		'conditions' =>
		[
			'display' => 1,
			'context' =>
			[
				'type' => '',
				'comparison' => '=',
				'subtype' => [],
				'id' => '',
				'deps' => [],
			]
		],
	];

	public function merge( array $meta ) : array
	{
		/**
		 * If empty, we can just return a default meta set
		 */
		if ( empty( $meta ) )
		{
			$meta = self::DEFAULT;

			$meta['conditions'] = [];
			$meta['locations'] = [];

			return $meta;
		}
		/**
		 * Merge base
		 */
		$base = self::DEFAULT;
		unset( $base['locations'] );
		unset( $base['conditions'] );
		$meta = $this->backwardsCompat( $meta );
		$meta = wp_parse_args($meta, $base);
		/**
		 * Merge locations
		 */
		if ( isset( $meta['locations'] ) )
		{
			foreach ($meta['locations'] as $index => $location) {
				$meta['locations'][$index] = wp_parse_args( $location, self::DEFAULT['locations'] );
			}
		}
		else {
			$meta['locations'] = [];
		}
		/**
		 * Merge Conditions
		 */
		if ( isset( $meta['conditions'] ) )
		{
			foreach ( $meta['conditions'] as $group_index => $conditions )
			{
				foreach ( $conditions['context'] as $context_index => $context )
				{
					$meta['conditions'][$group_index]['context'][$context_index] = wp_parse_args( $context, self::DEFAULT['conditions']['context'] );
				}
			}
		}
		else {
			$meta['conditions'] = [];
		}
		/**
		 * Merge styles
		 */
		if ( isset( $meta['styles'] ) )
		{
			$meta['styles'] = wp_parse_args( $meta['styles'], self::DEFAULT['styles'] );
		}
		else {
			$meta['styles'] = self::DEFAULT['styles'];
		}
		/**
		 * Merge scripts
		 */
		if ( isset( $meta['scripts'] ) )
		{
			$meta['scripts'] = wp_parse_args( $meta['scripts'], self::DEFAULT['scripts'] );
		}
		else {
			$meta['scripts'] = self::DEFAULT['scripts'];
		}
		return $meta;
	}
	public function get( int $id ) : array
	{
		$meta = get_post_meta($id, DEVKIT_TEMPLATES_META_KEY, true );

		return $this->merge( $meta ?: [] );
	}
	protected function backwardsCompat( $meta )
	{
		if ( isset( $meta['conditions'] ) )
		{
			foreach ( $meta['conditions'] as $group_index => $condition )
			{
				if ( isset( $meta['conditions'][$group_index]['conditions'] ) )
				{
					$meta['conditions'][$group_index]['context'] = $meta['conditions'][$group_index]['conditions'];

					unset( $meta['conditions'][$group_index]['conditions'] );
				}

				foreach ( $meta['conditions'][$group_index]['context'] as $context_index => $context )
				{
					if ( isset( $meta['conditions'][$group_index]['context'][$context_index]['view'] ) )
					{
						if ( ! isset( $meta['conditions'][$group_index]['context'][$context_index]['type'] ) )
						{
							$meta['conditions'][$group_index]['context'][$context_index]['type'] = $meta['conditions'][$group_index]['context'][$context_index]['view'];
						}
						unset( $meta['conditions'][$group_index]['context'][$context_index]['view'] );
					}
				}
			}
		}
		return $meta;
	}
}