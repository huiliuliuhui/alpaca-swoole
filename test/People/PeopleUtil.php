<?php
/**
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/21 0021
 * Time: 下午 22:33
 */

namespace Test\People;


class PeopleUtil
{
    public $people;

    function __construct(People $people)
    {
        $this->people = $people;
    }


    public function sayHello(){
        $this->people->sayHello();
    }


}