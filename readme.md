# LSX Banners

Use with the lsx or storefront themes.

## Enable Functionality Via Filters

Enable the use of a placeholder service,  currently placeholdit

```add_filter('lsx_banner_enable_placeholder', function( $bool ) { return true; });```

Customize the placeholder service.

```add_filter('lsx_banner_placeholder_url', function( $url ) { return 'https://placeholdit.imgix.net/~text?txtsize=33&txt=1920%20600&w=1920&h=600'; });```

Enable the Use of a custom title

```add_filter('lsx_banner_enable_title', function( $bool ) { return true; });```

Enable the Use of a subtitle

```add_filter('lsx_banner_enable_subtitle', function( $bool ) { return true; });```


## Adding additional content to your banner

```add_action('lsx_banner_content','your_function_name');```