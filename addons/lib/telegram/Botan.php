<?php
namespace lib\telegram;

/** telegram **/

/**
 * Class Botan
 * @package YourProject
 *
 * Usage:
 *
 * private $token = 'token';
 *
 * public function _incomingMessage($message_json)
 * {
 *     $messageObj = json_decode($message_json, true);
 *     $messageData = $messageObj['message'];
 *
 *     $botan = new YourProject\Botan($this->token);
 *     $botan->track($messageData, 'Start');
 * }
 *
 */
class Botan
{
    /**
     * @var string Tracker url
     */
    protected $template_uri = 'https://api.botan.io/track?token=#TOKEN&uid=#UID&name=#NAME';
    /**
     * @var string Url shortener url
     */
    protected $shortener_uri = 'https://api.botan.io/s/?token=#TOKEN&user_ids=#UID&url=#URL';
    /**
     * @var string Yandex AppMetrica application api_key
     */
    protected $token;

    function __construct($token)
    {
        if (empty($token) || !is_string($token))
        {
            return 'Token should be a string';
        }
        $this->token = $token;
    }


    public function track($message, $event_name = 'Message')
    {
        if(!$message || !isset($message['from']['id']))
        {
            return 'input data is not valid';
        }
        $uid = $message['from']['id'];
        $url = str_replace(
            ['#TOKEN', '#UID', '#NAME'],
            [$this->token, $uid, urlencode($event_name)],
            $this->template_uri
        );
        $result = $this->request($url, $message);
        if (isset($result['status']) && $result['status'] !== 'accepted')
        {
            return 'Error Processing Request';
        }
        return $result;
    }


    public function shortenUrl($url, $user_id)
    {
        $request_url = str_replace(
            ['#TOKEN', '#UID', '#URL'],
            [$this->token, $user_id, urlencode($url)],
            $this->shortener_uri
        );
        $response = file_get_contents($request_url);
        return $response === false ? $url : $response;
    }


    function getHTTPResponseCode($headers)
    {
        $matches = [];
        $res = preg_match_all('/[\w]+\/\d+\.\d+ (\d+) [\w]+/', $headers[0], $matches);
        if ($res < 1)
        {
        	return 'Incorrect response headers';
        }
        $code = intval($matches[1][0]);
        return $code;
    }


    protected function request($url, $body)
    {
        $options =
        [
            'http' =>
            [
                'header'  => 'Content-Type: application/json',
                'method'  => 'POST',
                'content' => json_encode($body)
            ]
        ];
        $context  = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        if ($response === false)
        {
            return 'Error Processing Request';
        }
        $HTTPCode = $this->getHTTPResponseCode($http_response_header);
        if ($HTTPCode !== 200)
        {
            return "Bad HTTP responce code: $HTTPCode".print_r($http_response_header, true);
        }
        $responseData = json_decode($response, true);
        if ($responseData === false)
        {
            return 'JSON decode error';
        }
        return $responseData;
    }
}
?>