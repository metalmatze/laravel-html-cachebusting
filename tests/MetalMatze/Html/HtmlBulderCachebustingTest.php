<?php 

use MetalMatze\Html\HtmlBuilderCachebusting;

class HtmlBulderCachebustingUnitTests extends PHPUnit_Framework_TestCase
{
    private $url;
    private $filesystem;

    public function setUp()
    {
        $this->url = Mockery::mock('Illuminate\Routing\UrlGenerator');
        $this->filesystem = Mockery::mock('Illuminate\Filesystem\Filesystem');
        $this->md5 = Mockery::mock('MetalMatze\MD5\MD5Interface');
    }

    public function tearDown()
    {
        Mockery::close();
    }

    private function newHtmlBuilderCachebusting()
    {
        return new HtmlBuilderCachebusting($this->url, $this->filesystem, $this->md5);
    }

    public function testStyleBustFileNonExisting()
    {
        $this->filesystem->shouldReceive('exists')->once()->with('main.css')->andReturn(false);
        $this->url->shouldReceive('asset')->once()->with('main.css')->andReturn('http://example.com/main.css');

        $actual = $this->newHtmlBuilderCachebusting()->styleBust('main.css');

        $this->assertEquals('<link media="all" type="text/css" rel="stylesheet" href="http://example.com/main.css">'.PHP_EOL, $actual);
    }

    public function testStyle()
    {
        $this->filesystem->shouldReceive('exists')->once()->with('main.css')->andReturn(true);
        $this->md5->shouldReceive('file')->once()->with('main.css')->andReturn('b273ce');
        $this->filesystem->shouldReceive('extension')->once()->with('main.css')->andReturn('css');
        $this->url->shouldReceive('asset')->once()->with('main.b273ce.css')->andReturn('http://example.com/main.b273ce.css');

        $actual = $this->newHtmlBuilderCachebusting()->styleBust('main.css');

        $this->assertEquals('<link media="all" type="text/css" rel="stylesheet" href="http://example.com/main.b273ce.css">'.PHP_EOL, $actual);

    }

    public function testInsertBeforeExtension()
    {
        $this->filesystem->shouldReceive('extension')->once()->with('main.css')->andReturn('css');

        $actual = $this->newHtmlBuilderCachebusting()->insertBeforeExtension('main.css', '.foo.');

        $this->assertEquals('main.foo.css', $actual);
    }

    public function testInsertBeforeExtensionEmptyInsert()
    {
        $this->filesystem->shouldReceive('extension')->never();

        $actual = $this->newHtmlBuilderCachebusting()->insertBeforeExtension('main.css');

        $this->assertEquals('main.css', $actual);
    }

}
