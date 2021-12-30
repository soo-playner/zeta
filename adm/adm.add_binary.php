<?php
include_once('./_common.php');
include_once('./bonus/bonus_inc.php');

$now_datetime = date('Y-m-d H:i:s');

// $debug=1;

if(isset($_POST['mb_id'])){
    $mb_id = $_POST['mb_id'];
}

if(isset($mb_id)){
    add_binary($mb_id);
}

// 후원레그2 후원인 기록
function add_binary($mb_id){
    global $debug,$now_datetime;

    $origin_number_sql = "SELECT mb_recommend from g5_member WHERE mb_id = '{$mb_id}' ";
    $origin_number_result = sql_fetch($origin_number_sql);
    $origin_recom = $origin_number_result['mb_recommend'];

    $brecomme2 = array_brecommend_binary($origin_recom, 1);
    $target_key2 = min(array_keys($brecomme2));
    $now_brecom2 = $brecomme2[$target_key2];

    if ($now_brecom2['cnt'] == 0) {
        $now_type2 = 'L';
    } else {
        $now_type2 = 'R';
    }

    if ($debug) {
        echo "<br><br> 슈퍼레그 후원자찾기 :: ";
        print_R($now_brecom2);
    }

    $random_recom_update_sql = "INSERT g5_member_binary set mb_id = '{$mb_id}',mb_recommend='{$origin_recom}', mb_brecommend='{$now_brecom2['id']}',mb_bre_time ='{$now_datetime}', mb_brecommend_type='{$now_type2}'";

    //중복아이디 없을때만
    /* $dup_check_sql = "SELECT count(*) as cnt from g5_member_binary WHERE mb_id = '{$mb_id}' ";
    $dup_check_result = sql_fetch($dup_check_sql);
    $dup_check = $dup_check_result['cnt']; */

    if ($debug) {
        echo "<br><br>슈퍼레그 후원인 기록 :: ";
        print_R($random_recom_update_sql);
        $random_recom_update_result = 1;
    } else {
        $random_recom_update_result = sql_query($random_recom_update_sql);
    }

    if($random_recom_update_result){
        $msg = "후원2 레그 등록 = {$now_brecom2['id']} - $now_type2 \n";
        $msg .= "처리가 완료되었습니다.";
    
        echo (json_encode(array("result" => "success", "code" => "0001", "msg" => $msg), JSON_UNESCAPED_UNICODE));
    }else{
        echo (json_encode(array("result" => "failed", "code" => "0002", "msg" => "처리에러"), JSON_UNESCAPED_UNICODE));
    }
     
}


$brcomm_arr2 = [];
// 후원인 빈자리 찾기
function array_brecommend_binary($recom_id, $count)
{
	global $brcomm_arr2, $debug;


	// $new_arr = array();
	$b_recom_sql = "SELECT mb_id from g5_member_binary WHERE mb_brecommend='{$recom_id}' ";
	$b_recom_result = sql_query($b_recom_sql);
	$cnt = sql_num_rows($b_recom_result);

	if ($cnt < 2) {
		if ($debug) {
			echo "<br><br><br><br>";
			print_R($count . ' :: ' . $recom_id . ' :: ' . $cnt);
		}
		if (!$brcomm_arr2[$count]) {
			$brcomm_arr2[$count]['id'] = $recom_id;
			$brcomm_arr2[$count]['cnt'] = $cnt;
		}
	} else {
		++$count;
		while ($row = sql_fetch_array($b_recom_result)) {
			array_brecommend_binary($row['mb_id'], $count);
		}
	}
	return $brcomm_arr2;
}

?>