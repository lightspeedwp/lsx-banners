<div class="uix-field-wrapper">
	<div class="image-picker-side-bar {{#unless selection/thumbnail}}dashicons dashicons-format-image{{/unless}} uix-image-picker" 
		data-target="#lsx-banners-selection" 
		data-title="<?php echo __('Change Image', 'lsx-banners'); ?>" 
		data-button="<?php echo __('Use Image', 'lsx-banners'); ?>" 
		style="background: {{#if selection/url}}url('{{selection/url}}') no-repeat scroll center center{{/if}} #e3e3e3; margin: 0 10px 0 0;line-height: 1.5em; overflow: hidden; height: 300px; width: 940px;font-size: 205px; color: #bfbfbf; cursor: pointer;"
		>
	</div>
</div>
{{#if selection/thumbnail}}
<button type="button" data-remove-element="#lsx-banners-selection" class="button">Remove Default Banner</button>
{{/if}}
<input type="hidden" id="lsx-banners-selection" name="selection" value="{{json selection}}" data-live-sync="true">