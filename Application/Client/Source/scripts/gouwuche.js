$(function(){
	var spreadPrice=parseFloat($(".shortcComing span").text());
	var p_ItemName="";
	var index=0;
	var $BtnItemPrice=$(".btnPrice .price");
	var clickArray=new Array($BtnItemPrice.length);
	
	for(var i=0;i<clickArray.length;i++){
		clickArray[i]=0;
	}
	// 点击价钱的时候出现数量
	$BtnItemPrice.mouseover(function(){
		$(this).css("cursor","default");
	}).click(function(){
		  index=$BtnItemPrice.index(this);
		
		if(clickArray[index]<1){
			
			clickArray[index]=clickArray[index]+1;
			var button=document.createElement("p");
			
			var p=document.createElement("p");
			p.className="number";
			$(this).before(p);
			
			//p.before(button);
			$(this).parent().find(".btnSub").css("display","block");
			$(this).parent().find(".number").text(clickArray[index]);

			$clone=$("#demoClone").clone(true);//进行一次深克隆
			
			p_ItemName=$(".menuName").eq(index).text();
			
			$clone.find(".ItemName").text(p_ItemName);
			
			$clone.find(".show_count").val(clickArray[index]);

			// var PriceText=$BtnItemPrice.index(this).text();
			 var PriceText=$(this).text();
			$clone.find(".ItemPrice").text(PriceText);
			
    	    $(".listUl").append($clone);
    	    $clone.slideDown();
    	  tatal(clickArray,index);
    	

		}
		else{
			clickArray[index]=clickArray[index]+1;
			
			$(this).parent().find(".number").text(clickArray[index]);
			
			$("div:contains('John')")
			$(".ItemName:contains('"+p_ItemName+"')").siblings().find(".show_count").val(clickArray[index]);
			tatal(clickArray,index);
		}
		

	})
       

       //点击“-”的时候数量的变化（btnSub）
       $(".btnSub").mouseover(function(){
       	$(this).css("cursor","default");
       }).click(function(){
       	  index=$(".btnSub").index(this);
       
       	  var text=$(this).parent().siblings().find(".menuName").text();
       	  if(clickArray[index]>1){
       	  	clickArray[index]--;
      
       $(this).parent().find(".number").text(clickArray[index]);


       var text=$(this).parent().siblings().find(".menuName").text();
		$(".ItemName:contains('"+text+"')").siblings().find(".show_count").val(clickArray[index]);
		
	
        tatal(clickArray,index);
       	  }
       	  else{
       	  	$(this).css("display","none");
       	  	$(this).siblings(".number").remove();
       	  	clickArray[index]=0;
       	  	$(".ItemName:contains('"+text+"')").parent().remove();

       	  	   tatal(clickArray,index);

       	  }

       	  

       })





	//点击购物车的时候显示列表
	$("#gouwucheImg").click(function(){
		if($("#gouwuchelist").is(":visible")){
			$("#gouwuchelist").hide();
		}
		else{
			$("#gouwuchelist").show();
		}
	})


	// 点击减少一分菜
	$(".sub").click(function(){
		var text=$(this).parent().siblings(".ItemName").text();
		
		var ind=$(".menuName").index($(".menuName:contains('"+text+"')"));
		
		if(clickArray[ind]>1){
			clickArray[ind]--;
			$(this).next().val(clickArray[ind]);
			$(".btnPrice").eq(ind).find(".number").text(clickArray[ind]);
		tatal(clickArray,ind);
			
		}
	})
        // 点击增加一份菜
		$(".add").click(function(){
			var text=$(this).parent().siblings(".ItemName").text();
	
		var ind=$(".menuName").index($(".menuName:contains('"+text+"')"));
		
		
			clickArray[ind]++;
			$(this).prev().val(clickArray[ind]);
			$(".btnPrice").eq(ind).find(".number").text(clickArray[ind]);
		tatal(clickArray,ind);
			
			
	})

		//输入框改变数量
		$(".show_count").change(function(){
			var show_index=$(".show_count").index(this);
			
			var clickArrayItem=$(this).val();
			
			 var ind=$(".price").index($(".number").eq(show_index-1).siblings());
			
			clickArray[ind]=clickArrayItem;
			$(".btnPrice").eq(ind).find(".number").text(clickArray[ind]);
			tatal(clickArray,ind);
		})

       //点击删除按钮
		$(".deleteBtn").click(function(){
			$(this).parents(".gouwucheItem").remove();
			var metext=$(this).siblings(".ItemName").text();
			var _index=$(".menuName").index($(".menuName:contains('"+metext+"')"));
			clickArray[_index]=0;
			$(".menuName:contains('"+metext+"')").parent().siblings().find(".number").remove();
			$(".menuName:contains('"+metext+"')").parent().siblings().find(".btnSub").css("display","none");
			if($(".gouwucheItem").length==1){
				$("#labelBeizhu").css("display","none");
				$("#beizhu").css("display","none");

			}
			tatal(clickArray,index);
		})




		function tatal(clickArray,index){

			
			var jsonArray={"total":"","item":new Array(),"note":$("#beizhu").text()};
			//alert($(".account_menu").val());
			
			var number=0;
			var account=0;
			
			
			var $item=$(".gouwucheItem");
			
			for(var i=1;i<$item.length;i++){
				var nameItem=$(".ItemName").eq(i).text();
				
				var priceItem=$(".ItemPrice").eq(i).text();
				priceItem=priceItem.slice(1,priceItem.length);
				var countItem=$(".show_count").eq(i).val();
				var totalItem=priceItem*countItem;
				number+=parseInt(countItem);
				jsonArray["item"][i-1]={'name':nameItem,'price':priceItem,'count':countItem,'total':totalItem};
				account=account+totalItem;
				
			}
			if(account >= spreadPrice){//起送价限制
				$(".jiesuan").css("display","block");
				$(".shortcComing").css("display","none");

				//点击<a>提交数据postData
				$("#formSubmit").click(function(event){
					// event.preventDefault();//禁止标签的默认行
					$("#myForm").submit();
				})
			}
			else{
				$(".jiesuan").css("display","none");
				$(".shortcComing").css("display","block");
				var shortcComing=spreadPrice-account;
				$(".shortcComing span").text(shortcComing);
			}


			jsonArray["total"]=account;
			jsonArray["note"]=$("#beizhu").val();
			account="￥"+account;
			$(".account_menu").text(number);
			$(".tatal_price").text(account);
			//alert(jsonArray["item"][1]["count"]);


			//把数组转成json数组
			var jsonString=JSON.stringify(jsonArray);

			// 把数组传到hidden中
			$("#postData").val(jsonString);
		
		}


	//禁止<a>标签的默认行为
	$("#formSubmit").click(function(event){
		event.preventDefault();
	})

// 	//点击<a>提交数据postData
//     $("#formSubmit").click(function(){
// // **************************************这里还要判断是否满足起送价！！！！！！！！!!!
// 		alert(spreadPrice);
// 		if(parseInt($(".tatal_price").text().substring(1)) >= spreadPrice){
// 			// alert(parseInt($(".tatal_price").text().substring(1)));
// 			$("#myForm").submit()
// 		}
	  	
//     })

})

