<?php 

class Firebase_model extends CI_Model {

    private $api_key = "AAAAqh1Nstg:APA91bFxv6IjIge1pGr_2qAP9SIqUIpxZ8_0aYS998ZeBfjVux-Mg07cHAMvabyCf3AUiLXNcsLDQ7_4YdYBfRf2bljzOGWZ-ID03EKb3RWNaZNlaOK9zX7kZcngMsex6BwIqlQL9lNH";
    private $url = 'https://fcm.googleapis.com/fcm/send';

    public function save_user_notification($user_id, $title, $message, $type, $data_id)
    {
        
        $notification = array(
            "user_id" => $user_id,
            "data_id" => $data_id,
            "type" => $type,
            "title" => $title,
            "message" => $message,
            "date" => date("Y-m-d H:i:s")
        );

        $this->db->insert("notifications", $notification);
    }

    public function save_vendor_notification($v_id, $title, $message, $type, $data_id)
    {
        
        $vendor_notification = array(
            "v_id" => $v_id,
            "data_id" => $data_id,
            "type" => $type,
            "title" => $title,
            "message" => $message,
            "date" => date("Y-m-d H:i:s")
        );

        $this->db->insert("vendor_notification", $vendor_notification);
    }

    public function send_user_notification($user_id, $title, $message)
    {
        $user = $this->db->get_where("user", array("id" => $user_id))->row();
        
        if(!$user ) {
            return "NO USER";
        }

        $fire_keys = explode("::::", $user->device_token);
        foreach ($fire_keys as $key => $fire_key) {
            $tokens[] = $fire_key;
        }

        $result = $this->send_notification($title, $message, $tokens);
        return $result;
    }

    public function send_vendor_notification($user_id, $title, $message)
    {
        $user = $this->db->get_where("vendor", array("id" => $user_id))->row();
        if(!$user ) {
            return "NO USER";
        }

        $fire_keys = explode("::::", $user->device_token);
        foreach ($fire_keys as $key => $fire_key) {
            $tokens[] = $fire_key;
        }

        $result = $this->send_notification($title, $message, $tokens);
        return $result;
    }

    public function send_notification($title, $message, $tokens)
    {
		$custom_object=array();
		$data=array(
                "title" => $title,
                "body" => $message
            );
			
		$newdata=array_merge($data, $custom_object);

        $fields = array(
            'registration_ids' => $tokens,
            'priority' => "high",
            'data'=>$newdata ,
            'notification' => array('title' => $title, 'body' => $message ,'sound'=>'Default'),
        );

        $fields  = json_encode($fields);

        $headers = array(
            'Authorization: key=' .$this->api_key,
            'Content-Type: application/json'
        );

        $ch      = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
    
        curl_close($ch);

        return $result;
    }

}

?>