<?php
include_once('./_common.php');
include_once('./bonus/bonus_inc.php');
//include_once(G5_LIB_PATH.'/mailer.lib.php');

$now_datetime = date('Y-m-d H:i:s');
$now_date = date('Y-m-d');


// $debug = 1;

$uid = $_POST['uid'];
$status = $_POST['status'];
$refund = $_POST['refund'];
$coin = $_POST['coin'];
$in_amt = $_POST['amt'];
$func = $_POST['func'];

$count = 1;
$drain = 1;

// $bonus_row = bonus_pick('cycle');
// $bonus_rate = $bonus_row['rate'];
// echo "보너스 :: ".$bonus_row['rate'];


/* if ($debug) {
	$uid = 1;
	$status = 1;
	$refund = 'Y';
	$func = 'deposit';
	$coin = '원';
	$in_amt = '480000';
} */


if ($func == 'withrawal') {
	if ($status == '4' && $refund == 'Y') {
		$get_row = "SELECT * from {$g5['withdrawal']} where uid = {$uid} ";
		/* if ($debug) {
			print_r($get_row);
		} */
		$ret = sql_fetch($get_row);
		$mb_id = $ret['mb_id'];
		$in_amt_total = $ret['amt_total'];

		// 출금반환처리

		if($coin == '원'){
			$coin_target = "mb_deposit_calc";
		}else{
			$coin_target = "mb_" . strtolower($coin) . "_calc";
		}
		
		$sql1 = "update g5_member set {$coin_target} = {$coin_target} + {$in_amt_total}, mb_shift_amt = mb_shift_amt - {$in_amt_total}  where mb_id='{$mb_id}' ";

		if ($debug) {
			print_r($sql1);
			echo "<br>";
		} else {
			sql_query($sql1);
			
		}
	}

	$sql = "UPDATE {$g5['withdrawal']} set status = '{$status}' ";
	$sql .= ", update_dt = now() ";
	$sql .= " where uid = {$uid} ";

	$result = sql_query($sql);

	if($result){
		$code = "0000";
	}else{
		$code = "0001";
	}

	echo json_encode(array("code"=>$code));

} else if ($func == 'deposit') {

	if ($status == '1' && $coin != '') {
		$get_row = "SELECT * from {$g5['deposit']} where uid = {$uid} ";
		if ($debug) {
			print_r($get_row);
			echo "<br>";
		}
		$ret = sql_fetch($get_row);
		$mb_id = $ret['mb_id'];

		if($coin == '원'){
			$coin_target = "mb_deposit_point";
		}else{
			$coin_target = "mb_" . strtolower($coin) . "_account";
		}

		if($in_amt > 0 && $refund == 'Y'){
			$sql1 = "UPDATE g5_member set mb_deposit_point = mb_deposit_point + {$in_amt}  where mb_id='{$mb_id}' ";

			if ($debug) {
				print_r($sql1);
				echo "<br>";
			} else {
				sql_query($sql1);
			}
		}

	} // 승인인경우
	
	$sql = "UPDATE {$g5['deposit']} set status = '{$status}' ";
	$sql .= ", in_amt = {$in_amt}";
	$sql .= ", update_dt = '{$now_datetime}' ";
	$sql .= " where uid = {$uid} ";
	
	if ($debug) {
		echo "<br><br>";
		print_r($sql);
		echo "<br>";
	} else {
		$result = sql_query($sql);
	}
	
	if ($result && $status != '1' && $refund == 'Y') {
		echo (json_encode(array("result" => "success", "code" => "0001", "msg" =>"상태값이 변경되었습니다."),JSON_UNESCAPED_UNICODE));
	}else{
		echo (json_encode(array("result" => "success", "code" => "0002", "msg" =>"상태값변경과 입금/출금 처리가 완료되었습니다."),JSON_UNESCAPED_UNICODE));
	}

	
} else {
	echo (json_encode(array("result" => "failed", "code" => "9999", "sql" => "func can't find ERROR ")));
}

