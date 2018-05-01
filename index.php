<?php
ob_start();
include_once 'config.php';
include_once 'nextpay_payment.php';
define('API_KEY',$Api_Bot);
//-------------------------------------------//
function bot($method,$data=[]){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
}
//-------------------------------------------//
function sendmessage($chat_id, $text, $mode){bot('sendMessage',['chat_id'=>$chat_id,'text'=>$text,'parse_mode'=>$mode]);}
//-------------------------------------------//
function pay($chat_id,$Amount,$from_id){
    $callback_uri = $address.'/back.php?id='.$chat_id.'&amount='.$Amount;
    $order_id = time();

    $parameters = array(
        'api_key' 	=> $ApiKey_Nextpay,
        'amount' 	=> $Amount,
        'callback_uri' 	=> $callback_uri,
        'order_id' 	=> $order_id
    );

    try {
        $nextpay = new Nextpay_Payment($parameters);
        $nextpay->setDefaultVerify(Type_Verify::SoapClient);
        $result = $nextpay->token();
        if(intval($result->code) == -1){
            return $nextpay->request_http."/{$result->trans_id}";
        }
        else
        {
            $message = ' شماره خطا: '.$result->code.'<br />';
            $message .='<br>'.$nextpay->code_error(intval($result->code));
            echo $message;
            exit();
        }
    }catch (Exception $e) { echo 'Error'. $e->getMessage();  }
}
//-------------------------------------------//
$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$from_id = $message->from->id;
$chat_id = $message->chat->id;
$text = $message->text;
$first_name = $message->from->first_name;
$last_name = $message->from->last_name;
$username = $message->from->username;
$message_id = $update->message->message_id;
$reply = $update->message->reply_to_message;
$re_id = $update->message->reply_to_message->forward_from->id;
//-------------------------------------------//
$left = json_decode(file_get_contents("https://api.telegram.org/bot".API_KEY."/getChatMember?chat_id=$chat_id&user_id=$from_id"))->result->status;
//-------------------------------------------//
$command = file_get_contents("data/$from_id/command.txt");
$count = file_get_contents("data/$chat_id/country.txt");
$upay = file_get_contents("data/$chat_id/upay.txt"); 
$ubuy = file_get_contents("data/$chat_id/ubuy.txt");
$coin = file_get_contents("data/$chat_id/coin.txt");
$product = file_get_contents("data/$chat_id/product.txt");
$checkout = file_get_contents("data/$chat_id/checkout.txt");
$products = file_get_contents("data/$chat_id/products.txt");
$ShCH = file_get_contents("data/$chat_id/ShCH.txt");
$AllBuy = file_get_contents("data/$chat_id/AllBuy.txt");
$AllBuyT = file_get_contents("data/$chat_id/AllBuyT.txt");
//-------------------------------------------//
$members = file_get_contents("data/members.txt");
$memlist = explode("\n",$members);
$banlist = file_get_contents("data/banlist.txt");
$blist = explode("\n", $banlist);
//-------------------------------------------//
if($coin < 0){file_put_contents("data/$chat_id/coin.txt","0");}
//-------------------------------------------//
if($left == "left")
{
    bot('sendMessage',[
                        'chat_id'=>$chat_id,
                        'text'=>"
                                    برای فعال شدن ربات باید در کانال ربات عضو شوید
                                    ☂️ Channel : $channel
                                    
                              چرا باید عضو کانال شویم؟!
                                    🔹 زیرا جهت پشتیبانی و دریافت اطلاعیه ها و آموزش های ربات لازم است حتما عضو کانال باشید ...
                                    
                                    ⚠️ درصورت عضو نشدن ربات فعال نمی شود ...
                                    ✅ پس از عضویت در کانال دستور مورد نظر را دوباره تکرار کنید...
                                    ",
                        'parse_mode'=>'HTML',
                        'reply_markup'=>json_encode([
                            'inline_keyboard'=>[[[
                                    'text'=>"ورود به کانال",'url'=>"$channel_address"]]]])
    ]);
}
else
{

    if (strpos($text,'/start') !== false or $text == "↪️ بازگشت"){

        if (!in_array($chat_id,$memlist)){
            mkdir("data");
            mkdir("data/$from_id");
            $members .= $chat_id."\n";
            file_put_contents("data/members.txt","$members");
            file_put_contents("data/$chat_id/coin.txt","0");
            file_put_contents("data/$chat_id/ubuy.txt","0");
            file_put_contents("data/$chat_id/ref.txt","0");

            $id = str_replace("/start ","",$text);
            if ($id != "" && $text != "/start" && $id != $from_id){
                SendMessage($id,"یک نفر از طریق لینک شما وارد ربات شد","HTML");
                file_put_contents("data/$from_id/refe.txt","$id");
                $refs = file_get_contents("data/$id/ref.txt");
                $refs = $refs + 1;
                file_put_contents("data/$id/ref.txt","$refs");
            }
        }

        file_put_contents("data/$chat_id/command.txt","none");

        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"
         سلام ؛ به ربات فروشگاه مجازی خوش آمدید .
            
            هر سوال و یا مشکلی داشتید میتوانید از طریق قسمت پشتیبانی باما درمیان بگذارید.
             کانال ما : $channel
            ",
            'parse_mode'=>'HTML',
            'reply_markup'=>json_encode([
                'keyboard'=>[
                    [['text'=>"خرید محصول"]],
                    [['text'=>"اطلاعات حساب"],['text'=>"شارژ حساب"]],
                    [['text'=>"پشتیبانی"],['text'=>"درباره ما"]],
                    [['text'=>"محصولات"],['text'=>"راهنما"],['text'=>"قیمت ها"]],
                ],
                'resize_keyboard'=>true,
            ])
        ]);
    }
    //-------------------------------------------//
    elseif ($text == 'راهنما'){
        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"
        راهنما
        
  این فروشگاه مجازی میباشد و بصورت تست توسط نکست پی ساخته شده است.   
        از منو زیر جهت راهنمایی استفاده کنید.
        ",
            'parse_mode'=>'HTML',
            'reply_markup'=>json_encode([
                'keyboard'=>[
                    [['text'=>"نحوه کار با فروشگاه"],['text'=>"سوالات متداول"]],
                    [['text'=>"موارد بیشتر"],['text'=>" فیلم آموزش خرید"]],
                    [['text'=>"↪️ بازگشت"]],
                ],
            'resize_keyboard'=>true,
            ])
        ]);
    }
    //-------------------------------------------//
    elseif ($text == 'نحوه کار با فروشگاه'){
        bot('sendMessage',[
        'chat_id'=>$chat_id,
        'text'=>"
      نحوه کار با فروشگاه
        
       میتوانید از این نمونه فروشگاه برای موارد فروش محصول استفاده نمایید.
       توضیحات :
       
       تمامی توضیحات توسط شما تکمیل میشود.
                ",
        ]);
    }
    //-------------------------------------------//
    elseif ($text == 'سوالات متداول'){
        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"
            تمامی مواردی که موجب بروز سوال میشود در این قسمت بصورت تیتر و توضیح میتوانید قرار دهید.
                ",
        ]);
    }
    //-------------------------------------------//
    elseif ($text == 'موارد بیشتر'){
        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"
            راهنمای موارد بیشتر
                ",
        ]);
    }
    //-------------------------------------------//
    elseif ($text == 'فیلم آموزش خرید'){
        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"
فیلم های آموزشی در این قسمت قرار میگیرند.
        ",
        ]);
    }
    //-------------------------------------------//
    elseif ($text == 'محصولات'){
        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"
      محصولات :
      
      محصول شماره ۱ : توضیح محصول
      محصول شماره ۲ : توضیح محصول
      محصول شماره ۳ : توضیح محصول
      محصول شماره ۴ : توضیح محصول
      محصول شماره ۵ : توضیح محصول
      محصول شماره ۶ : توضیح محصول
        ",
            'parse_mode'=>'HTML',
        ]);
    }
    //-------------------------------------------//
    elseif ($text == 'قیمت ها'){
        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"
            لیست قیمت های محصول :
            قیمت محصول ۱ : ۱۲۰۱
                        قیمت محصول ۲ : ۱۲۰۲
                                    قیمت محصول ۳ : ۱۲۰۳
                                                قیمت محصول ۴ : ۱۲۰۴
                                                            قیمت محصول ۵ : ۱۲۰۵
                                                            قیمت محصول ۶ : ۱۲۰۶
        ",
        'parse_mode'=>'HTML',
        ]);
    }
    //-------------------------------------------//
    elseif ($text == 'درباره ما'){
        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"
            نمونه کد نوشته شده برا اساس یک فروشگاه مجازی است و لطفا این قسمت نیز توسط استفاده کننده تکمیل شود.
            با سپاس تیم فنی نکست پی
                ",
        'parse_mode'=>'HTML',
        ]);
    }
    //-------------------------------------------//
    elseif ($text == 'اطلاعات حساب'){
        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"
        نام : $first_name$last_name
        ◽️ یوزرنیم : @$username
        آیدی : $chat_id
        ➖➖➖➖➖➖➖➖➖➖➖➖
        موجودی : $coin تومان
        ☎️ تعداد خرید ها : $ubuy تا
        میزان محصولات ارائه شده : $checkout 
        ",
            'parse_mode'=>'HTML',
        ]);
    }
    //-------------------------------------------//
    elseif ($text == 'پشتیبانی'){
        file_put_contents("data/$chat_id/command.txt","support");
        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"پیام خود را ارسال کنید :",
            'parse_mode'=>'HTML',
            'reply_markup'=>json_encode([
                'keyboard'=>[
                    [['text'=>"↪️ بازگشت"]],
                ],
                'resize_keyboard'=>true
            ])
        ]);
    }
    //-------------------------------------------//
    elseif ($command == 'support'){
        if (!in_array($chat_id, $blist)){
            bot("forwardMessage",['chat_id' =>$admin,'from_chat_id'=>$chat_id,'message_id' => $message_id]);
            sendmessage($chat_id,"✅ پیام شما ارسال شد.","HTML");
        } else {
            file_put_contents("data/$chat_id/command.txt","none");
            sendmessage($chat_id,"⛔️ شما بدلیل تخلف مسدود شده اید","HTML");
        }
    }
    //-------------------------------------------//
    elseif ($chat_id == $admin and $reply){
        if($text == "/ban"){
            if(!in_array($re_id, $blist)){
            file_put_contents("data/banlist.txt","\n". $re_id,FILE_APPEND);
            sendmessage($chat_id,"❌ کاربر مسدود شد .","HTML");
            }
        }
        elseif($text == "/unban"){
            if(in_array($re_id, $blist)){
            $bli = str_replace("\n" . $re_id,'',$banlist);
            file_put_contents("data/banlist.txt", $bli);
            sendmessage($chat_id,"✅ کاربر آزاد شد .","HTML");
            }
        }else{
            sendmessage($re_id,$text,"HTML");
            sendmessage($chat_id,"✅ پیام شما ارسال شد.","HTML");
        }
    }
    //-------------------------------------------//
    elseif ($text == 'شارژ حساب'){
        sendmessage($chat_id,'💵 درحال ساخت لینک پرداخت...',"TEXT");
        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"
            ⚠️ برو روی مبلغ دلخواه کلیک کنید سپس به صفحه پرداخت می روید و پس از پرداخت وجه اتوماتیک حساب شما شارژ میشود.
                    ",
            'parse_mode'=>'TEXT',
            'reply_markup'=>json_encode([
                'inline_keyboard'=>[
                    [['text'=>" 2,000 تومان",'url'=>pay($chat_id,'2000',$from_id)],['text'=>"3,000 تومان",'url'=>pay($chat_id,'3000',$from_id)]],
                    [['text'=>"4,000 تومان",'url'=>pay($chat_id,'4000',$from_id)],['text'=>"5,000 تومان",'url'=>pay($chat_id,'5000',$from_id)]],
                    [['text'=>"10,000 تومان",'url'=>pay($chat_id,'10000',$from_id)],['text'=>"20,000 تومان",'url'=>pay($chat_id,'20000',$from_id)]],
                ]
            ])
        ]);
    }
    //-------------------------------------------//
    elseif ($text == 'خرید محصول'){
        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"سرویس مورد نظر را انتخاب کنید :",
            'parse_mode'=>'HTML',
            'reply_markup'=>json_encode([
                'keyboard'=>[
                    [['text'=>"محصول شماره ۱"],['text'=>"محصول شماره ۲"],['text'=>"محصول شماره ۳"]],
                    [['text'=>"محصول شماره ۴"],['text'=>"محصول شماره ۵"],['text'=>"محصول شماره ۶"]],
                    [['text'=>"↪️ بازگشت"]],
                ],
                'resize_keyboard'=>true,
            ])
        ]);
    }
    //-------------------------------------------//
    elseif ($text == 'محصول شماره ۱' or $text == 'محصول شماره ۲' or $text == 'محصول شماره ۳' or $text == 'محصول شماره ۴' or $text == 'محصول شماره ۵' or $text == 'محصول شماره ۶'){
        $az = array("محصول شماره ۱","محصول شماره ۲","محصول شماره ۳","محصول شماره ۴","محصول شماره ۵","محصول شماره ۶");
        $be = array("p1","p2","p3","p4","p5","p6");
        $text = str_replace($az,$be,$text);
        file_put_contents("data/$chat_id/products.txt","$text");

        bot('sendMessage',[
            'chat_id'=>$chat_id,
            'text'=>"محصول را انتخاب نمایید
            تمامی محصولاتی که در فروشگاه عرضه میشود
                    ",
            'parse_mode'=>'HTML',
            'reply_markup'=>json_encode([
                'keyboard'=>[
                    [['text'=>"$p1"],['text'=>"$p2"],['text'=>"$p3"]],
                    [['text'=>"$p4"],['text'=>"$p5"],['text'=>"$p6"]],
                    [['text'=>"↪️ بازگشت"]],
                ],
                'resize_keyboard'=>true
            ])
        ]);
    }
    //-------------------------------------------//
    elseif ($text == "$p1" or $text == "$p2" or $text == "$p3" or $text == "$p4" or $text == "$p5" or $text == "$p6"){
        $product_price_az = array("$p1","$p2","$p3","$p4","$p5","$p6");
        $product_price_be = array("p1","p2","p3","p4","p5","p6");
        $product_price = str_replace($product_price_az,$product_price_be,$text);
        file_put_contents("data/$chat_id/product_price.txt","$product_price");

        $price_az = array("p1","p2","p3","p4","p5","p6");
        $price_be = array("1201","1202","1203","1204","1205","1206");
        $upays = str_replace($price_az,$price_be,$product_price);
        file_put_contents("data/$chat_id/upay.txt","$upays");

        if ($coin >= $upays){
            $getproduct = json_decode(file_get_contents("https://example.com/products/index.php?ApiKey=$ApiKey&action=GetProduct&price=$product_price&product=$products"),true);
            if($getproduct['Result'] == 'OK'){
               file_put_contents("data/$chat_id/product.txt",$getproduct['productID']);
               file_put_contents("data/$chat_id/orders.txt",$getproduct['product']);

               bot('sendMessage',[
                   'chat_id'=>$chat_id,
                   'text'=>"
محصول شما خریداری شد
        Product : +".$getproduct['product']."

        ",
                   'parse_mode'=>'HTML',
                   'reply_markup'=>json_encode([
                       'keyboard'=>[
                            [['text'=>"دریافت کد سفارش"]],
                            [['text'=>"↪️ بازگشت"]],
                        ],
                        'resize_keyboard'=>true,
                    ])
                ]);
            }else{
                bot('sendMessage',[
                    'chat_id'=>$chat_id,
                    'text'=>"
                ⛔️ ربات در حال حاظر قادر به برقراری ارتباط نیست...
                
                ⚠️ لطفا چند دقیقه دیگر تلاش کنید ...
                ",
                    'parse_mode'=>'HTML',
                    'reply_markup'=>json_encode([
                        'keyboard'=>[
                            [['text'=>"↪️ بازگشت"]],
                        ],
                        'resize_keyboard'=>true,
                    ])
                ]);
            }

        }else{
            bot('sendMessage',[
                'chat_id'=>$chat_id,
                'text'=>"
            ⚠️ موجودی شما کافی نیست . . .
            
            💰موجودی شما : $coin
            🔰 هزینه این سفارش : $upays
                ",
                'parse_mode'=>'HTML',
                'reply_markup'=>json_encode([
                    'keyboard'=>[
                        [['text'=>"شارژ حساب"]],
                        [['text'=>"↪️ بازگشت"]],
                    ],
                    'resize_keyboard'=>true,
                ])
            ]);
        }
    }
    //-------------------------------------------//
    elseif ($text == 'دریافت کد سفارش'){
        $getnumber = json_decode(file_get_contents("https://example.com/products/index.php?ApiKey=$ApiKey&action=GetSms&product=$products&productID=$product"),true);
        if($getnumber['Massage'] == "no received"){
            bot('sendMessage',[
                'chat_id'=>$chat_id,
                'text'=>"
                هنوز محصول شما خریداری نشده است
    
    ✅ تا زمانی که کد را نگرفته اید از حساب شما  مبلغی کثر نخواهد شد.
    ",
                'parse_mode'=>'HTML',
                'reply_markup'=>json_encode([
                    'keyboard'=>[
                        [['text'=>"دریافت کد"]],
                        [['text'=>"↪️ بازگشت"]],
                    ],
                    'resize_keyboard'=>true,
                ])
            ]);
        }
        elseif($getnumber['Massage'] == "time out"){
            bot('sendMessage',[
                'chat_id'=>$chat_id,
                'text'=>"
            📛 زمان استفاده از این فروش تموم شده.
            💠 لطفا شماره دیگری مجدد دریافت کنید ...
                ",
                'parse_mode'=>'HTML',
                'reply_markup'=>json_encode([
                    'keyboard'=>[
                    [['text'=>"↪️ بازگشت"]],
                    ],
                    'resize_keyboard'=>true,
                ])
            ]);
        }
        elseif($getnumber['Result'] == "OK"){
            $AllBuy = 0;
            $coin -= $upay;
            file_put_contents("data/$chat_id/coin.txt","$coin");
            $ubuy += 1;
            file_put_contents("data/$chat_id/ubuy.txt","$ubuy");
            $AllBuy += $upay;
            file_put_contents("data/$chat_id/AllBuy.txt","$AllBuy");
            $AllBuyT += 1;
            file_put_contents("data/$chat_id/AllBuyT.txt","$AllBuyT");

            $strlen = strlen($ShCH)/2-2;
            $ShCH = str_replace(substr("$ShCH",$strlen,$strlen),"***",$ShCH);
            $product_price_az = array("p1","p2","p3","p4","p5","p6");
            $product_price_be = array("$p1","$p2","$p3","$p4","$p5","$p6");
            $product = str_replace($product_price_az,$product_price_be,$count);
            bot('sendMessage',[
                'chat_id'=>$channel,
                'text'=>"
            محصول شما $product خریداری شد!
            ⚜️اطلاعات شماره و خریدار 👇
            ➖➖➖➖➖➖➖➖
            number : +$ShCH
            ➖➖➖➖➖➖➖➖
            user : $chat_id
            ➖➖➖➖➖➖➖➖
          مشخصات فروش و لسیت سفارش را میتوانید در اینجا قرار دهید
            ",
            ]);

            bot('sendMessage',[
                'chat_id'=>$chat_id,
                'text'=>"
            ✅ کد دریافت شد :
             Code : ".$getnumber['Massage']."
            ",
                'parse_mode'=>'HTML',
                'reply_markup'=>json_encode([
                    'keyboard'=>[
                        [['text'=>"↪️ بازگشت"]],
                    ],
                    'resize_keyboard'=>true,
                ])
            ]);
        }
    }
}
//-------------------------------------------//
if ($text == '/stats' and $chat_id == $admin){
    sendmessage($chat_id," تعداد کل کاربران : ".count($memlist)."\n☎️ تعداد کل فروش : $AllBuyT\n💵 هزینه های استفاده شده کاربران : $AllBuy","HTML");
}
//-------------------------------------------//
if(file_exists("error_log"))unlink("error_log");