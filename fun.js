d = document;
var http;
var alert_mess='';
//
//
function is_null( mixed_var ){
return ( mixed_var === null );
}

function rel(){
	document.getElementById('kap').src = '/kcaptcha/?' + Math.random()*10000000000;
	}

function clear_num(num){
  var rg = new RegExp('[^1234567890\.,]','gi');
  var rg2 = new RegExp('[,]','gi');
  if(num.value.match(rg)){
    num.value = num.value.replace(rg,'');
	}
	num.value = num.value.replace(rg2,'.');
}

function show_input(a){
  if (navigator.userAgent.indexOf('Opera') >= 0) {
    a.className="inputhoveropera"
  }else{
    a.className="inputhover"
  };
}
function hide_input(a){
  if(foc[a.id] == 0){
    a.className="";
  }
}
function focus_input(a){
  foc[a.id]=1;
}
function blur_input(a){
  foc[a.id]=0;
  hide_input(a);
}

function substr( f_string, f_start, f_length ) {
 
    if(f_start < 0) {
        f_start += f_string.length;
    }
 
    if(f_length == undefined) {
        f_length = f_string.length;
    } else if(f_length < 0){
        f_length += f_string.length;
    } else {
        f_length += f_start;
    }
 
    if(f_length < f_start) {
        f_length = f_start;
    }
 
    return f_string.substring(f_start, f_length);
}
function createRequestObject()
{
	var ro;

	if (window.XMLHttpRequest)
		ro = new XMLHttpRequest();
	else
	{
		ro = new ActiveXObject('Msxml2.XMLHTTP');
		if(!ro) 
			ro = new ActiveXObject('Microsoft.XMLHTTP');
	}

	return ro;
}

