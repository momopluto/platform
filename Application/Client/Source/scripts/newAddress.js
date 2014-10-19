$(function(){

	// 点击添加送餐地址
	$("#songcanAddress").click(function(){
		$("#newAddress header").css("display","block");
		$("#newAddress").animate({left:"0px"},"fast");
		$("#order").css("display","none");

	})

	//添加备注
	$("#songcanNote").click(function(){
		$("#newNote header").css("display","block");
		$("#newNote").animate({left:"0px"},"fast");
		$("#order").css("display","none");
	})


  

	// 验证输入号码的正误
	$("#moblePhone").change(function(){
       
		var number=$(this).val();
	
		if(isNaN(number)||number.length!=11){
			alert("请输入正确的手机号");
			$(this).val("");
			$(this).focus();
		}
		
	})



	  // 点击新增地址
	$("#sure_address").mouseover(function(){
		$(this).css("cursor","default");
	}).click(function(){
		var full_address="";
		var input_value=$(".input");
		for(var i=0;i<input_value.length;i++){
			full_address+=input_value.eq(i).val()+"   ";
		}
	
		
		$("#newAddress").animate({left:"100%"},"fast");
		$("#songcanAddress .one").text(full_address);
		$(this).parents(".transfor").find("header").css("display","none");
		$("#order").css("display","block");

	})




	// 点击新增备注
	 
	$("#sure_note").mouseover(function(){
		$(this).css("cursor","default");
	}).click(function(){
		
		var full_note=$(".noteArea").val();
		if(!full_note){
			full_note = "无订单备注";
			// alert(full_note);
		}
		$("#newNote").animate({left:"100%"},"fast");
		$("#songcanNote .two").text(full_note+" >");
		$(this).parents(".transfor").find("header").css("display","none");
		$("#order").css("display","block");
	})


	//点击返回
	$(".fanhui").click(function(){
		
		$(this).parents(".transfor").animate({left:"100%"},"fast");
        $(this).parent().css("display","none");
        $("#order").css("display","block");
	}).mouseover(function(){
		$(this).css("cursor","default");
	})


	// 备注

	// 选择备注项
	$(".candidate p").click(function(){
		var text=$(this).text();
		var note=$(".noteArea").val();
		if(note==""){
			note=text;
		}
		else{
			note+=" "+text;
		}
		
		$(".noteArea").val(note);
	}).mouseover(function(){
		$(this).css("cursor","default");
	})


})