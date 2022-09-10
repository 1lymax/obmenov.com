function changeProject(){
  var project = $("select[name=projectid] option:selected");
  if(project.val() == 0){
  	  $('#projectid').focus();
  	  return false;
  }
  var projectcurrency = project.attr('gamecurrency');
  var rubamount		  = parseFloat(project.attr('rubvalue'));
  var currency = $("select[name=purse] option:selected").attr('currency');
  var imgg			  = project.attr('img');
  //alert ($("select[name=LMI_PAYEE_PURSE] option:selected").attr('currency'));
  var ImageSay = (imgg!='')?'<img width="88px" height="31px" src="'+imgg+'">':'Выберите игру:';
  $('#gameLogo').html(ImageSay);
  $('#gamecurrency').text(projectcurrency);
  $('#gamecurrency2').text(projectcurrency);
  var amount = $('#amount').val();
  var crs = (course[currency])?course[currency]:1;

  var gameAmount = amount/(rubamount/crs);
  $('#total').text(Math.floor(total*course['USD']/rubamount*100)/100);
  var colorGet = (gameAmount<1)?'red':'#000000';
  gameAmount = Math.floor(gameAmount*100)/100;
  $('input[name=getamount]').val(gameAmount).css('color',colorGet);
  //alert(gameAmount);
}
//document.forms[0].LMI_PAYEE_PURSE.options.selectedIndex.text
$(document).ready(function(){
 changeProject();
 	$('select[name=projectid]').bind('change click blur keyup',function(){
 	    changeProject();
	 });
 $('#amount').bind('change keyup blur',function(){
      changeProject();
 });
$('select[name=purse]').bind('change blur',function(){
      changeProject();
 });

 $('#getamount').bind('change keyup blur',function(){
     var getamount = $('#getamount').val();
	 var project = $("select[name=projectid] option:selected");
     if(project.val() == 0){
     	$('#projectid').focus();
     	return false;
     }

     var projectcurrency =   project.attr('gamecurrency');
     var rubamount		  =  parseFloat(project.attr('rubvalue'));
     var amount			  =  getamount*rubamount;
     amount     		  =  Math.floor(amount*100)/100;
     $('#amount').val(amount);

	//changeProject();
 });
 var mbStr = false;
 $('#wmgameform').submit(function(){
       if(!mbStr){
         var forma = $('#wmgameform').serialize();
         $('#gameresult').html('<img src="images/ajax-loader.gif" width="16" height="16">  Подождите..').css('color','#000000');
         $.post('gamepay.php',forma+'&ajax=true',function(data){
           if(data['status'] == 1){
             	$('#gameresult').html('Переход на оплату').css('color','#3A8D12');
             	mbStr = true;
             	$('#wmgameform').append('<input name="description" value="Оплата на ник '+data['nick']+' в игру '+$("select[name=projectid] option:selected").text()+'" type="hidden">');
				$('#wmgameform').submit();
             	//return false;
           }else{
              $('#gameresult').html(data['desc']).css('color','red');
              return false;
           }
         },'json');
         return false;
        }
      //return false;

 });


}
);