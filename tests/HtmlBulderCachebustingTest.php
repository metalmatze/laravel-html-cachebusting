<?php namespace MetalMatze;

use Illuminate\Config\Repository;
use MetalMatze\Html\HtmlBuilderCachebusting;
use Mockery;
use PHPUnit_Framework_TestCase;

class HtmlBulderCachebustingUnitTests extends PHPUnit_Framework_TestCase
{
    private $url;
    private $filesystem;
    private $md5;

    public function setUp()
    {
        $this->url = Mockery::mock('Illuminate\Routing\UrlGenerator');
        $this->filesystem = Mockery::mock('Illuminate\Filesystem\Filesystem');
        $this->md5 = Mockery::mock('MetalMatze\Html\MD5');
    }

    public function tearDown()
    {
        Mockery::close();
    }

    private function buildConfigMock($configMap = null)
    {
        $configMock = Mockery::mock('Illuminate\Config\Repository');

        $defaultConfigMap = array(
            "enabled" => true,
            "extensions" => array("js", "css"),
            "format" => ".%s."
        );

        $configMap = !empty($configMap) ? array_merge($defaultConfigMap, $configMap) : $defaultConfigMap;

        $packagePrefix = "laravel-html-cachebusting";
        
        foreach ($configMap as $setting => $value) {
            $this->addGetActionToConfigMock(
                $configMock,
                sprintf("%s::%s", $packagePrefix, $setting),
                $value
            );
        }

        return $configMock;
    }

    private function addGetActionToConfigMock($configMock, $key, $value)
    {
        $configMock->shouldReceive("get")
            ->withArgs(array($key, \Mockery::any()))
            ->andReturn($value);

        return $configMock;
    }

    private function htmlBuilderCachebusting(Repository $config)
    {
        return new HtmlBuilderCachebusting($this->url, $this->filesystem, $config, $this->md5);
    }

    public function testStyleBustFileNonExisting()
    {
        $configMock = $this->buildConfigMock();

        $this->filesystem
            ->shouldReceive('exists')
            ->once()
            ->with('main.css')
            ->andReturn(false);

        $this->url
            ->shouldReceive('asset')
            ->once()
            ->with('main.css', '')
            ->andReturn('http://example.com/main.css');

        $actual = $this->htmlBuilderCachebusting($configMock)->styleBust('main.css');

        $this->assertEquals(
            '<link media="all" type="text/css" rel="stylesheet" href="http://example.com/main.css">'.PHP_EOL,
            $actual
        );
    }

    public function testStyleBust()
    {
        $configMock = $this->buildConfigMock();

        $this->filesystem
            ->shouldReceive('exists')
            ->once()
            ->with('main.css')
            ->andReturn(true);

        $this->md5
            ->shouldReceive('file')
            ->once()
            ->with('main.css')
            ->andReturn('b273ce');

        $this->filesystem
            ->shouldReceive('extension')
            ->once()
            ->with('main.css')
            ->andReturn('css');

        $this->url
            ->shouldReceive('asset')
            ->once()
            ->withArgs(array('main.b273ce.css', \Mockery::any()))
            ->andReturn('http://example.com/main.b273ce.css');

        $actual = $this->htmlBuilderCachebusting($configMock)->styleBust('main.css');

        $this->assertEquals(
            '<link media="all" type="text/css" rel="stylesheet" href="http://example.com/main.b273ce.css">'.PHP_EOL,
            $actual
        );
    }

    public function testScriptBustFileNonExisting()
    {
        $configMock = $this->buildConfigMock();

        $this->filesystem
            ->shouldReceive('exists')
            ->once()
            ->with('main.js')
            ->andReturn(false);

        $this->url
            ->shouldReceive('asset')
            ->once()
            ->withArgs(array('main.js', \Mockery::any()))
            ->andReturn('http://example.com/main.js');

        $actual = $this->htmlBuilderCachebusting($configMock)->scriptBust('main.js');

        $this->assertEquals('<script src="http://example.com/main.js"></script>'.PHP_EOL, $actual);
    }

    public function testScriptBust()
    {
        $configMock = $this->buildConfigMock();

        $this->filesystem
            ->shouldReceive('exists')
            ->once()
            ->with('main.js')
            ->andReturn(true);

        $this->md5
            ->shouldReceive('file')
            ->once()
            ->with('main.js')
            ->andReturn('b273ce');

        $this->filesystem
            ->shouldReceive('extension')
            ->once()
            ->with('main.js')
            ->andReturn('js');

        $this->url
            ->shouldReceive('asset')
            ->once()
            ->withArgs(array('main.b273ce.js', \Mockery::any()))
            ->andReturn('http://example.com/main.b273ce.js');

        $actual = $this->htmlBuilderCachebusting($configMock)->scriptBust('main.js');

        $this->assertEquals('<script src="http://example.com/main.b273ce.js"></script>'.PHP_EOL, $actual);
    }

    public function testInsertBeforeExtension()
    {
        $configMock = $this->buildConfigMock();

        $this->filesystem
            ->shouldReceive('extension')
            ->once()
            ->with('main.css')
            ->andReturn('css');

        $actual = $this->htmlBuilderCachebusting($configMock)->insertBeforeExtension('main.css', '.foo.');

        $this->assertEquals('main.foo.css', $actual);
    }

    public function testInsertBeforeExtensionEmptyInsert()
    {
        $configMock = $this->buildConfigMock();

        $this->filesystem
            ->shouldReceive('extension')
            ->never();

        $actual = $this->htmlBuilderCachebusting($configMock)->insertBeforeExtension('main.css');

        $this->assertEquals('main.css', $actual);
    }

    public function testDontBustIfDisabled()
    {
        $configMock = $this->buildConfigMock(array("enabled" => false));

        $this->filesystem
            ->shouldReceive('extension')
            ->once()
            ->with('main.css')
            ->andReturn('css');

        $actual = $this->htmlBuilderCachebusting($configMock)->insertBeforeExtension('main.css', '.foo.');

        $this->assertEquals('main.css', $actual);
    }

    public function testDontBustIfNonBustableExtensions()
    {
        $configMock = $this->buildConfigMock(array("extensions" => array()));

        $this->filesystem
            ->shouldReceive('extension')
            ->once()
            ->with('main.css')
            ->andReturn('css');

        $actual = $this->htmlBuilderCachebusting($configMock)->insertBeforeExtension('main.css', '.foo.');

        $this->assertEquals('main.css', $actual);
    }
}
