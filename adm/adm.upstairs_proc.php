<?php
include_once('./_common.php');
include_once(G5_THEME_PATH.'/_include/wallet.php');
include_once(G5_PATH.'/util/purchase_proc.php');

// $debug = 1;
$now_datetime = date('Y-m-d H:i:s');
$now_date = date('Y-m-d');

if($_GET['debug']){
	$debug=1;
}


$mb_id = $_POST['mb_id'];
$mb_no = $_POST['mb_no'];

// $input_val ='1';
// $output_val ='169.09';
// $coin_val = 'eth';

$input_val= conv_number($_POST['input_val']);
$output_val = conv_number($_POST['output_val']);
$coin_val = $_POST['coin_val'];
$pack_name= $_POST['select_pack_name'];
$pack_id = $_POST['select_pack_id'];
$it_supply_point = $_POST['it_supply_point'];

/* if($debug){
	$input_val ='1200000'; // 코인결제
	$output_val ='1000'; // 상품금액 0
	$coin_val = '원'; // 코인
	$pack_name = 'P1';
	$pack_id = 2021051031;
	$it_supply_point = 100;
}
 */

if($pack_id == 2021051040){
	$pack_name = 'P3';
	$input_price = 0;
	$it_supply_point = 0;
}else{
	$input_price = $input_val;
}


$target = "mb_deposit_calc";
$target_price = coin_price('usd');
$orderid = date("YmdHis",time()).'01';
$pv = $it_supply_point * 10000;

$sql = "insert g5_shop_order set
	od_id				= '".$orderid."'
	, mb_no             = '".$mb_no."'
	, mb_id             = '".$mb_id."'
	, od_cart_price     = ".$output_val."
	, od_cash    		= ".$target_price."
	, od_name           = '{$pack_name}'
	, od_tno            = '{$pack_id}'
	, od_receipt_time   = '".$now_datetime."'
	, od_time           = '".$now_datetime."'
	, od_date           = '".$now_date."'
	, od_settle_case    = '".$coin_val."'
	, od_status         = '패키지구매(관리자)'
	, upstair    		= ".$input_price."
	, pv				= ".$pv." ";


if($debug){
	$rst = 1;
	echo "구매내역 Invoice 생성<br>";
	echo $sql."<br><br>";
}else{
	$rst = sql_query($sql);
}

$logic = purchase_package($mb_id,$pack_id);

$calc_value = conv_number($input_val);

if($rst && $logic){

	$update_point = " UPDATE g5_member set $target = ($target - $calc_value) ";

	if($pack_id == 2021051040){
		$servicepack_sql = "SELECT * FROM g5_shop_item WHERE it_id = {$pack_id} ";
    	$servicepack_result = sql_fetch($servicepack_sql)['it_point'];
		$calc_value = conv_number($servicepack_result * $usd_price);
		$update_point .= ", mb_level = 3 " ;
	}else{
		if($member['mb_level'] == 0){
			$update_point .= ", mb_level = 1 " ;
		}
	}
	
	$update_point .= ", mb_rate = ( mb_rate + {$pv}) ";
	$update_point .= ", mb_save_point = ( mb_save_point + {$calc_value}) ";
	$update_point .= ", rank_note = '{$pack_name}', sales_day = '{$now_datetime}' ";
	$update_point .= " where mb_id ='".$mb_id."'";



	if($debug){
		echo "회원 금액 반영<br>";
		echo $update_point."<br>";
	}else{
		sql_query($update_point);
		ob_end_clean();
		echo (json_encode(array("result" => "success",  "code" => "0000", "sql" => $save_hist)));
	}
}else{
	ob_end_clean();
	echo (json_encode(array("result" => "failed",  "code" => "0001", "sql" => $save_hist)));
}

?>

<?if($debug){?>
<style>
    .red{color:red;font-size:16px;font-weight:900}
    .blue{color:blue;font-size:16px;font-weight:900}
    .title {font-weight:900}
    code{text-decoration: italic;color:green;display:block}
    .box{background:#f5f5f5;border:1px solid #ddd;padding:20px;}
</style>
<?}?>
