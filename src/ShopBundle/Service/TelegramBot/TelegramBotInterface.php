<?php

namespace ShopBundle\Service\TelegramBot;


interface TelegramBotInterface {



    public function listen($token);

    public function start($text, $fullname);

    public function comand($comand);

    public function showsort();

    public function showprod($sort);

    public function help();

    public function login($text);

    public function logout();

    public function shop();

    public function shopend($text);

    public function cart();

    public function cartedit($kol);

    public function order();

    public function orderinfo();

    public function orderedit($text);

    public function ordernext();

    public function orderend($text);

    public function NewOrder();

    public function sticker($value);

    public function redisname();

    public function redisOldComand();

    public function redisNewComand($newcomand);

    public function keyboard($array);

}