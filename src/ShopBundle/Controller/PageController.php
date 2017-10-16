<?php

namespace ShopBundle\Controller;



use ShopBundle\Entity\Chat;
use ShopBundle\Entity\ChatRoom;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class PageController  extends Controller
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
            'last_username' =>$request->getSession()->get(Security::LAST_USERNAME),
            'error' => $error
        ));
    }


    //метод регистрации
    public function regAction(Request $request)
    {
        $session = $request->getSession();
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(Security::AUTHENTICATION_ERROR);
        }
        return $this->render('ShopBundle:Page:reg.html.twig', array(
            'last_username' =>$request->getSession()->get(Security::LAST_USERNAME),
            'error' => $error
        ));
    }

    //вывод комнот чата
    public function chatAction()
    {
        $em = $this->getDoctrine()->getManager();
        $chatroom = $em->getRepository('ShopBundle:ChatRoom')->findAll();
        return $this->render('ShopBundle:Page:chat.html.twig', array(
            'chatroom'=>$chatroom,
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

        return $this->render('ShopBundle:Page:chatroom.html.twig',array(
            'chat'=> $messages,
            'id'=>$id,
        ));
    }


    //отправка сообщений
    public function sendAction(Request $request, ChatRoom $chatRoom)
    {
        $message = json_decode($request->getContent());
        if( $this->container->get( 'security.authorization_checker' )->isGranted( 'IS_AUTHENTICATED_FULLY' ) )
        {
            $user =  $this->getUser()->getUserName();
        }
        else $user = "anonim";

        $data = [
            "user_autor" => $user,
            "message" => $message
        ];

        var_dump($chatRoom->getName());

        $ch = curl_init('http://127.0.0.1:8008/pub?id='.$chatRoom->getIdRoom());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_exec($ch);
        curl_close($ch);



        $chat  = new Chat();
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