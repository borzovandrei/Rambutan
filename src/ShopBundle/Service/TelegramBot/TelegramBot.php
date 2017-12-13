<?php

namespace ShopBundle\Service\TelegramBot;


use Doctrine\ORM\EntityManagerInterface;
use Predis\ClientInterface;
use ShopBundle\Entity\Order;
use ShopBundle\Entity\OrderItem;

class TelegramBot implements TelegramBotInterface
{

    /** @var ClientInterface */
    private $redisClient;

    protected $username;

    protected $oldCmd;

    /** @var EntityManagerInterface */
    protected $entityManager;


    public function __construct(ClientInterface $redisClient, EntityManagerInterface $entityManager)
    {
        $this->redisClient = $redisClient;
        $this->entityManager = $entityManager;
    }

    public function listen()
    {


        $last_update = $this->redisClient->get("telegram");
        $url = 'https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/getUpdates?offset=' . $last_update;
        set_time_limit(0);

        while (true) {
            $result = file_get_contents($url);
            $result = json_decode($result, true);

            foreach ($result["result"] as $key => $value) {
                if ($last_update < $value['update_id']) {
                    $this->redisClient->set("telegram", $value['update_id']);
                    $last_update = $value['update_id'];

                    $chat_id = $value["message"]["chat"]["id"];
                    $this->username = $value["message"]["from"]["id"];
                    $fullname = $value["message"]["from"]["first_name"] . " " . $value["message"]["from"]["last_name"];


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
                }
            }
        }
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
        $oldCmd = $this->oldCmd;
        dump($oldCmd);
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
                    $mes = "Введите логин и пароль";
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
                default:
                    $this->redisNewComand("none");
                    $mes = "Привет, " . $fullname . " . Я бот интернет магазина https://rambutan.ml\nДля справки напиши /help";
                    break;
            }
        }
        return $mes;
    }

    public function help()
    {
        $this->redisNewComand("help");
        $keyboard = [["/login", "/myorder", "/login"], ["https://t.me/addstickers/RambutanShop"]];
        $mes = "Наш набор стикеров:\nhttps://t.me/addstickers/RambutanShop\nПрежде чем делать заказ, необходимо авторизоваться(/login).\nКоманды бота:\n/login - вход в личный кабинет магазина\n/myorder - статус заказа\n/help - помощь";
        return [$mes, $keyboard];
    }

    public function login($text)
    {
        $username = $this->username;
        $login = substr($text, 0, stripos($text, " "));
        $password = substr($text, stripos($text, " ") + 1, strlen($text));
        $em = $this->entityManager;
        $user = $em->getRepository("ShopBundle:Users")->findBy(array('username' => $login, 'phone' => $password));

        if (!$user) {
            $mes = "Вы ввели не коректные данные.";
        } else {
            $redis = $this->redisClient->get("telegram_{$username}");
            $redis = json_decode($redis, true);
            $redis['login'] = 1;
            $redis['user']['username'] = $user[0]->getUsername();
            $redis['user']['firstname'] = $user[0]->getFirstname();
            $redis['user']['lastname'] = $user[0]->getLastname();
            $redis['user']['phone'] = $user[0]->getPhone();
            $redis['user']['email'] = $user[0]->getEmail();
            $redis['user']['address'] = $user[0]->getAddress();
            $redis['sumCart'] = 0;
            $redis['cart']['prod0'] = null;
            $redis['order'] = null;
            $this->redisClient->set("telegram_{$username}", json_encode($redis));
            $mes = "Вы вошли как " . $user[0]->getFirstname() . " " . $user[0]->getLastname() . "\nДля покупок напишите /shop\nДля выхода напишите /logout";
        }
        $this->redisNewComand("none");
        return $mes;

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
        if ($this->oldCmd == "shop") {
            $keyboard = [["/cart", "/shopend"], ["/order"]];
            $mes = "Вы уже находитесь в режиме покупок";
        } else {
            $this->redisNewComand("shop");
            $keyboard = [["/cart", "/shop"], ["/order"]];
            $mes = "Пришлите стикер что бы положить товар в корзину \nДля просмотра корзины напишите /cart \nДля завершения покупок напишите /shopend";
        }
        return [$mes, $keyboard];

    }

    public function shopend($text)
    {
        if ($text == '/shopend') {
            $this->redisNewComand("none");
            $keyboard = [["/cart", "/shop"], ["/order"]];
            $mes = "Вы закончили покупки \nДля просмотра корзины напишите /cart \nДля продолжения покупок напишите /shop\nДля оформления заказа напишите /order ";
        } else {
            $keyboard = ["/cart", "/shopend"];
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
        return $mes;
    }

    public function cartedit($kol)
    {
        $redis = json_decode($this->redisClient->get("telegram_{$this->username}"), true);
        if ($redis['login'] == 1) {
            dump($redis["cart"]);
            $mes = "НЕОБХОДИМО РЕАЛИЗОВАТЬ!";
//            $mes = "Продукт удален.\n" . $this->cart();
        } else {
            $mes = "Необходимо авторизоваться: /login";
        }

        return $mes;
    }


    public function order()
    {
        $this->redisNewComand("order");
        $mes = "Что бы выйти из режима оформления заказа /orderend ";
        return $mes;
    }


    public function orderend($text)
    {
        if ($text == '/orderend') {
            $this->redisNewComand("none");
            $mes = "Вы вышли из оформления заказа \nДля просмотра корзины напишите /cart \nДля продолжения заказа /order";
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
        return $mes;

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
        } else {
            $mes = "Для спрвки напишите /edit";
        }
        return $mes;

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

        return $mes;
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
        $order->setCreated();
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


    public function keyboard($keyboard)
    {
        $resp = array("keyboard" => $keyboard, "resize_keyboard" => true, "one_time_keyboard" => true);
        return json_encode($resp);
    }

}