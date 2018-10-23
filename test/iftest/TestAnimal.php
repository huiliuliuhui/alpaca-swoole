<?php
/**
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/21 0021
 * Time: 下午 22:19
 */

namespace Test\Iftest;


class TestAnimal
{
    public $animal = null;
    function __construct(Animal $animal)
    {
        $this->animal = $animal;
    }

    public function howl(){
        $this->animal->howl();
    }

}