/*  js of login */

$(document).ready(function(){

	$(".removing-tag").click(function(){
		$(".login-alert").fadeOut();
	});	
//close login-alert

		var pic=$(".validate-pic");

	$(".validate-pic").click(function(){
		var url="/microclass/index.php/Public/vcode.html?"+Math.random();
		pic.attr("src",url);
	});

	$("#change-pic").click(function(){
		var url="/microclass/index.php/Public/vcode.html?"+Math.random();
		pic.attr("src",url);
	});
//change validate pictures

	$(".login-button").click(function(){
		$("input").each(function(){
			var value=$(this).val();
			if(value=="")
			{
				$(this).css("border-color","red");
			}
		});
	});
//warning of none input


});