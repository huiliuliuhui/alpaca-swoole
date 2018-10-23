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

//$container = new Container();

////绑定TestAnimal这个工具类
//$container->bind("TestAnimal", function($container, $module_name){
//    return new \Test\Iftest\TestAnimal($container->make($module_name));
//});
//
////绑定狗猫实体类
//$container->bind("Cat",\Test\Iftest\Cat::class);
//$container->bind("Dog",\Test\Iftest\Dog::class);
//
//
//
////实例化狗
//$dog = $container->make("TestAnimal", "Dog");
//$dog->howl();
//
////实例化猫
//$cat = $container->make("TestAnimal", "Cat");
//$cat->howl();
//
////绑定PeopleUtil这个工具类
//$container->bind("PeopleUtil", function($container, $module_name){
//    return new \Test\People\PeopleUtil($container->make($module_name));
//});
//
////绑定各种人实体类
//$container->bind("Black",\Test\People\Black::class);
//$container->bind("White",\Test\People\White::class);
//$container->bind("Yellow",\Test\People\Yellow::class);
//
////实例化黑人
//$dog = $container->make("PeopleUtil", "Black");
//$dog->sayHello();
//
////实例化白人
//$dog = $container->make("PeopleUtil", "White");
//$dog->sayHello();
//
//
//$container->bind("Apple", \Test\Food\Apple::class);
////$container->singleton("Apple", \Test\Food\Apple::class);
//
//$apple1 = $container->make("Apple");
//$apple1->num = 10;
////
//$apple2 = $container->make("Apple");
//$apple2->num = 101;
//
//echo $apple1->num .'xxx'. $apple2->num = 101;

//Container::bind("Apple", \Test\Food\Apple::class);
Container::singleton("Apple", \Test\Food\Apple::class);

$apple1 = Container::make("Apple");
$apple1->num = 10;
//
$apple2 = Container::make("Apple");
$apple2->num = 101;

echo $apple1->num .'xxx'. $apple2->num = 101;






