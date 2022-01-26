/*
$(function(){
		 
		//파일올리기
		 var fileTarget = $('.filebox .upload-hidden'); 
		 fileTarget.on('change', function(){ // 값이 변경되면 
			 if(window.FileReader){ // modern browser 
				 var filename = $(this)[0].files[0].name; 
			 } else { // old IE 
				 var filename = $(this).val().split('/').pop().split('\\').pop(); // 파일명만 추출 
			 } // 추출한 파일명 삽입 
			 $(this).siblings('.upload-name').val(filename); 
		 }); 
});

*/
// var debug = '<?=$is_debug?>';
var debug = "";

// 인풋 세자리콤마
$(document).on('keyup','input[inputmode=numeric]',function(event){
	this.value = this.value.replace(/[^0-9]/g,'');   // 입력값이 숫자가 아니면 공백
	this.value = this.value.replace(/,/g,'');          // ,값 공백처리
	this.value = this.value.replace(/\B(?=(\d{3})+(?!\d))/g, ","); // 정규식을 이용해서 3자리 마다 , 추가 	
}); 

// 인풋 숫자 + -
$(document).on('keyup','input[inputmode=price]',function(event){
	this.value = this.value.replace(/[^0-9]/g,'');   // 입력값이 숫자가 아니면 공백
	this.value = this.value.replace(/,/g,'');          // ,값 공백처리
	this.value = this.value.replace(/\B(?=(\d{3})+(?!\d))/g, ","); // 정규식을 이용해서 3자리 마다 , 추가 	
}); 


// 개인정보 마스킹 처리 
let maskingFunc = { 
	
	checkNull : function (str){ 
		if(typeof str == "undefined" || str == null || str == ""){ 
			return true; 
		} else{ 
			return false; 
		} 
	}, 
	
	/* ※ 이메일 마스킹 ex1) 
	원본 데이터 : abcdefg12345@naver.com 변경 데이터 : ab**********@naver.com ex2) 
	원본 데이터 : abcdefg12345@naver.com 변경 데이터 : ab**********@nav****** 
	*/ 
	
	email : function(str){ 
		let originStr = str; 
		let emailStr = originStr.match(/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+)/gi); 
		let strLength; 
		
		if(this.checkNull(originStr) == true || this.checkNull(emailStr) == true){ 
			return originStr; 
		}else{ 
			strLength = emailStr.toString().split('@')[0].length - 3;
			// ex1) abcdefg12345@naver.com => ab**********@naver.com // 
			return originStr.toString().replace(new RegExp('.(?=.{0,' + strLength + '}@)', 'g'), '*'); 	
			// ex2) abcdefg12345@naver.com => ab**********@nav****** 
			return originStr.toString().replace(new RegExp('.(?=.{0,' + strLength + '}@)', 'g'), '*').replace(/.{6}$/, "******");
		} 
	},


	/* ※ 휴대폰 번호 마스킹 ex1) 
	원본 데이터 : 01012345678, 변경 데이터 : 010****5678 ex2) 
	원본 데이터 : 010-1234-5678, 변경 데이터 : 010-****-5678 ex3) 
	원본 데이터 : 0111234567, 변경 데이터 : 011***4567 ex4) 
	원본 데이터 : 011-123-4567, 변경 데이터 : 011-***-4567 
	*/ 
	phone : function(str){ 
		

		let originStr = str;
		let phoneStr;
		let maskingStr;

		if(this.checkNull(originStr) == true){ 
			return originStr;
		} 

		if (originStr.toString().split('-').length != 3) { // 1) -가 없는 경우 

			if(originStr.length < 11){
				phoneStr =  originStr.match(/\d{10}/gi)
			}else{
				phoneStr = 	originStr.match(/\d{11}/gi);
			}


			if(this.checkNull(phoneStr) == true){ 
				return originStr;
			} 
			
			if(originStr.length < 11) { 
				// 1.1) 0110000000 
				maskingStr = originStr.toString().replace(phoneStr, phoneStr.toString().replace(/(\d{3})(\d{3})(\d{4})/gi,'$1***$3'));
			} else { 
				// 1.2) 01000000000 
				maskingStr = originStr.toString().replace(phoneStr, phoneStr.toString().replace(/(\d{3})(\d{4})(\d{4})/gi,'$1****$3'));
			} 

		}else { // 2) -가 있는 경우 
			phoneStr = originStr.match(/\d{2,3}-\d{3,4}-\d{4}/gi);

			if(this.checkNull(phoneStr) == true){ 
				return originStr;
			} 

			if(/-[0-9]{3}-/.test(phoneStr)) { 
				// 2.1) 00-000-0000 
				maskingStr = originStr.toString().replace(phoneStr, phoneStr.toString().replace(/-[0-9]{3}-/g, "-***-"));
			}else if(/-[0-9]{4}-/.test(phoneStr)) { 
				// 2.2) 00-0000-0000 
				maskingStr = originStr.toString().replace(phoneStr, phoneStr.toString().replace(/-[0-9]{4}-/g, "-****-"));
			} 
		} 
		
		return maskingStr;
	}, 

	/* ※ 주민등록 번호 마스킹 (Resident Registration Number, RRN Masking) 
	ex1) 원본 데이터 : 990101-1234567, 변경 데이터 : 990101-1****** ex2) 
	원본 데이터 : 9901011234567, 변경 데이터 : 9901011****** 
	*/ 
	rrn : function(str){ let originStr = str;
			let rrnStr;
			let maskingStr;
			let strLength;
			if(this.checkNull(originStr) == true){ 
				return originStr;
			} 
			rrnStr = originStr.match(/(?:[0-9]{2}(?:0[1-9]|1[0-2])(?:0[1-9]|[1,2][0-9]|3[0,1]))-[1-4]{1}[0-9]{6}\b/gi);
				
				if(this.checkNull(rrnStr) == false){ 
					strLength = rrnStr.toString().split('-').length;
					maskingStr = originStr.toString().replace(rrnStr,rrnStr.toString().replace(/(-?)([1-4]{1})([0-9]{6})\b/gi,"$1$2******"));
				}else { 
					rrnStr = originStr.match(/\d{13}/gi);
				if(this.checkNull(rrnStr) == false){ 
					strLength = rrnStr.toString().split('-').length;
					maskingStr = originStr.toString().replace(rrnStr,rrnStr.toString().replace(/([0-9]{6})$/gi,"******"));
				}else{ 
					return originStr;
				} 

			} return maskingStr;
	}, 
			
	/* ※ 이름 마스킹 
	ex1) 원본 데이터 : 갓댐희, 변경 데이터 : 갓댐* 
	ex2) 원본 데이터 : 하늘에수, 변경 데이터 : 하늘** 
	ex3) 원본 데이터 : 갓댐, 변경 데이터 : 갓* 
	*/ 
	name : function(str){ 
		let originStr = str;
		let maskingStr;
		let strLength;

		if(this.checkNull(originStr) == true){ 
			return originStr;
		} 
		
		strLength = originStr.length;
		if(strLength < 3){ 
			maskingStr = originStr.replace(/(?<=.{1})./gi, "*");
		}else { 
			maskingStr = originStr.replace(/(?<=.{2})./gi, "*");
		} 
	return maskingStr;
	} 
};



// 숫자에 콤마 찍기
function Price(x){
	return String(x).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// 숫자에 콤마 제거
function conv_number(val) {
	number = val.replace(/,/g,'');
	return number;
}

// 숫자만 입력
function onlyNumber(id){
	document.getElementById(id).oninput = function(){
	  // if empty
	  if(!this.value) return;

	  // if non numeric
	  let isNum = this.value[this.value.length - 1].match(/[0-9.]/g);
	  if(!isNum) this.value = this.value.substring(0, this.value.length - 1);
	}
}

// 코인 숫자 8자리
function coin_val(val){
	return Number(val).toFixed(8);
}


// 보너스게이지
function move(bonus_per,main = 0) {
	var total_bonus_point = 0;
	if(bonus_per != undefined){
		var total_bonus_point = bonus_per;
	}

	if(debug){console.log(' total_bonus_point  =  '+total_bonus_point);}

	if(total_bonus_point >= '100'){total_bonus_point = '100'};

	var elem = document.getElementById("total_B_bar");
	var width = 1;
	var id = setInterval(frame, 20);
	function frame() {
		if(width >= 100){
			$('#total_B_bar').addClass('deg100');
			$('#total_B_bar').addClass('active');
			$('.bonus_per').addClass('active')
		}

		if(width >= 75){
			$('#total_B_bar').addClass('deg75');
		}
		if(width >= 50){
			$('#total_B_bar').addClass('deg50');
		}
		if(width >= 25){
			$('#total_B_bar').addClass('deg25');
		}

		if (width >= total_bonus_point) {
			clearInterval(id);

			// 수당초과시 팝업 
			/* if(total_bonus_point > 75 && main == 1){dialogModal('Total Bonus', 'Total Bonus more than 75%', 'warning');} */

		} else {
			width++;
			elem.style.width = width + '%';
		}
	}
}

function go_to_url(target){
	location.href="/page.php?id="+ target;
}

function getCookie(name) {

	var i, x, y, ARRcookies = document.cookie.split(";");
	for (i = 0; i < ARRcookies.length; i++) {

			x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));

			y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);

			x = x.replace(/^\s+|\s+$/g, "");

			if (x == name) {

					return unescape(y);

			}
	}

}

function setCookie(name, value, days) {
	if (days) {
			var date = new Date();

			date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));

			var expires = "; expires=" + date.toGMTString();
	} else {
		var expires = "";
	}
		document.cookie = name + "=" + value + expires + "; path=/";
	}

	
function commonModal(title, htmlBody, bodyHeight){
	$('#commonModal').modal('show');
	$('#commonModal .modal-header .modal-title').html(title);
	$('#commonModal .modal-body').html(htmlBody);
	if(bodyHeight){
		$('#commonModal .modal-body').css('height',bodyHeight+'px');
	} 
	$('#closeModal').focus();
}

function confirmModal(title, htmlBody, bodyHeight){
	$('#confirmModal').modal('show');
	$('#confirmModal .modal-header .modal-title').html(title);
	$('#confirmModal .modal-body').html(htmlBody);
	if(bodyHeight){
		$('#confirmModal .modal-body').css('height',bodyHeight+'px');
	} 
	$('#confirmModal').focus();
}

function dialogModal(title, htmlBody, category,dim = true){
	
	$('#dialogModal').modal('show');
	$('#dialogModal .modal-header .modal-title').html(title);

	if(dim == false){
		var dimhide = '';
	}else{
		var dimhide = "dimHide();";
	}

	if(category == 'success'){
		$('#dialogModal .modal-body').html("<div class=chkimg><img src='"+g5_url+"/img/comform_chk.gif'></div>" + htmlBody);
		$('#dialogModal .modal-footer').html("<button type='button' class='btn wd btn_defualt closed' data-dismiss='modal' id='modal_return_url' onclick='"+dimhide+"'>Close</button>");
	}
	else if(category == 'confirm'){
		$('#dialogModal .modal-body').html("<div class=chkimg><img src='"+g5_url+"/img/notice.png'></div>" + htmlBody);
		$('#dialogModal .modal-footer').html("<button type='button' class='btn btn-secondary cancle' data-dismiss='modal' onclick='"+dimhide+"'>Cancle</button> <button type='button' class='btn btn-primary confirm' id='modal_confirm' data-dismiss='modal' >OK</button>");
		
	}else if(category == 'warning'){
		$('#dialogModal .modal-body').html("<div class=chkimg><img src='"+g5_url+"/img/notice.png'></div>" + htmlBody);
		$('#dialogModal .modal-footer').html("<button type='button' class='btn wd btn_defualt closed' data-dismiss='modal' id='modal_return_url' onclick='"+dimhide+"'>Close</button>");
	}else if(category == 'input_confirm'){
		$('#dialogModal .modal-body').html("<div class=chkimg><img src='"+g5_url+"/img/notice.png'></div>" + htmlBody);
		$('#dialogModal .modal-footer').html("<button type='button' class='btn btn-secondary cancle' data-dismiss='modal' onclick='"+dimhide+"'>Cancle</button> <button type='button' class='btn btn-primary confirm' id='modal_confirm' data-dismiss='modal' >OK</button>");
	}
	else if(category == 'failed'){
		$('#dialogModal .modal-body').html("<div class=chkimg><img src='"+g5_url+"/img/notice_pop.gif'></div>" + htmlBody);
		$('#dialogModal .modal-footer').html("<button type='button' class='btn wd btn_defualt closed' data-dismiss='modal' id='modal_return_back' onclick='"+dimhide+"'>Close</button>");
	}

	$('#dialogModal').focus();
	
}
