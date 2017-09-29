<div class="uix-field-wrapper">
	<?php
		$display_settings_page = false;

		if ( class_exists( 'LSX_Currencies' ) ) {
			$display_settings_page = true;
		}
	?>

	<ul class="ui-tab-nav">
		<?php if ( false !== $display_settings_page ) { ?><li><a href="#ui-settings" class="active"><?php esc_html_e( 'Settings', 'lsx-banner' ); ?></a></li><?php } ?>
		<li><a href="#ui-keys" <?php if ( false === $display_settings_page ) { ?>class="active"<?php } ?>><?php esc_html_e( 'License Keys', 'lsx-banner' ); ?></a></li>
	</ul>

	<?php if ( false !== $display_settings_page ) { ?>
		<div id="ui-settings" class="ui-tab active">
			<p><?php esc_html_e( 'Please enter your user details (email address, API key, username, etc) below as required for the extensions that you have installed.', 'lsx-banner' ); ?></p>

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
					$lsx = admin_url( 'themes.php?page=lsx-welcome' );
					$message = sprintf( "Please enter the license and API key's for your add-ons below." );
					$message .= sprintf( " Follow this <a href='%s' title='LSX add-ons'>link</a> to see what extensions are available for LSX.", $lsx );
				?>

				<p class="info"><?php echo wp_kses_post( $message ); ?></p>

				<?php
					$api_keys_content = false;
					ob_start();
					do_action( 'lsx_framework_api_tab_content', 'api' );
					$api_keys_content = ob_end_clean();
				?>
			</tbody>
		</table>
	</div>

	<?php do_action( 'lsx_framework_api_tab_bottom', 'api' ); ?>
</div>
