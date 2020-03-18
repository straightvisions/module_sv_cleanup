<?php
	if ( current_user_can( 'activate_plugins' ) ) {
		?>
		<div class="sv_section_description"><?php echo $module->get_section_desc(); ?></div>
		
		<h3 class="divider"><?php _e( 'Decrease PageLoad', 'sv100_companion' ); ?></h3>
		<div class="sv_setting_flex">
			<?php
				echo $module->get_setting('jquery_migrate')->form();
				echo $module->get_setting('meta_data')->form();
				echo $module->get_setting('emoji_styles')->form();
			?>
		</div>
		
		<h3 class="divider"><?php _e( 'Improve Scriptloading', 'sv100_companion' ); ?></h3>
		<div class="sv_setting_flex">
			<?php
				echo $module->get_setting('wp_media')->form();
			echo $module->get_setting('css_lazyload')->form();
			?>
		</div>
		
		<h3 class="divider"><?php _e( 'Optimize W3C Validation', 'sv100_companion' ); ?></h3>
		<div class="sv_setting_flex">
			<?php
				echo $module->get_setting('alt_attr')->form();
				echo $module->get_setting('type_attr')->form();
			?>
		</div>
		<?php
	}
?>