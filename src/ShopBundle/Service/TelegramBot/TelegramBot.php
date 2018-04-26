<?php

namespace ShopBundle\Service\TelegramBot;


use CURLFile;
use Doctrine\ORM\EntityManagerInterface;
use Predis\ClientInterface;
use ShopBundle\Entity\Order;
use ShopBundle\Entity\OrderItem;

class TelegramBot implements TelegramBotInterface
{


    /** @var ClientInterface */
    private $redisClient;

    /** @var EntityManagerInterface */
    protected $entityManager;

    protected $username;

    protected $oldCmd;

    protected $chat_id;

    protected $mes_id;

    protected $prod_id;


    public function __construct(ClientInterface $redisClient, EntityManagerInterface $entityManager)
    {
        $this->redisClient = $redisClient;
        $this->entityManager = $entityManager;
    }

    public function proxy($uri)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_PROXY, '188.40.141.216:3128');
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, TRUE);
    }

    public function listen()
    {


        $last_update = $this->redisClient->get("telegram");
        $url = 'https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/getUpdates?offset=' . $last_update;
        set_time_limit(0);
        while (true) {

            $result = $this->proxy($url);


            foreach ($result["result"] as $key => $value) {
                if ($last_update < $value['update_id']) {
                    $last_update = $value['update_id'];
                    $this->redisClient->set("telegram", $value['update_id']);


                    if (!array_key_exists("callback_query", $value)) {

                        $chat_id = $value["message"]["chat"]["id"];
                        $this->chat_id = $chat_id;
                        $this->username = $value["message"]["from"]["id"];
//                        $fullname = $value["message"]["from"]["first_name"] . " " . $value["message"]["from"]["last_name"];
                        $fullname = $this->getusername($value);

                        if (!$this->redisClient->get("telegram_{$this->username}")) {
                            $this->redisname();
                        }

                        $this->oldCmd = $this->redisOldComand();
                        dump($this->oldCmd);

                        if (array_key_exists("text", $value["message"])) {
                            $message = $value["message"]["text"];
                            $msg = $this->start($message, $fullname);
                        } elseif (array_key_exists("sticker", $value["message"])) {
                            $msg = $this->sticker($value);
                        } elseif (array_key_exists("photo", $value["message"])) {
                            $msg = "ФОТО";
                        } elseif (array_key_exists("document", $value["message"])) {
                            $msg = "ДОКУМЕНТ";
                        }
//                        else {
//                            $msg = "Данный тип сообщения не поддерживается.";
//                        }

                        if (is_array($msg)) {
                            $msg[0] = urlencode($msg[0]);
                            $msg[1] = $this->keyboard($msg[1]);
                            $this->proxy("https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/sendMessage?text=" . $msg[0] . "&chat_id=" . $chat_id . "&reply_markup=" . $msg[1]);

                        } else {
                            $msg = urlencode($msg);
                            $this->proxy("https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/sendMessage?text=" . $msg . "&chat_id=" . $chat_id);
                        }

                    } else {
                        $chat_id = $value["callback_query"]["message"]["chat"]["id"];
                        $this->chat_id = $chat_id;
                        $this->username = $value["callback_query"]["from"]["id"];


                        if (!$this->redisClient->get("telegram_{$this->username}")) {
                            $this->redisname();
                        }
                        $this->oldCmd = $this->redisOldComand();
                        $data = $value["callback_query"]["data"];
                        $message_id = $value["callback_query"]["message"]["message_id"];

                        if ($this->oldCmd == "sort") {
                            $msg = $this->showprod($data);
                            $this->sendEdit($msg, $message_id);
                        } elseif ($this->oldCmd == "cartedit") {
                            $msg = $this->cartdel($data);
                            $this->sendEdit($msg, $message_id);
                        } elseif ($this->oldCmd == "orderedit") {
                            if ($data == "orderedit") {
                                $msg = $this->orderedit($data);
                                $this->sendEdit($msg, $message_id);
                            } else {
                                $msg = $this->ordereditparam($data);
                                $this->sendEdit($msg, $message_id);
                            }
//                        } elseif ($this->oldCmd == "ordereditparam") {
//                            $msg = $this->ordereditparamone($data);         //не дата а текст сообщения
//                            $this->sendEdit($msg, $message_id);
                        } elseif ($this->oldCmd == "products") {
                            switch ($data) {
                                case "gotosort":
                                    $msg = $this->showsort();
                                    $this->redisNewComand("sort");
                                    $this->sendEdit($msg, $message_id);
                                    break;
                                case "gotoprod":
                                    $msg = $this->showprod($this->mes_id);
                                    $this->sendEdit($msg, $message_id);
                                    break;
                                case "buy":
                                    $msg = $this->inlineCart($this->prod_id);
                                    $this->sendEdit($msg, $message_id);
                                    break;
                                default:
                                    $msg = $this->showproduct($data);
                                    $this->sendEdit($msg, $message_id);
                                    break;
                            }
                        } else {
                            dump("Error " . $data);
                            $msg = $this->comand($data);
                        }
                    }
                }
            }
        }
    }

    public function sendEdit($msg, $value)
    {
        if ($msg[1]) {
            $msg[0] = urlencode($msg[0]);
            $msg[1] = $this->keyboard($msg[1]);
            $msg[2] = $value;
            $this->proxy("https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/editMessageText?text=" . $msg[0] . "&chat_id=" . $this->chat_id . "&message_id=" . $msg[2] . "&reply_markup=" . $msg[1]);
        } else {
            $msg[0] = urlencode($msg[0]);
            $msg[1] = $value;
            $this->proxy("https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/editMessageText?text=" . $msg[0] . "&chat_id=" . $this->chat_id . "&message_id=" . $msg[1]);

        }

        return true;
    }

    public function showsort()
    {
        $em = $this->entityManager;
        $sort = $em->getRepository("ShopBundle:Sort")->findAll();
        $button[] = null;

        foreach ($sort as $key => $value) {
            $button[$key] = [["text" => $value->getName(), "callback_data" => $value->getId()]];
        }
        $msg = 'Выберете категорию товаров: ';

        return [$msg, $button];
    }

    public function showprod($sort)
    {
        $em = $this->entityManager;
        $prod = $em->getRepository("ShopBundle:Products")->findBy(["id_class" => $sort]);
        $this->redisNewComand("products");
        if ($prod) {
            $button[] = null;
            foreach ($prod as $key => $value) {
                $button[$key] = [["text" => $value->getName(), "callback_data" => $value->getId()]];
            }
            array_push($button, [["text" => "Назад в категории", "callback_data" => "gotosort"]]);
            $msg = 'Выберете товар: ';
        } else {
            $button[0] = [["text" => "Назад в категории", "callback_data" => "gotosort"]];
            $msg = 'В данной категории нет товаров: ';
        }
        return [$msg, $button];
    }

    public function inlineCart($prod)
    {
        $em = $this->entityManager;
        $product = $em->getRepository("ShopBundle:Products")->find($prod);

        if ($product) {
            $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
            $newProd = false;
            foreach ($redis['cart'] as $key => $value) {
                if ($product->getName() == $value['name']) {
                    $redis['cart'][$key]['sum']++;
                    $newProd = true;
                    break;
                }
            }
            if (!$newProd) {
                $redis['cart']["prod" . $redis['sumCart']]['name'] = $product->getName();
                $redis['cart']["prod" . $redis['sumCart']]['cost'] = $product->getShopPrice();
                $redis['cart']["prod" . $redis['sumCart']]['sum'] = 1;
                $redis['sumCart']++;
            }
            $this->redisClient->set("telegram_{$this->username}", json_encode($redis));
            $mes = "Продукт '" . $product->getName() . "' добавлен.";
        } else {
            $mes = "Продукт не существет или еще нельзя купить через telegram.";
        }
        $button[0] = [["text" => "Назад в продукты", "callback_data" => "gotosort"]];
        return [$mes, $button];
    }


    public function showproduct($prod)
    {

        $em = $this->entityManager;
        $prod = $em->getRepository("ShopBundle:Products")->find($prod);

        if ($prod) {
            $msg = 'Информация о товаре: ';
            $msg .= "\nНазавание: " . $prod->getName();
            $msg .= "\nЦена: " . $prod->getShopPrice();
            $msg .= "\nОстаток: " . $prod->getBalanse();
            $this->mes_id = $prod->getIdClass()->getId();
            $this->prod_id = $prod->getId();
            $file = $prod->getAbsolutePath();
//            $this->sendPhoto($file);

            $button[0] = [["text" => "Подробнее", "url" => 'https://rambutan.ml/product/' . $prod->getId()]];
            $button[1] = [["text" => "Купить", "callback_data" => "buy"]];
            $button[2] = [["text" => "Назад в продукты", "callback_data" => "gotoprod"]];

        } else {
            $button[0] = [["text" => "Назад в категории", "callback_data" => "gotosort"]];
            $button[1] = $prod;
            $msg = 'Упс... Данный товар не найден( ';
        }
        return [$msg, $button];
    }


    public function redisname()
    {
        $username = $this->username;
        $redis["username"] = $username;
        $redis["oldcomand"] = 'none';
        $redis["login"] = 0;
        $this->redisClient->set("telegram_{$redis["username"]}", json_encode($redis));
    }


    public function redisOldComand()
    {
        $username = $this->username;
        $redis = $this->redisClient->get("telegram_{$username}");
        $redis = json_decode($redis, true);
        return $redis["oldcomand"];

    }

    public function redisNewComand($newcomand)
    {
        $username = $this->username;
        $redis = $this->redisClient->get("telegram_{$username}");
        $redis = json_decode($redis, true);
        $redis['oldcomand'] = $newcomand;
        $this->redisClient->set("telegram_{$username}", json_encode($redis));
        return true;
    }


    public function start($text, $fullname)
    {
//        $this->redisNewComand("help");
        $redis = $this->redisClient->get("telegram_{$this->username}");
        $redis = json_decode($redis, true);
        if ($redis['login'] == 0) {
            $oldCmd = $this->oldCmd;
            if ($text == "/login") {
                $mes = $this->login();
            } elseif ($oldCmd == "help") {
                $mes = $this->help();
            } else {
                $mes = $this->help();
            }
        } else {
            $oldCmd = $this->oldCmd;
            if ($oldCmd == "login") {
                $mes = $this->login();
            } elseif ($oldCmd == "shop") {
                $mes = $this->shopend($text);
            } elseif ($oldCmd == "order") {
                $mes = $this->orderend($text);
            } elseif ($oldCmd == "ordereditparam") {
                $mes = $this->ordereditparamone($text);
            } else {
                switch ($text) {
                    case "/help":
                        $mes = $this->help();
                        break;
                    case "/login":
                        $this->redisNewComand("login");
                        $mes = $this->login();
                        break;
                    case "/logout":
                        $mes = $this->logout();
                        break;
                    case "/shop":
                        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
                        if ($redis['login'] == 1) {
                            $mes = $this->shop();
                        } else {
                            $mes = "Вам необходимо авторизоваться.";
                        }
                        break;
                    case "/cart":
                        $mes = $this->cart();
                        break;
                    case "/cartedit":
                        $mes = $this->cartedit($text);
                        break;
                    case "/shopend":
                        $mes = $this->shopend($text);
                        break;
                    case "/order":
                        $mes = $this->order();
                        break;
                    case "/orderend":
                        $mes = $this->orderend($text);
                        break;
                    case "/start":
                        $this->redisNewComand("start");
                        $message = "start";
                        $keyboard = [["/help"]];
                        $mes = [$message, $keyboard];
                        break;
                    case "/showsort":
                        $this->redisNewComand("sort");
                        $msg[0] = urlencode("В этом разделв вы можете купить товар.\n/cart - просмотр корзины \n/order - оформить заказ");
                        $msg[1] = $this->keyboard([["/cart", "/order"], ["/help"]]);
                        $this->proxy("https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/sendMessage?text=" . $msg[0] . "&chat_id=" . $this->chat_id . "&reply_markup=" . $msg[1]);
                        $mes = $this->showsort();
                        break;
                    default:
                        $this->redisNewComand("none");
                        $message = "Привет, " . $fullname . " . Я бот интернет магазина https://rambutan.ml\nДля справки напиши /help";
                        $keyboard = [["/help"]];
                        $mes = [$message, $keyboard];
                        break;
                }
            }
        }

        return $mes;
    }

    public function comand($comand)
    {
        if ($comand = "next") {
            $msg = "next";
        } elseif ($comand = "down") {
            $msg = "down";
        } else {
            $msg = "ELSE";
        }
        return $msg;
    }

    public function help()
    {
        $this->redisNewComand("help");
        $redis = $this->redisClient->get("telegram_{$this->username}");
        $redis = json_decode($redis, true);
        if ($redis['login'] == 0) {
            $keyboard = [["/login", "/order", "/shop"], ["/showsort"]];
            $mes = "Наш набор стикеров:\nhttps://t.me/addstickers/RambutanShop\nПрежде чем делать заказ, необходимо авторизоваться(/login).\nКоманды бота:\n/login - вход в личный кабинет магазина\nОстальные будут доступны после авторизации";
            return [$mes, $keyboard];
        } else {
            $keyboard = [["/login", "/order", "/shop"], ["/showsort"]];
            $mes = "Наш набор стикеров:\nhttps://t.me/addstickers/RambutanShop\nКоманды бота:\n/cart - просмотр корзины\n/order - оформление заказа\n/showsort - категории товаров\n/order - оформление заказа\n/shop - покупка с помощью стикеров\nЧто бы выйти /logout";
            return [$mes, $keyboard];
        }
    }


    public function login()
    {

        $mes = 'что бы выполнить вход - авторизуйтесь';
        $button[0] = [["text" => "Авторизоваться", "url" => "https://rambutan.ml/login?user=" . $this->username]];
        $this->redisNewComand("none");
        return [$mes, $button];


    }

    public function logout()
    {
        $username = $this->username;
        $redis = $this->redisClient->get("telegram_{$username}");
        $redis = json_decode($redis, true);
        if ($redis['login'] == 1) {
            $redis['login'] = 0;
            unset ($redis['user'], $redis["cart"], $redis["sumCart"], $redis["order"]);
            $this->redisClient->set("telegram_{$username}", json_encode($redis));

            $mes = "Вы успешно вышли!";
        } else {
            $mes = "Вы не были авторизованы(";
        }
        $this->redisNewComand("none");
        $keyboard = ["/login", "/help"];
        return [$mes, $keyboard];

    }

    public function shop()
    {
        $this->redisNewComand("shop");
        $keyboard = [["/cart", "/shopend"], ["/order"]];
        $mes = "Пришлите стикер что бы положить товар в корзину \nДля просмотра корзины напишите /cart \nДля завершения покупок напишите /shopend \nДля оформления заказа напишите /order";
        return [$mes, $keyboard];

    }

    public function shopend($text)
    {
        if ($text == '/shopend') {
            $this->redisNewComand("none");
            $keyboard = [["/cart", "/shop"], ["/order", "/help"]];
            $mes = "Вы закончили покупки \nДля просмотра корзины напишите /cart \nДля продолжения покупок напишите /shop\nДля оформления заказа напишите /order ";
        } elseif ($text == '/shop') {
            $keyboard = [["/shopend"]];
            $mes = "Вы уже находитесь в режиме покупок";
        } else {
            $keyboard = [["/shopend"]];
            $mes = "Что бы выйти из режима покупок напишите /shopend";
        }
        return [$mes, $keyboard];
    }

    public function cart()
    {
        $this->redisNewComand('cart');
        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
        $prod = '';
        $n = 0;
        $s = 0;
        sort($redis['cart']);
        foreach ($redis['cart'] as $key => $value) {
            $newkey = 'prod' . $key;
            $redis['cart'][$newkey] = $redis['cart'][$key];
            unset($redis['cart'][$key]);
        }
        $this->redisClient->set("telegram_{$this->username}", json_encode($redis));

        foreach ($redis['cart'] as $key => $value) {
            $n++;
            $s += $value['cost'] * $value['sum'];
            $prod .= "\n" . $n . ': ' . $value['name'] . ' по цене: ' . $value['cost'] . ' в колличестве ' . $value['sum'];
        }

        $mes = "Ваша корзина:" . $prod . "\nИтого: " . $s . "руб.\nДля удаления продуктов отправьте /cartedit:";
        $keyboard = [["/cartedit", "/showsort"], ["/order"]];
        return [$mes, $keyboard];
    }

    public function cartedit($text)
    {

        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
        if ($redis['login'] == 1) {
            $button[] = null;
            $this->redisNewComand('cartedit');
            if (array_key_exists('prod0', $redis['cart'])) {
                foreach ($redis['cart'] as $key => $value) {
                    dump($value['name']);
                    $key = substr($key, 4);
                    dump($key);
                    $button[$key] = [["text" => "Удалить \"" . $value['name'] . "\"", "callback_data" => $key]];
                }
                $mes = "Выберете, какокй продукт вы желаете убрать из корзины:";
                return [$mes, $button];
            } else {
                $this->redisNewComand('cart');
                $mes = "В корзине нет товаров";
                $button = [["/showsort", "/help"]];
                return [$mes, $button];
            }

        } else {
            $this->redisNewComand('none');
            $mes = "Необходимо авторизоваться: /login";
            $button = ["/login", "/help"];
            return [$mes, $button];
        }
    }


    public function cartdel($text)
    {
        dump($text);
        if ($text != null) {
            $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);

            unset($redis['cart']['prod' . $text]);
            sort($redis['cart']);
            foreach ($redis['cart'] as $key => $value) {
                $newkey = 'prod' . $key;
                $redis['cart'][$newkey] = $redis['cart'][$key];
                unset($redis['cart'][$key]);
            }


            $this->redisClient->set("telegram_{$this->username}", json_encode($redis));
            $this->redisNewComand('none');
            $msg = "Товар удален.\n/cart - просмотр корзины \n/cartedit - удалить еще";

            $button[0] = [["text" => "Товар удален.", "callback_data" => "/cartedit"]];
            //$this->proxy("https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/sendMessage?text=" . $msg[0] . "&chat_id=" . $this->chat_id . "&reply_markup=" . $msg[1]);

        }

        return [$msg, $button];


    }


    public function order()
    {
        $this->redisNewComand("order");
        $mes = "Для просмотра информации о заказе /orderinfo \nЧто бы выйти из режима оформления заказа /orderend ";
        $keyboard = [["/orderinfo", "/orderend"]];
        return [$mes, $keyboard];
    }


    public function orderend($text)
    {
        if ($text == '/orderend') {
            $this->redisNewComand("none");
            $keyboard = [["/cart", "/order"]];
            $mes = "Вы вышли из оформления заказа \nДля просмотра корзины напишите /cart \nДля продолжения заказа /order";
            return [$mes, $keyboard];
        } elseif ($text == '/cart') {
            $mes = $this->cart();
        } elseif ($text == '/edit') {
            $mes = $this->orderedit($text);
        } elseif ($text == '/next') {
            $mes = $this->ordernext();
        } elseif ($text == '/yesmyorder') {
            $mes = $this->newOrder();
        } else {
            $mes = $this->orderinfo();
        }
        return $mes;
    }


    public function orderinfo()
    {
        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
        if ($redis['order']==null){
            $redis['order'] = $redis['user'];
        }
        $redis['orderedit'] = null;
        $this->redisClient->set("telegram_{$this->username}", json_encode($redis));

        $redis = $redis['order'];
        $mes = "Информация по заказу:\n";
        $mes .= "Имя получателя: " . $redis['firstname'];
        $mes .= "\nФамилия получателя: " . $redis['lastname'];
        $mes .= "\nТелефон получателя: " . $redis['phone'];
        $mes .= "\nЕмайл получателя: " . $redis['email'];
        $mes .= "\nАдрес доставки: " . $redis['address'];
        $mes .= "\nСписок покупок ? /cart ";
        $mes .= "\nЖелаете изменть данные ? /edit ";
        $mes .= "\nПродолжить с этими данными ? /next ";
        $keyboard = [["/cart", "/edit", "/next"], ["/orderend"]];
        return [$mes, $keyboard];
    }

    public function orderedit($text)
    {
        $this->redisNewComand("orderedit");
        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
        $button[] = null;
        if (array_key_exists('order', $redis)) {
            $n = 0;
            foreach ($redis['order'] as $key => $value) {
                $button[$n] = [["text" => "Изменить \"" . $value . "\"", "callback_data" => $key]];
                $n++;
            }
            array_pop($button);
            $button[$n - 1] = [["text" => "Продолжить заказ", "callback_data" => "ordernext"]];
            $mes = "Выберете, что желаете изменить:";
            return [$mes, $button];
        } else {
            $mes = "Что-то не так. /help";
            $button = [["/showsort", "/help"]];
            return [$mes, $button];
        }
    }

    public function ordereditparam($text)
    {
        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);

//        if ($redis['orderedit'] != null) {
//            $redis['order'][$redis['orderedit']] = $text;
//            $this->redisClient->set("telegram_{$this->username}", json_encode($redis));
//
//        }


        if ($text == 'username' or $text == 'firstname' or $text == 'lastname' or $text == 'phone' or $text == 'email' or $text == 'address') {


            dump('text_!null  ' . $text);
            $redis['oldcomand'] = "ordereditparam";
            $redis['orderedit'] = $text;
            $this->redisClient->set("telegram_{$this->username}", json_encode($redis));
            $msg = "Введите нужную информацию:";
            $button = null;


        } elseif ($text == 'ordernext') {
            $this->redisNewComand("order");
            $msg = $this->orderinfo()[0];
            $button = null;

        } elseif ($redis['orderedit'] != null and $this->redisOldComand() == "orderedit") {
            $redis['order'][$redis['orderedit']] = "ИЗМЕНЕНО";
        } else {
            $msg = "Что-то не так (ordereditparam) " . $text;
            $button[0] = [["text" => "Ошибка.", "callback_data" => "none"]];
        }
        return [$msg, $button];
    }


    public function ordereditparamone($text)
    {
        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
        dump(121212);
        $olddata = $redis['order'][$redis['orderedit']];
        $redis['order'][$redis['orderedit']] = $text;
        $redis['orderedit'] = null;
        $redis['oldcomand'] = "orderedit";
        $this->redisClient->set("telegram_{$this->username}", json_encode($redis));

//        }
//        $this->redisNewComand("orderedit");

        $msg = "ИЗМЕНЕННО: " . $olddata . " -> " . $text;
        $button[0] = [["text" => "Ок.", "callback_data" => "ordernext"]];

        return [$msg, $button];

    }

    public function ordernext()
    {
        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
//        $redis['order'] = $redis['user'];
//        $this->redisClient->set("telegram_{$this->username}", json_encode($redis));

        $mes = "Подтверждаете заказ?\n";
        $redis = $redis['order'];
        $mes .= "Имя получателя: " . $redis['firstname'];
        $mes .= "\nФамилия получателя: " . $redis['lastname'];
        $mes .= "\nТелефон получателя: " . $redis['phone'];
        $mes .= "\nЕмайл получателя: " . $redis['email'];
        $mes .= "\nАдрес доставки: " . $redis['address'];
        $mes .= "\nСписок покупок ? /cart ";
        $mes .= "\nДА /yesmyorder ";
        $keyboard = [["/cart", "/yesmyorder"], ["/orderend"]];
        return [$mes, $keyboard];
    }

    public function NewOrder()
    {
        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
        $s = 0;
        //стоимость заказа
        foreach ($redis['cart'] as $key => $value) {
            $s += $value['cost'] * $value['sum'];
        }

        //уменьшение товара на складе, увеличение рейтнга
        foreach ($redis["cart"] as $key => $value) {
            $prod_balance = $this->entityManager->getRepository('ShopBundle:Products')->findBy(["name" => $redis["cart"][$key]["name"]]);
            $balance = $prod_balance[0]->getBalanse();
            $newbalance = $balance - $value["sum"];
            $prod_balance[0]->setBalanse($newbalance);

            $rating = $prod_balance[0]->getRating();
            $newrating = $rating + $value["sum"];
            $prod_balance[0]->setRating($newrating);

            $this->entityManager->persist($prod_balance[0]);
            $this->entityManager->flush();

        }

        //новый заказ
        $redis = $redis['order'];
        $order = new Order();
        $order->setFirstname($redis['firstname']);
        $order->setLastname($redis['lastname']);
        $order->setEmail($redis['email']);
        $user = $this->entityManager->getRepository('ShopBundle:Users')->find($redis['id']);
        $order->setUser($user);
        $order->setPhone($redis['phone']);
        $order->setAddress($redis['address']);
        $status = $this->entityManager->getRepository('ShopBundle:StatusOrder')->find(1);
        $order->setStatus($status);
        $order->setDate(new \DateTime(date("d-m-Y G:i:s")));
        $order->setPrice($s);
        $order->setComment("Заказанно через telegram");
        $order->setOderitem(strval(md5(rand())));

        //продукты нового заказа
        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
        $result = $redis["cart"];
        foreach ($result as $key => $value) {
            $idprod = $this->entityManager->getRepository('ShopBundle:Products')->findBy(['name' => $result[$key]["name"]]);
            $orderitem = new OrderItem();
            $orderitem->setOrderprod($order->getOderitem());
            $orderitem->setItem($idprod[0]->getId());
            $orderitem->setSum($result[$key]["sum"]);
            $this->entityManager->persist($orderitem);
        }


        //отправка в бд + обнулени информации
        $this->entityManager->persist($order);
        $this->entityManager->flush();
        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
        unset($redis["cart"]);
        $redis['cart']['prod0'] = null;
        $redis["sumCart"] = 0;
        $redis["order"] = null;
        $this->redisClient->set("telegram_{$this->username}", json_encode($redis));
        $this->redisNewComand("none");
        $mes = 'Заказ добавлен, https://rambutan.ml/room';
        $keyboard = [["/help"]];
        return [$mes, $keyboard];
    }


    public function sticker($value)
    {
        if ($this->oldCmd == "shop") {
            $product = $value["message"]["sticker"]["file_id"];
            $em = $this->entityManager;
            $product = $em->getRepository("ShopBundle:Products")->findBy(array('telegram' => $product));
            if ($product) {
                $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);

                $newProd = false;
                foreach ($redis['cart'] as $key => $value) {
                    if ($product[0]->getName() == $value['name']) {
                        $redis['cart'][$key]['sum']++;
                        $newProd = true;
                        break;
                    }
                }
                if (!$newProd) {
                    $redis['cart']["prod" . $redis['sumCart']]['name'] = $product[0]->getName();
                    $redis['cart']["prod" . $redis['sumCart']]['cost'] = $product[0]->getShopPrice();
                    $redis['cart']["prod" . $redis['sumCart']]['sum'] = 1;
                    $redis['sumCart']++;
                }

                $this->redisClient->set("telegram_{$this->username}", json_encode($redis));
                $mes = "Продукт '" . $product[0]->getName() . "' добавлен.";
            } else {
                $mes = "Продукт не существет или еще нельзя купить через telegram.";
            }
        } else {
            $mes = $value["message"]["sticker"]["emoji"];
        }

        return $mes;
    }

    public function getusername($value)
    {
        $username = " ";
        if (isset($value["message"]["from"]["first_name"])) {
            $username .= $value["message"]["from"]["first_name"] . " ";
        }
        if (isset($value["message"]["from"]["last_name"])) {
            $username .= $value["message"]["from"]["last_name"];
        }
        return $username;
    }

    public function keyboard($keyboard)
    {
        if (isset($keyboard[0][0]) && is_array($keyboard[0][0])) {
            $resp = ["inline_keyboard" => $keyboard];
        } else {
            $resp = array("keyboard" => $keyboard, "resize_keyboard" => true, "one_time_keyboard" => true);
        }
        return json_encode($resp);
    }

    public function sendPhoto($path)
    {
        $bot_url = "https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/";
        $url = $bot_url . "sendPhoto?chat_id=" . $this->chat_id;

        $post_fields = array('chat_id' => $this->chat_id,
            'photo' => new CURLFile(realpath($path))
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type:multipart/form-data"
        ));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        curl_exec($ch);

    }
}