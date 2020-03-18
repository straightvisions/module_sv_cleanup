<?php
namespace sv100_companion;

/**
 * @version         1.00
 * @author			straightvisions GmbH
 * @package			sv100_companion
 * @copyright		2017 straightvisions GmbH
 * @link			https://straightvisions.com
 * @since			1.0
 * @license			See license.txt or https://straightvisions.com
 */

class sv_cleanup extends modules {
	public function init() {
		// Section Info
		$this->set_section_title( __( 'Cleanup', 'sv100_companion' ) )
			->set_section_desc( __( 'Improve some WordPress Standards', 'sv100_companion' ) )
			->set_section_type( 'settings' )
			->set_section_template_path( $this->get_path( '/lib/backend/tpl/settings.php' ) );
		
		$this->get_root()->add_section($this);
		
		$this->load_settings();
		
		if($this->get_setting('jquery_migrate')->get_data()){
			add_action( 'wp_default_scripts', array($this, 'jquery_migrate') );
		}
		if($this->get_setting('meta_data')->get_data()){
			$this->meta_data();
		}
		if($this->get_setting('emoji_styles')->get_data()){
			remove_action('wp_print_styles', 'print_emoji_styles'); // remove emoji
		}
		if($this->get_setting('wp_media')->get_data()){
			//add_action('wp_print_styles', array($this, 'wp_print_styles'), 100);
			add_action('wp_print_footer_scripts', array($this, 'wp_print_styles'), 1);
		}

		if($this->get_setting('css_lazyload')->get_data()){
			add_action('init', array($this, 'wp_init'));
		}

		// Action Hooks
		add_action('wp_head', array($this, 'wp_start'), 1);
		add_action('wp_footer', array($this, 'wp_end'), 9999999);
	}
	public function wp_init(){
		if(!defined('WP_ROCKET_PATH')) {
			add_filter('style_loader_tag', array($this, 'css_lazyload'));
		}
	}
	public function css_lazyload($buffer){
		return str_replace( 		array(
			'media="all"',
			'media=\'all\'',
			'rel="stylesheet"',
			'rel=\'stylesheet\''
		), array(
			'',
			'',
			'rel="stylesheet" media="none" onload="if(media!=\'all\')media=\'all\'"',
			'rel="stylesheet" media="none" onload="if(media!=\'all\')media=\'all\'"',
		), $buffer);
	}
	public function jquery_migrate($scripts){
		if ( ! is_admin() && ! empty( $scripts->registered['jquery'] ) ) {
			$scripts->registered['jquery']->deps = array_diff(
				$scripts->registered['jquery']->deps,
				[ 'jquery-migrate' ]
			);
		}
	}
	public function meta_data(){
		remove_action('wp_head', 'rest_output_link_wp_head', 10); // Remove api.w.org REST API from WordPress header
		remove_action('wp_head', 'wp_oembed_add_discovery_links', 10); // Remove api.w.org REST API from WordPress header
		remove_action('wp_head', 'rsd_link');
		remove_action('wp_head', 'wlwmanifest_link');
		remove_action('wp_head', 'wp_shortlink_wp_head');
		remove_action('wp_head', 'wp_generator');
	}
	public function load_settings(): sv_cleanup {
			$this->get_setting('jquery_migrate')
				 ->set_title( __( 'Disable jQuery Migrate', 'sv100_companion' ) )
				 ->set_description( __( 'In most cases, you will not need jQuery-Migrate. Disabling this script will reduce pageload and improve Pagespeed. Check for Javascript-Errors in Frontend after activating this.', 'sv100_companion' ) )
				 ->load_type( 'checkbox' );
			
			$this->get_setting('meta_data')
				 ->set_title( __( 'Remove non-critical meta data', 'sv100_companion' ) )
				 ->set_description( __( 'Removes some lines of HTML-Meta-Data which are not critical for your site, but saves some byte of code in the frontend: rest_output_link_wp_head, wp_oembed_add_discovery_links, rsd_link, wlwmanifest_link, wp_shortlink_wp_head, wp_generator', 'sv100_companion' ) )
				 ->load_type( 'checkbox' );
			
			$this->get_setting('emoji_styles')
				 ->set_title( __( 'Removes Emoji Styles', 'sv100_companion' ) )
				 ->set_description( __( 'Instead of loading those styles from WP, default browser emojis will be displayed', 'sv100_companion' ) )
				 ->load_type( 'checkbox' );
			
			$this->get_setting('wp_media')
				 ->set_title( __( 'Load WP Media Styles inline', 'sv100_companion' ) )
				 ->set_description( __( 'To optimize your Pagespeed Score, you may need to load WP Media Styles inline if loaded. Activate this, if Pagespeed Test Tool says external WP Media Styles are renderblocking.', 'sv100_companion' ) )
				 ->load_type( 'checkbox' );
			
			$this->get_setting('css_lazyload')
				 ->set_title( __( 'Lazyload attached CSS files', 'sv100_companion' ) )
				 ->set_description( sprintf(__( 'Attached CSS should be lazyloaded. Even with WP-Rocket this is not solved completely %1$s(see issue)%2$s', 'sv100_companion' ),
					 '<a target="_blank" href="' . esc_url( 'https://github.com/wp-media/wp-rocket/issues/1814' ) . '">',
					 '</a>'
				 ) )
				 ->load_type( 'checkbox' );

			$this->get_setting('alt_attr')
				 ->set_title( __( 'Add alt-attributes to images if missing', 'sv100_companion' ) )
				 ->set_description( __( 'No image should be without alt-attribute, so if there are some without one, an empty one will be added.', 'sv100_companion' ) )
				 ->load_type( 'checkbox' );
			
			$this->get_setting('type_attr')
				 ->set_title( __( 'Remove type-attributes from style and script tags', 'sv100_companion' ) )
				 ->set_description( __( 'These are not needed for standard purposes anymore, W3C recommends to remove them if not needed. You will reduce your pageload as well.', 'sv100_companion' ) )
				 ->load_type( 'checkbox' );
		
		return $this;
	}
	public function wp_start(){
		ob_start();
	}
	public function wp_end(){
		$output				= ob_get_contents();
		
		if($this->get_setting('alt_attr')->get_data()) {
			$output			= $this->add_alt_tags( $output );
		}
		
		if($this->get_setting('type_attr')->get_data()) {
			$output			= $this->remove_type_attr($output);
		}
		
		ob_end_clean();
		echo $output;
		ob_start();
	}
	public function remove_type_attr($input){
		$input = str_replace(" type='text/javascript'", '', $input);
		$input = str_replace(' type="text/javascript"', '', $input);

		$input = str_replace(" type='text/css'", '', $input);
		$input = str_replace(' type="text/css"', '', $input);

		return $input;
	}
	public function add_alt_tags($content){
		preg_match_all('/<img (.*?)\/>/', $content, $images);
		if(!is_null($images))
		{
			foreach($images[1] as $index => $value)
			{
				if(!preg_match('/alt=/', $value))
				{
					$new_img = str_replace('<img', '<img alt=""', $images[0][$index]);
					$content = str_replace($images[0][$index], $new_img, $content);
				}
			}
		}
		return $content;
	}
	public function wp_print_styles(){
		if(wp_style_is('wp-mediaelement')){
			wp_dequeue_style('wp-mediaelement');
			
			ob_start();
			include(ABSPATH.WPINC.'/js/mediaelement/mediaelementplayer-legacy.min.css');
			include(ABSPATH.WPINC.'/js/mediaelement/wp-mediaelement.min.css');
			$css					= ob_get_contents();
			ob_end_clean();
			$css =  str_replace('mejs-controls.svg',includes_url('js/mediaelement/mejs-controls.svg'), $css);
			
			wp_add_inline_style('sv_core_init_style', $css);
		}
	}
}