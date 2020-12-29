<?php
// 指明给谁推送，为空表示向所有在线用户推送
$to_uid = "56_34";
// 推送的url地址，上线时改成自己的服务器地址
$push_api_url = "http://www.webmsg.local:2121";
$post_data = array(
   "type" => "publish",
   "content" => json_encode(['a'=>'ok']),
   "to" => $to_uid, 
);
$ch = curl_init ();
curl_setopt ( $ch, CURLOPT_URL, $push_api_url );
curl_setopt ( $ch, CURLOPT_POST, 1 );
curl_setopt ( $ch, CURLOPT_HEADER, 0 );
curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_data );
$return = curl_exec ( $ch );
curl_close ( $ch );
var_export($return);
