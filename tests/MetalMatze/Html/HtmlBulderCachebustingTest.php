<?php namespace MetalMatze;

use MetalMatze\Html\HtmlBuilderCachebusting;
use Mockery;
use PHPUnit_Framework_TestCase;

class HtmlBulderCachebustingUnitTests extends PHPUnit_Framework_TestCase
{
    private $url;
    private $filesystem;

    public function setUp()
    {
        $this->url = Mockery::mock('Illuminate\Routing\UrlGenerator');
        $this->filesystem = Mockery::mock('Illuminate\Filesystem\Filesystem');
        $this->md5 = Mockery::mock('MetalMatze\MD5\MD5');
    }

    public function tearDown()
    {
        Mockery::close();
    }

    private function htmlBuilderCachebusting()
    {
        return new HtmlBuilderCachebusting($this->url, $this->filesystem, $this->md5);
    }

    public function testStyleBustFileNonExisting()
    {
        $this->filesystem->shouldReceive('exists')->once()->with('main.css')->andReturn(false);
        $this->url->shouldReceive('asset')->once()->with('main.css')->andReturn('http://example.com/main.css');

        $actual = $this->htmlBuilderCachebusting()->styleBust('main.css');

        $this->assertEquals('<link media="all" type="text/css" rel="stylesheet" href="http://example.com/main.css">'.PHP_EOL, $actual);
    }

    public function testStyleBust()
    {
        $this->filesystem->shouldReceive('exists')->once()->with('main.css')->andReturn(true);
        $this->md5->shouldReceive('file')->once()->with('main.css')->andReturn('b273ce');
        $this->filesystem->shouldReceive('extension')->once()->with('main.css')->andReturn('css');
        $this->url->shouldReceive('asset')->once()->with('main.b273ce.css')->andReturn('http://example.com/main.b273ce.css');

        $actual = $this->htmlBuilderCachebusting()->styleBust('main.css');

        $this->assertEquals('<link media="all" type="text/css" rel="stylesheet" href="http://example.com/main.b273ce.css">'.PHP_EOL, $actual);
    }

    public function testScriptBustFileNonExisting()
    {
        $this->filesystem->shouldReceive('exists')->once()->with('main.js')->andReturn(false);
        $this->url->shouldReceive('asset')->once()->with('main.js')->andReturn('http://example.com/main.js');

        $actual = $this->htmlBuilderCachebusting()->scriptBust('main.js');

        $this->assertEquals('<script src="http://example.com/main.js"></script>'.PHP_EOL, $actual);
    }

    public function testScriptBust()
    {
        $this->filesystem->shouldReceive('exists')->once()->with('main.js')->andReturn(true);
        $this->md5->shouldReceive('file')->once()->with('main.js')->andReturn('b273ce');
        $this->filesystem->shouldReceive('extension')->once()->with('main.js')->andReturn('js');
        $this->url->shouldReceive('asset')->once()->with('main.b273ce.js')->andReturn('http://example.com/main.b273ce.js');

        $actual = $this->htmlBuilderCachebusting()->scriptBust('main.js');

        $this->assertEquals('<script src="http://example.com/main.b273ce.js"></script>'.PHP_EOL, $actual);
    }

    public function testInsertBeforeExtension()
    {
        $this->filesystem->shouldReceive('extension')->once()->with('main.css')->andReturn('css');

        $actual = $this->htmlBuilderCachebusting()->insertBeforeExtension('main.css', '.foo.');

        $this->assertEquals('main.foo.css', $actual);
    }

    public function testInsertBeforeExtensionEmptyInsert()
    {
        $this->filesystem->shouldReceive('extension')->never();

        $actual = $this->htmlBuilderCachebusting()->insertBeforeExtension('main.css');

        $this->assertEquals('main.css', $actual);
    }
}
