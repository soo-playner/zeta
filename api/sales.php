<?php
include_once('./_common.php');
// include_once(G5_ADMIN_PATH.'/bonus_inc.php');
$title="zetabyte :: API - 추천산하매출";
include_once('./header.php');
$matching_lvl = 3;
$member = $_REQUEST['mb_id'];

?>

<style>
    body{width:100%;}
    #container{width:100%;}
    .inpuset {width:100%;padding:20px 0;}
    .inpuset .mb_input{padding:5px;line-height:30px;font-size:16px;font-weight:600}
    .inpuset .mb_submit{width:100px;height:42px;border:0;background:black;color:white;cursor: pointer;}
    .mb_name{font-size:20px;font-weight:600;}
    li{width:100%;max-width:500px; list-style:none;border-top:1px solid #ccc;line-height:36px;}
    li.title{background:ghostwhite;line-height:50px;}
    li span{display:inline-block;}
    .li_foot{background:ghostwhite;line-height:50px;font-weight:600;text-align:center}
    .header{width:120px;font-weight:600;color:black;text-align:left;}
    .layer{width:60px;color:#999;text-align:center;}
    .price{min-width:150px;text-align:right;}
    a,a:visited, a:link{color:black;text-decoration:none;}
    a:hover{text-decoration:underline;}
</style>

<div class='inpuset'>
    <form action="./sales.php" method="POST">
        <input type='text' name='mb_id' class='mb_input' placeholder="검색할 회원아이디">
        <input type='submit' value='검색' class='mb_submit'>
    </form>
</div>

<?
if($member){ 
?>
<div id='container'>
<div class="mb_name">검색회원 : <?=$member?></div>
<li class='title'>
    <span class="header">회원</span>
    <span class="layer">대수</span>
    <span class='price'>매출금액</span>
</li>
</div>

<?}?>

<?
// 추천트리 하부 
$mem_list =[];


function  excute($mb,$category){
    global $matching_lvl;

    $sql = "SELECT * FROM g5_member WHERE mb_id ='{$mb}' ";
    $result = sql_fetch($sql);  
    if($result){
        if($category == 'sales'){
            $target = 'mb_save_point';

            $mining_down_tree_result = return_down_manager($result['mb_id'],$matching_lvl);
            $mining_matching_sum = array_sum(array_column($mining_down_tree_result, $target));

            echo "<li class='li_foot'>";
            echo "합계 :: ￦ ".number_format($mining_matching_sum);
            echo "</li>";
        }
    }else{
        echo "해당회원이 없습니다";
    }
}

function return_down_manager($mb_id,$cnt=0){
	global $config,$g5,$mem_list;

	$mb_result = sql_fetch("SELECT mb_id,mb_rate,mb_save_point from g5_member WHERE mb_id = '{$mb_id}' ");
    
    // 본인제외
	// $list = [];
	// $list['mb_id'] = $mb_result['mb_id'];
	// $list['mb_rate'] = $mb_result['mb_rate'];
	// $mem_list = [$list];

	$result = recommend_downtree($mb_result['mb_id'],0,$cnt);
	return $result;
}


function recommend_downtree($mb_id,$count=0,$cnt = 0){
	global $mem_list;

	if($cnt == 0 || ($cnt !=0 && $count < $cnt)){
		
		$recommend_tree_result = sql_query("SELECT mb_id,mb_rate,mb_save_point from g5_member WHERE mb_recommend = '{$mb_id}' ");
		$recommend_tree_cnt = sql_num_rows($recommend_tree_result);

		if($recommend_tree_cnt > 0 ){
			++$count;
			while($row = sql_fetch_array($recommend_tree_result)){
				$list['mb_id'] = $row['mb_id'];
                $list['mb_rate'] = $row['mb_rate'];
                $list['mb_save_point'] = $row['mb_save_point'];
				$list['depth'] = $count;
                
                echo "<li>";
                echo "<span class='header'><a href='./sales.php?mb_id=".$list['mb_id']."'>".$list['mb_id']."</a></span>";
                echo "<span class='layer'>";
                echo $list['depth'].'대';
                echo "</span>";
                echo "<span class='price'>";
                echo Number_format($list['mb_save_point']);
                echo "</span>";
                echo "</li>";

				array_push($mem_list,$list);
				recommend_downtree($row['mb_id'],$count,$cnt);
			}
		}
	}
	return $mem_list;
}



// array_column 5.4 대응
if( !function_exists( 'array_column' ) ):
    function array_column( array $input, $column_key, $index_key = null ) {
        $result = array();
        foreach( $input as $k => $v )
            $result[ $index_key ? $v[ $index_key ] : $k ] = $v[ $column_key ];
        
        return $result;
    }
    
endif;

if($member){
    excute($member,'sales');
}
?>





