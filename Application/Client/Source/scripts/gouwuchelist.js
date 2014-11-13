$(function() {

	var curRst_info;// 全局变量，用于存放取得的当前餐厅信息cookie数组
	var curRst_cookie_name = "pltf_curRst_info";//对应的cookie名
	
	var order_cookie_name = "pltf_order_cookie";//订单信息cookie名

	$(document).ready(function(){
		// 页面加载完毕后，即初始化前端数据**************************************************************
		if($.cookie(curRst_cookie_name)){
			// alert($.cookie(curRst_cookie_name));
			curRst_info = JSON.parse($.cookie(curRst_cookie_name));//初始化curRst_info
			// alert(curRst_info.isOpen);

			if(curRst_info != null){
				// alert(curRst_info + "curRst_info不空");
				rst_status_judge();//判断餐厅状态
				// order_cookie_judge();//判断是否已有选单cookie
			}
		}
	});


	// 餐厅状态判断，根据状态，相应展示
	function rst_status_judge(){

		if(curRst_info.isOpen == "1"){//主观，营业

			if(parseInt(curRst_info.open_status) % 10 == 4){//已过今天最晚营业时间，休息
				alert("该餐厅已打烊");
				// $(".add_sub").attr("disabled", "true");
				// $(".show_count").attr("disabled", "true");
				$("#formSubmit2").css("background", "rgb(141,213,153)");
			}else{
				if(curRst_info.rst_is_bookable == "1"){//可预订
					// alert("可预订");
					// $(".add_sub").removeAttr("disabled");
					// $(".show_count").removeAttr("disabled");
					$("#formSubmit2").css("background", "rgb(76,218,100)");
				}else{//不可预订
					
					if(curRst_info.open_status == "1" || curRst_info.open_status == "2" || curRst_info.open_status == "3"){//营业时间
                        // alert("不可预订 营业时间");
                        // $(".add_sub").removeAttr("disabled");
						// $(".show_count").removeAttr("disabled");
						$("#formSubmit2").css("background", "rgb(76,218,100)");
                    }else{//非营业时间
                    	alert("目前非该餐厅营业时间");
                        // $(".add_sub").attr("disabled", "true");
						// $(".show_count").attr("disabled", "true");
						$("#formSubmit2").css("background", "rgb(141,213,153)");
                    }
				}
			}
		}else{//主观，暂停营业
			alert("餐厅暂停营业");
			// $(".add_sub").attr("disabled", "true");
			// $(".show_count").attr("disabled", "true");
			$("#formSubmit2").css("background", "rgb(141,213,153)");
		}
	}

		// 点击减一份菜
		$(".sub").click(function() {

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

			var number = parseInt($(this).siblings(".show_count").val());
			number++;
			$(this).siblings(".show_count").val(number);
			total();
		})


		//点击删除按钮
		$(".deleteBtn").click(function() {

			$(this).parents(".gouwucheItem").remove();
			total();
		})


		function showEmpty(){

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

			$(".totalAllMenu").css("display", "none");
			$("#formSubmit2").css("display", "none");

			if($.cookie(order_cookie_name)){
				// 删除cookie
				$.cookie(order_cookie_name, null, {expires:-1});
			}
		}


		function total() {
			
			var menulist = $(".gouwucheItem");

			// 当购物车为空时显示图片并隐藏”确认美食“列
			if(menulist.length == 0){
				showEmpty();
			}else{
				var number = 0;
				var totalPrice = 0;
				// alert("chelist  134" + curRst_info.rid);
				var jsonArray = {
					"rid": curRst_info.rid,
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
						'count': show_count + "",
						'total': (show_count * ItemPrice) + ""
					};
					number += show_count;
					totalPrice += (show_count * ItemPrice);
				}
				jsonArray["total"] = totalPrice + "";
				$("#account").text(number);
				$("#total").text(totalPrice);
				if (number == 0) {
					$(".totalAllMenu").css("display", "none");
				}
				var jsonString = JSON.stringify(jsonArray);

				// 把数组传到hidden中
				// $("#postData").val(jsonString);
				// alert(jsonString);
				$.cookie("pltf_order_cookie", jsonString); //设置cookie 

				if (totalPrice < parseInt(curRst_info.rst_agent_fee)) {
					var balance = parseInt(curRst_info.rst_agent_fee) - totalPrice;
					$("#formSubmit2").text("还差 " + balance + "元起送").css("background", "rgb(141,213,153)");

				} else {
					$("#formSubmit2").text("确认美食").removeAttr("disabled").css("background", "rgb(76,218,100)");
				}

				$(".empty").fadeOut();
				$("#formSubmit2").css("display", "block");
			}
		}


	$("#formSubmit2").click(function() {
		
		event.preventDefault();

		if(parseInt($("#total").text()) >= parseInt(curRst_info.rst_agent_fee)){

			order_submit_judge();//提交订单
			
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
					$("#myForm2").submit();
				}else{//不可预订
					
					if(curRst_info.open_status == "1" || curRst_info.open_status == "2" || curRst_info.open_status == "3"){//营业时间
                        // alert("不可预订 营业时间");
                        $("#myForm2").submit();
                    }else{//非营业时间
                    	alert("目前非该餐厅营业时间");
                    }
				}
			}
		}else{//主观，暂停营业
			alert("餐厅暂停营业");
		}
	}

})