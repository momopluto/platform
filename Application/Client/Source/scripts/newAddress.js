$(function() {

	var flagAddress = false; //用于输入地址验证的辅助变量
	var flagNumber = false; //用于验证号码的辅助变量
	var flagName = false //用于验证姓名的辅助变量

	if($("#newAddressStr").text() != '添加送餐地址'){
		// flagAddress = true;
		// flagNumber = true;
		// flagName = true;
		
		if($("#inputOne").val() != ""){
			flagName = true;
		}
		if($("#moblePhone").val() != ""){
			flagNumber = true;
		}
		if($("#inputAddress").val() != ""){
			flagAddress = true;
		}
	}

	

	//默认备注
	var noteDefault = "无订单备注";


	//下单
	$("#order").click(function(event) {

		// 判断是否已填送餐信息
		if(flagName && flagAddress && flagNumber){
			// 往cookie中添加数据

			var date = new Date();
			var nowHours = date.getHours();

			if ($.cookie("pltf_order_cookie") != null) {

				// json转化数组样式
				var order_list = $.cookie("pltf_order_cookie");//cookie中的订单信息

				alert("42");
				
				var jsonArray = JSON.parse(order_list);

				jsonArray["c_name"] = $("#inputOne").val();
				jsonArray["c_address"] = $("#inputAddress").val();
				jsonArray["c_phone"] = $("#moblePhone").val() + " " + $("#inputLast").val();
				var noteStr = $("#songcanNote .two").text();
				noteStr = noteStr.slice(0, noteStr.length - 2);
				if (noteStr == noteDefault) {
					jsonArray["note"] = noteDefault;

				} else {
					jsonArray["note"] = noteStr;
				}


				jsonArray["deliverTime"] = $("#select").val();
				order_list = JSON.stringify(jsonArray);
				$("#postData").val(order_list);
				

				// 各种验证
				if ((nowHours >= 7 && nowHours <= 14) || (nowHours >= 14 && nowHours <= 19)) {

				if ($("#newAddressStr").text() == '添加送餐地址') {
					// $("#order").css("background", "rgb(141,213,153)").attr("disabled", "disabled");
					alert("请输入送餐地址！");
				
					event.preventDefault();
				} 

				} else {
					alert("营业时间：10:00--14:00  16:00-19:00");
					event.preventDefault();
				}
			}
		}else{
			$("#songcanAddress").val("");
			alert("送餐地址不能为空！");
		}

	})


	


	// 点击添加送餐地址切换到输入送餐信息
	$("#songcanAddress").click(function() {
		$("#newAddress header").css("display", "block");
		$("#newAddress").animate({
			left: "0px"
		}, "fast");
		$("#order").css("display", "none");
		$(".defaulted header").css("display","none");

	})



	// 验证姓名输入是否正确
	$("#inputOne").change(function() {
		var name = $(this).val();
		if (name = "") {
			alert("姓名不能为空！");
			flagName = false;
			$(this).focus();
		} else {
			flagName = true;
		}
	})



	// 验证地址输入是否正确
	$("#inputAddress").change(function() {
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
	$("#moblePhone").change(function() {

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


	// 验证备用电话是否输入正确
	$("#inputLast").change(function() {
		var numberSecond = $(this).val();
		if (isNaN(numberSecond)) {
			alert("请输入正确的联系方式！");
			$(this).focus();
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
			var full_address = "";

			var input_value = $(".input");
			for (var i = 0; i < input_value.length; i++) {
				full_address += input_value.eq(i).val() + "   ";
			}

			$("#newAddress").animate({
				left: "100%"
			}, "fast");
			$("#songcanAddress #newAddressStr").text(full_address);
			$(this).parents(".transfor").find("header").css("display", "none");
			$("#order").css("display", "block");
			$(".defaulted header").css("display","block");
		}
	})


	// 添加备注，切换到选择备注信息
	$("#songcanNote").click(function() {
		$("#newNote header").css("display", "block");
		$("#newNote").animate({
			left: "0px"
		}, "fast");
		$("#order").css("display", "none");
		$(".defaulted header").css("display","none");
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
		$(".defaulted header").css("display","block");

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
		$(".defaulted header").css("display","block");
	}).mouseover(function() {
		$(this).css("cursor", "default");
	})

})