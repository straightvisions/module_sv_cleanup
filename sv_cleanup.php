<?php
namespace sv_100;

/**
 * @version         1.00
 * @author			straightvisions GmbH
 * @package			sv_100
 * @copyright		2017 straightvisions GmbH
 * @link			https://straightvisions.com
 * @since			1.0
 * @license			See license.txt or https://straightvisions.com
 */

class sv_cleanup extends init {
	public function init() {
		// Module Info
		$this->set_module_title( 'SV Cleanup' );
		$this->set_module_desc( __( 'Improve some WordPress Standards', $this->get_module_name() ) );
		
		// Section Info
		$this->set_section_title( __( 'Cleanup', 'straightvisions_100' ) )
			 ->set_section_desc( __( 'Improve some WordPress Standards', 'straightvisions_100' ) )
			 ->set_section_type( 'settings' );
		
		$this->get_root()->add_section($this);
		
		$this->load_settings();
		
		if($this->s['jquery_migrate']->run_type()->get_data()){
			add_action( 'wp_default_scripts', array($this, 'jquery_migrate') );
		}
		if($this->s['meta_data']->run_type()->get_data()){
			$this->meta_data();
		}
		if($this->s['emoji_styles']->run_type()->get_data()){
			remove_action('wp_print_styles', 'print_emoji_styles'); // remove emoji
		}
		if($this->s['wp_media']->run_type()->get_data()){
			add_action('wp_print_styles', array($this, 'wp_print_styles'), 100);
			add_action('wp_print_footer_scripts', array($this, 'wp_print_styles'), 1);
		}

		// Action Hooks
		add_action('wp_head', array($this, 'wp_start'), 1);
		add_action('wp_footer', array($this, 'wp_end'), 9999999);
		
		// lazy load attached CSS
		add_filter('rocket_buffer', function($buffer){ return str_replace('rel="stylesheet"', 'rel="stylesheet" media="none" onload="if(media!=\'all\')media=\'all\'"', $buffer); }, 999999);
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
		$this->s['jquery_migrate'] =
			$this->get_setting()
				 ->set_ID( 'jquery_migrate' )
				 ->set_title( __( 'Disable jQuery Migrate', 'straightvisions_100' ) )
				 ->set_description( __( 'In most cases, you will not need jQuery-Migrate. Disabling this script will reduce pageload and improve Pagespeed. Check for Javascript-Errors in Frontend after activating this.', 'straightvisions_100' ) )
				 ->load_type( 'checkbox' );
		
		$this->s['meta_data'] =
			$this->get_setting()
				 ->set_ID( 'meta_data' )
				 ->set_title( __( 'Remove non-critical meta data', 'straightvisions_100' ) )
				 ->set_description( __( 'Removes some lines of HTML-Meta-Data which are not critical for your site, but saves some byte of code in the frontend: rest_output_link_wp_head, wp_oembed_add_discovery_links, rsd_link, wlwmanifest_link, wp_shortlink_wp_head, wp_generator', 'straightvisions_100' ) )
				 ->load_type( 'checkbox' );
		
		$this->s['emoji_styles'] =
			$this->get_setting()
				 ->set_ID( 'emoji_styles' )
				 ->set_title( __( 'Removes Emoji Styles', 'straightvisions_100' ) )
				 ->set_description( __( 'Instead of loading those styles from WP, default browser emojis will be displayed', 'straightvisions_100' ) )
				 ->load_type( 'checkbox' );
		
		$this->s['wp_media'] =
			$this->get_setting()
				 ->set_ID( 'wp_media' )
				 ->set_title( __( 'Load WP Media Styles inline', 'straightvisions_100' ) )
				 ->set_description( __( 'To optimize your Pagespeed Score, you may need to load WP Media Styles inline if loaded. Activate this, if Pagespeed Test Tool says external WP Media Styles are renderblocking.', 'straightvisions_100' ) )
				 ->load_type( 'checkbox' );
		
		$this->s['wp_media'] =
			$this->get_setting()
				 ->set_ID( 'wp_media' )
				 ->set_title( __( 'Load WP Media Styles inline', 'straightvisions_100' ) )
				 ->set_description( __( 'To optimize your Pagespeed Score, you may need to load WP Media Styles inline if loaded. Activate this, if Pagespeed Test Tool says external WP Media Styles are renderblocking.', 'straightvisions_100' ) )
				 ->load_type( 'checkbox' );
		
		$this->s['alt_attr'] =
			$this->get_setting()
				 ->set_ID( 'alt_attr' )
				 ->set_title( __( 'Add alt-attributes to images if missing', 'straightvisions_100' ) )
				 ->set_description( __( 'No image should be without alt-attribute, so if there are some without one, an empty one will be added.', 'straightvisions_100' ) )
				 ->load_type( 'checkbox' );
		
		$this->s['type_attr'] =
			$this->get_setting()
				 ->set_ID( 'type_attr' )
				 ->set_title( __( 'Remove type-attributes from style and script tags', 'straightvisions_100' ) )
				 ->set_description( __( 'These are not needed for standard purposes anymore, W3C recommends to remove them if not needed. You will reduce your pageload as well.', 'straightvisions_100' ) )
				 ->load_type( 'checkbox' );
		
		return $this;
	}
	public function wp_start(){
		ob_start();
	}
	public function wp_end(){
		$output				= ob_get_contents();
		
		if($this->s['alt_attr']->run_type()->get_data()) {
			$output			= $this->add_alt_tags( $output );
		}
		
		if($this->s['type_attr']->run_type()->get_data()) {
			$output			= $this->remove_type_attr($output);
		}
		
		ob_end_clean();
		echo $output;
		ob_start();
	}
	public function remove_type_attr($input){
		$input = str_replace("type='text/javascript'", '', $input);
		$input = str_replace('type="text/javascript"', '', $input);

		$input = str_replace("type='text/css'", '', $input);
		$input = str_replace('type="text/css"', '', $input);

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
			echo '<style data-sv_100_module="'.$this->get_module_name().'_wp_mediaelement">';
			ob_start();
			include(ABSPATH.WPINC.'/js/mediaelement/mediaelementplayer-legacy.min.css');
			include(ABSPATH.WPINC.'/js/mediaelement/wp-mediaelement.min.css');
			$css					= ob_get_contents();
			ob_end_clean();
			echo str_replace('mejs-controls.svg',includes_url('js/mediaelement/mejs-controls.svg'), $css);
			echo '</style>';
		}
	}
}