<?php namespace MetalMatze\Html;

use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Html\HtmlBuilder;
use Illuminate\Routing\UrlGenerator;
use MetalMatze\Html\MD5;

class HtmlBuilderCachebusting extends HtmlBuilder
{
    protected $url;
    protected $filesystem;
    protected $md5;
    protected $format;
    protected $isBustingEnabled;
    protected $bustableAssetExtensions;
    protected $configPrefix = "laravel-html-cachebusting";

    public function __construct(
        UrlGenerator $url      = null,
        Filesystem $filesystem = null,
        Repository $config     = null,
        MD5 $md5               = null,
        $format                = null
    ) {
        $this->url        = $url;
        $this->filesystem = $filesystem;
        $this->md5        = $md5;
        $this->config     = $config;

        $this->isBustingEnabled        = $this->readConfig("enabled", false);
        $this->format                  = $format ?: $this->readConfig("format", ".%s.");
        $this->bustableAssetExtensions = $this->readConfig("extensions", array());
    }

    private function readConfig($key, $default=null) {
        return $this->config->get(sprintf("%s::%s", $this->configPrefix, $key), $default);
    }

    /**
     * @param $url
     * @param array $attributes
     * @param null $secure
     * @param bool $force
     * @return string
     */
    public function styleBust($url, $attributes = array(), $secure = null, $overrideConfig = true)
    {
        return parent::style(
            $this->tryBuildBustableUrl($url, $overrideConfig),
            $attributes,
            $secure
        );
    }

    /**
     * @param $url
     * @param array $attributes
     * @param null $secure
     * @param bool $force
     * @return string
     */
    public function scriptBust($url, $attributes = array(), $secure = null, $overrideConfig = true)
    {
        return parent::script(
            $this->tryBuildBustableUrl($url, $overrideConfig),
            $attributes,
            $secure
        );
    }

    /**
     * Generate a link to a JavaScript file.
     *
     * @param  string  $url
     * @param  array   $attributes
     * @param  bool    $secure
     * @return string
     */
    public function script($url, $attributes = array(), $secure = null)
    {
        // Try to bust script, but respect cache buster settings in config
        return $this->scriptBust($url, $attributes, $secure, false);
    }

    /**
     * Generate a link to a CSS file.
     *
     * @param  string  $url
     * @param  array   $attributes
     * @param  bool    $secure
     * @return string
     */
    public function style($url, $attributes = array(), $secure = null)
    {
        // Try to bust style, but respect cache buster settings in config
        return $this->styleBust($url, $attributes, $secure, false);
    }

    public function insertBeforeExtension($filename, $insert = null, $overrideConfig = false)
    {
        if (is_null($insert)) {
            return $filename;
        }

        $extension               = $this->filesystem->extension($filename);
        $extensionWithDotLength  = strlen($extension) + 1;
        $isBustableExtension     = in_array($extension, $this->bustableAssetExtensions);

        $isBustableAsset         = $overrideConfig || ($isBustableExtension && $this->isBustingEnabled);
        if (!$isBustableAsset) { // Is non-bustable extension and config not overridden?
            return $filename;
        }

        $fileNameWithCacheBuster = substr_replace($filename, $insert, -$extensionWithDotLength);

        return sprintf("%s%s", $fileNameWithCacheBuster, $extension);
    }

    private function tryBuildBustableUrl($url, $overrideConfig = false) {
        if (!$this->filesystem->exists($url)) {
            return $url;
        }

        $md5               = $this->md5->file($url);

        $cacheBusterString = sprintf($this->format, $md5);

        return $this->insertBeforeExtension($url, $cacheBusterString, $overrideConfig);
    }
}
