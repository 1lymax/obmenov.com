foc=Array();
foc['SumIn']=0;
foc['SumOut']=0;
var alert_mess='';
d.$ = function ( obj_id ) {
	var obj;
    if( document.all )
    {
        obj = document.all[obj_id];
    }
    else if( document.getElementById )
    {
        obj = document.getElementById(obj_id);
    }
    else if( document.getElementsByName )
    {
        obj = document.getElementsByName(obj_id);
    }
    return obj;
}

function CheckInOutSumm (num){
 clear_num(num);
 if(CheckCurr()==1) {
 course=money[d.$("moneyIn").value][d.$("moneyOut").value];
  
  if (num.id=='SumIn')  {d.$("SumOut").value=round2(CheckSumm(num,course,d.$("moneyIn").value));}
  if (num.id=='SumOut')  {d.$("SumIn").value=round2(CheckSumm(num,course,d.$("moneyOut").value));}
  if (d.$("moneyIn").value == 'WMU' || d.$("moneyIn").value == 'WMZ' || d.$("moneyIn").value == 'WMR' || d.$("moneyIn").value == 'WME')
	 {ShowRow('needtohave',needtohave);
	 d.$("needIn").innerHTML = Math.round((d.$('SumIn').value*1.008+0.01)*100)/100;}
  else {ShowRow('needtohave',!needtohave);}
	
  if (isNaN(d.$("SumIn").value) || isNaN(d.$("SumOut").value) ) 
  {d.$("SumIn").value=0;d.$("SumOut").value=0;
  ShowRow('mess',mess);
  //ShowRow('bank',!bank);  
  ShowRow('needtohave',!needtohave);
  ShowRow('divprcourse',!divprcourse);
  }
  else {
  ShowRow('mess',!mess);
  //ShowRow('needtohave',needtohave);
  //ShowRow('bank',bank);
  ShowRow('divprcourse',divprcourse);
  }

 }
  
  else{
	if (num.id=='SumOut')  {d.$("SumIn").value=0;}
  	if (num.id=='SumIn')  {d.$("SumOut").value=0;}
  }
 
 
 
printcourse();
 
 }


function CheckCurr ()
{
	//d.$("bank").innerHTML='';
	ii=d.$("moneyIn").value;
	oo=d.$("moneyOut").value;
	alert_mess='';
	if ((ii == oo) ||
             ((ii == "USD") ||(ii == "EUR") ||(ii == "UAH") ||(ii == "P24UAH"||(ii == "P24USD")))&&
             ((oo == "USD")||(oo == "EUR")||(oo == "UAH")||(oo == "P24UAH")||(oo == "P24USD")))
             {alert_mess='Недопустимая операция с одинаковыми или наличными валютами либо направление обмена не поддерживается!';
			 ShowRow('mess',mess);
			 //ShowRow('bank',!bank);
			 ShowRow('needtohave',!needtohave);
			 ShowRow('divprcourse',!divprcourse);
			 return 0;
	
	}
	//if ( oo == "P24UAH" ) {d.$("bank").innerHTML="Подразумевается перевод средств на карточный счет любого коммерческого банка Украины. Если счет открыт в Приватбанке, зачисление в тот же день, когда оплачена заявка. В остальных случаях - до 3-х дней.";}
	//if ( oo == "P24USD" ) {d.$("bank").innerHTML="Данный способ вывода подразумевает перевод средств на Ваш карточный счет, открытый в системе Приват24, зачисление происходит в тот же день (исключение - праздники и выходные), когда оформлена и оплачена заявка.";}
	//if ( ii == "P24UAH" ) {d.$("bank").innerHTML=".";}
	//ShowRow('bank',bank);
	ShowRow('mess',!mess);
    ShowRow('needtohave',needtohave);
	ShowRow('divprcourse',divprcourse);
	return 1;
 
}
 
 function round2(val){
         return Math.round((val+0.0000001)*100)/100;
 }
function round3(val){
         return Math.round((val+0.0000001)*1000)/1000;
 }
 
function ShowRow(id, show) {
  if(d.$(id) != null) {
    if(show) d.$(id).style.display = '';
    else d.$(id).style.display = 'none';
  }
  else alert("id == null as ShowRow parameter");
}

function start(){
	ShowRow('mess',!mess);
    ShowRow('needtohave',needtohave);
	ShowRow('divprcourse',divprcourse);
	
	printcourse();
	CheckInOutSumm(d.$('SumIn'));
}
//end my
