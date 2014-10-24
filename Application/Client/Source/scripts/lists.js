$(function() {
	var loginFlag=false;//登录状态的切换
	var date = new Date();

	var nowHours = date.getHours();


	
	if($(".listHall").hasClass("gray")){
		$(".gray").find(".accept").css({"background":"rgb(200,200,200)","width":"80px"}).text("暂不接受订单");
	}


	$(".hallHref").click(function(event) {
			event.preventDefault();
	})


	// 点击展开登录界面
	$("#menu").mouseover(function() {
		$(this).css("cursor", "pointer");

	}).click(function() {
		if(loginFlag){
			
			$(".login").slideUp();
			loginFlag=false;
			
		}
		else{
			loginFlag=true;
			$(".login").slideDown();
			
		}
	
	});



})