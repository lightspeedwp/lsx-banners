<div class="uix-field-wrapper">

	<?php
	$display_settings_page = false;
	if ( class_exists( 'LSX_Currencies' ) ) {
		$display_settings_page = true;
	}
	?>

	<ul class="ui-tab-nav">
		<?php if ( false !== $display_settings_page ) { ?><li><a href="#ui-settings" class="active"><?php esc_html_e( 'Settings', 'lsx-banners' ); ?></a></li><?php } ?>
		<li><a href="#ui-keys" <?php if ( false === $display_settings_page ) { ?>class="active"<?php } ?>><?php esc_html_e( 'License Keys', 'lsx-banners' ); ?></a></li>
	</ul>

	<?php if ( false !== $display_settings_page ) { ?>
		<div id="ui-settings" class="ui-tab active">
			<table class="form-table" style="margin-top:-13px !important;">
				<tbody>
					<?php do_action( 'lsx_framework_api_tab_content', 'settings' ); ?>
				</tbody>
			</table>
		</div>
	<?php } ?>

	<div id="ui-keys" class="ui-tab <?php if ( false === $display_settings_page ) { ?>active<?php } ?>">
		<table class="form-table" style="margin-top:-13px !important;">
			<tbody>
			<?php
				$api_keys_content = false;
				ob_start();
				do_action( 'lsx_framework_api_tab_content', 'api' );
				$api_keys_content = ob_end_clean();
				if ( false !== $api_keys_content ) {
					?>
						<p class="info"><?php esc_html_e( 'Enter the license keys for your add-ons in the boxes below.', 'lsx-banners' ); ?></p>
					<?php
					do_action( 'lsx_framework_api_tab_content', 'api' );
				} else {
					?>
					<p class="info"><?php esc_html_e( 'You have not installed any add-ons yet. View our list of add-ons', 'lsx-banners' ); ?> <a href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>?page=lsx-welcome"><?php esc_html_e( 'here', 'lsx-banners' ); ?></a>.</p>
				<?php }	?>
			</tbody>
		</table>
	</div>

	<?php do_action( 'lsx_framework_api_tab_bottom', 'api' ); ?>
</div>
