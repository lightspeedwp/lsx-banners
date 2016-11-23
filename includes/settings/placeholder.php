<?php if ( isset( $this->current_tab ) ) { ?>
	
	<div class="uix-field-wrapper">
		<table class="form-table">
			<tbody>
				<?php do_action( 'lsx_framework_' . $this->current_tab  .'_tab_content_top', $this->current_tab ); ?>
				<?php do_action( 'lsx_framework_' . $this->current_tab . '_tab_content', $this->current_tab ); ?>
			</tbody>
		</table>
		<?php do_action( 'lsx_framework_' . $this->current_tab . '_tab_bottom', $this->current_tab ); ?>
	</div>

<?php } ?>
