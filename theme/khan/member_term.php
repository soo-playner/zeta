<?
include_once('./_common.php');
include_once(G5_THEME_PATH.'/_include/gnb.php');


login_check($member['mb_id']);

$sql = "SELECT wr_subject, wr_content FROM g5_write_agreement ORDER BY wr_id ASC";
$result = sql_query($sql);

$agreement = array();
while($row = sql_fetch_array($result)){
    array_push($agreement, $row);
}

$title = '회원약관';

?>
	
</body>
<main>
	<div class="container member_term pt20">
		<p class="mb-2">서비스 이용약관</p>
        <div class="white mb-4"></div>
        <p class="mb-2">개인정보 수집 및 이용약관</p>
        <div class="white mb-4"></div>
	</div>
</main>

<? include_once(G5_THEME_PATH.'/_include/tail.php'); ?>