<?php

require_once('./LINEBotTiny.php');

$channelAccessToken = 'LZT2JmEJaQ1weMtwlpLIN1yPUb5CxlXoPFyOY+QlYqAyqyeJCsZc2GfeRqoIHM6x6DzfmqUcWuZPhbTRwGq38avHKcz0shsgogOfaV2OrkNdMCuZgVD//fAKdPjdgfVwIseYHDu1/70LFYrpyXPQJQdB04t89/1O/w1cDnyilFU=';
$channelSecret = '94e83393b170dc46f8e82ddc1250e8e9';

$client = new LINEBotTiny($channelAccessToken, $channelSecret);
foreach ($client->parseEvents() as $event) {
    switch ($event['type']) {
        case 'message':
            $message = $event['message'];
            switch ($message['type']) {
                case 'text':
                    $client->replyMessage([
                        'replyToken' => $event['replyToken'],
                        'messages' => [
                            [
                                'type' => 'text',
                                'text' => $message['text']
                            ]
                        ]
                    ]);
                    break;
                default:
                    error_log('Unsupported message type: ' . $message['type']);
                    break;
            }
            break;
        default:
            error_log('Unsupported event type: ' . $event['type']);
            break;
    }
};
