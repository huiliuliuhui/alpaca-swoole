<?php
/**
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/21 0021
 * Time: 下午 22:44
 */

namespace Test\Food;


class Apple
{
    public $num = 5;
    function __construct()
    {

    }

    public function getNumber(){
        echo $this->num . "个苹果";
    }

}