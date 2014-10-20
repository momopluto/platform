$(function() {

		// demo只是测试数据
		var jsonArraydemo = {
			'hallId': '123456',
		    "total": "300",
		    "item": [{
		        "name": "\u5c0f\u83dc00",
		        "price": "100",
		        "count": "2",
		        "total": "200"
		    }, {
		        "name": "\u83dc\u540d22",
		        "price": "10",
		        "count": "10",
		        "total": "100"
		    }],
		};

		var jsonStringdemo = JSON.stringify(jsonArraydemo);

		$("#postData").val(jsonStringdemo);

		// demo只是测试数据



// *****************************************************************************起送价
		var spreadPrice = 12;


		onTime();



		// 点击减一份菜
		$(".sub").click(function() {
			onTime();

			var number = parseInt($(this).siblings(".show_count").val());
			if (number > 1) {
				number--;
				$(this).siblings(".show_count").val(number);
			} else if (number == 1) { //1份-即删除该菜
				$(this).parents(".gouwucheItem").remove();
			}
			total();
		})


		// 点击加一份菜
		$(".add").click(function() {
			onTime();
			var number = parseInt($(this).siblings(".show_count").val());
			number++;
			$(this).siblings(".show_count").val(number);
			total();
		})


		//点击删除按钮
		$(".deleteBtn").click(function() {
			onTime();
			$(this).parents(".gouwucheItem").remove();
			total();
		})



		function total() {
			
			var menulist = $(".gouwucheItem");
			var number = 0;
			var totalPrice = 0;
			var jsonArray = {
				'hallId': $("#hallId").val(),
				"total": "",
				"item": new Array(),
				"note": ""
			};
			for (var i = 0; i < menulist.length; i++) {
				var listItem = menulist.eq(i);
				var nameItem = listItem.find(".ItemName").text();
				var priceItem = listItem.find(".ItemPrice span").text();
				var ItemPrice = parseFloat(listItem.find("span").text());

				var show_count = parseInt(listItem.find(".show_count").val());
				jsonArray["item"][i] = {
					'name': nameItem,
					'price': priceItem,
					'count': show_count,
					'total': show_count * ItemPrice
				};
				number += show_count;
				totalPrice += (show_count * ItemPrice);
			}
			jsonArray["total"] = totalPrice;
			$("#account").text(number);
			$("#total").text(totalPrice);
			if (number == 0) {
				$(".totalAllMenu").css("display", "none");
			}
			var jsonString = JSON.stringify(jsonArray);

			// 把数组传到hidden中
			// $("#postData").val(jsonString);
			$.cookie("pltf_order_cookie", jsonString); //设置cookie 

			if (totalPrice < spreadPrice) {
				var balance = spreadPrice - totalPrice;
				$("#formSubmit2").text("还差 " + balance + "元起送").css("background", "rgb(141,213,153)");



			} else {
				$("#formSubmit2").text("确认美食").removeAttr("disabled").css("background", "rgb(76,218,100)");
			}



			// 当购物车为空时显示图片并隐藏”确认美食“列
			if (menulist.length == 0) {

				// 获得当前窗口的大小
				$bodyChildHeight = $("body>*");
				var childHeight = 0;
				for (var i = 0; i < $bodyChildHeight.length; i++) {
					childHeight += $bodyChildHeight.height();
				}
				// alert($bodyChildHeight.length);
				var bodyHeight = parseInt($("body").height() - childHeight);
				$("body").height(bodyHeight);
				// alert(bodyHeight);
				// alert($("body").height());
				// alert(document.documentElement.clientHeight);


				$(".empty").height($("body").height());
				$(".empty").fadeIn();
				$("#formSubmit2").css("display", "none");

			} else {
				$(".empty").fadeOut();
				$("#formSubmit2").css("display", "block");
			}

		}


	


	//点击<a>提交数据postData
	// if (parseFloat($("#total").text()) > spreadPrice) {
		$("#formSubmit2").click(function() {
			onTime();
			// 获得当前时间
		var date = new Date();
		var nowHours = date.getHours();

			if (nowHours > 8 && nowHours < 14 || nowHours > 14 && nowHours < 19) {
				if($("#total").text() >= spreadPrice){
					$("#myForm2").submit();
					

				}else{
					event.preventDefault();
				}

			}else{
				alert("营业时间：10:00--14:00   16:00--19:00");
			}


		})
	



	function onTime() {
		// 获得当前时间
		var date = new Date();
		var nowHours = date.getHours();



		// 获得现在是多少点

		var onBusiness = "营业时间:  10:00--14:00   16:00-19:00"

		// 与营业时间进行对比
		if (nowHours > 8 && nowHours < 14 || nowHours > 14 && nowHours < 19) {
			// alert("dskjfd");
			$(".add_sub").removeAttr("disabled");
			$(".show_count").removeAttr("disabled");
			$("#formSubmit2").css("background", "rgb(76,218,100)");
		} else {
			$(".add_sub").attr("disabled", "true");
			$(".show_count").attr("disabled", "true");
			$("#formSubmit2").css("background", "rgb(141,213,153)");
			// alert(onBusiness);

		}

	}

})