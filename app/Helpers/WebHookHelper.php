<?php

namespace App\Helpers;

use App\Jobs\WebHookSender;
use App\Models\User;

class WebHookHelper
{
    public static function sendWebhook($user_id, $data)
    {
        $wallet = new User();
        $data = [];
        $data['user_id'] = $user_id;
        $data['amount'] = $amount;
        $data['commission_type'] = $commission_type;
        $data['narration'] = $narration;
       
        
    }
}
