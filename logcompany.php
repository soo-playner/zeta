<?
define('LIVE_MODE',false);
define('CONFIG_TITLE','ZETABYTE');
define('CONFIG_SUB_TITLE','ZETABYTE');

// 메일설정
define('CONFIG_MAIL_ACCOUNT','wizclass.inc');
// define('CONFIG_MAIL_PW','willsoft0780!@');
define('CONFIG_MAIL_PW','izmvwaprbjxgftme');
define('CONFIG_MAIL_ADDR','wizclass.inc@gmail.com');

// 기준통화설정
define('ASSETS_CURENCY','원');
define('BALANCE_CURENCY','원');
define('WITHDRAW_CURENCY','원');


$minings = ['원','eth','etc','fil'];
$mining_hash = ['mh/s','mh/s'];

$before_mining_coin = 1;
$before_mining_target = 'mb_mining_'.$before_mining_coin;
$before_mining_amt_target = $before_mining_target.'_amt';

$now_mining_coin = 2;
$mining_target = 'mb_mining_'.$now_mining_coin;
$mining_amt_target = $mining_target.'_amt';


$version_date = '2022-09-20';

define('ASSETS_NUMBER_POINT',0); // 입금 단위
define('BONUS_NUMBER_POINT',0); // 수당계산,정산기준단위
define('COIN_NUMBER_POINT',4); // 코인 단위

// 회사지갑 설정
define('ETH_ADDRESS','');

// 텔레그램 설정
define('TELEGRAM_ALERT_USE',FALSE);

$log_ip = '183.98.71.48';
$log_pw = "*744489A21B79279202FA838C7CB7E1BE3134EB77";


// 이더사용 및 회사지갑 설정
// False 설정시 현금사용
define('USE_WALLET',FALSE);


$log_ip = '183.98.71.48';
$log_pw = "*744489A21B79279202FA838C7CB7E1BE3134EB77";



//영카트 로그인체크 주소
if(strpos($_SERVER['HTTP_HOST'],"localhost") !== false){
    $port_number = "";
    define('SHOP_URL',"http://localhost:{$port_number}/bbs/login_check.php");
}else{
    define('SHOP_URL',"http://khanshop.willsoft.kr/bbs/login_check.php");
}

?>
