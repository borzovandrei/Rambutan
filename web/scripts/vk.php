<?php


if (!isset($_REQUEST)) {
    exit;
}


callback_handleEvent();


function callback_handleEvent()
{

    $data = json_decode(file_get_contents('php://input'));

    $user_id = $data->object->user_id;
    $new_message = $data->object->body;
    $user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&v=5.0"));
    $first_name = $user_info->response[0]->first_name;
    $last_name = $user_info->response[0]->last_name;

    switch ($data->type) {
        case 'confirmation':
            echo '1256c01f';
            exit;

        case 'message_new':
            $param = [
                'user_id' => $user_id,
                'message' => "Привет " . $first_name . " " . $last_name.". В ближайшее время с Вами свяжется наш менеджер.",
                'access_token' => '1fafba2a246e03817c051eba004f54020c6665e6210e2bccdcdc7d2fcd05d76ee8d12c918311c5824dd32',
                'v' => '5.69',
            ];

            $message = [
                "message" => $new_message,
                "user_id" => $user_id,
                "service"=>'vk',
            ];

            $data = [
                "user_autor" => "ВК: " . $first_name . " " . $last_name,
                "message" => $message,
            ];

            $ch = curl_init('http://rambutan.ml/pub?id=8');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_exec($ch);
            curl_close($ch);

            file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($param));
            _callback_okResponse();
            break;
    }


    _callback_okResponse();
}

function _callback_okResponse()
{
    _callback_response('ok');
}

function _callback_response($data)
{
    echo $data;
    exit;
}
