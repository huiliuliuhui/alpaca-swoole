<?php
namespace Swoole\Command;

class MakeApplication
{
    static function init($dir)
    {
        mkdir($dir.'/Controllers');
        mkdir($dir.'/configs');
        mkdir($dir.'/Models');
        mkdir($dir.'/classes');
        mkdir($dir.'/events');
        mkdir($dir.'/templates');
        mkdir($dir.'/factory');
    }
}