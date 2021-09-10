<?php
include_once('./_common.php');

/*수당설정 로드*/
define('ASSETS_CURENCY','원');
$now_datetime = date('Y-m-d H:i:s');
$now_date = date('Y-m-d');


if($_GET['debug']){
	$debug = 1;
}

$bonus_sql = "select * from {$g5['bonus_config']} WHERE used < 2 order by no asc";
$list = sql_query($bonus_sql);

$pre_setting = sql_fetch($bonus_sql);
$pre_condition ='';
$admin_condition = " and "." mb_level < 10 ";

// 이미지급받은경우
$file_name = explode(".",basename($_SERVER['PHP_SELF']));
$code=$file_name[1];
$bonus_day = $_GET['to_date'];

if(!$debug){
    $dupl_check_sql = "select mb_id from {$g5['bonus']} where day='".$bonus_day."' and allowance_name = '{$code}' ";
	$get_today = sql_fetch( $dupl_check_sql);

	if($get_today['mb_id']){
		alert($bonus_day.' '.$code." 수당은 이미 지급되었습니다.");
		die;
	}
}

/*수당지급조건*/
if($pre_setting['layer'] != ''){
    $pre_condition = ' and '.$pre_setting['layer'];
    $pre_condition_in = $pre_setting['layer'];
}else{
    $pre_condition_in = ' mb_level < 9 and mb_rate > 0';
}


// 지난주 날짜 구하기 
$today=$bonus_day;
$timestr        = strtotime($today);

$week           = date('w', strtotime($today));
$weekfr         = $timestr - ($week * 86400);
$weekla         = $weekfr + (6 * 86400);

$week_frdate    = date('Y-m-d', $weekfr - (86400 * 6)); // 지난주 시작일자
$week_todate    = date('Y-m-d', $weekla - (86400 * 6)); // 지난주 종료일자




function bonus_pick($val){    
    global $g5;
    $pick_sql = "select * from {$g5['bonus_config']} where code = '{$val}' ";
    $list = sql_fetch($pick_sql);
    return $list;
}

function bonus_condition_tx($bonus_condition){
    if($bonus_condition == 1){
        $bonus_condition_tx = '추천 계보';
    }else if($bonus_condition == 2){
        $bonus_condition_tx = '후원(바이너리) 계보';
    }else{
        $bonus_condition_tx='';
    }
    return $bonus_condition_tx;
}

function bonus_layer_tx($bonus_layer){
    if($bonus_layer == ''){
        $bonus_layer_tx = '전체지급';
    }else{
        $bonus_layer_tx = $bonus_layer.'단계까지 지급';
    }
    return $bonus_layer_tx;
}

function bonus_limit_tx($bonus_limit){
    if($bonus_limit == ''){
        $bonus_limit_tx = '상한제한없음';
    }else{
        $bonus_limit_tx = (Number_format($bonus_limit*100)).'% 까지 지급';
    }
    return $bonus_limit_tx;
}


/* 수당초과 계산 */
function bonus_limit_check($mb_id,$bonus,$bonus_day = ''){
    global $bonus_limit;

    // $mem_sql="SELECT mb_balance, mb_rate,(SELECT SUM(benefit) FROM soodang_pay WHERE mb_id ='{$mb_id}' AND DAY = '{$bonus_day}') AS b_total FROM g5_member WHERE mb_id ='{$mb_id}' ";
    $mem_sql="SELECT mb_balance, mb_rate, mb_save_point FROM g5_member WHERE mb_id ='{$mb_id}' ";
    $mem_result = sql_fetch($mem_sql);

    $mb_balance = $mem_result['mb_balance'];
    $mb_pv = $mem_result['mb_save_point']*$bonus_limit;

    if($mb_pv > 0 ){
        if( ($mb_balance + $bonus) < $mb_pv){
            $mb_limit = $bonus;
        }else{
            $mb_limit = $mb_pv - $mb_balance;
            if($mb_limit < 0){
                $mb_limit = 0;
            }
        }
    }else{
        $mb_limit = 0;
    }

    return array($mb_balance,$mb_pv,$mb_limit);
}



function it_item_return($it_id,$target,$func = 'id'){
    if($func == 'id'){
        $sql = " SELECT * from g5_shop_item WHERE it_id = '{$it_id}' ";
    }else{
        $where = "it_".$func;
        $sql = " SELECT * from g5_shop_item WHERE $where = '{$it_id}' ";
    }
    $result = sql_fetch($sql);

    if($result){
        return $result['it_'.$target];
    }else{
        return 0;
    }
}

function soodang_record($mb_id, $code, $bonus_val,$rec,$rec_adm,$bonus_day,$mb_no='',$mb_level = ''){
    global $g5,$debug,$now_datetime;

    $soodang_sql = " insert `{$g5['bonus']}` set day='".$bonus_day."'";
    $soodang_sql .= " ,mb_id			= '".$mb_id."'";
    $soodang_sql .= " ,allowance_name	= '".$code."'";
    $soodang_sql .= " ,benefit		=  ".$bonus_val;	
    $soodang_sql .= " ,rec			= '".$rec."'";
    $soodang_sql .= " ,rec_adm		= '".$rec_adm."'";
    $soodang_sql .= " ,datetime		= '".$now_datetime."'";

    if($mb_no != ''){
        $soodang_sql .= " ,mb_no		= '".$mb_no."'";
    }
    if($mb_level != ''){
        $soodang_sql .= " ,mb_level		= '".$mb_level."'";
    }

    // 수당 푸시 메시지 설정
    /* $mb_push_data = sql_fetch("SELECT fcm_token,mb_sms from g5_member WHERE mb_id = '{$mb_id}' ");
    $push_agree = $mb_push_data['mb_sms'];
    $push_token = $mb_push_data['fcm_token'];

    $push_images = G5_URL.'/img/marker.png';
    if($push_token != '' && $push_agree == 1){
        setPushData("[DFINE] - ".$mb_id." 수당 지급 ", $code.' =  +'.$bonus_val.' ETH', $push_token,$push_images);
    } */
    
    if($debug){
        echo "<code>";
        print_r($soodang_sql);
        echo "</code>";
        return true;
    }else{
        return sql_query($soodang_sql);
    }
}


$bonus_row = bonus_pick($code);
if($bonus_row['limited'] > 0){
    $bonus_limit = $bonus_row['limited']/100;
}else{
    $bonus_limit = $bonus_row['limited'];
}
$bonus_limit_tx = bonus_limit_tx($bonus_limit);


if(strpos($bonus_row['rate'],',')>0){
    $bonus_rates = explode(',',$bonus_row['rate']);
}else{
    $bonus_rate = $bonus_row['rate']*0.01;
}

$bonus_condition = $bonus_row['source'];
$bonus_condition_tx = bonus_condition_tx($bonus_condition);

$bonus_layer = $bonus_row['layer'];
$bonus_layer_tx = bonus_layer_tx($bonus_layer);


function get_shop_item($table=null){
	$array = array();
	$sql = "SELECT * FROM g5_shop_item";
	$sql .= " WHERE it_use = 1 ";

	if($table != null){
		$table = strtoupper($table);
		$sql .= " WHERE it_use = 1 AND it_name='{$table}'";
	}
	
	$result = sql_query($sql);

	while($row = sql_fetch_array($result)){
		array_push($array,$row);
	}

	return $array;
}

if( !function_exists( 'array_column' ) ):
    
    function array_column( array $input, $column_key, $index_key = null ) {
    
        $result = array();
        foreach( $input as $k => $v )
            $result[ $index_key ? $v[ $index_key ] : $k ] = $v[ $column_key ];
        
        return $result;
    }
endif;

function ordered_items($mb_id, $table=null){

	$item = get_shop_item($table);

	$upgrade_array = array();
	for($i = 0; $i < count($item); $i++){

		if($table != null){
			$name_lower = $table;
		}else{
			$name_lower = strtolower($item[$i]['it_name']);
		}
	
		$sql = "SELECT * FROM package_".$name_lower." WHERE mb_id = '{$mb_id}' AND promote = 0";
		$result = sql_query($sql);

		for($j = 0; $j < sql_num_rows($result); $j++){
			$row = sql_fetch_array($result);

			$order_sql = "SELECT * FROM g5_shop_order WHERE od_id = '{$row['od_id']}'";
			$order_row = sql_fetch($order_sql);

			array_push($upgrade_array, array(
				"it_id" => $item[$i]['it_id'],
				"it_name" => $item[$i]['it_name'],
				"it_price" => $item[$i]['it_point'],
				"it_supply_point" => $item[$i]['it_supply_point'],
				"it_option_subject" => $item[$i]['it_option_subject'],
				"it_supply_subject" => $item[$i]['it_supply_subject'],
				"od_cart_price" => $order_row['od_cart_price'],
				"upstair" => $order_row['upstair'],
				"pv" => $order_row['pv'],
				"od_time" => $order_row['od_time'],
				"od_settle_case" => $order_row['od_settle_case'],
				// "coin" => $order_row['od_settle_case'],
				// "upgrade_id" => $item[$i+1]['it_id'],
				// "upgrade_name" => $item[$i+1]['it_name'],
				// "upgrade_price" => $item[$i+1]['it_point'],
				"row" => $row
				));

		}
	}

	return $upgrade_array;
}


// 배열키찾기 
function array_key($list,$code,$column){
	$key = array_search($code,array_column($list,$column));
	return $key;
}


