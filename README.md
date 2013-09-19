# laravel-html-cachebusting

Extends laravel's HtmlBuilder with styleBust() &amp; scriptBust() which insert the file's md5 to the url.

This package enables you to bust the browser cache by placing a md5 checksum into the filename of your asset.

Busting the cache for css files:
```php
{{ HTML::styleBust('main.css') }}
```
`<link media="all" type="text/css" rel="stylesheet" href="http://example.com/css/main.ae3ab568f451e151a6d7a9b8615efaeb.css">`

Busting the cache for javascript files:  
```php
{{ HTML::scriptBust('main.js') }}
```
`<script src="http://example.com/js/main.d3b8d8cde26b65f660ff8f8b0879ee94.js"></script>`

For more functionality take a look at [Laravel Cachebuster](https://github.com/TheMonkeys/laravel-cachebuster).

## Installation

Begin by installing this package through Composer. Edit your project's `composer.json` file to require `metalmatze\laravel-html-cachebusting`.

    "require": {
        "laravel/framework": "4.0.*",
        "metalmatze\laravel-html-cachebusting": "dev-master"
    },
    "minimum-stability" : "dev"

Next, update Composer from the Terminal:

    composer update

Once this operation completes, the final step is to add the service provider. Open `app/config/app.php`, and **append** a new item to the providers array.

    'MetalMatze\Html\HtmlServiceProvider'

**DO NOT replace it with laravel's default `Illuminate\Html\HtmlServiceProvider` since there would be no Form.**

### Adding the redirect to your `.htaccess`
    RewriteRule ^(css|js)/(.+)\.([0-9a-f]{32})\.(js|css|png|jpg|gif)$ /(css|js)/$1.$3 [L]