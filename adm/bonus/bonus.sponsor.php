<?php
$sub_menu = "600200";
include_once('./_common.php');
include_once('./bonus_inc.php');

auth_check($auth[$sub_menu], 'r');


//회원 리스트를 읽어 온다.
$sql_common = " FROM {$g5['bonus']} ";
$sql_search=" WHERE allowance_name = 'binary' AND day = '{$bonus_day}' ";
$sql_mgroup=' GROUP BY mb_id ORDER BY mb_no asc';

$pre_sql = "select count(*) 
                {$sql_common}
                {$sql_search}
                {$sql_mgroup}";
$pre_result = sql_query($pre_sql);
$result_cnt = sql_num_rows($pre_result);

ob_start();

// 설정로그 
echo "<strong>".strtoupper($code)." 지급비율 : ". $bonus_row['rate']."%   </strong> |    지급조건 :".$pre_condition.' | '.$bonus_condition_tx." | ".$bonus_layer_tx."<br>";
echo "<strong>".$bonus_day."</strong><br>";
echo "<br><span class='red'> 기준대상자(매출발생자) : ".$result_cnt."</span><br><br>";
echo "<div class='btn' onclick='bonus_url();'>돌아가기</div>";

?>

<html><body>
<header>정산시작</header>    
<div>
<?

$price_cond=", SUM(benefit) AS hap";

$sql = "SELECT *
            {$sql_common}
            {$sql_search}
            {$sql_mgroup}";
$result = sql_query($sql);

// 디버그 로그 
if($debug){
	echo "<code>";
    print_r($sql);
	echo "</code><br>";
}


$history_cnt=0;
$rec='';

excute();

function  excute(){

    global $result;
    global $g5, $bonus_day, $bonus_condition, $code, $bonus_rate,$bonus_rates,$pre_condition_in,$bonus_limit,$bonus_layer,$history_cnt ;
    global $debug;

    for ($i=0; $row=sql_fetch_array($result); $i++) {   

        $today_sales2=0;
        $confirm_exit1=0;

        $today=$row['datetime'];
        $comp=$row['mb_id'];
        
        $first=0; 
        $firstname='';
        $firstid='';
        
        $today_sales=$row['benefit'];

        echo "<br><br><span class='title' style='font-size:30px;'>".$comp."</span><br>";

        while(  $comp!='admin'  ){   
            $sql = " SELECT mb_no, mb_id, mb_name,grade,mb_level, mb_balance, mb_recommend, mb_brecommend, mb_deposit_point FROM g5_member WHERE mb_id= '{$comp}' ";
            if($debug){echo "<code>".$sql."</code>";}
            $recommend = sql_fetch($sql);

            $mb_no=$recommend['mb_no'];
            $mb_id=$recommend['mb_id'];
            $mb_name=$recommend['mb_name'];
            $mb_level=$recommend['mb_level'];
            $mb_deposit=$recommend['mb_deposit_point'];
            $mb_balance=$recommend['mb_balance'];
            $grade=$recommend['grade'];


            // 추천, 후원 조건
            if($bonus_condition < 2){
                $recom=$recommend['mb_recommend'];
            }else{
                $recom=$recommend['mb_brecommend'];
            }
            

            // 관리자 제외
            if($mb_id != 'admin' && $mb_level > 9 ){ break;} 
                
            if( $history_cnt==0 ){ // 본인
                $firstname=$mb_name;
                $firstid=$mb_id;

            }else if($history_cnt <= $bonus_layer){                 // 본인 제외 - 지정대수까지
                
                if($pre_condition_in){	

                $hist = $history_cnt-1;	
                $bonus_rate = $bonus_rates[$hist];

                $benefit=($today_sales*($bonus_rate * 0.01));// 매출자 * 수당비율

                list($mb_balance,$balance_limit,$benefit_limit) = bonus_limit_check($mb_id,$benefit);
                
                echo "<br><br><strong>".$mb_id."</strong> | <strong>".$grade.' STAR</strong> | '.$history_cnt." 단계 :: ".$today_sales.'*'.$bonus_rate.'%';

                // 디버그 로그
                
                echo "<code>";
                echo "현재수당 : ".Number_format($mb_balance)."  | 수당한계 :". Number_format($balance_limit).' | ';
                echo "발생할수당: ".Number_format($benefit)." | 지급할수당 :".Number_format($benefit_limit);
                echo "</code><br>";
                
                

                $rec=$code.' Bonus from '.$firstid.'('. $firstname.') :: step : '.$history_cnt.')';
                $rec_adm= ''.$firstid.' - '.$history_cnt.'대 :'.$today_sales.'*'.$bonus_rate.'='.$benefit;
                
                    
                /* if($benefit_limit > $balance_limit){
                    $benefit_limit = $balance_limit;
                    $rec_adm = "benefit overflow";
                    echo " <span class=red> ▶▶ 수당 초과 (한계까지만 지급)".$benefit_limit." </span><br>";
                    
                }else{

                    // 수당 로그
                    echo $mb_id." | ".$history_cnt." 단계 :: ".$today_sales.'*'.$bonus_rate.'%';
                    echo "<span class=blue> ▶▶▶ 수당 지급 : ".Number_format($benefit)."</span><br>";
                } */


                // 등급계산
                /* $grade_condition = (($history_cnt*2)-1) ; 
                
                if($grade > 0 && $grade >= $grade_condition){
                   $grade_check = 1;
                   
                }else{
                   $grade_check = 0;
                   echo "<span class='red'>등급기준 미달 :: </span>";
                   
                } 

                echo " 등급기준: ".$grade_condition." | ".$grade."</span>";
                */

                $package_condition = (($history_cnt*2)-1) ; 

                $high_item = max_item_level_array($mb_id);
                $high_item_num = substr($high_item,1,1);

                if($high_item_num > 0 && $high_item_num >= $package_condition){
                    $package_check = 1; 
                }else{
                    $package_check = 0;
                    echo "<span class='red'>패키지 구매 등급기준 미달 :: </span>";
                } 

                echo "구매등급 기준 : P".$package_condition."이상 | <span class='blue'>".$high_item."</span> 보유</span>";



                
                if($package_check > 0){
                    if($benefit > $benefit_limit && $balance_limit != 0 ){

                        $rec_adm .= "<span class=red> |  Bonus overflow :: ".Number_format($benefit_limit - $benefit)."</span>";
                        echo "<span class=blue> ▶▶ 수당 지급 : ".Number_format($benefit)."</span>";
                        echo "<span class=red> ▶▶▶ 수당 초과 (한계까지만 지급) : ".Number_format($benefit_limit)." </span><br>";
                    }else if($benefit != 0 && $balance_limit == 0 && $benefit_limit == 0){
                
                        $rec_adm .= "<span class=red> | Sales zero :: ".Number_format($benefit_limit - $benefit)."</span>";
                        echo "<span class=blue> ▶▶ 수당 지급 : ".Number_format($benefit)."</span>";
                        echo "<span class=red> ▶▶▶ 수당 초과 (기준매출없음) : ".Number_format($benefit_limit)." </span><br>";
                
                    }else if($benefit == 0){
                        echo "<span class=blue> ▶▶ 수당 미발생 </span>";
                
                    }else{
                        echo "<span class=blue> ▶▶ 수당 지급 : ".Number_format($benefit)."</span><br>";
                    }
                }else{
                    $benefit_limit = 0;
                    echo "<span class=blue> ▶▶ 수당 미발생 </span>";
                }

                if($benefit > 0 && $benefit_limit > 0 && $package_check > 0){

                $record_result = soodang_record($mb_id, $code, $benefit_limit,$rec,$rec_adm,$bonus_day);

                if($record_result){
                    
                    $balance_up = "update g5_member set mb_balance = mb_balance + {$benefit_limit}  where mb_id = '".$mb_id."'";
                        // 디버그 로그
                        if($debug){
                            echo "<code>";
                            print_R($balance_up);
                            echo "</code>";
                        }else{
                            sql_query($balance_up);
                        }
                    }
                }
            }

            }

            $rec='';
            $grade_check = 0;
            $comp=$recom;
            $history_cnt++;
        } // while

        $history_cnt=0;
        $today_sales=0;
    
    }
}


function max_item_level_array($mb_id){
    $oreder_result = array_column(ordered_items($mb_id),'it_name');
    if(count($oreder_result) > 0){
        $key = max($oreder_result);
    }else{
        $key = 0;
    }
    return $key;
}

function return_down_manager($mb_id,$cnt=0){
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
				$manager != 'dfine'
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

?>

<?include_once('./bonus_footer.php');?>

<?
if($debug){}else{
    $html = ob_get_contents();
    //ob_end_flush();
    $logfile = G5_PATH.'/data/log/'.$code.'/'.$code.'_'.$bonus_day.'.html';
    fopen($logfile, "w");
    file_put_contents($logfile, ob_get_contents());
}
?>