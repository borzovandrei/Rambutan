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

    public function listen()
    {

//        foreach ($results as $result) {
//            if ($result instanceof Callback) {
//                $callbackRunner->run($result);
//            }
//            if ($result instanceof Message) {
//            }
//        }

        $last_update = $this->redisClient->get("telegram");
        $url = 'https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/getUpdates?offset=' . $last_update;
        set_time_limit(0);

        while (true) {
            $result = file_get_contents($url);
            $result = json_decode($result, true);

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
                        } else {
                            $msg = "Данный тип сообщения не поддерживается.";
                        }

                        if (is_array($msg)) {
                            $msg[0] = urlencode($msg[0]);
                            $msg[1] = $this->keyboard($msg[1]);
                            file_get_contents("https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/sendMessage?text=" . $msg[0] . "&chat_id=" . $chat_id . "&reply_markup=" . $msg[1]);

                        } else {
                            $msg = urlencode($msg);
                            file_get_contents("https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/sendMessage?text=" . $msg . "&chat_id=" . $chat_id);
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


                        if ($this->oldCmd == "sort") {
                            $msg = $this->showprod($data);
                        } elseif ($this->oldCmd == "products") {
                            switch ($data) {
                                case "gotosort":
                                    $msg = $this->showsort();
                                    $this->redisNewComand("sort");
                                    break;
                                case "gotoprod":
                                    $msg = $this->showprod($this->mes_id);
                                    break;
                                case "buy":
                                    dump($this->prod_id);
                                    $msg = $this->inlineCart($this->prod_id);
                                    break;
                                default:
                                    $msg = $this->showproduct($data);
                                    break;
                            }
                        } else {
                            dump("Error");
                            $msg = $this->comand($data);
                        }

                        $msg[0] = urlencode($msg[0]);
                        $msg[1] = $this->keyboard($msg[1]);
                        $msg[2] = $value["callback_query"]["message"]["message_id"];

                        file_get_contents("https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/editMessageText?text=" . $msg[0] . "&chat_id=" . $chat_id . "&message_id=" . $msg[2] . "&reply_markup=" . $msg[1]);

                    }


                }
            }
        }
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
            $this->sendPhoto($file);

            $button[0] = [["text" => "Назад в продукты", "callback_data" => "gotoprod"]];
            $button[1] = [["text" => "Купить", "callback_data" => "buy"]];

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

//        if(method_exists($this, $text)) {
//
//          return  $this->{$text}();
//        }
//
//        return 'hello';


        $oldCmd = $this->oldCmd;
        if ($oldCmd == "login") {
            $mes = $this->login($text);
        } elseif ($oldCmd == "shop") {
            $mes = $this->shopend($text);
        } elseif ($oldCmd == "order") {
            $mes = $this->orderend($text);
        } else {
            switch ($text) {
                case "/help":
                    $mes = $this->help();
                    break;
                case "/login":
                    $this->redisNewComand("login");
                    $mes = $this->login(" ");
//                    $mes = "Введите логин и пароль";
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
                case "/myorder";
                    $this->redisNewComand("myorder");
                    $mes = "you order";
                    break;
                case "/start":
                    $this->redisNewComand("start");
                    $mes = "start";
                    break;
                case "/showsort":
                    $this->redisNewComand("sort");
                    $msg[0] = urlencode("В этом разделв вы можете купить товар.\n/cart - просмотр корзины \n/order - оформить заказ");
                    $msg[1] = $this->keyboard([["/order", "/shop"]]);
                    file_get_contents("https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/sendMessage?text=" . $msg[0] . "&chat_id=" . $this->chat_id . "&reply_markup=" . $msg[1]);
                    $mes = $this->showsort();
                    break;
                default:
                    $this->redisNewComand("none");
                    $mes = "Привет, " . $fullname . " . Я бот интернет магазина https://rambutan.ml\nДля справки напиши /help";
                    break;
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
        $keyboard = [["/login", "/myorder", "/shop"], ["/showsort"]];
        $mes = "Наш набор стикеров:\nhttps://t.me/addstickers/RambutanShop\nПрежде чем делать заказ, необходимо авторизоваться(/login).\nКоманды бота:\n/login - вход в личный кабинет магазина\n/myorder - статус заказа\n/showsort - категории товаров";
        return [$mes, $keyboard];
    }


    public function login($text)
    {

        $mes = 'что бы выполнить вход - авторизуйтесь';
        $button[0] = [["text" => "Авторизоваться", "url" => "https://rambutan.ml/login?user=".$this->username]];
        $this->redisNewComand("none");
        return [$mes,$button];


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
        $keyboard = ["/login", "/myorder", "/help"];
        return [$mes, $keyboard];

    }

    public function shop()
    {
        $this->redisNewComand("shop");
        $keyboard = [["/cart", "/shopend"], ["/order"]];
        $mes = "Пришлите стикер что бы положить товар в корзину \nДля просмотра корзины напишите /cart \nДля завершения покупок напишите /shopend";
        return [$mes, $keyboard];

    }

    public function shopend($text)
    {
        if ($text == '/shopend') {
            $this->redisNewComand("none");
            $keyboard = [["/cart", "/shop"], ["/order"]];
            $mes = "Вы закончили покупки \nДля просмотра корзины напишите /cart \nДля продолжения покупок напишите /shop\nДля оформления заказа напишите /order ";
        } elseif ($text == '/shop') {
            $keyboard = [["/cart", "/shopend"], ["/order"]];
            $mes = "Вы уже находитесь в режиме покупок";
        } else {
            $keyboard = [["/cart", "/shopend"], ["/order"]];
            $mes = "Что бы выйти из режима покупок напишите /shopend";
        }
        return [$mes, $keyboard];
    }

    public function cart()
    {
        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
        $prod = '';
        $n = 0;
        $s = 0;
        foreach ($redis['cart'] as $key => $value) {
            $n++;
            $s += $value['cost'] * $value['sum'];
            $prod .= "\n" . $n . ': ' . $value['name'] . ' по цене: ' . $value['cost'] . ' в колличестве ' . $value['sum'];
        }
        $mes = "Ваша корзина:" . $prod . "\nИтого: " . $s . "руб.\nДля удаления продуктов отправьте /cartedit:";
        $keyboard = [["/cartedit", "/shop"]];
        return [$mes, $keyboard];
    }

    public function cartedit($kol)
    {
        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
        if ($redis['login'] == 1) {
            $mes = "НЕОБХОДИМО РЕАЛИЗОВАТЬ!";
            $keyboard = ["/SDELAYU POTOM"];
//            $mes = "Продукт удален.\n" . $this->cart();
        } else {
            $mes = "Необходимо авторизоваться: /login";
            $keyboard = ["/login", "/help"];

        }

        return [$mes, $keyboard];
    }


    public function order()
    {
        $this->redisNewComand("order");
        $mes = "Что бы выйти из режима оформления заказа /orderend ";
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
        $redis['order'] = $redis['user'];
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
//        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
//        $redis = $redis['order'];
        if ($text == "/edit") {
            $mes = "Изменить:";
            $mes .= "\nФамилия получателя: /firstname";
            $mes .= "\nИмя получателя: /lastname";
            $mes .= "\nТелефон получателя: /phone";
            $mes .= "\nЕмайл получателя: /email";
            $mes .= "\nАдрес доставки: /address";
            $mes .= "\nСохранить: /ok ";
            $keyboard = [["/login", "/help", "/phone"], ["/email", "/address"], ["/ok", "/orderend"]];
        } else {
            $mes = "Для спрвки напишите /orderinfo";
            $keyboard = [["/edit", "/orderinfo"], ["/orderend"]];
        }
        return [$mes, $keyboard];

    }

    public function ordernext()
    {
        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
        $redis['order'] = $redis['user'];
        $this->redisClient->set("telegram_{$this->username}", json_encode($redis));

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
        return $mes = 'Заказ добавлен, https://rambutan.ml/room';
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