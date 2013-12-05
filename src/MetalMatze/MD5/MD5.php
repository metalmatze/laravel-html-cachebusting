<?php 
namespace MetalMatze\MD5;

class MD5
{
    public function file($filename)
    {
        return md5_file($filename);
    }
}
