$(function() {
	// demo只是测试数据
	// var jsonArraydemo = {
	// 	"rid": "123456",
	//     "total": "300",
	//     "item": [{
	//         "name": "\u5c0f\u83dc00",
	//         "price": "100",
	//         "count": "2",
	//         "total": "200"
	//     }, {
	//         "name": "\u83dc\u540d22",
	//         "price": "10",
	//         "count": "10",
	//         "total": "100"
	//     }],
	// };

	// var jsonStringdemo = JSON.stringify(jsonArraydemo);

	// $("#postData").val(jsonStringdemo);


	//起送价，全局变量
	// parseInt(curRst_info.rst_agent_fee)即为起送价
	var p_ItemName = "";

	// 全局变量、用于clickArray数组下标
	var index = 0;

	var btnRemove = true;//用于清空购物车

	// list页面右边的单价的列表对象
	var $BtnItemPrice = $(".btnPrice .price");

	// clickArray用于存储一种饭菜点了多少份
	var clickArray = new Array($BtnItemPrice.length);

	// 初始化没有点餐
	for (var i = 0; i < clickArray.length; i++) {
		clickArray[i] = 0;
	}


	// 全局变量，用于存放取得的订单信息cookie数组
	var order_list;
	var order_cookie_name = "pltf_order_cookie";//对应的cookie名
	// 全局变量，用于存放取得的当前餐厅信息cookie数组
	var curRst_info;
	var curRst_cookie_name = "pltf_curRst_info";//对应的cookie名

	$(document).ready(function(){
		// 页面加载完毕后，即初始化前端数据**************************************************************
		if($.cookie(curRst_cookie_name)){
			// alert($.cookie(curRst_cookie_name));
			curRst_info = JSON.parse($.cookie(curRst_cookie_name));//初始化curRst_info
			// alert(curRst_info.isOpen);

			if(curRst_info != null){
				alert(curRst_info + "curRst_info不空");

				rst_status_judge();//判断餐厅状态
				order_cookie_judge();//判断是否已有选单cookie
			}
		}
	});
	

	// 餐厅状态判断，根据状态，相应展示
	function rst_status_judge(){

		if(curRst_info.isOpen == "1"){//主观，营业

			if(parseInt(curRst_info.open_status) % 10 == 4){//已过今天最晚营业时间，休息
				alert("已打烊");
				$(".price").attr("disabled", "disabled").text("已打烊").css({
					"fontSize": "14px",
					"color": "#555",
					"background": "rgb(255,255,255)"
				});
				$(".number").attr("disabled", "disabled");
			}else{
				if(curRst_info.rst_is_bookable == "1"){//可预订
					alert("可预订");
					$(".price").removeAttr("disabled").css({
						"fontSize": "14px",
						"color": "rgb(255,255,255)",
						"background": "rgb(49,153,232)"
					});
					$(".number").removeAttr("disabled");
				}else{//不可预订
					
					if(curRst_info.open_status == "1" || curRst_info.open_status == "2" || curRst_info.open_status == "3"){//营业时间
                        alert("不可预订 营业时间");
                        $(".price").removeAttr("disabled").css({
							"fontSize": "14px",
							"color": "rgb(255,255,255)",
							"background": "rgb(49,153,232)"
						});
						$(".number").removeAttr("disabled");
                    }else{//非营业时间
                    	alert("不可预订 非营业时间");
                        $(".price").attr("disabled", "disabled").text("休息中").css({
							"fontSize": "14px",
							"color": "#555",
							"background": "rgb(255,255,255)"
						});
						$(".number").attr("disabled", "disabled");
                    }
				}
			}
		}else{//主观，暂停营业
			alert("暂停营业");
			$(".price").attr("disabled", "disabled").text("暂停营业").css({
				"fontSize": "14px",
				"color": "#555",
				"background": "rgb(255,255,255)"
			});
			$(".number").attr("disabled", "disabled");
		}
	}

	// 判断是否有选单的cookie，初始化order_list
	function order_cookie_judge(){
		// $.cookie(order_cookie_name,jsonString,{expires:-1});
		if ($.cookie(order_cookie_name)) {
			alert($.cookie(order_cookie_name));
			// json转化数组样式
			order_list = JSON.parse($.cookie(order_cookie_name));
			// alert($.cookie(order_cookie_name));

			if(order_list != null){
				alert(order_list + "order_list不空")
				// 回复每一项的具体点餐数
				for (var i = 0; i < order_list.item.length; i++) {
					var name = order_list.item[i].name;
					var price = order_list.item[i].price;

					var count = parseInt(order_list.item[i].count);
					var total = order_list.item[i].total;
					var $menuListItem = $(".menuListItem");
					for (var j = 0; j < $menuListItem.length; j++) {
						var menuName = $menuListItem.eq(j).find(".menuName").text();
						var $BtnPrice = $menuListItem.eq(j).find(".price");
						if (name == menuName) {
							clickArray[j] = count;

							if (order_list.rid == curRst_info.rid) {

								var button = document.createElement("p");

								var p = document.createElement("p");
								p.className = "number";
								$BtnPrice.before(p);


								$BtnPrice.parent().find(".btnSub").css("display", "block");
								$BtnPrice.parent().find(".number").text(clickArray[j]);
							}
							$clone = $("#demoClone").clone(true); //进行一次深克隆
							$clone.find(".ItemName").text(name);
							$clone.find(".show_count").val(clickArray[j]);

							$clone.find(".ItemPrice").text("￥" + price);
							$(".listUl").append($clone);
							$clone.slideDown();
							tatal(clickArray, j);
						}
					}
				}
			}
		}	
	}
	


	// 点击价钱的时候出现数量
	$BtnItemPrice.mouseover(function() {
		$(this).css("cursor", "default");
	}).click(function() {
		if (order_list != null && curRst_info != null) {
			alert(order_list.rid);
			alert(curRst_info.rid);
			if (order_list.rid == curRst_info.rid) {
				index = $BtnItemPrice.index(this);

				if (clickArray[index] < 1) {

					clickArray[index] = clickArray[index] + 1;
					var button = document.createElement("p");

					var p = document.createElement("p");
					p.className = "number";
					$(this).before(p);

					//p.before(button);
					$(this).parent().find(".btnSub").css("display", "block");
					$(this).parent().find(".number").text(clickArray[index]);


					$clone = $("#demoClone").clone(true); //进行一次深克隆

					p_ItemName = $(".menuName").eq(index).text();

					$clone.find(".ItemName").text(p_ItemName);

					$clone.find(".show_count").val(clickArray[index]);

					var PriceText = $(this).text();
					$clone.find(".ItemPrice").text(PriceText);

					$(".listUl").append($clone);
					$clone.slideDown();
					tatal(clickArray, index);
				} else {
					clickArray[index] = clickArray[index] + 1;

					$(this).parent().find(".number").text(clickArray[index]);

					$("div:contains('John')")
					$(".ItemName:contains('" + p_ItemName + "')").siblings().find(".show_count").val(clickArray[index]);
					tatal(clickArray, index);
				}
			} else {
				if (btnRemove && order_list != null) {
					var deleteOrNot = confirm("是否清空美食篮子中的所有美食");
					if (deleteOrNot == true) {
						$(".gouwucheItem:gt(0)").remove();

						for (var n = 0; n < clickArray.length; n++) {
							clickArray[n] = 0;
						}
						$.cookie(order_cookie_name, null);
						order_list.rid = curRst_info.rid;//$("#rid").val();

						tatal(clickArray, index);
						btnRemove = false;
					}
				}

			}
		} else {
			index = $BtnItemPrice.index(this);

			if (clickArray[index] < 1) {

				clickArray[index] = clickArray[index] + 1;
				var button = document.createElement("p");

				var p = document.createElement("p");
				p.className = "number";
				$(this).before(p);

				//p.before(button);
				$(this).parent().find(".btnSub").css("display", "block");
				$(this).parent().find(".number").text(clickArray[index]);


				$clone = $("#demoClone").clone(true); //进行一次深克隆

				p_ItemName = $(".menuName").eq(index).text();

				$clone.find(".ItemName").text(p_ItemName);

				$clone.find(".show_count").val(clickArray[index]);


				var PriceText = $(this).text();
				$clone.find(".ItemPrice").text(PriceText);

				$(".listUl").append($clone);
				$clone.slideDown();
				tatal(clickArray, index);
			} else {
				clickArray[index] = clickArray[index] + 1;

				$(this).parent().find(".number").text(clickArray[index]);

				$("div:contains('John')")
				$(".ItemName:contains('" + p_ItemName + "')").siblings().find(".show_count").val(clickArray[index]);
				tatal(clickArray, index);
			}

		}



	})


	//点击“-”的时候数量的变化（btnSub）
	$(".btnSub").mouseover(function() {
		$(this).css("cursor", "default");
	}).click(function() {
		index = $(".btnSub").index(this);

		var text = $(this).parent().siblings().find(".menuName").text();
		if (clickArray[index] > 1) {
			clickArray[index]--;

			$(this).parent().find(".number").text(clickArray[index]);


			var text = $(this).parent().siblings().find(".menuName").text();
			$(".ItemName:contains('" + text + "')").siblings().find(".show_count").val(clickArray[index]);


			tatal(clickArray, index);
		} else {
			$(this).css("display", "none");
			$(this).siblings(".number").remove();
			clickArray[index] = 0;
			$(".ItemName:contains('" + text + "')").parent().remove();

			tatal(clickArray, index);

		}
	})



	//点击购物车的时候显示列表
	$("#gouwucheImg").click(function() {
		if ($("#gouwuchelist").is(":visible")) {
			$("#gouwuchelist").hide();
		} else {
			$("#gouwuchelist").show();
		}
	})


	// 点击减少一分菜
	$(".sub").click(function() {
		var text = $(this).parent().siblings(".ItemName").text();

		var ind = $(".menuName").index($(".menuName:contains('" + text + "')"));

		if (clickArray[ind] > 1) {
			clickArray[ind]--;
			$(this).next().val(clickArray[ind]);
			$(".btnPrice").eq(ind).find(".number").text(clickArray[ind]);
			tatal(clickArray, ind);

		}
	})
	// 点击增加一份菜
	$(".add").click(function() {
		var text = $(this).parent().siblings(".ItemName").text();

		var ind = $(".menuName").index($(".menuName:contains('" + text + "')"));


		clickArray[ind]++;
		$(this).prev().val(clickArray[ind]);
		$(".btnPrice").eq(ind).find(".number").text(clickArray[ind]);
		tatal(clickArray, ind);


	})

	//输入框改变数量
	$(".show_count").change(function() {
		var show_index = $(".show_count").index(this);

		var clickArrayItem = $(this).val();

		var ind = $(".price").index($(".number").eq(show_index - 1).siblings());

		clickArray[ind] = clickArrayItem;
		$(".btnPrice").eq(ind).find(".number").text(clickArray[ind]);
		tatal(clickArray, ind);
	})

	//点击删除按钮
	$(".deleteBtn").click(function() {
		$(this).parents(".gouwucheItem").remove();
		var metext = $(this).siblings(".ItemName").text();
		var _index = $(".menuName").index($(".menuName:contains('" + metext + "')"));
		clickArray[_index] = 0;
		$(".menuName:contains('" + metext + "')").parent().siblings().find(".number").remove();
		$(".menuName:contains('" + metext + "')").parent().siblings().find(".btnSub").css("display", "none");
		if ($(".gouwucheItem").length == 1) {
			$("#labelBeizhu").css("display", "none");
			$("#beizhu").css("display", "none");

		}
		tatal(clickArray, index);
	})


	//点击去结算列
	$("#formSubmit").click(function(event) {

		var total_price = parseInt($(".tatal_price").text().slice(1));
		if (total_price >= parseInt(curRst_info.rst_agent_fee)) {
			// alert(total_price + " --" + spreadPrice);
			$("#form1").submit();
/*
			if(curRst_info.isOpen == "1"){//主观，营业

				if(parseInt(curRst_info.open_status) % 10 == 4){//已过今天最晚营业时间，休息

				}else{
					if(curRst_info.rst_is_bookable == "1"){//可预订

					}else{//不可预订
						if(curRst_info.open_status == "1" || curRst_info.open_status == "2" || curRst_info.open_status == "3"){//营业时间

	                    }else{//非营业时间

	                    }
					}
				}
			}else{//主观，暂停营业

			}
*/
		} else {
			event.preventDefault();
		}

	})



	function tatal(clickArray, index) {

		var jsonArray = {
			"rid": "",//curRst_info.rid
			"total": "",
			"item": new Array(),
			"note": ""
		};
		// jsonArray["item"] = new Array();
		var number = 0;
		var account = 0;


		var $item = $(".gouwucheItem");

		for (var i = 1; i < $item.length; i++) {
			var nameItem = $(".ItemName").eq(i).text();

			var priceItem = $(".ItemPrice").eq(i).text();

			priceItem = priceItem.slice(1, priceItem.length);
			var countItem = $(".show_count").eq(i).val();
			var totalItem = priceItem * countItem;
			number += parseInt(countItem);
			jsonArray["item"][i - 1] = {
				"name": nameItem,
				"price": priceItem,
				"count": countItem,
				"total": totalItem + ""
			};
			account = account + totalItem;

		}
		if (account >= parseInt(curRst_info.rst_agent_fee)) {
			$(".jiesuan").css("display", "block");
			$(".shortcComing").css("display", "none");

			//点击<a>提交数据postData
			$("#formSubmit").click(function(event){
				$("#myForm").submit();
			})
		} else {

			$(".jiesuan").css("display", "none");
			$(".shortcComing").css("display", "block");
			var shortcComing = parseInt(curRst_info.rst_agent_fee) - account;

			$(".shortcComing span").text(shortcComing);
		}


		jsonArray["total"] = account + "";
		jsonArray["note"] = $("#beizhu").val();
		account = "￥" + account;
		$(".account_menu").text(number);
		$(".tatal_price").text(account);


		//把数组转成json数组
		var jsonString = JSON.stringify(jsonArray);

		// alert(jsonString);
		// 把数组传到hidden中
		$("#postData").val(jsonString);
		//此处提交要改为AJAX

	}

	//禁止<a>标签的默认行为
	$("#formSubmit").click(function(event){
		event.preventDefault();
	})

})