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



	// 首先获得餐单的单价，用于营业时间恢复现场
	onTime();
	// 判断当前是否是营业时间



	// 获得最低起送价
	var spreadPrice = parseFloat($(".shortcComing span").text());
	var p_ItemName = "";

	// 全局变量、用于clickArray数组下标
	var index = 0;

	// 全局变量、用于存放取得的cookie数组
	var order_list;

	var btnRemove = true;

/*=======================此test由表单hidden给出，为餐厅id======================*/
	var test = 123456;

	// list页面右边的单价的列表对象
	var $BtnItemPrice = $(".btnPrice .price");

	// clickArray用于存储一种饭菜点了多少份
	var clickArray = new Array($BtnItemPrice.length);

	// 初始化没有点餐
	for (var i = 0; i < clickArray.length; i++) {
		clickArray[i] = 0;
	}


	// tatal(clickArray, index);
	// 对cookie进行操作

	var cookie_name = "pltf_order_cookie";

	// $.cookie(cookie_name,jsonString,{expires:-1});
	if ($.cookie(cookie_name)) {

		// json转化数组样式
		order_list = JSON.parse($.cookie(cookie_name));
		// order_list = $.cookie(cookie_name);

		// 回复每一项的具体点餐数、
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

/*=======================此test由表单hidden给出，为餐厅id======================*/
					if (order_list.hallId == test) {

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


	// 点击价钱的时候出现数量
	$BtnItemPrice.mouseover(function() {
		$(this).css("cursor", "default");
	}).click(function() {
		if ($.cookie(cookie_name)) {
			// if (order_list.hallId == test) {
			if (order_list.hallId == $("#hallId").val()) {
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
				if (btnRemove) {
					var deleteOrNot = confirm("是否清空美食篮子中的所有美食");
					if (deleteOrNot == true) {
						$(".gouwucheItem:gt(0)").remove();

						for (var n = 0; n < clickArray.length; n++) {
							clickArray[n] = 0;
						}
						$.cookie(cookie_name, null);
						order_list.hallId = $("#hallId").val();

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
		var date=new Date();
		var nowHours=date.getHours();
		if ((nowHours >= 8 && nowHours <= 14) || (nowHours >= 14 && nowHours <= 19)) {
			var total_price = parseInt($(".tatal_price").text().slice(1));

			if (total_price >= spreadPrice) {
				$("#form1").submit();

			} else {
				event.preventDefault();

			}

		} else {

			alert("营业时间为：10:00--14:00  16:00--19:00");
		}
		

	})



	function onTime() {


		var date = new Date();
		var nowHours = date.getHours();
		// alert(nowHours);
		if ((nowHours >= 7 && nowHours <= 14) || (nowHours >= 14 && nowHours <= 19)) {

			$(".price").removeAttr("disabled").css({
				"fontSize": "14px",
				"color": "rgb(255,255,255)",
				"background": "rgb(49,153,232)"
			});
			$(".number").removeAttr("disabled");
		} else {

			$(".price").attr("disabled", "disabled").text("休息中").css({
				"fontSize": "14px",
				"color": "#555",
				"background": "rgb(255,255,255)"
			});
			$(".number").attr("disabled", "disabled");

		}

	}



	function tatal(clickArray, index) {


		var jsonArray = {
			"hallId": $("#hallId").val(),
			"total": "",
			"item": new Array(),
			"note": $("#beizhu").text()
		};

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
				'name': nameItem,
				'price': priceItem,
				'count': countItem,
				'total': totalItem
			};
			account = account + totalItem;

		}
		if (account >= spreadPrice) {
			$(".jiesuan").css("display", "block");
			$(".shortcComing").css("display", "none");

			//点击<a>提交数据postData
			$("#formSubmit").click(function(event){
				$("#myForm").submit();
			})
		} else {

			$(".jiesuan").css("display", "none");
			$(".shortcComing").css("display", "block");
			var shortcComing = spreadPrice - account;

			$(".shortcComing span").text(shortcComing);
		}


		jsonArray["total"] = account;
		jsonArray["note"] = $("#beizhu").val();
		account = "￥" + account;
		$(".account_menu").text(number);
		$(".tatal_price").text(account);


		//把数组转成json数组
		var jsonString = JSON.stringify(jsonArray);

		// alert(jsonString);
		// 把数组传到hidden中
		$("#postData").val(jsonString);

	}

	//禁止<a>标签的默认行为
	$("#formSubmit").click(function(event){
		event.preventDefault();
	})

})