<?php
ob_start();
include('config.php');
include('nextpay_payment.php');
define('API_KEY',$API_KEY);
function bot($method,$datas=[]){
$url = "https://api.telegram.org/bot".API_KEY."/".$method;
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
$res = curl_exec($ch);
if(curl_error($ch)){
var_dump(curl_error($ch));
}else{
return json_decode($res);
}
}
function sendmessage($chat_id, $text, $mode){
 bot('sendMessage',[
 'chat_id'=>$chat_id,
 'text'=>$text,
 'parse_mode'=>$mode
 ]);
 }
//-----------------------------------//
$nextpay = new Nextpay_Payment();
$trans_id = isset($_POST['trans_id']) ? $_POST['trans_id'] : false ;
$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : false ;
$amount = $_GET['amount'];
$user = $_GET['id'];
if (!is_string($trans_id) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $trans_id) !== 1)) {
    $message = ' شماره خطا: -34 <br />';
    $message .='<br>'.$nextpay->code_error(intval(-34));
    echo $message;
    exit();
}
$parameters = array
(
    'api_key'	=> $ApiKey_Nextpay,
    'order_id'	=> $order_id,
    'trans_id' 	=> $trans_id,
    'amount'	=> $amount
);
try {
    $result = $nextpay->verify_request($parameters);
    if( $result < 0 ) {
	$message ='<br>پرداخت موفق نبوده است';
	$message .='<br>شماره تراکنش : <span>' . $trans_id .'</span><br>';
	$message = ' شماره خطا: ' . $result . ' <br />';
	$message .='<br>'.$nextpay->code_error(intval($result));
	echo $message;
	exit();
    } elseif ($result==0) {
	$message ='<br>پرداخت موفق است';
	$message .='<br>شماره تراکنش : <span>' . $trans_id .'</span><br>';
	$message .='<br>شماره پیگیری : <span>' . $order_id .'</span><br>';
	$message .='<br>مبلغ : ' . $amount .'<br>';
	echo $message;
	$cbuy = file_get_contents("data/$user/coin.txt");
	$cbuy = $cbuy + $amount;
	file_put_contents("data/$user/coin.txt","$cbuy");
	sendMessage("$user","✅ پرداخت با موفقیت انجام شد و مبلغ $amount تومان حساب شما شارژ شد.","html");

	$refell = file_get_contents("data/$user/refe.txt");
	$refcoin = file_get_contents("data/$refell/coin.txt");
	$refcoin = $refcoin + ($amount/10) ;
	file_put_contents("data/$refell/coin.txt","$refcoin");
	sendMessage("$refell","✅ زیرمجموعه شما خرید کرد و حساب شما $refcoin تومان شارژ شد.","html");
    }else{
	$message ='<br>پرداخت موفق نبوده است';
	$message .='<br>شماره تراکنش : ' . $trans_id .'<br>';
	echo $message;
    }
}catch (Exception $e) { echo 'Error'. $e->getMessage();  }
?>