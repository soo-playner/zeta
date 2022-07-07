<?php
include_once('./_common.php');

print_R($_POST);
$now_datetime = G5_TIME_YMDHIS;

$sql = "SELECT * FROM g5_write_kyc WHERE wr_id = '{$_POST['id']}'";
$pre_result = sql_fetch($sql);


if ($pre_result) {
    
    $sql = "UPDATE g5_write_kyc SET wr_2 = '{$_POST['value']}',wr_4 = '{$now_datetime}' WHERE wr_id = '{$_POST['id']}' ";
    $result = sql_query($sql);

    $mb_update = "UPDATE g5_member SET kyc_cert = {$_POST['value']}, kyc_regdt = '{$now_datetime}' WHERE mb_id = '{$pre_result['mb_id']}'";
    $update_result = sql_query($mb_update);

    if ($update_result) {
        echo json_encode(array("code" => "00001","result" => "success"));
    }else{
        echo json_encode(array("code" => "00002","result" => "error"));
    }
}
