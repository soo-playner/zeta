<?php
include_once('./_common.php');
include_once(G5_THEME_PATH.'/_include/wallet.php');

// 출금처리 PROCESS

$now_datetime = date('Y-m-d H:i:s');
$now_date = date('Y-m-d');

/* 메일인증 - 사용안함 */
//include_once('../lib/otphp/lib/otphp.php');
//include_once(G5_LIB_PATH.'/mailer.lib.php');
/* 코인출금시 */
/* $wallet_addr	= trim($_POST['wallet_addr']);
$select_coin    = trim($_POST['select_coin']);  */

$func				= trim($_POST['func']);
$mb_id			= trim($_POST['mb_id']);
$amt		= trim($_POST['amt']);
$select_coin 		= $_POST['select_coin'];

/* 원화계좌출금*/
$bank_name = trim($_POST['bank_name']);
$bank_account = trim($_POST['bank_account']);
$account_name = trim($_POST['account_name']);

// $debug = 1;

if($debug){
	$mb_id = 'arcthan';
	$func = 'withdraw';
	$amt = 100000;
	$select_coin = '원';
	$bank_name = '농협';
	$account_name = '로그컴퍼니';
	$bank_account = '123-456789-012';
}


// 출금 설정 
$withdrwal_setting = wallet_config('withdrawal');
$fee = $withdrwal_setting['fee'];
$min_limit = $withdrwal_setting['amt_minimum'];
$max_limit = $withdrwal_setting['amt_maximum'];
$day_limit = $withdrwal_setting['day_limit'];

// 출금가능금액 검증
$withdrwal_total = floor($total_withraw);

if($max_limit != 0 && ($total_withraw * $max_limit*0.01) < $withdrwal_total){
  $withdrwal_total = $total_withraw * ($max_limit*0.01);
}

$fee_calc = floor($amt*($fee*0.01)); // 수수료
$in_amt = $amt - $fee_calc; // 실제출금 차감포인트
	

//출금기록 확인
$today_ready_sql = "SELECT * FROM {$g5['withdrawal']} WHERE mb_id = '{$mb_id}' AND date_format(create_dt,'%Y-%m-%d') = '{$now_date}' ";
$today_ready = sql_query($today_ready_sql);
$today_ready_cnt = sql_num_rows($today_ready);

if($is_debug) echo "<code>일제한: ".$day_limit .' / 오늘 : '.$today_ready_cnt."<br><br>".$today_ready_sql."/ 총 필요금액".$amt_eth_cal."</code><br><br>";

// 일 요청 제한
if($day_limit != 0 && $today_ready_cnt >= $day_limit){
	echo (json_encode(array("result" => "Failed", "code" => "0010","sql"=>"<span style='font-size:12px'>Daily withdrawal count exceeded per day $day_limit time(s)</span><br><span style='font-size:13px'>(일일 출금 횟수 초과입니다. 하루 $day_limit 회 가능)</span>"),JSON_UNESCAPED_UNICODE)); 
	return false;
}
if($is_debug) echo "<code>최소: ".$min_limit .' / 최대가능금액 : '.$withdrwal_total."  (".$max_limit."%) / 현재출금가능".$total_withraw."</code><br><br>";


// 최소금액 제한 확인
if( $min_limit != 0 && $amt < $min_limit ) {
	echo (json_encode(array("result" => "Failed", "code" => "0002","sql"=>"Input correct Minimum Quantity value")));
	return false;
}

// 최대금액 제한 확인
if( $max_limit != 0 && $amt > $withdrwal_total ) {
	echo (json_encode(array("result" => "Failed", "code" => "0002","sql"=>"Input correct Maximun Quantity value")));
	return false;
}

// 출금잔고 재확인 
$fund_check_sql = "SELECT sum(mb_balance) as total from g5_member WHERE mb_id = '{$mb_id}' ";
$fund_check_val = sql_fetch($fund_check_sql)['total'];


if($fund_check_val < $amt){
	echo (json_encode(array("result" => "Failed", "code" => "0002","sql"=>"Not sufficient account balance")));
	return false;
}


/* 
if($is_debug) {echo "수수료 ".$calc_fee." /   토탈 :".$total_balance_usd." /  출금요청 : ".$amt_haz_cal."<br>" ;}

$check_total_sql = "SELECT SUM(amt_total) as total_sum FROM wallet_withdrawal_request WHERE mb_id ='{$mb_id}' and coin = '{$select_coin}' AND STATUS = 0 ";
$total_row = sql_fetch($check_total_sql);

if($total_row['total_sum'] != ""){

	if($amt_eth_cal > $total_bal - $total_row['total_sum']){
		echo (json_encode(array("result" => "Failed", "code" => "0002","sql"=>"Not enough balance of ".strtoupper($select_coin). " because of unconfirmed withdrawal")));
		return false;
	}

}else{

	// 잔고 초과
	if($amt_eth_cal > $total_bal){
		echo (json_encode(array("result" => "Failed", "code" => "0002","sql"=>"Not enough balance of ".strtoupper($select_coin))));
		return false;
	}

} */


// 출금주소 확인
/* if(!$wallet_addr){
	echo (json_encode(array("result" => "Failed", "code" => "0003","sql"=>"Please Input Your Etherium Wallet Address")));
	return false;
} */


//출금 처리
$proc_receipt = "insert {$g5['withdrawal']} set
mb_id ='{$mb_id}'
, addr = ''
, bank_name = '{$bank_name}'
, bank_account = '{$bank_account}'
, account_name = '{$account_name}'
, account = '{$fund_check_val}'
, amt ={$in_amt}
, fee = $fee_calc
, fee_rate = {$fee}
, amt_total = {$amt}
, coin = '{$select_coin}'
, status = '0'
, create_dt = '".$now_datetime."'
, cost = '1'
, out_amt = '{$in_amt}'
, od_type = '출금요청' ";


if($debug){ 
	$rst = 1;
	echo "<br>".$proc_receipt."<br><br>"; 
}else{
	$rst = sql_query($proc_receipt);
}

// 회원정보업데이트
// 출금시 선차감
if($rst){
	$amt_query = "UPDATE g5_member set 
	bank_name = '{$bank_name}'
	, bank_account = '{$bank_account}'
	, account_name = '{$account_name}'
	, mb_deposit_calc = mb_deposit_calc - {$amt}
	, mb_shift_amt = mb_shift_amt + {$amt}
	where mb_id = '{$mb_id}' ";
}
 
if($debug){ 
	$amt_result = 1;
	print_R($amt_query); 
}else{ 
	$amt_result = sql_query($amt_query);
}


if($rst && $amt_result){
	echo (json_encode(array("result" => "success", "code" => "1000")));
}else{
	echo (json_encode(array("result" => "Failed", "code" => "0001","sql"=>"Please retry again")));
}
