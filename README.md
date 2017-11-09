# laravel-html-cachebusting

**This repository is archived. If this is still a thing in 2017 and beyond, please contact me on [Twitter](https://twitter.com/MetalMatze). I might transfer this project to you!**

This package extends Laravel's ```HtmlBuilder``` with simple cache busting, either by enabling it via a configuration and using the built-in ```HTML::script``` and ```HTML::style```, or more explicitly by replacing  calls to ```HTML::script``` and ```HTML::style``` in templates with ```HTML::scriptBust``` and ```HTML::styleBust```. Both methods bust browser cache by placing an md5 checksum into the filename of your asset.

For a cache buster providing more functionality, have a look at [Laravel Cachebuster](https://github.com/TheMonkeys/laravel-cachebuster).

[![Build Status](https://travis-ci.org/MetalMatze/laravel-html-cachebusting.png?branch=master)](https://travis-ci.org/MetalMatze/laravel-html-cachebusting)

### Cache busting via configuration
After installation of this package calls to ```HTML::script``` and ```HTML::style``` will _not_ automatically apply cache busting. To change this, publish the package configuration using Artisan. 

	php artisan config:publish metalmatze/laravel-html-cachebusting

This copies the file ```config.php``` into ```<app-config-directory>/packages/metalmatze/laravel-html-cachebusting```. Setting ```enabled``` to ```true``` enables cache busting for all calls to both ```HTML::script``` and ```HTML::style```.

Aside from that the configuration requires a white-list of extensions that should be busted, which by default is set to .js and .css files.

And the ```format``` setting makes it possible to format the cache buster string _a little bit_; it is essentially an ```sprintf``` formatter, which expects exactly one ```%s```.  

### Explicit cache busting
**Note**: explicit cache busting overrides the ```enabled``` setting of the configuration file.

Busting cache for css files:

```php
{{ HTML::styleBust('main.css') }}
```
`<link media="all" type="text/css" rel="stylesheet" href="http://example.com/css/main.ae3ab568f451e151a6d7a9b8615efaeb.css">`

Busting cache for javascript files:  
```php
{{ HTML::scriptBust('main.js') }}
```
`<script src="http://example.com/js/main.d3b8d8cde26b65f660ff8f8b0879ee94.js"></script>`

## Installation
Begin by installing this package through Composer. Edit your project's `composer.json` file to require `metalmatze\laravel-html-cachebusting`.

    "require": {
        "laravel/framework": "4.0.*",
        "metalmatze/laravel-html-cachebusting": "dev-master"
    },
    "minimum-stability" : "dev"

Next, update Composer from the Terminal:

    composer update

Once this operation completes, the final step is to add the service provider. Open `app/config/app.php`, and **append** a new item to the providers array.

    'MetalMatze\Html\HtmlServiceProvider'

**DO NOT replace it with laravel's default `Illuminate\Html\HtmlServiceProvider` since there would be no Form.**

### Adding the redirect to your `.htaccess`
    RewriteRule ^(css|js)/(.+)\.([0-9a-f]{32})\.(js|css|png|jpg|gif)$ /(css|js)/$1.$3 [L]

### Adding the redirect to nginx
    rewrite "^(.+)\.([0-9a-f]{32})\.(js|css|png|jpg|gif)$" /$1.$3;

## Contributing
This package adheres to PSR-2 standards, so before committing make sure your code is PSR-2 error free. To test compliance run

	php vendor/bin/phpcs --standard=PSR2 src/ tests/```.
