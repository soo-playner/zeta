<?php
$sub_menu = "800200";
include_once('./_common.php');
include_once(G5_THEME_PATH . '/_include/wallet.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '메세지관리';
include_once('../admin.head.php');

$list = sql_query("SELECT * from app_msg");

?>

<style>
    .frm_input{width:100%;min-width:100px;}
    .frm_input.content{min-height:40px;}
</style>

<link rel="stylesheet" href="<?= G5_THEME_URL ?>/css/scss/custom.css">
<link rel="stylesheet" href="../css/scss/admin_custom.css">

<div class="local_ov01 local_ov">
    <?php echo $listall ?>
    <p>
        - 이모지 사용<br>
	</p>
</div>

<form name="msg_form" id="msg_form" action="./.php" onsubmit="return fmemberlist_submit(this);" method="post">
<div class="tbl_head02 tbl_wrap">
    <table >
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
	<p> 수당 설정 </p>
    <tr>
        <th scope="col" width="30px">No</th>
        <th scope="col" width="100px">메세지명</th>
        <th scope="col" width="200px">타이틀</th>	
        <th scope="col" width="250px">내용</th>
        <th scope="col" width="80px">이미지</th>
        <th scope="col" width="30px">사용유무</th>
    </tr>
    </thead>

    <tbody>
    <?for($i=0; $row=sql_fetch_array($list); $i++){?>
    
    <tr class='<?if($i == 0){echo 'first';}?>'>
   
    <td style="text-align:center">
        <input type="hidden" name="no[]" value="<?=$row['no']?>">
        <input type='checkbox' class='checkbox' name='check' <?php echo $row['used'] > 0?'checked':''; ?>>
    </td>
    <td style="text-align:center"><input class='frm_input' name="name[]"  value="<?=$row['name']?>"></input></td>
    <td style="text-align:center;width:250px;"><input class='frm_input' name="title[]"  value="<?=$row['title']?>"></input></td>
    <td style="text-align:center"><textarea class='frm_input content' name="contents[]" ><?=$row['contents']?></textarea></td>
    <td style="text-align:center"><input class='frm_input' name="image[]"  value="<?=$row['image']?>"></input></td>
    <td style="text-align:center"><input type='checkbox' class='checkbox' name='check' <?php echo $row['used'] > 0 ?'checked':''; ?>></td>
    </tr>
    <?}?>
    </tbody>
    
</table>

    <div>
        <input style="align:center;padding:15px 50px;background:cornflowerblue;" type="submit" class="btn btn_confirm btn_submit" value="저장하기" id="com_send"></input>
    </div>
</form>
</div>

<?php
include_once('../admin.tail.php');
?>
