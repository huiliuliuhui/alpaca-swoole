<?php
/**
 * Created by 刘辉.
 * User: HiWin10
 * Date: 2018/10/21 0021
 * Time: 下午 22:09
 */
namespace Test;
use Test\Food\Apple;

require_once __DIR__ . "/../autoLoad.php";


//绑定TestAnimal这个工具类
Container::bind("TestAnimal", function($container, $module_name){
    return new \Test\Iftest\TestAnimal(Container::make($module_name));
});

//绑定狗猫实体类
Container::bind("Cat",\Test\Iftest\Cat::class);
Container::bind("Dog",\Test\Iftest\Dog::class);



//实例化狗
$dog = Container::make("TestAnimal", "Dog");
$dog->howl();

//实例化猫
$cat = Container::make("TestAnimal", "Cat");
$cat->howl();

//绑定PeopleUtil这个工具类
Container::bind("PeopleUtil", function($container, $module_name){
    return new \Test\People\PeopleUtil(Container::make($module_name));
});

//绑定各种人实体类
Container::bind("Black",\Test\People\Black::class);
Container::bind("White",\Test\People\White::class);
Container::bind("Yellow",\Test\People\Yellow::class);

//实例化黑人
$dog = Container::make("PeopleUtil", "Black");
$dog->sayHello();

//实例化白人
$dog = Container::make("PeopleUtil", "White");
$dog->sayHello();

//实例化黄人
$dog = Container::make("PeopleUtil", "Yellow");
$dog->sayHello();


Container::bind("Apple", \Test\Food\Apple::class);
//Container::singleton("Apple", \Test\Food\Apple::class);

$apple1 = Container::make("Apple");
$apple1->num = 10;
//
$apple2 = Container::make("Apple");
$apple2->num = 101;

echo $apple1->num .'xxx'. $apple2->num = 101;






