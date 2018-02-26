<?php

namespace ShopBundle\Controller;

use ShopBundle\Entity\Chat;
use ShopBundle\Entity\ChatRoom;
use ShopBundle\Entity\Feedback;
use ShopBundle\Entity\Order;
use ShopBundle\Entity\OrderItem;
use ShopBundle\Entity\Users;
use ShopBundle\Form\EmileType;
use ShopBundle\Form\NewUserType;
use ShopBundle\Form\OrderType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class PageController extends Controller
{

    //метод авторизазии
    public function loginAction(Request $request)
    {
        $token = null;
        $data = null;
        if ($request->get("code") !== null) {
            $token = json_decode(file_get_contents('https://oauth.vk.com/access_token?client_id=' . $this->getParameter('vk_id') . '&display=page&redirect_uri=' . $this->getParameter('vk_url') . '&client_secret=' . $this->getParameter('vk_secret') . '&code=' . $_GET["code"]), true);
        }
        if ($token !== null) {
            $data = json_decode(file_get_contents('https://api.vk.com/method/users.get?user_id=' . $token['user_id'] . '&access_token=' . $token['access_token'] . '&fields=uid,sex,bdate,city,nickname,sex'), true);
        }
        if ($data !== null) {
            var_dump($data);
            $this->regVK($data["response"][0]);

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('ShopBundle:Users')->findOneBy(['username'=>$data["response"][0]["uid"]]);
            $token = new UsernamePasswordToken(
                $user,
                null,
                'vk',
                $user->getRoles()
            );

            $this->get("security.token_storage")->setToken($token);
            $request->getSession()->set('_security_vk', serialize($token));

            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);


            return $this->redirectToRoute("shop_room");
        }


        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);

        } else {
            $error = $request->getSession()->get(Security::AUTHENTICATION_ERROR);
        }

        $res = $this->render('ShopBundle:Page:login.html.twig', [
            'last_username' => $request->getSession()->get(Security::LAST_USERNAME),
            'error' => $error
        ]);

        if (isset($_GET["user"])) {
            $res->headers->setCookie(new Cookie("name", $_GET["user"], Ctime() + 3600));
        }

        return $res;


    }

    //метод вк_регистрации
    private function regVK($data)
    {
        $em = $this->getDoctrine()->getManager();
        $username = 'vk' . $data["uid"];
        $find = $em->getRepository('ShopBundle:Users')->findBy(['username' => $username]);
        dump($find);

        if (!$find) {
            $user = new Users();
            dump($data);
            if ($data["sex"] == 1) {
                $data["sex"] = 2;
            } else {
                $data["sex"] = 1;
            }
            $sex = $em->getRepository('ShopBundle:Sex')->findOneBy(['id' => $data["sex"]]);
            $role = $em->getRepository('ShopBundle:Role')->find(2);
            $user->setAddress($data["city"])
                ->setAge(new \DateTime($data["bdate"]))
                ->setUsername('vk' . $data["uid"])
                ->setFirstname($data["first_name"])
                ->setLastname($data["last_name"])
                ->setEmail('email@vk.com')
                ->setPhone('000')
                ->setSex($sex)
                ->setPath('vk_user.png');

            $user->setSalt(md5(time()));
            $encoder = new MessageDigestPasswordEncoder('sha512', true, 10);
            $password = $encoder->encodePassword($data["uid"], $user->getSalt());
            $user->setPassword($password);
            $user->getUserRoles()->add($role);


            $em->persist($user);
            $em->flush();

        }


        return true;
//        return $this->redirectToRoute("shop_homepage");
    }


    public function regVkAction()
    {
        return $this->redirect("https://oauth.vk.com/authorize?client_id=6382685&display=page&redirect_uri=http://dev.rambutan.ml/login&response_type=code");
    }


    //метод регистрации
    public function regAction(Request $request)
    {
        $user = new Users();

        $form = $this->createForm(NewUserType::class, $user);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $role = $em->getRepository('ShopBundle:Role')->find(2);
        $user->getUserRoles()->add($role);

        $user->setSalt(md5(time()));
        $encoder = new MessageDigestPasswordEncoder('sha512', true, 10);
        $password = $encoder->encodePassword($form["password"]->getData(), $user->getSalt());
        $user->setPassword($password);

        dump($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->upload();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("shop_login");
        }

        return $this->render('ShopBundle:Page:reg.html.twig', [
            'form_add_user' => $form->createView()
        ]);
    }


    //О компании
    public function aboutAction(Request $request)
    {
//
//        $em = $this->getDoctrine()->getManager();
//        $user = $em->getRepository('ShopBundle:Users')->find(1);
//        $token = new UsernamePasswordToken(
//            $user,
//            null,
//            'vk',
//            $user->getRoles()
//        );
//
//        $this->get("security.token_storage")->setToken($token);
//        $request->getSession()->set('_security_vk', serialize($token));
//
//        $event = new InteractiveLoginEvent($request, $token);
//        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);


        $feedback = new Feedback();
        $form = $this->createForm(EmileType::class, $feedback);

        if ($request->isMethod($request::METHOD_POST)) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $message = \Swift_Message::newInstance()
                    ->setSubject('Rambutan feedback')
                    ->setFrom('rambutan@feedback.com')
                    ->setTo('borzovandrei45@gmail.com')
                    ->setBody($this->renderView('ShopBundle:Email:feedbackEmail.txt.twig', ['enquiry' => $feedback]));

                $this->get('mailer')->send($message);

                $this->get('session')->getFlashBag()->add('shop-notice', 'Спасибо! Ваше письмо было отправлено!');

                return $this->redirect($this->generateUrl('shop_about'));

            }

        }
        return $this->render('ShopBundle:Page:about.html.twig', [
            'form' => $form->createView()
        ]);
    }

    //ЛИЧНЫЙ КАБИНЕТ
    public function roomAction(Request $request)
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $orders = $em->getRepository('ShopBundle:Order')->findBy(['user' => $user]);

        return $this->render('ShopBundle:Page:room.html.twig', [
            'user' => $user,
            'order' => $orders,
        ]);
    }

    //редактировнаие
    public function room_editAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $data = $data['data'];

        $em = $this->getDoctrine()->getManager();
        $sex = $em->getRepository('ShopBundle:Sex')->findOneBy(['name' => $data["sex"]]);
        $user = $this->getUser();

        $user->setUsername($data["username"])
            ->setFirstname($data["firstname"])
            ->setLastname($data["lastname"])
            ->setPhone($data["tel"])
            ->setAddress($data["address"])
            ->setEmail($data["email"])
            ->setAge(new \DateTime($data["date"]));
        $user->setSex($sex);

        $em->persist($user);
        $em->flush();

        return new JsonResponse($data);
    }

    //редактировнаие пароля
    public function room_edit_passAction(Request $request)
    {

        $pass = json_decode($request->getContent(), true);
        $pass = $pass['pass'];

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $user->setSalt(md5(time()));
        $encoder = new MessageDigestPasswordEncoder('sha512', true, 10);
        $password = $encoder->encodePassword($pass, $user->getSalt());
        $user->setPassword($password);

        $em->persist($user);
        $em->flush();

//        $this->get('session')->getFlashBag()->add('pass_mes', 'Спасибо! Ваше письмо было отправлено! на ' . $pass);

        return new JsonResponse([
            'redirectLink' => $this->generateUrl('_security_logout')
        ], 200);
    }


    //редактировнаие изображеия
    public function room_edit_imgAction(Request $request)
    {
        $user = $this->getUser();
        var_dump("1231231");
        $user->upload();
        $em = $this->getDoctrine()->getManager();

        return true;

    }

    //быстрый просмотр продуктов
    public function room_prodAction(Request $request)
    {
        $id = json_decode($request->getContent(), true);
        $id = $id['id'];

        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository('ShopBundle:OrderItem')->findBy(["orderprod" => $id]);
        dump($order);
        $prod = [];
        foreach ($order as $k => $v) {
            $product = $em->getRepository('ShopBundle:Products')->findOneBy(["id" => $v->getItem()]);
            $prod[$product->getName()] = $v->getSum();
        }

        dump($prod);
        $response = new JsonResponse();
        $response->setData($prod);

        return $response;
    }

    //просмотр определенного заказа
    public function room_orderAction(Request $request)
    {
        $products = null;
        $sum = null;
        $id = $request->get("id");
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository('ShopBundle:Order')->find($id);
        $orderitem = $em->getRepository('ShopBundle:OrderItem')->findBy(['orderprod' => $order->getOderitem()]);
        foreach ($orderitem as $key => $value) {
            $products[] = $em->getRepository('ShopBundle:Products')->find($orderitem[$key]->getItem());
            $sum[] = $orderitem[$key]->getSum();
        }

        $params = [
            'geocode' => $order->getAddress(),           // адрес
            'format' => 'json',                          // формат ответа
            'results' => 1,                              // количество выводимых результатов
        ];
        $response = json_decode(file_get_contents('http://geocode-maps.yandex.ru/1.x/?' . http_build_query($params, '', '&')));

        if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {
            $map = explode(" ", $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
        } else {
            $map = null;
        }
//        $map = null;

        return $this->render('ShopBundle:Page:room_order.html.twig', [
            'order' => $order,
            'products' => $products,
            'sum' => $sum,
            'map' => $map,
        ]);
    }


    //корзина
    public function cartAction(Request $request)
    {
        $request = Request::createFromGlobals();
        $cardId = $request->cookies->get('PHPSESSID');

        $redis = $this->get('snc_redis.default');
        $redis->get("cart_{$cardId}");
        $redis = json_decode($redis->get("cart_{$cardId}"), true);


        if ($redis["products"]) {
            foreach ($redis["products"] as $key => $value) {
                $manyprod[] = $redis["products"][$key]["id"];
            }
            $result = array_count_values($manyprod);
            $em = $this->getDoctrine()->getManager();
            foreach ($result as $key => $value) {
                $product[] = $em->getRepository('ShopBundle:Products')->find($key);
                $prod = $em->getRepository('ShopBundle:Products')->find($key);
                $price[] = $prod->getShopPrice();
                $ar = array_map(function ($price, $result) {
                    return $price * $result;
                }, $price, $result);
            }
        } else {
            $product = null;
            $result = null;
            $price[] = null;
            $ar[] = null;
        };


        $sum = array_sum($ar);

        return $this->render('ShopBundle:Page:cart.html.twig', array(
            'carts' => $product,
            'result' => $result,
            'sum' => $sum,
        ));
    }


    //заказ
    public function orderAction(Request $request)
    {
        //ПРАВАЯ СТОРОНА
        $order = new Order();
        $user = $this->getUser();
        if (!$user) {
            $firstname = null;
            $lastname = null;
            $phone = null;
            $address = null;
            $email = null;
        } else {
            $firstname = $user->getFirstname();
            $lastname = $user->getLastname();
            $phone = $user->getPhone();
            $address = $user->getAddress();
            $email = $user->getEmail();
        }
        $form = $this->createForm(OrderType::class, $order, ['arg1' => $firstname, 'arg2' => $lastname, 'arg3' => $phone, 'arg4' => $address, 'arg5' => $email]);
        $form->handleRequest($request);


        //ЛЕВАЯ СТОРОНА
        $cardId = $request->cookies->get('PHPSESSID');
        $redis = $this->get('snc_redis.default');
        $redis->get("cart_{$cardId}");
        $redis = json_decode($redis->get("cart_{$cardId}"), true);
        if ($redis["products"]) {
            foreach ($redis["products"] as $key => $value) {
                $manyprod[] = $redis["products"][$key]["id"];
            }
            $result = array_count_values($manyprod);
            $em = $this->getDoctrine()->getManager();
            foreach ($result as $key => $value) {
                $product[] = $em->getRepository('ShopBundle:Products')->find($key);
                $prod = $em->getRepository('ShopBundle:Products')->find($key);
                $price[] = $prod->getShopPrice();
                $ar = array_map(function ($price, $result) {
                    return $price * $result;
                }, $price, $result);
            }
        } else {
            $this->get('session')->getFlashBag()->add('room_order', 'Что бы перейти к оформлению заказа, пожалуйста, положите товар в корзину.');
            return $this->redirectToRoute("shop_homepage");
        };

        $sum = array_sum($ar);


//        отправка в бд
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            //уменьшение товара на складе, увеличение рейтнга
            if ($result) {
                foreach ($result as $key => $value) {
                    $prod_balance[] = $em->getRepository('ShopBundle:Products')->find($key);
                }
                foreach ($prod_balance as $key => $value) {
                    $balance = $prod_balance[$key]->getBalanse();
                    $newbalance = $balance - (1 * $result[$prod_balance[$key]->getId()]);
                    $prod_balance[$key]->setBalanse($newbalance);


                    $rating = $prod_balance[$key]->getRating();
                    $newrating = $rating + (1 * $result[$prod_balance[$key]->getId()]);
                    $prod_balance[$key]->setRating($newrating);

                    $em->persist($prod_balance[$key]);
                    $em->flush();
                }

                //создание заказа
                $order->setPrice($sum);
                $status = $em->getRepository('ShopBundle:StatusOrder')->find(1);
                $order->setStatus($status);
                if ($user) {
                    $order->setUser($user);
                }
                $order->setOderitem(strval(md5(rand())));
                dump($order);


                //сохранение в заказ продуктов
                foreach ($result as $key => $value) {
                    $orderitem = new OrderItem();
                    $orderitem->setOrderprod($order->getOderitem());
                    $orderitem->setItem($key);
                    $orderitem->setSum($result[$key]);
                    $em->persist($orderitem);
                }

//                $em->flush();
//
                //очистка редиса
                $redis = $this->get('snc_redis.default');
                $redis->del("cart_{$cardId}", '*');
                $em->persist($order);
                $em->flush();


                if (!$user) {
                    return $this->redirectToRoute("shop_homepage");
                } else {
                    $this->get('session')->getFlashBag()->add('room_order', 'Спасибо, что вы выбрали нас! Чек о покупке Вы сможете найти на странице заказа.');
                    return $this->redirectToRoute("shop_room");
                }

            }
        }
        return $this->render('ShopBundle:Page:order.html.twig', [
            'form_order' => $form->createView(),
            'user' => $user,
            'carts' => $product,
            'result' => $result,
            'sum' => $sum,
        ]);
    }

    //вывод комнот чата
    public function chatAction()
    {
        $em = $this->getDoctrine()->getManager();
        $chatroom = $em->getRepository('ShopBundle:ChatRoom')->findAll();
        return $this->render('ShopBundle:Page:chat.html.twig', [
            'chatroom' => $chatroom,
        ]);
    }


    //вывод сообщений в определенной комнате
    public function chatroomAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $chat = $em->getRepository('ShopBundle:ChatRoom')->find($id);
        if (!$chat) {
            throw $this->createNotFoundException('Данного чата не существует');
        }
        $messages = $em->getRepository('ShopBundle:Chat')->getChat($chat->getIdRoom());

        return $this->render('ShopBundle:Page:chatroom.html.twig', [
            'chat' => $messages,
            'id' => $id,
        ]);
    }


    //отправка сообщений
    public function sendAction(Request $request, ChatRoom $chatRoom)
    {
        $message = json_decode($request->getContent());
        if ($this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->getUser()->getUserName();
        } else $user = "anonim";

        $data = [
            "user_autor" => $user,
            "message" => $message
        ];

        $ch = curl_init('http://rambutan.ml/pub?id=' . $chatRoom->getIdRoom());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_exec($ch);
        curl_close($ch);

        $chat = new Chat();
        $chat->setChatroom($chatRoom);
        $chat->setAuthor($user);
        $chat->setMessage($message->message);
        $chat->setDate(new \DateTime(date("d-m-Y G:i:s")));

        $em = $this->getDoctrine()->getManager();
//        $em->persist($chat);
//        $em->flush();
        return new JsonResponse($data);

    }

}