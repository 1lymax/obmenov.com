foc=Array();
d=document;
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
  if (d.$("moneyIn").value == 'WMU' || d.$("moneyIn").value == 'WMZ' || d.$("moneyIn").value == 'WMR' || d.$("moneyIn").value == 'WME'){
	ShowRow('needtohave',1);
	d.$("needIn").innerHTML = Math.round((d.$('SumIn').value*1.008+0.01)*100)/100;}
  else {ShowRow('needtohave',0);}
	
  if (isNaN(d.$("SumIn").value) || isNaN(d.$("SumOut").value) ) {
	d.$("SumIn").value=0;d.$("SumOut").value=0;
	ShowRow('needtohave',0);
	ShowRow('prcourse',1);
	 
  } else {
	ShowRow('prcourse',1);
  }

 }else{
	if (num.id=='SumOut')  {d.$("SumIn").value=0;}
  	if (num.id=='SumIn')  {d.$("SumOut").value=0;}
	ShowRow('prcourse',1);
	d.$("prcourse").innerHTML= "����������� ������ �� ��������������";
 }
 
 
 
printcourse();
 
 }


function CheckCurr ()
{
	d.$("bank").innerHTML='';
	ii=d.$("moneyIn").value;
	oo=d.$("moneyOut").value;
	alert_mess='';
	if ((ii == oo) ||
             ((ii == "USD") ||(ii == "EUR") ||(ii == "UAH") ||(ii == "P24UAH"||(ii == "P24USD")))&&
             ((oo == "USD")||(oo == "EUR")||(oo == "UAH")||(oo == "P24UAH")||(oo == "P24USD")))
             {alert_mess='������������ �������� � ����������� ��� ��������� �������� ���� ����������� ������ �� ��������������!';
			 ShowRow('mess',1);
			 ShowRow('bank',0);
			 ShowRow('needtohave',0);
			 ShowRow('prcourse',0);
			 return 0;
	
	}
	if ( oo == "P24UAH" ) {d.$("bank").innerHTML="��������������� ������� ������� �� ��������� ���� ���������� �������. ���� ����� ������ �� ��������� ��������� ������, �������� ����� ���������� ��� �� ����� � ������� 3-� ����� � �������������� ������. ���������� �� ������ ��������� ������� ����� �� �����, ��� ����������� ������ �� ���������� ����������� (� ���� ������ ����� ���������� ������ �������������).";}
	if ( oo == "P24USD" ) {d.$("bank").innerHTML="��������������� ������� ������� �� ��������� ���� ���������� �������. ���� ����� ������ �� ��������� ��������� ������, �������� ����� ���������� ��� �� ����� � ������� 3-� ����� � �������������� ������. ���������� �� ������ ��������� ������� ����� �� �����, ��� ����������� ������ �� ���������� ����������� (� ���� ������ ����� ���������� ������ �������������).";}
	if ( ii == "MCVUAH" || ii == "MCVUSD" || ii == "MCVRUR" ) {d.$("bank").innerHTML='����������� �������, ���������� �, �������, ������� ������ ���������� ������ ������������ ��������. �� ���������� �������������� ������ �� "����������", ������� �������� ����� ��������� ����������� ����������� ���������� �������� � ���������.';}
	ShowRow('bank',1);
	ShowRow('mess',0);
    ShowRow('needtohave',1);
	ShowRow('prcourse',1);
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


//end my
