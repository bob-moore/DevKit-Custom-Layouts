<?php
/**
 * Frontend controller class
 *
 * Organize and display template parts
 *
 * @class FrontEnd
 * @package CustomLayouts\Classes
 */
namespace DevKit\Layouts;

use \DevKit\Layouts\Classes;

defined( 'ABSPATH' ) || exit;

class FrontEnd extends Base
{
	/**
	 * Javascript to push into the footer
	 *
	 * @var string
	 * @access protected
	 */
	protected string $_js = '';
	/**
	 * CSS to push into the footer
	 *
	 * @var string
	 * @access protected
	 */
	protected string $_css = '';
	/**
	 * Queue of layouts to display on this page
	 *
	 * @var array
	 * @access protected
	 */
	protected array $_queue = [];
	/**
	 * Register actions
	 *
	 * Uses the subscriber class to ensure only actions of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @return void
	 * @see  https://developer.wordpress.org/reference/functions/add_action/
	 */
	public function addActions() : void
	{
		Subscriber::addAction( 'wp_enqueue_scripts', [$this, 'enqueueAssets'] );
		Subscriber::addAction('wp', [$this, 'queueCssJs'], 100);
		Subscriber::addAction('devkit/layouts/before_render', [$this, 'container'], 1);
		Subscriber::addAction('devkit/layouts/after_render', [$this, 'container'], 20);
	}
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
		Subscriber::addFilter('devkit/layouts/the_content', 'do_blocks');
		Subscriber::addFilter('devkit/layouts/the_content', 'wptexturize');
		Subscriber::addFilter('devkit/layouts/the_content', 'convert_smilies');
		Subscriber::addFilter('devkit/layouts/the_content', 'convert_chars');
		Subscriber::addFilter('devkit/layouts/the_content', 'shortcode_unautop');
		Subscriber::addFilter('devkit/layouts/the_content', 'do_shortcode');
		Subscriber::addFilter('devkit/layouts/the_content', 'wp_make_content_images_responsive');
		Subscriber::addFilter('devkit/layouts/the_content', 'prepend_attachment');
	}
	/**
	 * Register shortcodes
	 *
	 * Uses the subscriber class to ensure only shortcodes of this instance are added
	 * and the instance can be referenced via subscriber
	 *
	 * @return void
	 */
	public function addShortcodes() : void
	{
		add_shortcode( 'devkit_layout', [$this, 'shortcode'] );
	}
	public function enqueueAssets() : void
	{
		$assets = include DEVKIT_TEMPLATES_PATH . '/dist/scripts/frontend.asset.php';

		wp_enqueue_script(
			'devkit-layouts-frontend',
			DEVKIT_TEMPLATES_URL . '/dist/scripts/frontend.js',
			$assets['dependencies'],
			$assets['version'],
			true
		);

		if ( ! empty( $this->_js ) )
		{
			wp_add_inline_script('devkit-layouts-frontend', $this->_js );
		}

		wp_enqueue_style(
			'devkit-layouts-frontend',
			DEVKIT_TEMPLATES_URL . '/dist/styles/frontend.css',
			[],
			DEVKIT_TEMPLATES_VERSION,
			'all');

		if ( ! empty($this->_css ) )
		{
			wp_add_inline_style('devkit-layouts-frontend', $this->_css );
		}
	}

	public function queueCssJs()
	{
		$queue = Subscriber::getInstance('Locations')->getQueued();

		if ( ! $queue )
		{
			return;
		}

		foreach ( $queue as $location )
		{
			foreach ( $location as $layout )
			{
				/**
				 * Add Styles
				 */
				if ( ! empty( $layout->styles ) )
				{
					if ( stripos($layout->styles, '</style>') !== false )
					{
						$this->_css .= trim( preg_replace('#<style[^>]*>(.*)</style>#is', '$1', $layout->styles ) );
					}
					else
					{
						$this->_css .= trim($layout->styles);
					}
				}
				/**
				 * Add Scripts
				 */
				if ( ! empty( $layout->scripts ) )
				{
					if ( stripos( $layout->scripts, '</script>' ) !== false )
					{
						$this->_js .= trim(preg_replace( '#<script[^>]*>(.*)</script>#is', '$1', $layout->styles ) );
					}
					else
					{
						$this->_js .= trim($layout->scripts);
					}
				}
			}
		}
	}
	/**
	 * Global callback to run wp functions
	 *
	 * Allows us to use undefined functions on hooks, instead of closures. This
	 * allows us to also do `remove_action`, since the name gets defined but the function
	 * doesn't.
	 *
	 * @param string $function Name of the called function
	 * @param array $args function arguments passed
	 * @return void/string Return string if on `the_content` hook
	 */
	public function __call( string $function, $args )
	{
		/**
		 * Get and check the name of what was called
		 *
		 * If not calling a specific render function, along with an ID, bail
		 * @var array
		 */
		$caller = self::getIdFromName($function);

		if ($caller[0] !== 'devkit/layouts/render' || !is_numeric($caller[1])) {
			return;
		}

		$current_action = current_action();

		$layout = Subscriber::getInstance( 'Locations' )->getQueued( $current_action, $caller[1] );

		if ($current_action === 'the_content')
		{
			$priority = intval( $layout->locations['the_content']['priority'] );

			ob_start();

			$this->render($layout);

			$output = ob_get_clean();

			if (intval($priority) <= 5) {
				$final = $output . $args[0];
			} else {
				$final = $args[0] . $output;
			}
			return $final;
		} else {
			$this->render($layout);
		}
	}
	/**
	 * Render frontend output
	 *
	 * @param object $layout
	 * @return void
	 */
	public function render( object $layout ) : void
	{
		/**
		 * Do not render at location during an edit
		 */
		if (get_the_id() === $layout->id) {
			return;
		}

		do_action('devkit/layouts/before_render', $layout);

		switch ($layout->type)
		{
			case 'snippet':
				Subscriber::getInstance('Timber')->renderString( trim( $layout->snippet ) );
				break;
			case 'partial':
				Subscriber::getInstance('Timber')->render( $layout->partial );
				break;
			default:
				/**
				 * Allow other builders (beaver builder, elementor, etc.) to short circuit with
				 * their own content
				 */
				$content = apply_filters('devkit/layouts/content', '', $layout);
				if ( ! empty( $content ) )
				{
					echo $content;
				}
				/**
				 * Default echo the content
				 */
				else
				{
					$content = apply_filters('devkit/layouts/the_content', get_the_content(null, true, $layout->id));
					$content = str_replace(']]>', ']]&gt;', $content);

					echo $content;
				}
				break;
		}

		do_action('devkit/layouts/after_render', $layout);
	}
	/**
	 * Render layout via shortcode
	 *
	 * @param array $atts
	 * @return string
	 */
	public function shortcode( array $atts = [] ) : string
	{
		$atts = shortcode_atts(
			[
				'id' => '',
				'conditional' => false,
			],
			'devkit_layouts'
		);

		if ( empty( $id ) )
		{
			return '';
		}
		$layout = new Layout( $atts['id'] );

		if ( ! $layout )
		{
			return '';
		}

		if ( $atts['conditional'] )
		{
			$conditions_met = Subscriber::getInstance('Controllers\\Conditions')->shouldDisplay($layout);
		}

		if ( $conditions_met ?? true )
		{
			ob_start();
			$this->render( $layout );
			return ob_get_clean();
		}
		else {
			return '';
		}
	}
	/**
	 * Render the layout container
	 *
	 * @param object $layout
	 * @return void
	 */
	public function container($layout) : void
	{
		if ( empty($layout->container) || doing_action( 'wp_head' ) )
		{
			return;
		}

		if (current_action() === 'devkit/layouts/before_render')
		{
			printf(
				'<%s id="devkit-layout-%s" class="%s">',
				$layout->container,
				$layout->id,
				trim('devkit-layout ' . trim($layout->class))
			);
		} else {
			echo "</{$layout->container}>";
		}
	}
	/**
	 * Helper function to split action names and get dynamic parts
	 *
	 * @param  string $name The string (name) we want to split into pieces
	 * @param  string $delimiter where to split at
	 * @param  int|integer $offset which instance form the end to split
	 * @return array split name
	 */
	protected function getIdFromName(string $name, string $delimiter = '/', int $offset = 1): array
	{

		$parts = explode($delimiter, $name);

		$name = [];

		for ($i = 0; $i < $offset; $i++) {
			if (empty($parts)) {
				break;
			}
			$name[] = array_pop($parts);
		}

		return [
			implode($delimiter, $parts),
			implode($delimiter, $name)
		];
	}
}