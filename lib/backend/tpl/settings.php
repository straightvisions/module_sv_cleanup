<?php
	if ( current_user_can( 'activate_plugins' ) ) {
		?>
		<div class="sv_section_description"><?php echo $module->get_section_desc(); ?></div>
		
		<h3 class="divider"><?php _e( 'Decrease PageLoad', 'sv100_companion' ); ?></h3>
		<div class="sv_setting_flex">
			<?php
				echo $module->get_settings()['jquery_migrate']->run_type()->form();
				echo $module->get_settings()['meta_data']->run_type()->form();
				echo $module->get_settings()['emoji_styles']->run_type()->form();
			?>
		</div>
		
		<h3 class="divider"><?php _e( 'Improve Scriptloading', 'sv100_companion' ); ?></h3>
		<div class="sv_setting_flex">
			<?php
				echo $module->get_settings()['wp_media']->run_type()->form();
			?>
		</div>
		
		<h3 class="divider"><?php _e( 'Optimize W3C Validation', 'sv100_companion' ); ?></h3>
		<div class="sv_setting_flex">
			<?php
				echo $module->get_settings()['alt_attr']->run_type()->form();
				echo $module->get_settings()['type_attr']->run_type()->form();
			?>
		</div>
		<?php
	}
?>