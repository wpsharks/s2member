<?php

/*
Updated class to Mailchimp API v3
Since v230529
*/

class Mailchimp
{
    public $lists;

    public function __construct($api_key, $timeout = 30)
    {
        $this->lists = new MailchimpV3($api_key, $timeout);
    }
}

class MailchimpV3
{
    private $api_key;
    private $server;
    private $timeout;

    public function __construct($api_key, $timeout = 30)
    {
        $this->api_key = $api_key;
        $this->server = explode('-', $this->api_key)[1];
        $this->timeout = $timeout;
    }

    public function subscribe($list_id, $email, $merge_fields = [], $email_type = 'html', $double_optin = true, $update_existing = true, $replace_interests = true, $send_welcome = false)
    {
        $url = "https://{$this->server}.api.mailchimp.com/3.0/lists/{$list_id}/members";

        $data = [
            'email_address' => $email,
            'status' => $double_optin ? 'pending' : 'subscribed',
            'email_type' => $email_type,
            'merge_fields' => (object)$merge_fields,
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\nAuthorization: Basic " . base64_encode('user:' . $this->api_key),
                'method'  => 'POST',
                'content' => json_encode($data),
                'timeout' => $this->timeout,
            ],
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            /* Handle error */
            return false;
        }

        $result = json_decode($result, true);
        $result['email'] = $result['email_address'];
        return $result;
    }

    public function unsubscribe($list_id, $email, $delete_member = false, $send_goodbye = false, $send_notify = false)
    {
        $subscriber_hash = md5(strtolower($email));
        $url = "https://{$this->server}.api.mailchimp.com/3.0/lists/{$list_id}/members/{$subscriber_hash}";

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\nAuthorization: Basic " . base64_encode('user:' . $this->api_key),
                'method'  => 'DELETE',
                'timeout' => $this->timeout,
           ],
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            /* Handle error */
            return false;
        }

        $result = json_decode($result, true);
        $result['complete'] = true; 
        return $result;
    }
}
