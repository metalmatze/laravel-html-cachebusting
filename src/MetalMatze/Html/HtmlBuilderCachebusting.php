<?php namespace MetalMatze\Html;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Html\HtmlBuilder;
use Illuminate\Routing\UrlGenerator;
use MetalMatze\MD5\MD5;

class HtmlBuilderCachebusting extends HtmlBuilder
{

    protected $url;
    protected $filesystem;
    protected $md5;

    public function __construct(
        UrlGenerator $url = null,
        Filesystem $filesystem = null,
        MD5 $md5 = null
    ) {
        $this->url = $url;
        $this->filesystem = $filesystem;
        $this->md5 = $md5;
    }

    public function styleBust($url, $attributes = array())
    {
        if ($this->filesystem->exists($url)) {
            $md5 = $this->md5->file($url);
            $url = $this->insertBeforeExtension($url, ".$md5.");
        }

        return parent::style($url, $attributes);
    }

    public function scriptBust($url, $attributes = array())
    {
        if ($this->filesystem->exists($url)) {
            $md5 = $this->md5->file($url);
            $url = $this->insertBeforeExtension($url, ".$md5.");
        }

        return parent::script($url, $attributes);
    }

    public function insertBeforeExtension($filename, $insert = null)
    {
        if (is_null($insert)) {
            return $filename;
        }

        $extension = $this->filesystem->extension($filename);
        $extensionWithDotLength = (strlen($extension) + 1);

        return substr_replace($filename, $insert, -$extensionWithDotLength).$extension;
    }
}
