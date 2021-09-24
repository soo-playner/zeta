<?
define('CONFIG_TITLE','ZETABYTE');
define('CONFIG_SUB_TITLE','ZETABYTE');

// 메일설정
define('CONFIG_MAIL_ACCOUNT','willsoftkr');
define('CONFIG_MAIL_PW','willsoft0780');
define('CONFIG_MAIL_ADDR','willsoftkr@gmail.com');

// 기준통화설정
define('ASSETS_CURENCY','원');
define('BALANCE_CURENCY','원');

define('ASSETS_NUMBER_POINT',0);
define('BONUS_NUMBER_POINT',0);

// 회사지갑 설정
define('ETH_ADDRESS','');

//영카트 로그인체크 주소
if(strpos($_SERVER['HTTP_HOST'],"localhost") !== false){
    $port_number = "";
    define('SHOP_URL',"http://localhost:{$port_number}/bbs/login_check.php");
}else{
    define('SHOP_URL',"http://khanshop.willsoft.kr/bbs/login_check.php");
}

?>
