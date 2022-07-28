<?
include_once('./_common.php');
include_once(G5_THEME_PATH . '/_include/wallet.php');
include_once(G5_THEME_PATH . '/_include/gnb.php');

login_check($member['mb_id']);
$title = '마이닝';

$ordered_items = ordered_items($member['mb_id'], $_GET['item']);
$mining_cnt = count($ordered_items);


// 내 마이닝상품 리스트 기본값 1
$list_cnt  = 1;
if ($_GET['myitem'] == 'all') {
    $list_cnt = $mining_cnt;
}

// 마이닝출금 설정값
$withdrawal_mining = wallet_config('withdrawal_mining');
$fee = $withdrawal_mining['fee'];
$max_limit = $withdrawal_mining['amt_maximum'];
$min_limit = $withdrawal_mining['amt_minimum'];
$day_limit = $withdrawal_mining['day_limit'];

$max_mining_total = $mining_total;


/* 리스트 기본값*/
$mining_history_limit1 = " AND DAY IN (SELECT MAX(DAY) FROM soodang_mining)";
$mining_history_limit2 = " AND DAY IN (SELECT MAX(DAY) FROM soodang_mining) GROUP BY DAY";
$mining_history_limit_text = '전체 내역보기';
$mining_amt_limit = "limit 0,1 ";
$mining_amt_limit_text = '전체 내역보기';

if ($_GET['history_limit'] == 'all') {
    $mining_history_limit1 = " ";
    $mining_history_limit2 = "GROUP BY DAY ORDER BY day desc ";
    $mining_history_limit_text = "최근내역만보기";
}

if ($_GET['amt_limit'] == 'all') {
    $mining_amt_limit = "";
    $mining_amt_limit_text = "최근내역만보기";
}

// 마이닝 내역
// $mining_history_sql = "SELECT * from {$g5['mining']} WHERE mb_id = '{$member['mb_id']}'  {$mining_history_limit} GROUP BY allowance_name ";
$mining_history_sql = "SELECT *
    FROM soodang_mining
    WHERE mb_id = '{$member['mb_id']}' AND allowance_name != 'super_mining' {$mining_history_limit1} UNION
    SELECT NO, DAY,allowance_name,mb_id, SUM(mining) AS mining,currency,rate,rec,rec_adm, DATETIME,HASH,overcharge
    FROM soodang_mining
    WHERE mb_id = '{$member['mb_id']}' AND allowance_name = 'super_mining'  {$mining_history_limit2} 
    ";


$mining_history = sql_query($mining_history_sql);
$mining_history_cnt = sql_num_rows($mining_history);

// 마이닝 출금 내역
$mining_amt_log = sql_query("SELECT * from {$g5['withdrawal']} WHERE mb_id = '{$member['mb_id']}' AND coin = '{$minings[0]}' ");
$mining_amt_cnt = sql_num_rows($mining_amt_log);

// 마이닝 출금 승인 내역 
$mining_amt_auth_log = sql_query("SELECT * from {$g5['withdrawal']} WHERE mb_id = '{$member['mb_id']}' AND coin = '{$minings[0]}' AND status = 1 ");
$mining_amt_auth_cnt = sql_num_rows($mining_amt_auth_log);

//kyc인증
$kyc_cert = $member['kyc_cert'];

function category_badge($val)
{
    if ($val == 'mining') {
        return "<span class='badge b_green'>" . strtoupper($val) . "</span>";
    } else if ($val == 'mega_mining') {
        return "<span class='badge b_orange'>" . strtoupper($val) . "</span>";
    } else if ($val == 'zeta_mining') {
        return "<span class='badge b_pink'>" . strtoupper($val) . "</span>";
    } else if ($val == 'zetaplus_mining') {
        return "<span class='badge b_purple'>" . strtoupper($val) . "</span>";
    } else if ($val == 'super_mining') {
        return "<span class='badge b_blue'>" . strtoupper($val) . "</span>";
    }
}


function overcharge($val,$category){
    global $member;
    $target = $category;
    if($category == 'super_mining'){
        $rate = 1;
    }else{
        $rate = 3;
    }

    if((100*$rate) <= remain_hash($member[$target],$rate ,false)){
        return "<span class='red'>".$val."</sapn>";
    }else{
        return $val;
    }
}
?>

<? include_once(G5_THEME_PATH . '/_include/breadcrumb.php'); ?>
<!-- <link href="<?= G5_THEME_URL ?>/css/scss/page/mining.css" rel="stylesheet"> -->
<style>
    input[type='text'].modal_input {
        background: #ededed;
        margin-top: 10px;
        box-shadow: inset 1px 1px 1px rgb(0 0 0 / 50%);
        border: 0;
        text-align: center;
        width: 50%;
    }

    .time_remained {
        display: block;
        text-align: center
    }

    .processcode {
        color: red;
        display: block;
        text-align: center;
        font-size: 13px;
    }
    .red{
        color:red;
    }
    .result span{text-align:right}
</style>
<main>
    <div id="mining" class="container mining">

        <section id='mymining' class="mt10">
            <h3 class="title">내 마이닝 상품 (<?= $mining_cnt ?>)</h3>
            <form name="myitemform" method="get" action="<?= G5_URL ?>/page.php?id=mining" style="display:contents;">
                <input type="hidden" name="id" value='mining' />
                <input type="hidden" id="myitem" name="myitem" value="<? if ($list_cnt == 1) {
                                                                            echo 'all';
                                                                        } else {
                                                                            echo '';
                                                                        } ?>" />
                <? if ($mining_cnt > 1) { ?><input type="submit" class="btn all_view" value="<? if ($list_cnt == 1) {
                                                                                                echo '전체보기';
                                                                                            } else {
                                                                                                echo '접어두기';
                                                                                            } ?>"></input><? } ?>
            </form>

            <div class="mining_wrap">
                <?
                if ($mining_cnt < 1) {
                    echo "<div class='no_data box_on'>내 보유 상품이 존재하지 않습니다</div>";
                } else {

                    for ($i = 0; $i < $list_cnt; $i++) {
                        $color_num = substr($ordered_items[$i]['it_maker'], 1, 1);
                ?>
                        <div class="product_buy_wrap round r_card r_card_<?= $color_num ?> col-12">
                            <li class="row">
                                <p class="title col-12" style="font-size:14px;">

                                    <? if ($color_num > 0) {
                                        echo $ordered_items[$i]['it_option_subject'] . " -";
                                    } ?>
                                    <?= $ordered_items[$i]['it_name'] ?></span>
                                </p>

                                <p class="num col-12" style="font-size:12px;padding-left:0;padding-right:15px;line-height:12px;">No. <?= $ordered_items[$i]['row']['od_id'] ?></p>
                            </li>
                            <div class="b_line4"></div>
                            <li class="row">
                                <div class="value col-10">
                                    <div class="value1" style="font-size:18px;line-height:42px;">
                                        <?= $ordered_items[$i]['pv'] ?> mh/s
                                    </div>
                                    <div class="date"><?= $ordered_items[$i]['row']['cdate'] ?> ~ <?= expire_date($ordered_items[$i]['row']['cdate']) ?></div>
                                </div>
                                <div class="col-2" style='padding:8px 0px 0px;'>
                                    <span class='mining_ico'><img src='<?= coin_prices('eth', 'icon') ?>' /></span>
                                </div>
                            </li>
                        </div>
                        <?php
                        // echo "<script>slide_color('$color_num')</script>";
                        ?>
                <?php }
                } ?>
            </div>
        </section>

        <div class="b_line5 mt10"></div>

        <section id='mining_withdraw' class='col-12 content-box round mt40'>

            <div class="polding_btn">
                <div class="btn_title"><span class="wallet_title">마이닝 출금</span></div>
                <div class="max_with">[ <?= $max_mining_total ?> <?= $minings[0] ?> 출금가능 ]</div>
                <div class="caret_box"><span class="btn inline"><i class="ri-arrow-down-s-line"></i></span></div>
            </div>

            <div class="polding mt10 hidden">

                <div class="input_address">
                    <label class="sub_title">- 출금주소</label><span class='comment'></span>
                    <input type="text" id="withdrawal-address" class="send_coin b_ghostwhite f_small" placeholder="ETH 출금 주소를 입력해주세요" 
                    value=<? if ($member['withdraw_wallet'] != '0') {echo $member['withdraw_wallet'];}?>>
                </div>

                <div class="input_shift_value">
                    <label class="sub_title">- 출금금액 (<?= $minings[0] ?>)</label>
                    <div style='display:inline-block; float:right;'><button type='button' id='max_value' class='btn inline' value=''>max</button></div>

                    <div class='mt10'>
                        <input type="text" id="sendValue" class="send_coin b_ghostwhite" placeholder="출금 금액을 입력해주세요">
                        <label class='currency-right'><?= $minings[0] ?></label>
                    </div>

                    <div class="row fee hidden" style='width:initial'>
                        <div class="col-5" style="text-align:left">
                            <i class="ri-exchange-fill"></i>
                            <span id="active_amt">0</span>
                        </div>

                        <div class="col-7" style="text-align:right">
                            <label class="fees">- 수수료(<?= $fee ?>%) :</label>
                            <i class="ri-coins-line"></i>
                            <span id="fee_val">0</span>
                        </div>
                    </div>
                </div>

                <div class="b_line5"></div>
                <div class="otp-auth-code-container mt20">
                    <div class="verifyContainerOTP">
                        <label class="sub_title">- 출금 비밀번호</label>
                        <input type="password" id="pin_auth_with" class="b_ghostwhite" name="pin_auth_code" maxlength="6" placeholder="6 자리 핀코드를 입력해주세요">

                    </div>
                </div>

                <div class="send-button-container row">
                    <div class="col-5">
                        <button id="pin_open" class="btn wd yellow form-send-button" >인증</button>
                    </div>
                    <div class="col-7">
                        <button type="button" class="btn wd btn_wd form-send-button" id="withdrawal_btn" data-toggle="modal" data-target="" disabled>출금신청</button>
                    </div>
                </div>
            </div>
        </section>

    </div> <!-- mining end-->

    <section id='mining_history'>

        <div class="history_box content-box mt20">
            <div>
                <h3 class="hist_tit">내 마이닝 내역 <span class='mymining_total'><?= shift_coin($mining_acc) ?> <?= strtoupper($minings[0]) ?></span></h3>

                <? if ($mining_history_cnt < 1) { ?>
                    <ul class="row">
                        <li class="no_data">내 마이닝 내역이 존재하지 않습니다</li>
                    </ul>
                <? } else { ?>
                    <? while ($row = sql_fetch_array($mining_history)) { ?>
                        <ul class="row">
                            <li class="col-3 hist_date"><?= $row['day'] ?></li>
                            <li class="col-5 hist_td"><?= category_badge($row['allowance_name']) ?>
                                <? if ($row['allowance_name'] == 'super_mining') {
                                    echo "<a href='/dialog.php?id=mining_detail&day={$row['day']}' class='btn more_record' style='margin:0' data-day='" . $row['day'] . "'>
                                <i class='ri-menu-add-line'></i></a>";
                                } ?>
                            </li>
                            <li class="col-4 hist_value">
                                <?= overcharge(shift_coin($row['mining']),$row['allowance_name']) ?> <?= strtoupper($row['currency']) ?></li>
                            <li class="col-12 hist_rec">
                                <?
                                if ($row['allowance_name'] != 'super_mining') {
                                    echo $row['rec'];
                                } else {
                                    echo "click more btn";
                                }
                                ?>
                            </li>
                        </ul>
                    <? } ?>

                    <div><button type='button' id="mining_history_more" class="btn wd"><?= $mining_history_limit_text ?></button></div>
  
                <? } ?>
            </div>
                
            <? if ($mining_amt_cnt > 0) { ?>
                <div class="b_line6"></div>
                <div id='mining_amt_log' class='mt20'>

                    <h3 class="hist_tit">마이닝 출금 내역 <span class='mymining_total'> <?= shift_coin($mining_amt) ?> <?= strtoupper($minings[0]) ?></span></h3>
                    <? while ($row = sql_fetch_array($mining_amt_log)) { ?>
                        <ul class='row'>
                            <li class="col-12">
                                <span class="col-8 nopadding"><i class="ri-calendar-check-fill"></i><?= $row['create_dt'] ?></span>
                                <span class="col-4 nopadding text-right amt_total"><?= shift_coin($row['amt_total']) ?> <?= $row['coin'] ?></span>
                            </li>

                            <li class="col-12">
                                <span class="col-8 nopadding amt"><i class="ri-coins-line"></i>수수료 : - <?= shift_coin($row['fee'])  ?> <?= $row['coin'] ?>
                                </span>
                                <span class="col-4 nopadding text-right amt"><i class="ri-refund-2-line"></i><?= shift_coin($row['out_amt']) ?> <?= $row['coin'] ?></span>
                            </li>

                            <li class="col-12 "><span class='hist_bank'><i class="ri-wallet-2-fill"></i><?= $row['addr'] ?></span></li>

                            <li class="col-12 mt10">
                                <span class="col-6 nopadding amt"><i class="ri-survey-line"></i>처리결과</span>
                                <span class="col-6 nopadding text-right result "><? string_shift_code($row['status']) ?></span>
                            </li>
                        </ul>
                        <? } ?>
                    <div><button type='button' id="mining_amt_more" class="btn wd"><?= $mining_amt_limit_text ?></button></div>
                </div>
                <? } ?>

        </div>
    </section>

</main>
<div class="gnb_dim"></div>

<script>
    $(function() {
        $(".top_title h3").html("<span >마이닝</span>")
    });

    $(function() {

        $('.polding_btn').click(function() {
            /* var out_count = Number("<?=$mining_amt_cnt ?>");
            if(out_count < 1){
                dialogModal('KYC 인증', "<strong> 안전한 출금을 위해 최초 1회  KYC 인증을 진행해주세요<br><a href='/page.php?id=profile' class='btn btn-primary'>KYC인증</a></strong>", 'warning');
            } */
            var target = $(this).parent();
            target.find('.polding').slideToggle(300);
            target.toggleClass('open');

            if (target.hasClass('open')) {
                $(this).find('.caret_box i').attr('class', 'ri-arrow-up-s-line');
            } else {
                $(this).find('.caret_box i').attr('class', 'ri-arrow-down-s-line');
            }
        });

        $('#mining_history_more').on('click', function() {
            locationURL('history_limit', 'mining_history');
        });

        $('#mining_amt_more').on('click', function() {
            locationURL('amt_limit', 'mining_amt_log');
        });

        function locationURL(keyword, anchor = '') {
            var url = location.href.split('?')[0];
            var params = new URLSearchParams(location.search);

            if (params.get(keyword) == 'all') {
                params.set(keyword, '0');
            } else {
                params.set(keyword, 'all');
            }
            var queryString = params.toString();

            console.log(url + '?' + queryString);
            window.location.href = url + '?' + queryString + '#' + anchor;
        }

        onlyNumber('pin_auth_with');

        /* 출금*/
        var WITHDRAW_CURENCY = '<?= $minings[0] ?>';
        var COIN_NUMBER_POINT = '<?= COIN_NUMBER_POINT ?>';

        var mb_block = Number("<?= $member['mb_block'] ?>"); // 차단
        var mb_id = '<?= $member['mb_id'] ?>';
        var fee_total = fee_calc = coin_amt = 0;
        var eth_price = '<?= $eth_price ?>';

        // 출금설정
        var nw_with = '<?= $nw_with ?>'; // 출금서비스 가능여부
        var personal_with = '<?=$member['mb_leave_date']?>'; // 별도구분회원 여부

        var fee = (<?= $fee ?> * 0.01);
        var min_limit = '<?= $min_limit ?>';
        var max_limit = '<?= $max_limit ?>';
        var day_limit = '<?= $day_limit ?>';

        // 문자인증
        var time_reamin = false;
        var is_sms_submitted = false;
        var check_pin = false;
        var process_step = false;

        var mb_hp = '<?= $member['mb_hp'] ?>';

        function input_timer(time, where) {
            var time = time;
            var min = '';
            var serc = '';

            var x = setInterval(function() {
                min = parseInt(time / 50);
                sec = time % 60;

                $(where).html(min + "분 " + sec + "초");
                time--;

                if (time < 0) {
                    clearInterval(x);
                    $(where).html("시간초과");
                    time_reamin = false;
                }
            }, 1000)
        }

        function check_auth_mobile(val) {
            $.ajax({
                type: "POST",
                url: "./util/check_auth_sms.php",
                dataType: "json",
                cache: false,
                async: false,
                data: {
                    pin: val,
                },
                success: function(res) {
                    if (res.result == "success") {
                        check_pin = true;
                    } else {
                        check_pin = false;
                    }
                }
            });
        }


        // 최대출금가능금액
        var mb_max_limit = '<?= $max_mining_total ?>';
        // console.log(` min_limit : ${min_limit}\n max_limit:${max_limit}\n day_limit:${day_limit}\n fee: ${fee}`);

        function withdraw_value(num) {
            return Number(num).toFixed(COIN_NUMBER_POINT);
        }

        function withdraw_curency(num) {
            return Number(num) + ' ' + WITHDRAW_CURENCY;
        }

        // 출금금액 변경 
        function input_change() {
            // var inputValue = $('#sendValue').val().replace(/,/g, '');
            var inputValue = $('#sendValue').val();

            if (inputValue > mb_max_limit) {
                dialogModal('출금가능 금액확인', '<strong> 출금가능금액내에서 정수 단위로 입력해주세요 </strong>', 'warning');
            }

            fee_total = withdraw_value(inputValue * fee);
            fee_calc = Number(inputValue) - Number(fee_total);
            coin_amt = withdraw_value(fee_calc);

            // console.log(`fee : ${fee_total}\nfee_calc : ${fee_calc}\n실제출금계산액 :: ${fee_calc}`);

            $('.fee').css('display', 'flex');
            $('#fee_val').text(withdraw_curency(fee_total));
            $('#active_amt').text(withdraw_curency(coin_amt));
        }

        $('#sendValue').change(input_change);

        // 출금가능 맥스
        $('#max_value').on('click', function() {
            $("#sendValue").val(mb_max_limit.toLocaleString('ko-KR'));
            input_change();
        });


        /*핀 입력*/
        $('#pin_open').on('click', function(e) {

            // 회원가입시 핀입력안한경우
            if ("<?= $member['reg_tr_password'] ?>" == "") {
                dialogModal('출금 비밀번호(핀코드) 인증', '<p>출금 비밀번호(핀코드) 등록해주세요.</p>', 'warning');
                $('#modal_return_url').click(function() {
                    location.href = "./page.php?id=profile";
                })
                return;
            }

            if ($('#pin_auth_with').val() == "") {
                dialogModal('출금 비밀번호(핀코드) 인증', '<p>출금 비밀번호(핀코드) 입력해주세요.</p>', 'warning');
                return;
            }

            $.ajax({
                url: './util/pin_number_check_proc.php',
                type: 'POST',
                cache: false,
                async: false,
                data: {
                    "mb_id": mb_id,
                    "pin": $('#pin_auth_with').val()
                },
                dataType: 'json',
                success: function(result) {
                    if (result.response == "OK") {
                        dialogModal('출금 비밀번호(핀코드) 인증', '<p>출금 비밀번호가 인증되었습니다.</p>', 'success');
                        $('#withdrawal_btn').attr('disabled', false);
                        $('#pin_open').attr('disabled', true);
                        $("#pin_auth_with").attr("readonly", true);
                    } else {
                        dialogModal('출금 비밀번호(핀코드) 인증', '<p>출금 비밀번호가 일치 하지 않습니다.</p>', 'failed');
                    }
                },
                error: function(e) {
                    //console.log(e);
                }
            });
        });


        $('#withdrawal_btn').on('click', function() {
            
            var inputVal = $('#sendValue').val().replace(/,/g, '');
            
            // 모바일 등록 여부 확인
            if (mb_hp == '' || mb_hp.length < 10) {
                dialogModal('정보수정', '<strong> 안전한 출금을 위해 인증가능한 모바일 번호를 등록해주세요.</strong>', 'warning');

                $('.closed').on('click', function() {
                    location.href = '/page.php?id=profile';
                })
                return false;
            }
            
            //KYC 인증
            var out_count = Number("<?=$mining_amt_auth_cnt ?>");
            var kyc_cert = Number("<?=$kyc_cert?>");

            if(out_count < 1 && kyc_cert != 1){
                dialogModal('KYC 인증 미등록/미승인 ', "<strong> KYC인증이 미등록 또는 미승인 상태입니다.<br>안전한 출금을 위해 최초 1회 KYC 인증을 진행해주세요<br><a href='/page.php?id=profile' class='btn btn-primary'>KYC인증</a></strong>", 'warning');
                return false;
            }
            

            // 출금주소 입력확인
            if ($('#withdrawal-address').val() == "") {
                dialogModal('출금주소확인', '<strong> 올바른 출금 주소를 입력해주세요.</strong>', 'warning');
                return false;
            }

            // 출금서비스 이용가능 여부 확인
            if (nw_with == 'N') {
                dialogModal('서비스이용제한', '<strong>현재 출금가능한 시간이 아닙니다.</strong>', 'warning');
                return false;
            }

            if(personal_with != ''){
                dialogModal('서비스이용제한', '<strong>관리자에게 연락주세요</strong>', 'warning');
                return false;
            }

            // 금액 입력 없을때 
            if (inputVal == '' || inputVal <= 0) {
                dialogModal('금액 입력 확인', '<strong>출금 금액을 확인해주세요.</strong>', 'warning');
                return false;
            }

            // 최소 금액 확인
            if (min_limit != 0 && inputVal < Number(min_limit)) {
                dialogModal('금액 입력 확인', '<strong> 최소가능금액은 ' + min_limit + WITHDRAW_CURENCY + ' 입니다.</strong>', 'warning');
                return false;
            }

            //최대 금액 확인
            if (max_limit != 0 && inputVal > Number(max_limit)) {
                dialogModal('금액 입력 확인', '<strong> 최대가능금액은 ' + max_limit + WITHDRAW_CURENCY + ' 입니다.</strong>', 'warning');
                return false;
            }

            process_pin_mobile().then(function() {
                $.ajax({
                    type: "POST",
                    url: "./util/withdrawal_coin_proc.php",
                    cache: false,
                    async: false,
                    dataType: "json",
                    data: {
                        mb_id: mb_id,
                        wallet_addr: $('#withdrawal-address').val(),
                        func: 'mining-withdraw',
                        amt: inputVal,
                        fee: fee_total,
                        coin_amt: coin_amt,
                        coin_cost: eth_price,
                        select_coin: WITHDRAW_CURENCY
                    },
                    success: function(res) {
                        if (res.result == "success") {
                            dialogModal('출금신청이 정상적으로 처리되었습니다.', '<p>실제 출금까지 24시간 이상 소요될수있습니다.</p>', 'success');

                            $('.closed').click(function() {
                                location.reload();
                            });
                        } else {
                            dialogModal('출금 신청 실패!', "<p>" + res.sql + "</p>", 'warning');
                        }
                    }
                });
            });

        });



        function process_pin_mobile() {

            return new Promise(
                function(resolve, reject) {
                    dialogModal('본인인증', "<p>" + maskingFunc.phone(mb_hp) + "<br>모바일로 전송된 인증코드 6자리를 입력해주세요<br><input type='text' class='modal_input' id='auth_mobile_pin' name='auth_mobile_pin'></input><span class='time_remained'></span><span class='processcode'></span></p>", 'confirm');

                    if (is_sms_submitted == false) {
                        is_sms_submitted = true;

                        $.ajax({
                            type: "POST",
                            url: "./util/send_auth_sms.php",
                            cache: false,
                            async: false,
                            dataType: "json",
                            data: {
                                mb_id: mb_id,
                            },
                            success: function(res) {
                                if (res.result == "success") {
                                    time_reamin = true;
                                    input_timer(res.time, '.time_remained');

                                    $('#modal_confirm').on('click', function() {

                                        if (!time_reamin) {
                                            is_sms_submitted = false;
                                            alert("시간초과로 다시 시도해주세요");
                                        } else {
                                            var input_pin_val = $("#auth_mobile_pin").val();
                                            check_auth_mobile(input_pin_val);

                                            if (!check_pin) {
                                                $(".processcode").html("인증코드가 일치하지 않습니다.");
                                                return false;
                                            } else {
                                                is_sms_submitted = false;
                                                process_step = true;
                                                resolve();
                                            }

                                        }
                                    });

                                    $('#dialogModal .cancle').on('click', function() {
                                        is_sms_submitted = false;
                                        location.reload();
                                    });

                                }
                            }
                        });

                    } else {
                        alert('잠시 후 다시 시도해주세요.');
                    }
                });
        }

    });
</script>

<? include_once(G5_THEME_PATH . '/_include/tail.php'); ?>