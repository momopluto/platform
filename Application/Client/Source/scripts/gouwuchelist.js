$(function(){
	total();



	 // 点击减一份菜
	 $(".sub").click(function(){

	 	var number=parseInt($(this).siblings(".show_count").val());
	 	if(number>1){
	 		number--;
	 		$(this).siblings(".show_count").val(number);
	 	}else if(number == 1){//1份-即删除该菜
	 		$(this).parents(".gouwucheItem").remove();
	 	}
	 	total();
	 })


	 // 点击加一份菜
	 $(".add").click(function(){
	 	var number=parseInt($(this).siblings(".show_count").val());
	 	number++;
	 	$(this).siblings(".show_count").val(number);
	 	total();
	 })


	 //点击删除按钮
	 $(".deleteBtn").click(function(){
	 	$(this).parents(".gouwucheItem").remove();
	 	total();
	 })



    
	function total(){
		var menulist=$(".gouwucheItem");
		var number=0;
		var totalPrice=0;
		var jsonArray={"total":"","item":new Array(),"note":""};
		for(var i=0;i<menulist.length;i++){
			var listItem=menulist.eq(i);
			var nameItem=listItem.find(".ItemName").text();
			var priceItem=listItem.find(".ItemPrice span").text();
			var ItemPrice=parseFloat(listItem.find("span").text());

			var show_count=parseInt(listItem.find(".show_count").val());
			jsonArray["item"][i]={'name':nameItem,'price':priceItem,'count':show_count,'total':show_count*ItemPrice};
			number+=show_count;
			totalPrice+=(show_count*ItemPrice);
		}
		jsonArray["total"]=totalPrice;
		$("#account").text(number);
		$("#total").text(totalPrice);
		if(number==0){
			$(".totalAllMenu").css("display","none");
		}
		var jsonString=JSON.stringify(jsonArray);

			// 把数组传到hidden中
			$("#postData").val(jsonString);

	}


	//点击<a>提交数据postData
    $("#formSubmit2").click(function(){
// **************************************满足起送价才能点击！！！！！！
		// alert('111');
		event.preventDefault();//禁止标签的默认行
		$("#myForm2").submit()
    })

})