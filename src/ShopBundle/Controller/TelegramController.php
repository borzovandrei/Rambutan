<?php
namespace ShopBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TelegramController extends Controller
{
    public function telegramAction()
    {
        $last_update = 285615263;
        $url = 'https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/getUpdates?offset=' . $last_update;
        set_time_limit(0);
        $em = $this->getDoctrine();


        while (true) {
            $result = file_get_contents($url);
            $result = json_decode($result, true);
            dump($result);
            foreach ($result["result"] as $key => $value) {
                if ($last_update < $value['update_id']) {

                    $last_update = $value['update_id'];
                    $chat_id = $value["message"]["chat"]["id"];


                    if (array_key_exists("sticker",$value["message"])) {
                        $sticker = $value["message"]["sticker"];

                        $mes= $value["message"]["sticker"]["emoji"];

                    } else {
                        $sticker = '';
                        $mes="none";
                    }

                    if (array_key_exists("text",$value["message"])) {
                        $text = $value["message"]["text"];
                    } else $text = '';


                    if (substr($text, 0, 1) == "/") {
                        switch ($text) {
                            case "/help":
                                $mes = "Наш набор стикеров:
                                https://t.me/addstickers/RambutanShop
                                Прежде чем делать заказ, необходимо авторизоваться(/login).
                                Команды бота:
                                /login - вход в личный кабинет магазина'
                                /myorder - статус заказа
                                /help - помощь";
                                break;
                            case "/login":
                                $mes = "Введите в форме:'login ... ,  pass ...'";
                                break;
                            case "/myorder":
                                $mes = "myorder";
                                break;
                            case "/start":
                                $mes = "start";
                                break;
                        }


                    } elseif (stripos($text, "login") !== false and stripos($text, "pass") !== false) {

                        $login = substr($text, stripos($text, "login")+5 , stripos($text, "pass")-stripos($text, "login")-5);
                        $password = substr($text, stripos($text, "pass")+4, strlen($text));
                        $user = $em->getRepository("ShopBundle:Users")->findBy(array('username' => trim($login), 'phone' => trim($password)));

                        if ($user) {
                            $redis = $this->get('snc_redis.default');
                            $username = $value["message"]["chat"]["username"];
                            $data = trim($login);
                            $redis->set("telegram_{$username}", $data);

                            $mes = "вы выполнили вход под: " . $user[0]->getUsername() . " " . $user[0]->getPhone();
                        } else {
                            $mes = "Данный пользователь не найден: ".$login." | ".$password;
                        }
                    } elseif ($text == "привет") {
                        $mes = "Привет, " . $value["message"]["from"]["first_name"] . " " . $value["message"]["from"]["last_name"];
                    } else {
                        $mes = "для начала работы напиши /help";
                    }


                    $keyboard = [["/start", "/help", "/login"], ["4"]];
                    $resp = array("keyboard" => $keyboard, "resize_keyboard" => true, "one_time_keyboard" => true);
                    $reply = json_encode($resp);
                    file_get_contents('https://api.telegram.org/bot429703583:AAEToCrYueFNrgRAdX1NyP8TYJvt5QQDrEY/sendMessage?text=' . $mes . '&chat_id=' . $chat_id . "&reply_markup=" . $reply);

                }
            }

        }

        return phpinfo();
    }
}