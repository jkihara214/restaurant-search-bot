<?php

$access_token = 'LZT2JmEJaQ1weMtwlpLIN1yPUb5CxlXoPFyOY+QlYqAyqyeJCsZc2GfeRqoIHM6x6DzfmqUcWuZPhbTRwGq38avHKcz0shsgogOfaV2OrkNdMCuZgVD//fAKdPjdgfVwIseYHDu1/70LFYrpyXPQJQdB04t89/1O/w1cDnyilFU=';

// APIから送信されてきたイベントオブジェクトを取得
$json_string = file_get_contents('php://input');

// 受け取ったJSON文字列をデコード
$json_obj = json_decode($json_string, true);

//受け取ったイベント内容
$event = $json_obj['events'][0];

// このイベントへの応答に使用するトークン
$reply_token = $event['replyToken'];

// イベント種別（今回は2種類のみ）
// message（メッセージが送信されると発生）
// postback（ポストバックオプションに返事されると送信）
$type = $event['type'];

// メッセージオブジェクト（今回は4種類のみ）
// text（テキストを受け取った時）
// sticker（スタンプを受け取った時）
// image（画像を受け取った時）
// location（位置情報を受け取った時）
$msg_obj = $event['message']['type'];


// メッセージ受け取り時
if($type === 'message') {
    // テキストを受け取った時
    if($msg_obj === 'text') {
        $msg_text = $event['message']['text'];
//        if($msg_text === '予約') {
//            $message = array(
//                'type' => 'template',
//                'altText' => 'いつのご予約ですか？',
//                'template' => array(
//                    'type' => 'confirm',
//                    'text' => 'いつのご予約ですか？',
//                    'actions' => array(
//                        array(
//                            'type' => 'postback',
//                            'label' => '予約しない',
//                            'data' => 'action=back'
//                        ), array(
//                            'type' => 'datetimepicker',
//                            'label' => '期日を指定',
//                            'data' => 'datetemp',
//                            'mode' => 'date'// date：日付を選択します。time：時刻を選択します。datetime：日付と日時を選択します。
//                        )
//                    )
//                )
//            );
//        } else {
        $message = array('type' => 'text',
            'text' => '【'.$msg_text.'】とは何ですか？');
//        }
    // スタンプを受け取った時
    } elseif($msg_obj === 'sticker') {
        $message = array(
            'type' => 'sticker',
            'packageId' => '1',
            'stickerId' => '3'
        );
//   } elseif($msg_obj === 'image') {
//        // 画像を受け取った時
//        $message = array(
//            'type' => 'image',
//            // オリジナル画像（タップしたら表示される画像）
//            'originalContentUrl' => 'https://XXXXXXXXXX/line/original_image.png',
//            // サムネイル画像（トーク中に表示される画像）
//            'previewImageUrl' => 'https://XXXXXXXXXX/line/preview_image.png'
//        );
    // 位置情報を受け取った時
    } elseif($msg_obj === 'location') {
        $lat = $event['message']['latitude'];
        $lng = $event['message']['longitude'];
        $my_location_url = 'https://webservice.recruit.co.jp/hotpepper/gourmet/v1/?key=62329986d52e32d1&lat=' . $lat . '&lng=' . $lng . '&range=5&order=4';
        $my_location_ch = curl_init(); //はじめ cURLのセッションを初期化
        curl_setopt($my_location_ch, CURLOPT_URL, $my_location_url); //取得するURL
        curl_setopt($my_location_ch, CURLOPT_RETURNTRANSFER, true); //tureを設定することでcurl_exex()の戻り値を文字列で返す
        $my_location_result = curl_exec($my_location_ch); //cURLセッションを実行
        curl_close($my_location_ch); //おわり cURLのセッションを閉じる
        $change_to_json = simplexml_load_string($my_location_result); //変換したいxmlファイルを指定
        $my_location_json = json_encode($change_to_json); //指定したファイルをjsonファイルへ変換
        $my_location_json_obj = json_decode($my_location_json, true); //jsonファイルのデータを配列化
        $count = $my_location_json_obj['results_returned'];
        if ($count === '0') {
        $message = array('type' => 'text',
            'text' => '付近にお店が有りません');
        } else {
            for ($i = 0; $i <= $count - 1; $i++) {
                $shop_name .= '店名' . $i + 1 . ' : ' . $my_location_json_obj['shop'][$i]['name'] . "\n";
            }
            $message = array('type' => 'text',
            'text' => rtrim($shop_name));
        }

//        $message = array(
//            'type' => 'location',
//            'title' => '皇居',
//            'address' => '〒100-8111 東京都千代田区千代田１-１',
//            'latitude' => 35.683798,
//            'longitude' => 139.754182
//        );
    }
// } else if($type === 'postback') {
//    // ポストバック受け取り時
//
//    // 送られたデータ
//    $postback = $json_obj->{'events'}[0]->{'postback'}->{'data'};
//
//    if($postback === 'datetemp') {
//        // 日にち選択時
//        $message = array(
//            'type' => 'text',
//            'text' => '【'.$json_obj->{'events'}[0]->{'postback'}->{'params'}->{'date'}.'】にご予約を承りました。'
//        );
//    } elseif($postback === 'action=back') {
//        // 戻る選択時
//        $message = array(
//            'type' => 'text',
//            'text' => '何もしませんでした。'
//        );
//    }
}

$post_data = json_encode(array(
'replyToken' => $reply_token,
'messages' => array($message)
));

// build request headers
$headers = array('Content-Type: application/json',
                'Authorization: Bearer ' . $access_token);

$url = 'https://api.line.me/v2/bot/message/reply';

// post json with curl
$options = array(CURLOPT_URL            => $url,
                CURLOPT_CUSTOMREQUEST  => 'POST',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POSTFIELDS     => $post_data);

$curl = curl_init();
curl_setopt_array($curl, $options);
curl_exec($curl);
curl_close($curl);


//
//require_once('./LINEBotTiny.php');
//
//$channelAccessToken = 'LZT2JmEJaQ1weMtwlpLIN1yPUb5CxlXoPFyOY+QlYqAyqyeJCsZc2GfeRqoIHM6x6DzfmqUcWuZPhbTRwGq38avHKcz0shsgogOfaV2OrkNdMCuZgVD//fAKdPjdgfVwIseYHDu1/70LFYrpyXPQJQdB04t89/1O/w1cDnyilFU=';
//$channelSecret = '94e83393b170dc46f8e82ddc1250e8e9';
//
//$client = new LINEBotTiny($channelAccessToken, $channelSecret);
//foreach ($client->parseEvents() as $event) {
//    switch ($event['type']) {
//        case 'message':
//            $message = $event['message'];
//            switch ($message['type']) {
//                case 'text':
//                    $client->replyMessage([
//                        'replyToken' => $event['replyToken'],
//                        'messages' => [
//                            [
//                                'type' => 'text',
//                                'text' => $message['text']
//                            ]
//                        ]
//                    ]);
//                    break;
//                default:
//                    error_log('Unsupported message type: ' . $message['type']);
//                    break;
//            }
//            break;
//        default:
//            error_log('Unsupported event type: ' . $event['type']);
//            break;
//    }
//};