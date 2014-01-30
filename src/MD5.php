<?php namespace MetalMatze\Html;

class MD5
{
    public function file($filename)
    {
        return md5_file($filename);
    }
}
