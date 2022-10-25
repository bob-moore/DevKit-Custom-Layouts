<?php
/**
 * Conditions Controller
 *
 * Manage when layouts are displayed
 *
 * @class Conditions
 * @package Layouts/Controllers
 */

namespace DevKit\Layouts;

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

//			Plugin::log($display);
			/**
			 * If we already have a valid display group, we can skip validating additional groups. This is because
			 * after 1 valid group, it should be displayed
			 */
			if ( intval( $condition['display'] ) && $display === true )
			{
				continue;
			}
			/**
			 * Validate the condition
			 */
			if ( $this->validateGroup($condition['context']) )
			{
				/**
				 * We only need to validate the first of a type
				 * We don't want later invalid rules to invalidate this group
				 */
				if ( intval( $condition['display'] ) && ! $display )
				{
					$display = true;
				}
				/**
				 * The first true rule to hide, we can break. We don't need
				 * to look anymore because it's already hidden
				 */
				elseif( ! intval($condition['display']) && ! $hide )
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
			$condition_met = false;

			/**
			 * Don't waste time on empty conditions
			 */
			if ( empty( $condition['type'] ) )
			{
				continue;
			}
			/**
			 * Check for direct function call
			 */
			if ( function_exists( $condition['type'] ) )
			{
				$condition_met = call_user_func( $condition['type'] );
			}
			/**
			 * Check for matching class method
			 */
			elseif ( method_exists( $this, $condition['type'] ) )
			{
				$condition_met = call_user_func( [ $this, $condition['type'] ], $condition );
			}
			/**
			 * Do "other" - using switch to leave room for future validation types
			 */
			else {
				switch ( $condition['type'] )
				{
					case 'post_type' :
						$condition_met = $this->postType( $condition );
						break;
					default :
						break;
				}
			}
			/**
			 * Check for `is` vs `is not` comparison
			 */
			if ( $condition_met && $condition['type'] === 'schedule' )
			{
				++$valid_conditions;
			}
			elseif ( $condition_met && $condition['subtype'] === '=' )
			{
				++$valid_conditions;
			}
			elseif ( ! $condition_met && $condition['subtype'] === '!=' )
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
	 * Validate view conditions (is_singular, is_404, etc)
	 *
	 * @param array $condition
	 * @return bool
	 */
	public function view( array $condition ) : bool
	{
		foreach ( $condition['deps'] as $view )
		{
			if ( function_exists( $view ) && call_user_func( $view ) )
			{
				$condition_met = true;
				break;
			}
			else {
				$condition_met = apply_filters( "devkit/layouts/condition/view/{$view}", false, $condition );
				if ( $condition_met ) {
					break;
				}
			}
		}
		return apply_filters('devkit/layouts/condition/view', $condition_met ?? false, $condition);
	}
	/**
	 * Validate post type conditions
	 *
	 * Is valid if
	 *  - Singular any of the specified post types
	 *  - Archive for any of the specified post types
	 *
	 * @param array $condition
	 * @return bool
	 */
	public function postType( array $condition ) : bool
	{
		if ( empty( $condition['deps'] ) )
		{
			$condition_met = false;
		}
		else {
			$condition_met = is_singular($condition['deps']) || is_post_type_archive($condition['deps']);
		}
		return apply_filters('devkit/layouts/condition/post_type', $condition_met ?? false, $condition);
	}
	/**
	 * Validate post type conditions
	 *
	 * Is valid if
	 *  - Singular has any of the specified terms
	 *  - Archive for any of the specified terms
	 *
	 * @param array $condition
	 * @return bool
	 */
	public function term( array $condition ) : bool
	{
		foreach ( $condition['deps'] as $term_id )
		{
			$term = get_term( $term_id );

			if ( is_singular() && has_term( $term->term_taxonomy_id, $term->taxonomy, get_the_id() ) )
			{
				$condition_met = true;
				break;
			}
			elseif ( $term->taxonomy === 'category' && is_category( $term->term_taxonomy_id ) )
			{
				$condition_met = true;
				break;
			}
			elseif ( $term->taxonomy === 'post_tag' && is_tag( $term->term_taxonomy_id ) )
			{
				$condition_met = true;
				break;
			}
			elseif ( is_tax( $term->term_taxonomy_id, $term->taxonomy ) )
			{
				$condition_met = true;
				break;
			}
		}
		return apply_filters('devkit/layouts/condition/term', $condition_met ?? false, $condition);
	}
	/**
	 * Validate user roles
	 *
	 * Is valid if
	 *  - Function exists (is_user_logged_in, etc) and function is true
	 *  - User has any of the specified user roles
	 *
	 * @param array $condition
	 * @return bool
	 */
	public function user( array $condition ) : bool
	{
		foreach ( $condition['deps'] as $role )
		{
			if ( function_exists( $role ) && call_user_func( $role ) )
			{
				$condition_met = true;
				break;
			}
			else {
				$user = wp_get_current_user();
				if ( in_array( $role, $user->roles ) )
				{
					$condition_met = true;
					break;
				}
			}
		}
		return apply_filters('devkit/layouts/condition/user', $condition_met ?? false, $condition);
	}
	/**
	 * Validate author conditions
	 *
	 * Is valid if
	 *  - Singular has any of the specified authors
	 *  - Archive for any of the specified authors
	 *
	 * @param array $condition
	 * @return bool
	 */
	public function author( array $condition ) : bool
	{
		if ( ! empty( $condition['deps'] ) )
		{
			if ( is_singular() && in_array( get_post_field('post_author', get_the_id() ), $condition['deps'] ) )
			{
				$condition_met = true;
			}
			elseif ( is_author( $condition['deps'] ) )
			{
				$condition_met = true;
			}
		}
		return apply_filters('devkit/layouts/condition/author', $condition_met ?? false, $condition);
	}
	/**
	 * Validate schedule conditions
	 *
	 * Is valid if
	 *  - Specified date is passed, and subtype is "start"
	 *  - Specified date is not yet reached, and subtype is "end"
	 *
	 * @param array $condition
	 * @return bool
	 */
	public function schedule( array $condition ) : bool
	{
		if ( ! empty( $condition['deps'] ) )
		{
			/**
			 * Linux time of current date/time
			 * @var int
			 */
			$now = strtotime( wp_date( 'm/d/Y h:i:s a' ) );
			/**
			 * Linux time of condition date/time
			 * @var int
			 */
			$scheduled = strtotime( $condition['deps'] );
			/**
			 * Linux time of the difference between $condition and $now
			 * @var int
			 */
			$diff = $scheduled - $now;

			if ( $diff <= 0 ) // Time has passed
			{
				$condition_met = $condition['subtype'] === 'start';
			}
			else {
				$condition_met = $condition['subtype'] === 'end';
			}
		}
		return apply_filters('devkit/layouts/condition/schedule', $condition_met ?? false, $condition);
	}
	/**
	 * Validate specific posts
	 *
	 * Is valid if current view is singular, and in the specified post id's
	 *
	 * @param array $condition
	 * @return bool
	 */
	public function posts( array $condition ) : bool
	{
		$condition_met = is_singular() && in_array( get_the_id(), $condition['deps'] );
		return apply_filters('devkit/layouts/condition/posts', $condition_met ?? false, $condition);
	}
	/**
	 * Validate custom conditions
	 *
	 * Assumes false, valid if user manually validates using provided filters
	 *
	 * @param  array $condition
	 * @return bool
	 */
	public function custom( array $condition ) : bool
	{
		/**
		 * Generic custom filter to validate all custom rules
		 */
		$condition_met = apply_filters( 'devkit/layouts/condition/custom', false, $condition );
		/**
		 * Specific function to validate based on custom rule ID set in metabox
		 */
		return apply_filters( "devkit/layouts/condition/{$condition['id']}", $condition_met, $condition );
	}
}