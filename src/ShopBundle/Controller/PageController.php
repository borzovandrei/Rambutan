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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Security;

class PageController extends Controller
{

    //метод авторизазии
    public function loginAction(Request $request)
    {
        $session = $request->getSession();
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(Security::AUTHENTICATION_ERROR);
        }
        return $this->render('ShopBundle:Page:login.html.twig', array(
            'last_username' => $request->getSession()->get(Security::LAST_USERNAME),
            'error' => $error
        ));
    }


    //метод регистрации
    public function regAction(Request $request)
    {
        $user = new Users();

        $form = $this->createForm(NewUserType::class, $user);
        $form -> handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $role = $em->getRepository('ShopBundle:Role')->find(2);
        $user->getUserRoles()->add($role);

        $user->setSalt(md5(time()));
        $encoder = new MessageDigestPasswordEncoder('sha512', true, 10);
        $password = $encoder->encodePassword($form["password"]->getData(), $user->getSalt());
        $user->setPassword($password);

        dump($request);

        if ($form->isSubmitted() &&  $form->isValid()){
            $user->upload();
            $em -> persist($user);
            $em->flush();
            return $this->redirectToRoute("shop_login");
        }

        return $this->render('ShopBundle:Page:reg.html.twig', array(
            'form_add_user'=> $form -> createView()
        ));
    }


    //О компании
    public function aboutAction(Request $request)
    {   $feedback = new Feedback();

        $form = $this->createForm(EmileType::class, $feedback);

        if ($request->isMethod($request::METHOD_POST)) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $message = \Swift_Message::newInstance()
                    ->setSubject('Rambutan feedback')
                    ->setFrom('rambutan@feedback.com')
                    ->setTo('borzovandrei45@gmail.com')
                    ->setBody($this->renderView('ShopBundle:Email:feedbackEmail.txt.twig', array('enquiry' => $feedback)));

                $this->get('mailer')->send($message);

                $this->get('session')->getFlashBag()->add('shop-notice', 'Спасибо! Ваше письмо было отправлено!');

                return $this->redirect($this->generateUrl('shop_about'));

            }

        }
        return $this->render('ShopBundle:Page:about.html.twig', array(
            'form' => $form->createView()
        ));
    }

    //ЛИЧНЫЙ КАБИНЕТ
    public function roomAction()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $orders = $em->getRepository('ShopBundle:Order')->findBy(array('firstname' => $user->getFirstname(), 'phone' => $user->getPhone()));


        return $this->render('ShopBundle:Page:room.html.twig', array(
            'user' => $user,
            'order' => $orders,
        ));
    }


    //просмотр определенного заказа
    public function room_orderAction(Request $request)
    {
        $products=null;
        $sum=null;
        $id = $request->get("id");
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository('ShopBundle:Order')->find($id);
        $orderitem = $em->getRepository('ShopBundle:OrderItem')->findBy(array('orderprod'=>$order->getOderitem()));
        foreach ($orderitem as $key => $value) {
            $products[] = $em->getRepository('ShopBundle:Products')->find($orderitem[$key]->getItem());
            $sum[]=$orderitem[$key]->getSum();
        }

        $params = array(
            'geocode' => $order->getAddress(), // адрес
            'format'  => 'json',                          // формат ответа
            'results' => 1,                               // количество выводимых результатов
        );
        $response = json_decode(file_get_contents('http://geocode-maps.yandex.ru/1.x/?' . http_build_query($params, '', '&')));

        if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0)
        {
            $map = explode(" ", $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
        }
        else
        {
            $map=null;
        }


        return $this->render('ShopBundle:Page:room_order.html.twig', array(
            'order' => $order,
            'products' => $products,
            'sum' => $sum,
            'map'=>$map,
        ));
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
            $product = null;
            $result = null;
            $price[] = null;
            $ar[] = null;
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
                $order->setCreated();
                $status= $em->getRepository('ShopBundle:StatusOrder')->find(1);
                $order->setStatus($status);
                if ($user) {
                    $order->setIdUser($user);
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
                    $this->get('session')->getFlashBag()->add('room_order', 'Спасибо, что вы выбрали нас! Чек о покупке Вы сможеье найти на странице заказа.');
                    return $this->redirectToRoute("shop_room");
                }

            }
        }
        return $this->render('ShopBundle:Page:order.html.twig', array(
            'form_order' => $form->createView(),
            'user' => $user,
            'carts' => $product,
            'result' => $result,
            'sum' => $sum,
        ));
    }

    //вывод комнот чата
    public function chatAction()
    {
        $em = $this->getDoctrine()->getManager();
        $chatroom = $em->getRepository('ShopBundle:ChatRoom')->findAll();
        return $this->render('ShopBundle:Page:chat.html.twig', array(
            'chatroom' => $chatroom,
        ));
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

        return $this->render('ShopBundle:Page:chatroom.html.twig', array(
            'chat' => $messages,
            'id' => $id,
        ));
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