$(function() {

	var flagAddress = false; //用于输入地址验证的辅助变量
	var flagNumber = false; //用于验证号码的辅助变量
	var flagName = false //用于验证姓名的辅助变量
	$("#newAddressStr").text(full_address());
	

	if ($("#newAddressStr").text() != '添加送餐地址') {

		if ($("#inputOne").val() != "") {
			flagName = true;

		}
		if ($("#moblePhone").val() != "") {
			flagNumber = true;

		}
		if ($("#inputAddress").val() != "") {
			flagAddress = true;

		}
	}


	if($.cookie("pltf_curRst_info")){
		// 全局变量，用于存放取得的当前餐厅信息cookie数组
		var curRst_info = JSON.parse($.cookie("pltf_curRst_info"));
	}



	//默认备注
	var noteDefault = "无订单备注";


	//下单
	$("#order").click(function(event) {

		event.preventDefault();

		// 判断是否已填送餐信息
		if (flagName && flagAddress && flagNumber) {
			// 往cookie中添加数据

			if ($.cookie("pltf_order_cookie") != null) {

				// json转化数组样式
				var order_list = $.cookie("pltf_order_cookie"); //cookie中的订单信息

				// alert("60");

				var jsonArray = JSON.parse(order_list);

				jsonArray["c_name"] = $("#inputOne").val();
				jsonArray["c_address"] = $("#inputAddress").val();
				jsonArray["c_phone"] = $("#moblePhone").val();
				var noteStr = $("#songcanNote .two").text();
				noteStr = noteStr.slice(0, noteStr.length - 2);
				if (noteStr == noteDefault) {
					jsonArray["note"] = "";

				} else {
					jsonArray["note"] = noteStr;
				}


				jsonArray["deliverTime"] = $("#select").val();

				jsonArray["cTime"] = Math.round(new Date().getTime()/1000);//UNIX时间戳

				order_list = JSON.stringify(jsonArray);
				// $("#postData").val(order_list);
				// alert(order_list);

				$.cookie("pltf_order_cookie", order_list);

				order_submit_judge();//提交订单
			}
		} else {
			$("#songcanAddress").val("");
			alert("送餐地址不能为空！");
		}

	})


	// 判断餐厅状态是否可提交订单
	function order_submit_judge(){

		if(curRst_info.isOpen == "1"){//主观，营业

			if(parseInt(curRst_info.open_status) % 10 == 4){//已过今天最晚营业时间，休息
				alert("该餐厅已打烊");
			}else{
				if(curRst_info.rst_is_bookable == "1"){//可预订
					// alert("可预订");
					$("#myForm3").submit();
				}else{//不可预订
					
					if(curRst_info.open_status == "1" || curRst_info.open_status == "2" || curRst_info.open_status == "3"){//营业时间
                        // alert("不可预订 营业时间");
                        $("#myForm3").submit();
                    }else{//非营业时间
                    	alert("目前非该餐厅营业时间");
                    }
				}
			}
		}else{//主观，暂停营业
			alert("餐厅暂停营业");
		}
	}




	// 点击添加送餐地址切换到输入送餐信息
	$("#songcanAddress").click(function() {
		$("#newAddress header").css("display", "block");
		$("#newAddress").animate({
			left: "0px"
		}, "fast");
		$("#order").css("display", "none");
		$(".defaulted header").css("display", "none");

	})



	// 验证姓名输入是否正确
	$("#inputOne").blur(function() {
		var name = $(this).val();
		if (name == "") {
			alert("姓名不能为空！");
			flagName = false;
			$(this).focus();
		} else {
			flagName = true;
		}
	})



	// 验证地址输入是否正确
	$("#inputAddress").blur(function() {
		var address = $(this).val();
		if (address == "") {
			alert("地址项不能为空！");
			$(this).focus();
			flagAddress = false;

		} else if (address.length <= 4) {
			alert("地址长度不能少于4！");
			$(this).focus();
			flagAddress = false;
		} else {
			flagAddress = true;
		}
	})



	// 验证输入号码的正误
	$("#moblePhone").blur(function() {

		var number = $(this).val();
		if (isNaN(number) || number.length != 11) {
			alert("请输入正确的手机号");
			$(this).val("");
			$(this).focus();
			flagNumber = false;
		} else {
			flagNumber = true;
		}

	})

	// 点击“确定”，新增地址，确定地址
	$("#sure_address").mouseover(function() {
		$(this).css("cursor", "default");
	}).click(function() {
		if (!flagName) {
			alert("请输入姓名！");

		} else if (!flagAddress) {
			alert("请输入送餐地址！");

		} else if (!flagNumber) {
			alert("请输入正确的联系方式！");
		} else {
			// if (flagAddress && numberFlag) {
			var fullAddress= full_address();
		


			$("#newAddress").animate({
				left: "100%"
			}, "fast");
			$("#songcanAddress #newAddressStr").text(fullAddress);
			$(this).parents(".transfor").find("header").css("display", "none");
			$("#order").css("display", "block");
			$(".defaulted header").css("display", "block");
		}
	})


	// 添加备注，切换到选择备注信息
	$("#songcanNote").click(function() {
		$("#newNote header").css("display", "block");
		$("#newNote").animate({
			left: "0px"
		}, "fast");
		$("#order").css("display", "none");
		$(".defaulted header").css("display", "none");
	})



	// 点击备注确认按钮，确定备注信息
	$("#sure_note").mouseover(function() {
		$(this).css("cursor", "default");
	}).click(function() {

		var full_note = $(".noteArea").val();
		if (!full_note) {
			full_note = noteDefault;

		}
		$("#newNote").animate({
			left: "100%"
		}, "fast");
		$("#songcanNote .two").text(full_note + " >");
		$(this).parents(".transfor").find("header").css("display", "none");
		$("#order").css("display", "block");
		$(".defaulted header").css("display", "block");

		// OrderOrNot();
	})


	// 备注

	// 选择备注项
	$(".candidate p").click(function() {
		var text = $(this).text();
		var note = $(".noteArea").val();
		if (note == "") {
			note = text;
		} else {
			note += " " + text;
		}

		$(".noteArea").val(note);
	}).mouseover(function() {
		$(this).css("cursor", "default");
	})



	//点击返回
	$(".fanhui").click(function() {

		$(this).parents(".transfor").animate({
			left: "100%"
		}, "fast");
		$(this).parent().css("display", "none");
		$("#order").css("display", "block");
		$(".defaulted header").css("display", "block");
	}).mouseover(function() {
		$(this).css("cursor", "default");
	})


	//把四个input的内容传到地址栏
	function full_address() {
		var full_address = "";
		var input_value = $(".input");
		for (var i = 0; i < input_value.length; i++) {
			full_address += input_value.eq(i).val() + "   ";
		}
		return full_address;
	}

})