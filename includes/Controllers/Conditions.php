<?php
/**
 * Conditions Controller
 *
 * Manage when layouts are displayed
 *
 * @class Conditions
 * @package Layouts/Controllers
 */

namespace DevKit\Layouts\Controllers;

use \DevKit\Layouts\Plugin;
use \DevKit\Layouts\Base;

defined( 'ABSPATH' ) || exit;

class Conditions extends Base
{
	/**
	 * Determine if all conditions are met to display a layout
	 *
	 * Validate all condition groups with an OR relationship
	 *
	 * @param [type] $layout
	 * @return boolean
	 */
	public function shouldDisplay( $layout ) : bool
	{
		/**
		 * Flag if display conditions are met
		 */
		$display = false;
		/**
		 * Flag if hidden conditions are met
		 */
		$hide = false;

		foreach ( $layout->conditions as $condition )
		{
			/**
			 * If we already have a valid display group, we can skip
			 * validating additionals
			 */
			if (intval($condition['display']) && $display === true )
			{
				continue;
			}
			/**
			 * Validate the condition
			 */
			if ( $this->validateGroup($condition['conditions']) )
			{
				/**
				 * We only need to validate the first of a type
				 * We dont' want later invalid rules to invalidate this group
				 */
				if ( intval($condition['display'] ) && ! $display )
				{
					$display = true;
				}
				/**
				 * The first true rule to hide, we can break. We don't need
				 * to look anymore because it's already hidden
				 */
				elseif(! intval($condition['display']) && ! $hide )
				{
					$hide = true;
					break;
				}
			}
		}
		$should_display = $display === true && $hide === false;
		return apply_filters( 'devkit/layouts/condition/display', $should_display, $layout->conditions );
	}
	/**
	 * Validate a set of conditions with an AND relationship
	 *
	 * @param array $group
	 * @return boolean
	 */
	public function validateGroup( array $group ) : bool
	{
		/**
		 * Tracker of how many conditions in group are met
		 */
		$valid_conditions = 0;

		foreach ( $group as $condition )
		{
			$valid = false;
			/**
			 * Don't waste time on empty conditions
			 */
			if ( empty( $condition['view'] ) )
			{
				continue;
			}
			/**
			 * Setup method name
			 */
			$method = 'condition' . ucfirst($condition['view']);
			/**
			 * Maybe call from class method
			 */
			if (method_exists($this, $method))
			{
				$condition_met = call_user_func([$this, $method], $condition);
			}
			/**
			 * Maybe call from inbuilt
			 */
			elseif (function_exists($condition['view']))
			{
				$condition_met = call_user_func($condition['view']);
			}

			if ( $condition_met && $condition['comparison'] === '=' )
			{
				++$valid_conditions;
			}
			elseif ($condition['comparison'] === '!=' )
			{
				++$valid_conditions;
			}
		}
		/**
		 * Valid if all conditions in group are true
		 */
		return $valid_conditions >= count($group);
	}
	/**
	 * Determine if custom conditions are met
	 *
	 * @param  array $condition
	 * @return bool
	 */
	public function conditionCustom( array $condition ) : bool
	{
		/**
		 * Generic custom filter to validate all custom rules
		 */
		$condition_met = apply_filters( 'devkit/layouts/condition/custom', false, $condition );
		/**
		 * Specific function to validate based on custom rule ID set in metabox
		 */
		return apply_filters( "devkit/layouts/condition/{$condition['condition_id']}", $condition_met, $condition );
	}
	/**
	 * Validate singular view conditions
	 *
	 * @param array $condition
	 * @return boolean
	 */
	public function conditionSingular( array $condition ) : bool
	{
		if ( is_singular() )
		{
			switch ($condition['subtype'])
			{
				case '':
					$condition_met = true;
					break;
				case 'post_type':
					$condition_met = in_array( get_post_type(), $condition['deps']);
					break;
				case 'term':
					$condition_met = has_term( $condition['deps'], '', get_the_id() );
					break;
				case 'author':
					$condition_met = in_array( get_post_field('post_author', get_the_id() ), $condition['deps']);
					break;
				case 'posts':
					$condition_met = in_array( get_the_id(), $condition['deps'] );
					break;
				default:
					$condition_met = false;
					break;
			}
		}
		return apply_filters('devkit/validation/singular', $condition_met ?? false, $condition);
	}
	/**
	 * Validate archive view conditions
	 *
	 * @param array $condition
	 * @return boolean
	 */
	public function conditionArchive(array $condition) : bool
	{
		if ( is_archive() )
		{
			switch ($condition['subtype']) {
				case '':
					$condition_met = true;
					break;
				case 'post_type':
					$condition_met = is_post_type_archive($condition['deps']);
					break;
				case 'term':
					foreach ( $condition['deps'] as $term_id )
					{
						$term = get_term($term_id);
						/**
						 * Check categories
						 */
						if ( $term->taxonomy === 'category' )
						{
							if (is_category($term['id'])) {
								$condition_met = true;
								break;
							}
						}
						/**
						 * Check tags
						 */
						elseif ($term->taxonomy === 'post_tag') {
							if (is_tag($term['id'])) {
								$condition_met = true;
								break;
							}
						}
						/**
						 * Check everything else
						 */
						elseif ( is_tax( $term_id, $term->taxonomy ) ) {
							$condition_met = true;
							break;
						}
					}
					break;
				case 'taxonomy':
					// $condition_met = is_author($condition['deps']);
					break;
				case 'author':
					$condition_met = is_author($condition['deps']);
					break;
				default:
					$condition_met = false;
					break;
			}
		}
		return apply_filters('devkit/validation/archive', $condition_met ?? false, $condition);
	}
	/**
	 * Validate 'user' conditions
	 *
	 * @param array $condition
	 * @return boolean
	 */
	public function conditionUser(array $condition ) : bool
	{
		if ( function_exists( $condition['subtype'] ) )
		{
			$condition_met = call_user_func($condition['subtype']);
		}
		else {
			$user = wp_get_current_user();
			$condition_met = in_array($condition['subtype'], $user->roles);
		}
		return apply_filters('devkit/validation/user', $condition_met ?? false, $condition);
	}
}