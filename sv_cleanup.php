<?php
	namespace sv_100;
	
	/**
	 * @author			Matthias Reuter
	 * @package			sv_100
	 * @copyright		2017 Matthias Reuter
	 * @link			https://straightvisions.com
	 * @since			1.0
	 * @license			See license.txt or https://straightvisions.com
	 */
	class sv_cleanup extends init{
		static $scripts_loaded						= false;

		public function __construct($path,$url){
			$this->path								= $path;
			$this->url								= $url;
			$this->name								= get_class($this);
			
			add_action('wp_head', array($this, 'wp_start'), 1);
			add_action('wp_footer', array($this, 'wp_end'), 9999999);
			
			// WP media
			add_action('wp_print_styles', array($this, 'wp_print_styles'), 100);
			add_action('wp_print_footer_scripts', array($this, 'wp_print_styles'), 1);
			
			$this->init();
		}
		public function init(){
			remove_action('wp_print_styles', 'gforms_css'); // remove gravity form styles
			remove_action('wp_print_styles', 'print_emoji_styles'); // remove emoji
			remove_action('wp_head', 'rest_output_link_wp_head', 10); // Remove api.w.org REST API from WordPress header
			remove_action('wp_head', 'wp_oembed_add_discovery_links', 10); // Remove api.w.org REST API from WordPress header
			remove_action('wp_head', 'rsd_link');
			remove_action('wp_head', 'wlwmanifest_link');
			remove_action('wp_head', 'wp_shortlink_wp_head');
			remove_action('wp_head', 'wp_generator');
		}
		public function wp_start(){
			ob_start();
		}
		public function wp_end(){
			$output				= ob_get_contents();
			$output				= $this->remove_type_attr($this->add_alt_tags($output));
			ob_end_clean();
			echo $output;
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
?>