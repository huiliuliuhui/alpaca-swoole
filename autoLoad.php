<?php
/**
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/17 0017
 * Time: 下午 23:28
 */

include 'Loader.php'; // 引入加载器

Loader::$vendorMap = require __DIR__ . "/configs/namespace.php";

spl_autoload_register('Loader::autoload'); // 注册自动加载


