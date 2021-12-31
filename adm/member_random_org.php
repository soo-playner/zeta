<?php
$sub_menu = "650200";

include_once('./_common.php');
include_once(G5_THEME_PATH.'/_include/wallet.php');
include_once('./inc.member.class.php');
auth_check($auth[$sub_menu], 'r');

$token = get_token();


$g5['title'] = '후원 조직도2';
include_once ('./admin.head.php');

/* 
if($member['avatar_last'] > 0){
    $avatar_id = $member['mb_id'].'_'.sprintf("%02d", $member['avatar_last']);
    $avatar_list_sql = "SELECT * from g5_avatar_log WHERE mb_id = '{$member['mb_id']}' ";
    $avatar_list_result = sql_query($avatar_list_sql);
} */

if($_GET['start_id']){
    $start_id = $_GET['start_id'];
}else{
    if($member['avatar_last'] > 0){
        $start_id = $avatar_id;
    }else{
        $start_id = $config['cf_admin'];
    }
}


/* 
function left_bottom($start_id){
    $left_sql = "select mb_id from g5_member_binary where mb_brecommend='".$start_id."' and mb_brecommend_type='L'";
    $rst = sql_query($left_sql);
    $cnt = sql_num_rows($rst);
    
    if($cnt > 0){
        while($row = sql_fetch_array($rst)){
            echo "cnt :: ".$cnt.' | '.$row['mb_id']."<br>";
            get_left_bottom($row['mb_id']); 
        }
    }else{
        $result = sql_fetch($left_sql);
        return $result['mb_id'];   
    }
}

function right_bottom($start_id){
    $left_sql = "select mb_id from g5_member_binary where mb_brecommend='".$start_id."' and mb_brecommend_type='R'";
    $rst = sql_query($left_sql);
    $cnt = sql_num_rows($rst);
    
    if($cnt > 0){
        while($row = sql_fetch_array($rst)){
            echo "cnt :: ".$cnt.' | '.$row['mb_id']."<br>";
            right_bottom($row['mb_id']); 
        }
    }else{
        $result = sql_fetch($left_sql);
        return $result['mb_id'];   
    }
} */

function get_left_bottom($start_id){

    $sql = "select mb_id from g5_member_binary where mb_brecommend='".$start_id."' and mb_brecommend_type='L'";
    $rst = sql_fetch($sql);
    $temp = $rst['mb_id'];

    if($temp==null || $temp==""){return '';}
    $left_bottom  = $temp;

    while(true){
        $sql2 = "select mb_id from g5_member_binary where mb_brecommend='".$temp."' and mb_brecommend_type='L'";
        $rst2 = sql_fetch($sql2);

        /* echo $rst2['mb_id'];
        echo "<br>"; */

        if($rst2['mb_id']!=null &&  $rst2!=""){
            $temp = $rst2['mb_id'];
            $left_bottom  = $temp;
        }
        else
        {
            break;
        }

    }
    return $left_bottom;
}

function get_right_bottom($start_id){

    $sql = "select mb_id from g5_member_binary where mb_brecommend='".$start_id."' and mb_brecommend_type='R' ";
    $rst = sql_fetch($sql);
    $temp = $rst['mb_id'];
    if($temp==null || $temp==""){return '';}
    $right_bottom  = $temp;
    while(true){
        $sql2 = "select mb_id from g5_member_binary where mb_brecommend='".$temp."' and mb_brecommend_type='R' ";
        $rst2 = sql_fetch($sql2);

        if($rst2['mb_id']!=null && $rst2!=""){
            $temp = $rst2['mb_id'];
            $right_bottom  = $temp;
        }
        else
        {
            break;
        }

    }
    return $right_bottom;
}


$left_bottom = get_left_bottom($start_id);
$right_bottom = get_right_bottom($start_id);

/* ____________________________________________________________________________*/


$start_sql = "SELECT * from g5_member_binary WHERE mb_id = '{$start_id}' ";
$start_result = sql_fetch($start_sql);

$b_recom_arr_l = array();
$b_recom_arr_r = array();

$b_recom_arr_lr[0] = array();
$b_recom_arr_lr[1] = array();
$b_recom_arr_lr[2] = array();
$b_recom_arr_lr[3] = array();


array_brecommend($start_result['mb_id']);

/* echo "<br><br>RESULT :: <br>"; 
print_R($b_recom_arr_lr[0][0]['recom']); */

function array_brecommend($recom_id, $count=0)
{
    global $b_recom_arr_lr,$b_recom_arr_l,$b_recom_arr_r;
    
	$b_recom_sql = "SELECT mb_id from g5_member_binary WHERE mb_brecommend='{$recom_id}' ORDER BY mb_brecommend_type asc ";
	$b_recom_result = sql_query($b_recom_sql);
	$cnt = sql_num_rows($b_recom_result);
    
        $b_recom_sql_l = "SELECT mb_id from g5_member_binary WHERE mb_brecommend='{$recom_id}' and mb_brecommend_type = 'L' ";
        $b_recom_result_l = sql_fetch($b_recom_sql_l);
        if(!$b_recom_result_l['mb_id']){
            $b_recom_result_l['mb_id'] = '';
        }
        $b_recom_arr_l[$count]['recom']= $b_recom_result_l['mb_id'];
        $b_recom_arr_l[$count]['nation']= $b_recom_result_l['nation_number'];
        array_push($b_recom_arr_lr[$count], $b_recom_arr_l[$count]);
        
        
        $b_recom_sql_r = "SELECT mb_id from g5_member_binary WHERE mb_brecommend='{$recom_id}' and mb_brecommend_type = 'R' ";
        $b_recom_result_r = sql_fetch($b_recom_sql_r);
        if(!$b_recom_result_r['mb_id']){
            $b_recom_result_r['mb_id'] = '';
        }
        $b_recom_arr_r[$count]['recom']= $b_recom_result_r['mb_id'];
        array_push($b_recom_arr_lr[$count], $b_recom_arr_r[$count]);
        
    $count++;
    if($count < 4){
        while ($row = sql_fetch_array($b_recom_result)) {
            // echo "<br>count: ".$count."  |  ".$row['mb_id'];
            array_brecommend($row['mb_id'], $count);
        }
    }
    
}


function milloin_number($val){
	return Number_format($val/10000);		
}

/* ____________________________________________________________________________*/
?>


	<link href="https://cdn.jsdelivr.net/npm/remixicon@2.3.0/fonts/remixicon.css" rel="stylesheet">
	<!-- <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"> -->
	<!-- <link rel="stylesheet" type="text/css" href="<?=G5_THEME_URL?>/_common/css/level_structure.css"> -->
    <link rel="stylesheet" type="text/css" href="<?=G5_THEME_URL?>/_common/css/binary.css">
	<link rel="stylesheet" type="text/css" href="<?=G5_THEME_URL?>/css/scss/custom.css">
    <script src="/adm/admin.js" type="text/javascript"></script>   

    <style>
	.material-icons{vertical-align:bottom;}
	
    .lvl {border:none;padding:0;}
    .lvl:hover{background:none;color:black;}
    .lvl .box{display:inline-block;padding:5px 10px; border:1px solid #999;min-height:68px;min-width:50px;}
    .lvl .box .name{margin:10px 0 -10px 0; font-weight:600}
    
    .line_con{width: 100%;height: 40px;clear: both;}
    .line_set{float:left}
    .line_top{float: left;height: 20px;margin: 0 auto;box-sizing: border-box;border-right: 2px solid #aaa;}
    .line_under{height: 20px;margin: 0 auto;border-left: 2px solid #aaa;border-right: 2px solid #aaa;border-top: 2px solid #aaa;box-sizing: border-box;clear: both;}

    .w50{width:50%;}
    .w25{width:25%;}
    .w12{width:12.5%;}

    .binary_wrap{margin:30px; border: 1px solid #999;padding:20px; }
    .binary_wrap .label{display: inline-block; margin-right:20px;}
    .binary_wrap #binary_seach{height:32px;padding-left:10px;}
    .col-8{width:200px;display: inline-block;}
    .col-4{width:100px;display: inline-block;}

    .random_leg{float:right;width:100px;background:crimson;border:0;outline:0;color:white;height:34px;margin:10px;}

	.lvl-icon {
		width: 34px;
		height: 34px;
		margin-right: 5px;
		display: inline-block;
		line-height:30px;
		margin-bottom:10px;
	}
	.userid{
		font-weight:600;
		color:#2b3a6d;
		font-family: Arial, Helvetica, sans-serif;
		font-size:18px;
	}
    </style>
	

		<section class="v_center binary_wrap">
			<!-- 아이디검색 -->
			<div class="btn_input_wrap">

				<form id="sForm" name="sForm" method="post" >
               
				<div class='row'>
                    <div class='label'>회원검색</div>
					<div class='col-8'><input type="text" placeholder="Member Search" name="binary_seach" id="binary_seach" style='font-size:16px;' data-i18n='[placeholder]binary.회원찾기'/></div>
                    <div class='col-4'><button type="button" class="btn wd blue search-button"  id="search_btn"><span data-i18n='binary.검색'>Search</span></button></div>						
                    <!-- <button type='button' id="random_leg_btn" class='random_leg'> 레그랜덤생성</button> -->
                </div>
				</form>

				<div class="search_container">
					<div class="search_result" id="search_result" style='overflow:scroll'></div>
					<div class="result_btn">Close</div>
                </div>

               
            </div>
            
            
        </section>
        
        <section>
        <div class="bin_top" style='margin:15px 0;'><h3> Member Binary Struture </h3></div>
				<div class="tree-container">
					<div class="tree">

                        <!--1단계-->
						<div class="lvl1"> 
							<div class="lvl" id="1">


								<div class="box" id="1" >
									<!-- 템플릿 -->
									<span class="lvl-icon" style='margin-right:0'><?=user_icon($start_result['mb_id'],'icon')?></span>
									<div class='userid'><?echo $start_result['mb_id']?></div>
									<!-- <div class='grade_package'>
										<span class='badge grade color<?=user_grade($start_result['mb_id'])?>'><?=user_grade($start_result['mb_id'])?> S</span>
										<span class='direct_cnt badge'><i class='ri-user-star-line'></i> <?=$member_info[1]['direct_cnt']?></span>
										<span class='badge pv'>
										<?=milloin_number($member_info[1]['mb_rate'])?>
										</span>
									</div> -->
								</div>

								
							</div>
						</div>
						<div class="line_1">
							<div class="line1-1"></div>
							<div class="line2"></div>
						</div>



						<div class="lvl2"> <!--2단계-->
						<?for($i=0; $i<2;$i++){
                            $tree = 0;
							if($b_recom_arr_lr[$tree][$i]['recom']){?>
                                <div class="lvl" id="<?=$i?>" data-tree='<?=$tree?>' data-name='<?=$b_recom_arr_lr[$tree][$i]['recom']?>'>
									<!-- 템플릿 -->
									<div class="box" id="1" >
									<span class="lvl-icon" style='margin-right:0'><?=user_icon($b_recom_arr_lr[$tree][$i]['recom'],'icon')?></span>
									<div class='userid'><?=$b_recom_arr_lr[$tree][$i]['recom']?></div>
									</div>
                                </div>
                            <?}else{?>
                                <div class="lvl" id="<?echo $i ;?>" >
								<div class='box'>
                                -
                                </div>
                                </div>
                            <?}
						}?>
						</div>
						<!--line-->
						<div class="line_con">
							<div class="line_set w50">
                                <div class="line_top w50"></div>
								<div class="line_under w50"></div>
							</div>
							<div class="line_set w50">
								<div class="line_top w50"></div>
								<div class="line_under w50"></div>
                            </div>
						</div>


						<div class="lvl3"> <!--3단계-->
						<?for($i=0; $i<4;$i++){
							$tree = 1;
							if($b_recom_arr_lr[$tree][$i]['recom']){?>
                                <div class="lvl" id="<?=$i?>" data-tree='<?=$tree?>' data-name='<?=$b_recom_arr_lr[$tree][$i]['recom']?>'>
								<div class="box" id="1" >
									<span class="lvl-icon" style='margin-right:0'><?=user_icon($b_recom_arr_lr[$tree][$i]['recom'],'icon')?></span>
									<div class='userid'><?=$b_recom_arr_lr[$tree][$i]['recom']?></div>
									</div>
                                </div>
                            <?}else{?>
                                <div class="lvl" id="<?echo $i ;?>" > <div class='box'>
                                -
                                </div></div>
                            <?}
						}?>
                        </div>
						<!--line-->
						<div class="line_con">
							<div class="line_set w25">
                                <div class="line_top w50"></div>
								<div class="line_under w50"></div>
							</div>
							<div class="line_set w25">
								<div class="line_top w50"></div>
								<div class="line_under w50"></div>
                            </div>
                            <div class="line_set w25">
                                <div class="line_top w50"></div>
								<div class="line_under w50"></div>
							</div>
							<div class="line_set w25">
								<div class="line_top w50"></div>
								<div class="line_under w50"></div>
                            </div>
						</div>


                        <div class="lvl4"> <!--4단계-->
                            <?for($i=0; $i<8;$i++){
                                $tree = 2;
                                if($b_recom_arr_lr[$tree][$i]['recom']){?>
                                    <div class="lvl" id="<?=$i?>" data-tree='<?=$tree?>' data-name='<?=$b_recom_arr_lr[$tree][$i]['recom']?>'>
										<div class="box" id="1" >
										<span class="lvl-icon" style='margin-right:0'><?=user_icon($b_recom_arr_lr[$tree][$i]['recom'],'icon')?></span>
										<div class='userid'><?=$b_recom_arr_lr[$tree][$i]['recom']?></div>
										</div>
                                    </div>
                                <?}else{?>
                                    <div class="lvl" id="<?echo $i ;?>" > <div class='box'>
                                    -
                                    </div></div>
                                <?}
                            }?>
                        </div>
                        <!--line-->
						<div class="line_con">
							<div class="line_set w12">
                                <div class="line_top w50"></div>
								<div class="line_under w50"></div>
							</div>
							<div class="line_set w12">
								<div class="line_top w50"></div>
								<div class="line_under w50"></div>
                            </div>
                            <div class="line_set w12">
                                <div class="line_top w50"></div>
								<div class="line_under w50"></div>
							</div>
							<div class="line_set w12">
								<div class="line_top w50"></div>
								<div class="line_under w50"></div>
                            </div>

                            <div class="line_set w12">
                                <div class="line_top w50"></div>
								<div class="line_under w50"></div>
							</div>
							<div class="line_set w12">
								<div class="line_top w50"></div>
								<div class="line_under w50"></div>
                            </div>
                            <div class="line_set w12">
                                <div class="line_top w50"></div>
								<div class="line_under w50"></div>
							</div>
							<div class="line_set w12">
								<div class="line_top w50"></div>
								<div class="line_under w50"></div>
                            </div>
                        </div>
                        
                        <div class="lvl4"> <!--5단계-->
                            <?for($i=0; $i<16;$i++){
                                $tree = 3;
                                if($b_recom_arr_lr[$tree][$i]['recom']){?>
                                    <div class="lvl" id="<?=$i?>" data-tree='<?=$tree?>' data-name='<?=$b_recom_arr_lr[$tree][$i]['recom']?>'>
										<div class="box" id="1" >
										<span class="lvl-icon" style='margin-right:0'><?=user_icon($b_recom_arr_lr[$tree][$i]['recom'],'icon')?></span>
										<div class='userid'><?=$b_recom_arr_lr[$tree][$i]['recom']?></div>
										</div>
                                    </div>
                                <?}else{?>
                                    <div class="lvl" id="<?echo $i ;?>" > <div class='box'>
                                    -
                                    </div></div>
                                <?}
                            }?>
                        </div>

					</div>

					<div class="page-scroll">
						<span id="left_top" data-i18n='binary.왼쪽 맨 아래로'>Left bottom</span>
						<span id="go_top" data-i18n='binary.맨 위로 가기'>Back to top</span>
						<span id="go_up_one" data-i18n='binary.한 단계 위로 가기'>One level up</span>
						<span id="right_top" data-i18n='binary.오른쪽 맨 아래로'>Right bottom</span>
					</div>
				</div>
					
				

		</section>
		<div class="dim"></div>

		<!-- SELECT TEMPLATE -->
		<select style="display:none;" id="dup" >
			<option value=""></option>
		</select>


	<script>
	var b_recom_arr = JSON.parse('<? echo json_encode($b_recom_arr);?>');
	var $div = $('<div>');
	var data1 = {};

	$(function() {
		
		// 리스트 호출 로그인멤버기준
		$( ".lvl-open" ).each(function( index ) {
			var upperId = Math.floor($(this).attr("id")/2);
			var id = $(this).attr("id");
			var mem_id = "<?=$member['mb_id']?>";

			console.log("upperId : " +  upperId + " | mem : "+ mem_id + " | " + b_recom_arr[upperId]);
			//console.log("success : "+ b_recom_arr[upperId]);
			if(b_recom_arr[upperId]){
				$.ajax({
					url: g5_url+'/util/binary_tree_mem.php',
					type: 'POST',
					async: false,
					data: {
						mb_id: mem_id
					},
					dataType: 'json',
					success: function(result) {

						console.log("success" +result);

						$div.empty();
						$.each(result, function( index, obj ) {
							var opt = $('#dup > option').clone();
							opt.attr('value', obj.mb_id);
							opt.html(obj.mb_id);
							$div.append(opt);
						});
						$('#'+id+'.lvl-open').find('select').append($div.html());
					}
				});
			}
		});


		// 후원인 추가 등록 버튼
		$('.addMem').click(function(){
			//console.log('후원인등록');

			var no = $(this).parent().attr('id');
			var upperId = Math.floor(no/2);

			if(!b_recom_arr[upperId]){ // 상위 회원이 없을때
				commonModal('Error',"Can not place this position.",80);
				return;
			}


			if(!$(this).siblings('select').val()){
				commonModal('Error',"Select Member",80);
				return;
			}

			var set_type = "";
			if(no%2 == 0){ // 나머지가 0이면 좌측 노드
				set_type = "L";
			}else{
				set_type = "R";
			}
			 //console.log(set_type);
			 //console.log($(this).siblings('select').val());
			data1 = {
				"set_id": b_recom_arr[upperId],
				"set_type": set_type,
				"recommend_id": $(this).siblings('select').val()
			};
			$('#confirmModal').modal('show');
		});


		// 후원인 추가 등록 확인 > 저장
		$('#confirmModal #btnSave').on('click',function(e){
			$.ajax({
				url: g5_url+'/util/binary_tree_add.php',
				type: 'POST',
				async: false,
				data: data1,
				dataType: 'json',
				success: function(result) {
					//console.log(result);
					location.reload();
				},
				error: function(e){
					console.log(e);
				}
			});
		});

		//상단 나열이름 클릭
		$('.leg-name').click(function(){
			var move_id = $(this).attr("name");
			if(move_id){
				location.replace(g5_url + "/page.php?id=binary&start_id="+move_id);
			}
		});

		//회원카드 클릭
		$('.lvl').click(function(){
            var name = $(this).data('name');
            if(name){
                location.replace("/adm/member_random_org.php?id=binary&start_id="+name);
            }
		});


		//회원검색 SET
		$('button.search-button').click(function(){
			if($("#binary_seach").val() == ""){
				alert('Please enter a keyword.');
				$("#binary_seach").focus();
			}else{
				$.post(g5_url + "/util/ajax_get_tree_member_binary.php", $("#sForm").serialize(),function(data){
					// dimShow();
					$('.search_container').addClass("active");
					$("#search_result").html(data);
				});
			}

		});


		$('.result_btn').click(function(){
			$('.search_container').removeClass('active');
			// dimHide();
		});

		/*
			$('#binary_seach').on('keydown',function(e){
				if(e.which == 13) {
					e.preventDefault();
					$('#search_btn').trigger('click');
				}
			});
		*/


		// 하단 4단계 버튼


		$("#left_top").click(function(){
			//var left_bottom = $('.8').val();
			var left_bottom =  "<?=$left_bottom?>";
			if(left_bottom!=null && left_bottom!=""){
				location.replace("/adm/member_random_org.php?id=binary&start_id="+left_bottom);
			}
			else
				//alert("Can't move left bottom");
				commonModal('Error',"Can't move left bottom.",80);
		});

		$("#go_top").click(function(){
			location.replace("/adm/member_random_org.php?id=binary&start_id=<?=$config['cf_admin']?>");
		});

		$("#go_up_one").click(function(){

			var id = "<?=$start_id?>";
			//console.log(id);
			$.ajax({
				type: "POST",
				url: g5_url + "/util/binary_random_tree_uptree.php",
				cache: false,
				async: false,
				dataType: "json",
				data:  {
					start_id : id
				},
				success: function(data) {
						//alert(data.result);
						if(data.result!="")
							location.replace("/adm/member_random_org.php?id=binary&start_id="+data.result);
						else
							//alert("Now member is Top");
							commonModal('Notice',"Now member is Top",80);
				}
			});
		});

		$("#right_top").click(function(){
			var right_bottom = "<?=$right_bottom?>";
			if(right_bottom!=null && right_bottom!=""){
				location.replace("/adm/member_random_org.php?id=binary&start_id="+right_bottom);
			}
			else
				//alert("Can't move left bottom");
				commonModal('Error',"Can't move left bottom.",80);
		});

		$('.mem_btn').on('click',function(){
			var target = $(this).data('mem');
			go_member(target);
        });
        
        $('#random_leg_btn').on('click',function(){
            var result = confirm('현재레그를 초기화하고 랜덤으로 재생성하시겟습니까');
            if(result){
                location.href='/adm/adm.random_leg.php';
            }
        });

	});

	function go_member(go_id){
		//location.replace(g5_url + "/page.php?id=binary&start_id="+data.result);
		location.replace("/adm/member_random_org.php?id=binary&start_id="+go_id);
    }
    
</script>


