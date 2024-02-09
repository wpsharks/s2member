<?php

/*
Updated class to Mailchimp API v3
Since v230530
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
        $subscriber_hash = md5(strtolower($email['email']));
        $url = "https://{$this->server}.api.mailchimp.com/3.0/lists/{$list_id}/members/{$subscriber_hash}";

        //230708 Convert the groupings to interest IDs. 
        $interests = [];
        if (isset($merge_fields['GROUPINGS'])) {
            $interests = $this->groupings_to_interests($list_id, $merge_fields['GROUPINGS']);
            unset($merge_fields['GROUPINGS']);
        }

        $merge_fields['FNAME'] = $merge_fields['MERGE1'];
        $merge_fields['LNAME'] = $merge_fields['MERGE2'];

        $data = [
            'email_address' => $email['email'],
            'status' => 'subscribed',
            'status_if_new' => $double_optin ? 'pending' : 'subscribed',
            'email_type' => $email_type,
            'merge_fields' => (object)$merge_fields,
        ];
        //230925
        if (!empty($interests))
            $data['interests'] = (object)$interests; //231103 object

        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\nAuthorization: Basic " . base64_encode('user:' . $this->api_key),
                'method'  => $update_existing ? 'PUT' : 'POST', 
                'content' => json_encode($data),
                'timeout' => $this->timeout,
            ],
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            c_ws_plugin__s2member_utils_logs::log_entry('mailchimp-api', get_defined_vars());
            return false;
        }

        $result = json_decode($result, true);
        $result['email'] = $result['email_address'];
        c_ws_plugin__s2member_utils_logs::log_entry('mailchimp-api', get_defined_vars());

        return $result;
    }

    public function unsubscribe($list_id, $email, $delete_member = false, $send_goodbye = false, $send_notify = false, $groupings = [])
    {
        $subscriber_hash = md5(strtolower($email['email']));
        $url = "https://{$this->server}.api.mailchimp.com/3.0/lists/{$list_id}/members/{$subscriber_hash}";

        //230714
        if ($delete_member) {
            $data = [];
            $method = 'DELETE';
        } else {
            $interests = [];
            if (isset($groupings['GROUPINGS'])) {
                $interests = $this->groupings_to_interests($list_id, $groupings['GROUPINGS']);
                // Set all to false for unsub.
                $interests = array_fill_keys(array_keys($interests), false);
            }
    
            $data = [
                'status' => 'unsubscribed',
            ];
            //230925
            if (!empty($interests))
                $data['interests'] = (object)$interests; //231103 object
                
            $method = 'PATCH';
        }
    
        $options = [
            'http' => [
                'header'  => "Content-type: application/json\r\nAuthorization: Basic " . base64_encode('user:' . $this->api_key),
                'method'  => $method,
                'content' => json_encode($data),
                'timeout' => $this->timeout,
            ],
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            c_ws_plugin__s2member_utils_logs::log_entry('mailchimp-api', get_defined_vars());
            return false;
        }

        $result = json_decode($result, true);
        $result['complete'] = true;

        c_ws_plugin__s2member_utils_logs::log_entry('mailchimp-api', get_defined_vars());

        return $result;
    }
    
    //230708
    public function groupings_to_interests($list_id, $groupings)
    {
        $interests = [];

        // These are the group/interests for the list in s2's MC options
        // listid::groupcateg::group|group|group
        $grouping = $groupings[0];
        $group_categ_name = $grouping['name'];
        $group_names = $grouping['groups'];

        // Get all interest categories for the list
        // Interest is the API name for Group
        $interest_categs_url = "https://{$this->server}.api.mailchimp.com/3.0/lists/{$list_id}/interest-categories?count=1000";
        $context = stream_context_create([
            'http' => [
                'header'  => "Authorization: Basic " . base64_encode('user:' . $this->api_key),
            ],
        ]);
        $all_interest_categs = json_decode(file_get_contents($interest_categs_url, false, $context), true)['categories'];

        // Find the interest category id that corresponds to the group category name
        foreach ($all_interest_categs as $interest_categ) {
            if ($interest_categ['title'] === $group_categ_name) {
                $interest_categ_id = $interest_categ['id'];
                break;
            }
        }

        if (!empty($interest_categ_id)) {
            // Get all interests for the interest category
            $interests_url = "https://{$this->server}.api.mailchimp.com/3.0/lists/{$list_id}/interest-categories/{$interest_categ_id}/interests?count=1000";
            $all_interests = json_decode(file_get_contents($interests_url, false, $context), true)['interests'];
    
            $interests_map = [];
            foreach ($all_interests as $interest) {
                $interests_map[$interest['name']] = $interest['id'];
            }
            // If the interest name is in the group names, add it to the interests array
            foreach ($group_names as $group_name) {
                if (isset($interests_map[$group_name])) {
                    $interests[$interests_map[$group_name]] = true;
                }
            }

        } else {
            // Log an error message or throw an exception
            c_ws_plugin__s2member_utils_logs::log_entry('mailchimp-api', "Group category not found: {$group_categ_name}");
        }

        c_ws_plugin__s2member_utils_logs::log_entry('mailchimp-api', get_defined_vars());

        return $interests;
    }
}

