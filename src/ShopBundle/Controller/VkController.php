<?php

namespace ShopBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class VkController extends Controller
{
    public function botAction()
    {
        if (!isset($_REQUEST)) {
            exit;
        }
        $data = json_decode(file_get_contents('php://input'));
        $redis = $this->get('snc_redis.default');

        $confirmation_token = $this->getParameter('vkbot_confirmation_token');
        $token = $this->getParameter('vkbot_token');

        $user_id = $data->object->user_id;
        $new_message = $data->object->body;
        $user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&v=5.0"));
        $first_name = $user_info->response[0]->first_name;
        $last_name = $user_info->response[0]->last_name;
        $redis_info[] = [
            'id' => $user_id,
            'first' => $first_name,
            'last' => $last_name,
            'read' => 0,
        ];


        switch ($data->type) {
            case 'confirmation':
                echo $confirmation_token;
                exit;

            case 'message_new':
//                $param = [
//                    'user_id' => $user_id,
//                    'message' => "Привет " . $first_name . " " . $last_name . ". В ближайшее время с Вами свяжется наш менеджер.",
//                    'access_token' => $token,
//                    'v' => '5.69',
//                ];
//            file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($param));


                if (!$redis->get("vk")) {
                    $redis->set("vk", json_encode($redis_info));
                }
                $users = json_decode($redis->get("vk"), true);

                foreach ($users as $k => $v) {
                    if ($v['id'] !== $user_id) {
                        $users = array_merge($users, $redis_info);
                    }
                }
                $users = array_map('unserialize', array_unique(
                    array_map('serialize', $users)));

                $redis->set("vk", json_encode($users));


                $message = [
                    "message" => $new_message,
//                    "message" => $users,
                    "user_id" => $user_id,
                    "service" => 'vk',
                ];

                $data = [
                    "user_autor" => $first_name . " " . $last_name,
                    "message" => $message,
                ];


                $ch = curl_init('http://rambutan.ml/pub?id=8');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_exec($ch);
                curl_close($ch);

                echo('ok');
                exit;

        }


        echo('ok');
        exit;
    }


}