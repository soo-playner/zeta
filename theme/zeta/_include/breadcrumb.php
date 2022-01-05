<?
//매출액
$mysales = $member['mb_deposit_point'];

//보너스/예치금 퍼센트
// $bonus_per = bonus_state($member['mb_id']);
$bonus_per = bonus_per();

//시세 업데이트 시간
$next_rate_time = next_exchange_rate_time();

//내 직추천인
$direct_reffer_sql = "SELECT count(mb_id) as cnt from g5_member WHERE mb_recommend = '{$member['mb_id']}' ";
$direct_reffer_result = sql_fetch($direct_reffer_sql);
$direct_reffer = $direct_reffer_result['cnt'];

//추천산하매출 
$recom_sale =  refferer_habu_sales($member['mb_id']);

//후원산하매출
$brecom_sale =  refferer_habu_sales($member['mb_id'],'b');
// $recom_sale_power = refferer_habu_sales_power($member['mb_id']);
// $recom_sale_weak = ($recom_sale - $recom_sale_power);

// 공지사항
$notice_sql = "select * from g5_write_notice where wr_1 = '1' order by wr_datetime desc limit 0,1";
$notice_sql_query = sql_query($notice_sql);
$notice_result_num = sql_num_rows($notice_sql_query);

function check_value($val){
	if($val == 1){
		$icon = "<i class='ri-checkbox-circle-line icon value_yes'></i>";
	}else{
		$icon = "<i class='ri-close-circle-line icon value_no'></i>";
	}
	return $icon;
}

function percent_value($value){
	if($value < 1){
		$return  = '-';
	}else{
		$return  = sprintf('%0.2f', ($value/100) );
	} 
	return $return;
}

function side_exp($val){
	return "<span class='sideexp'>".$val."</span>";
}


$title = 'Dashboard';
?>


<style>
	.dash_news{position:absolute;z-index:1000;border-top:1px solid #f0f0f0;border-radius:0;padding:10px 20px 20px;background:whitesmoke;box-shadow:0 5px 5px 2px #ccc}
	.dash_news h5 {font-size:16px;font-weight:800;line-height:40px;margin-bottom: 10px;
    border-bottom: 2px solid white;
    padding-bottom: 5px;}
	.dash_news h5 i{font-weight:300;}
	.close_today.btn{margin:0;    background: #111;
    color: white;
    padding: 0 10px;
    margin: 5px 10px;
    font-size: 11px !important;
    border-radius: 5px;
    height: 30px;}

	.notice_open{    border-radius: 50%;
    background: white;
    width: 40px;
    height: 40px;
    margin-right: 10px;
    margin-top: 5px;}
	.notice_open i{font-size:20px;color:red}

	.sideexp{font-size:11px;letter-spacing:-0.5px;}
</style>

<section class='breadcrumb'>
		<!-- 공지사항 -->
		<?if($notice_result_num > 0){ ?>
			
			<div class="col-sm-12 col-12 content-box round dash_news" style='margin-bottom:-10px;'>
				<h5>
					<span class="title" data-i18n='dashboard.공지사항' >Notification</span>
					
					<i class="ri-close-circle-line close_news" style="font-size: 30px;float: right;cursor: pointer;"></i>
					<button class=" f_right btn line_btn close_today" >
						<span data-i18n="dashboard.오늘하루 열지않기"> Close for 24hrs</span>
					</button>
					<!-- <img class="close_news f_right small" src="<?=G5_THEME_URL?>/_images/close_round.gif" alt="공지사항 닫기"> -->
				<!-- 				
					 -->
				</h5>

				<?while( $row = sql_fetch_array($notice_sql_query) ){ ?>
				<div>
					<span><?=$row['wr_content']?></span>
				</div>

				<?}?>
			</div>
		<?}?>

		<div class="user-info">
			<!-- 회원기본정보 -->
			<div class='user-content' style='border-radius:20px;line-height:40px;'>
				<div>
					<span class='userid user_level'><?=$user_icon?></span>
					<h4 class='bold'><?=$member['mb_id']?>님</h4>
					<h4 class='mygrade badge color<?=user_grade($member['mb_id'])?>'><?=user_grade($member['mb_id'])?> STAR</h4>
					<h4 class='mygrade badge' style="margin-left:0;"><?=$user_level?></h4>

					<?if($notice_result_num > 0){ ?>
						<button class="btn text-white b_darkblue_round notice_open f_right" >
						<i class="ri-alarm-warning-line"></i>
						</button>
					<?}?>
				</div>
			</div>
			<style>
				.total_view_wrap .currency{font-size:12px;padding-left:3px;}
			</style>
			<!-- 회원상세정보 -->
			<div class="total_view_wrap">
				<div class="total_view_top">
					<ul class="row top">
						<li class="col-4">
							<dt class="title" >총 추천 보너스</dt>
							<dd class="value" style='font-size:15px;'><?=shift_auto($total_bonus)?><span class='currency'></span></dd>
						</li>
						<li class="col-4">
							<dt class="title" >출금 가능 포인트 </dt>
							<dd class="value" style='font-size:15px;'><?=shift_auto($total_withraw)?><span class='currency'></span></dd>
						</li>
						<li class="col-4">
							<dt class="title">출금 가능 코인</dt>
							<dd class="value" style='font-size:15px;'><?=shift_auto($mining_total,$minings[0])?><span class='currency'><?=$minings[0]?></span></dd>
						</li>
					</ul> 
				</div>
				<div class="total_view_top collapse" id="collapseExample">
					<ul class="row">
						<li class="col-4">
							<dt class="title" >마이 해쉬</dt>
							<dd class="value"><?=$member['mb_rate']?> <?=side_exp($mining_hash[0])?></dd>
						</li>
						<li class="col-4">
							<dt class="title" >총보너스해시</dt>
							<dd class="value"><?=percent_value($member['recom_mining'] + $member['brecom_mining'] + $member['brecom2_mining'] + $member['super_mining'])?> <?=side_exp($mining_hash[0])?></dd>
						</li>
						<li class="col-4">
							<dt class="title" >메가풀 보너스 해쉬</dt>
							<dd class="value"><?=percent_value($member['recom_mining'])?> <?=side_exp($mining_hash[0])?> </dd>
						</li>
					</ul>
					<ul class="row">
						<li class="col-4">
							<dt class="title">제타풀 보너스 해쉬</dt>
							<dd class="value"><?=percent_value($member['brecom_mining'])?> <?=side_exp($mining_hash[0])?></dd>
						</li>
						<li class="col-4">
							<dt class="title">제타2풀 보너스 해쉬</dt>
							<dd class="value"><?=percent_value($member['brecom2_mining'])?> <?=side_exp($mining_hash[0])?></dd>
						</li>
						<li class="col-4">
							<dt class="title" >슈퍼풀 보너스 해쉬</dt>
							<dd class="value"><?=percent_value($member['super_mining'])?> <?=side_exp($mining_hash[0])?> </dd>
						</li>
					</ul>

					<ul class="row">
					<li class="col-4">
							<!-- <dt class="title"><span class='badge'>Mining : <?=Number_format($member['mb_rate'])?> mh/s</span></dt>
							<dd class="value"><?=$mining_total?> <span style='font-size:12px;'><?=$minings[0]?></span></dd> -->
							<dt class="title" >직추천인</dt>
							<dd class="value"><?=$direct_reffer?></dd>
						</li>

						<li class="col-4">
							<dt class="title" >나의구매등급</dt>
							<dd class="value"><?=$member['rank_note']?><?=rank_name($member['rank_note'])?></dd>
						</li>

						<li class="col-4">
							<dt class="title" >승급대상포인트</dt>
							<dd class="value"><?=Number_format($member['recom_sales'])?> </dd>
						</li>

						<!-- <li class="col-4">
							<dt class="title" >수당한계</dt>
							<dd class="value">
								<div class='bonus_state_bg' data-per='<?=$bonus_per?>'>
									<div class='bonus_state_bar' id='total_B_bar'></div>
								</div>
								
								<div class='exp_per'>
									<p class='start'>0%</p>
									<p class='end'>250%</p>
								</div>
							</dd>
						</li> -->

						
					</ul>

					<ul class="row">
						<li class="rank_title">승급조건달성</li>

						<!-- <li class="col-4">
							<dt class="title">본인매출</dt>
							<dd class="value">
								<?=check_value($member['mb_5'])?>
							</dd>
						</li> -->

						<li class="col-6">
							<dt class="title">구매등급</dt>
							<dd class="value">
								<?=check_value($member['mb_5'])?>
							</dd>
						</li>

						<li class="col-6">
							<dt class="title">승급대상포인트</dt>
							<dd class="value">
								<?=check_value($member['mb_7'])?>
							</dd>
						</li>
						
					</ul>
					</div>
				</div>

				<div class="fold_wrap" data-toggle="collapse" href="#collapseExample" aria-expanded="false" aira-controls="collapseExample">
					<div class="collap"><p class='txt'>펼쳐보기</p></div>
					<div class="fold_img_wrap">
						<img class="updown" src="<?=G5_THEME_URL?>/img/arrow_down.png">
					</div>
				</div>

			</div>
		</div>

</section>
<!-- <script src="<?=G5_THEME_URL?>/_common/js/timer.js"></script> -->
<script>
	
	$(function(){

		// 공지사항 - 하단공지로 사용안함
		var notice_open = getCookie('notice');

		if(notice_open == '1'){
			$('.dash_news').css("display","none");
		}else{
			$('.dash_news').css("display","block");
		}

		
		$('.close_news').click(function(){
			$('.dash_news').css("display","none");
			$('.notice_open').css("display","block");
		});

		$('.close_today').click(function(){
			setCookie('notice', '1', 1);
			$('.dash_news').css("display","none");
			$('.notice_open').css("display","block");
		});


		$('.notice_open').click(function(){
			$('.dash_news').css("display","block");
			$(this).css("display","none");
		});

	});
</script>

<!-- 펼쳐보기 -->
<script>
	$(document).ready(function(){
		move(<?=bonus_per()?>,1);
	});

	$(document).ready(function() {
		
		$('.collapse').on('show.bs.collapse', function() {
			var img_src_down = "<?php echo G5_THEME_URL?>/img/arrow_down.png";
			$('.collap p').css('display','block');
			$('.updown').attr('src',img_src_down);
			$('.fold_img_wrap img').css('vertical-align','sub');
		});

		$('.collapse').on('shown.bs.collapse', function() {
			var img_src_up = "<?php echo G5_THEME_URL?>/img/arrow_up.png";
			$('.collap p ').css('display','none');
			$('.updown').attr('src',img_src_up);
			$('.fold_img_wrap img').css('vertical-align','baseline');
		});

		$('.collapse').on('hide.bs.collapse', function() {
			var img_src_down = "<?php echo G5_THEME_URL?>/img/arrow_down.png";
			$('.collap p').css('display','block');
			$('.updown').attr('src',img_src_down);
			$('.fold_img_wrap img').css('vertical-align','sub');
		});

		$('.collapse').on('hidden.bs.collapse', function() {
			var img_src_down = "<?php echo G5_THEME_URL?>/img/arrow_down.png";
			$('.collap').css('display','block');
			$('.updown').attr('src',img_src_down);
			$('.fold_img_wrap img').css('vertical-align','sub');
		});
	});
</script>