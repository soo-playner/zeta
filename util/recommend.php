<?
/* 추천인 트리 */
$mem_list = [];

/* 추천상위매니저 검색 */
function return_up_manager($mb_id,$cnt=0){
	global $config;
	$origin = $mb_id;
	$manager_list = [];
	$i = 0;
    
    if($mb_id != 'admin' && $mb_id != $config['cf_admin']){
		
		if($cnt == 0){
			do{
				$manager = recommend_uptree($mb_id);
				$mb_id = $manager;
				array_push($manager_list,$manager);
			}while( 
				$manager != 'khan'
			);
		
			if(count($manager_list) < 2){
				return $origin;
			}else{
				return $manager_list[count($manager_list)-2];
			}
		}else{
			do{
				$i++;
				$manager = recommend_uptree($mb_id);
				$mb_id = $manager;
				array_push($manager_list,$manager);
			}while( $i < $cnt );

			return $manager_list[$cnt-1];
		}
    }else{
        return $mb_id;
    }
}



function recommend_uptree($mb_id){
    $result = sql_fetch("SELECT mb_recommend,mb_level from g5_member WHERE mb_id = '{$mb_id}' ");
    return $result['mb_recommend'];
}

function return_down_manager($mb_no,$cnt=0){
	global $config,$g5,$mem_list;

	$mb_result = sql_fetch("SELECT mb_id,mb_level,grade,mb_rate from g5_member WHERE mb_no = '{$mb_no}' ");
	$list = [];
	$list['mb_id'] = $mb_result['mb_id'];
	$list['mb_level'] = $mb_result['mb_level'];
	$list['grade'] = $mb_result['grade'];
	$list['depth'] = 0;
	$list['mb_rate'] = $mb_result['mb_rate'];
	
	$mb_add = sql_fetch("SELECT COUNT(mb_id) as cnt,IFNULL( (SELECT noo  from  recom_bonus_noo WHERE mb_id = '{$mb_result['mb_id']}' ) ,0) AS noo FROM g5_member WHERE mb_recommend = '{$mb_result['mb_id']}' ");
	
	$list['cnt'] = $mb_add['cnt'];
	$list['noo'] = $mb_add['noo'];

	$mem_list = [$list];
	$result = recommend_downtree($mb_result['mb_id'],1,$cnt);
	// print_R(arr_sort($result,'count'));
	// prinT_R($result);
	return $result;
}


function recommend_downtree($mb_id,$count=0,$cnt = 0){
	global $mem_list;

	if($cnt == 0 || ($cnt !=0 && $count < $cnt)){
		
		$recommend_tree_result = sql_query("SELECT mb_id,mb_level,grade,mb_rate from g5_member WHERE mb_recommend = '{$mb_id}' ");
		$recommend_tree_cnt = sql_num_rows($recommend_tree_result);
		if($recommend_tree_cnt > 0 ){
			++$count;

			while($row = sql_fetch_array($recommend_tree_result)){
				$list['mb_id'] = $row['mb_id'];
				$list['mb_level'] = $row['mb_level'];
				$list['grade'] = $row['grade'];
				$list['mb_rate'] = $row['mb_rate'];
				
				$mb_add = sql_fetch("SELECT COUNT(mb_id) as cnt,IFNULL( (SELECT noo  from  recom_bonus_noo WHERE mb_id = '{$row['mb_id']}' ) ,0) AS noo FROM g5_member WHERE mb_recommend = '{$row['mb_id']}' ");
	
				$list['cnt'] = $mb_add['cnt'];
				$list['noo'] = $mb_add['noo'];
				$list['depth'] = $count;
				// $list['maxItem'] = "<span class='badge t_white color".max_item_level_array($row['mb_id'],'number')."'>".max_item_level_array($row['mb_id'],'name')."</span>";
				array_push($mem_list,$list);
				recommend_downtree($row['mb_id'],$count,$cnt);
			}
		}
	}
	return $mem_list;
}



function arr_sort($array, $key, $sort='asc') {
	$keys = array();
	$vals = array();

	foreach ($array as $k=>$v) {
		$i = $v[$key].'.'.$k;
		$vals[$i] = $v;
		array_push($keys, $k);
	}

	unset($array);

	if ($sort=='asc') {
		ksort($vals);
	} else {
		krsort($vals);
	}

	$ret = array_combine($keys, $vals);
	unset($keys);
	unset($vals);

	return $ret;
}
?>