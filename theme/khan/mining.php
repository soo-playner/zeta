
<?
	include_once('./_common.php');
	include_once(G5_THEME_PATH.'/_include/gnb.php');
	include_once(G5_THEME_PATH.'/_include/wallet.php');

	$title = '마이닝';
?>

    <link rel="stylesheet" href="<?=G5_THEME_CSS_URL?>/withdrawal.css">
    <?include_once(G5_THEME_PATH.'/_include/breadcrumb.php');?>
    <main>
        <div id="mining" class="container mining">
            <h3 class="title">내 마이닝 상품</h3>
            <button class="btn all_view b_darkblue_round">전체보기</button>
            <!-- 전체보기 스크립 -->
            <script>
                $(document).ready(function() {
                    $('.all_view').on('click',function() {
                        var this_url = location.href;
                        var str_arr = this_url.split('&');
                        var result_url = this_url.replace(str_arr[1]," ");

                        location.href=result_url;
                    });
                });
            </script>
            <style>
                .product_buy_wrap .title{padding-right:0;}
                .mining_ico{vertical-align: middle;}
                .mining_ico, .mining_ico img{margin-left:5px;height:22px;}
            </style>
            <div style="margin-top: 30px;">
             <?php 
               $ordered_items = ordered_items($member['mb_id'],$_GET['item']);
                if(count($ordered_items) == 0) {?>
                    <div class="no_data box_on">내 보유 상품이 존재하지 않습니다</div>
                <?}
                for($i = 0; $i < count($ordered_items); $i++){	
                    $color_num = str_replace('M','',$ordered_items[$i]['it_name']);
                    $it_supply_subject =$ordered_items[$i]['it_supply_subject'];	
                    $dead_line = strtotime("+$it_supply_subject months");  
				?>
                <div class="content-box3 product_buy_wrap product_buy_wrap_<?=$color_num-1?> col-12">
                    <li class="row">
                        <p class="title col-5"><?=$ordered_items[$i]['it_name']?><span class='mining_ico'><img src='<?=G5_THEME_URL?>/img/mining_ico.png'/></span></p>
                        <p class="num col-7">No. <?=$ordered_items[$i]['row']['od_id']?></p>
                    </li>
                    <div class="b_line4"></div>
                    <li class="row">
                        <div class="value col-12">
                            <div class="value">
                           ETH <?=$ordered_items[$i]['it_option_subject']?> mh / s
                            </div>
                            <div class="date"><?=$ordered_items[$i]['row']['cdate']?> ~ <?=date("Y-m-d",$dead_line)?></div>
                        </div>
                    </li>
                </div>
                <?php
                    echo "<script>slide_color('$color_num')</script>";
                ?>
                <?php } ?>
            </div>
            <div class="history_box content-box5">
                <h3 class="hist_tit">내 마이닝 내역</h3>

                <!-- 마이닝 내역 존재하지 않을 시 -->
                <!-- <ul class="row">
                    <li class="no_data">내 마이닝 내역이 존재하지 않습니다</li>
                </ul> -->

                <ul class="row">
                    <li class="col-3 hist_date">21.02.02</li>
                    <li class="col-5 hist_td"><span>M1</span></li>
                    <li class="col-4 hist_value">0.001ETH</li>
                </ul>
                <ul class="row">
                    <li class="col-3 hist_date">21.02.02</li>
                    <li class="col-5 hist_td"><span>M1</span></li>
                    <li class="col-4 hist_value">0.001ETH</li>
                </ul>
                <ul class="row">
                    <li class="col-3 hist_date">21.02.02</li>
                    <li class="col-5 hist_td"><span>M1</span></li>
                    <li class="col-4 hist_value">0.001ETH</li>
                </ul>
            </div>
        </div>
    </main>
    <div class="gnb_dim"></div>
    <script>
        $(function(){
            $(".top_title h3").html("<span data-i18n=''>마이닝</span>")
        });
    </script>

<? include_once(G5_THEME_PATH.'/_include/tail.php'); ?>
