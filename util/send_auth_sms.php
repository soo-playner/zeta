<?php
// if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once('./_common.php');
include_once(G5_LIB_PATH.'/icode.sms.lib.php');

login_check($member['mb_id']);
// $debug = 1;

$otp_key = mt_rand(100000, 999999);
$mb_id = $member['mb_id'];
$mb_hp = $member['mb_hp'];

$update_auth_mobile = "UPDATE g5_member set otp_key = '{$otp_key}' WHERE mb_id = '{$mb_id}' ";
if($debug){
    print_R($update_auth_mobile);
}else{
    sql_query($update_auth_mobile);
}

//----------------------------------------------------------
// SMS 문자전송 시작
//----------------------------------------------------------

$sms_contents = "[".$config['cf_title']."] 출금 인증번호 (".$otp_key.") 를 입력해주세요.";

// 핸드폰번호에서 숫자만 취한다
$receive_number = preg_replace("/[^0-9]/", "", $mb_hp);  // 수신자번호 (회원님의 핸드폰번호)
$send_number = preg_replace("/[^0-9]/", "", $sms5['cf_phone']); // 발신자번호

if($debug){
    echo "<br>";
    print_R($sms_contents);
    echo "<br>receive_number : ".$receive_number;
    echo "<br>send_number : ".$send_number;
    ob_clean();
    echo json_encode(array("result" => "success",  "time" => 500));
}else{
    $SMS = new SMS; // SMS 연결
    $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
    $SMS->Add($receive_number, $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_contents)), "");
    $result = $SMS->Send();
    $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
    $result =1;
    
    if($result){
        echo json_encode(array("result" => "success",  "time" => 500));
    } else {
        echo json_encode(array("result" => "failed","error" => "현재 이용할수 없습니다."));
    }

}

//----------------------------------------------------------
// SMS 문자전송 끝
//----------------------------------------------------------

?>
