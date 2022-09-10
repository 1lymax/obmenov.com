JSON_URL = 'lib/local.js';
first_countries = Array('RU','UA','KZ', 'BY', 'LV'); // Страна (двух буквенный код страны) которая будет первой в списке

function show(node) {
	node.style.display = '';
	return node;
}

function hide(node) {
	node.style.display = 'none';
	return node;
}

function update(node, text) {
	node.appendChild(document.createTextNode(text));
	return node;
}

function clear(node) {
	while (node.hasChildNodes()) {
		node.removeChild(node.firstChild);
	}
	return node;
}

function $(id) { return document.getElementById(id) }

function show$(id) { return show($(id)) }

function hide$(id) { return hide($(id)) }

function update$(id, text) { return update($(id), text) }

function clear$(id) { return clear($(id)) }

function selectCost(cost){
	document.getElementById('s_amount').value = cost/1;
	if(cost)show$('sub');
	else hide$('sub');
}
function updateInstructions(json,data) {
	show$('instructions');
	show(clear$('select_cost'));
	hide$('sub');
	var select_cost = clear$('select_cost');
	var def = document.createElement('option');
	update(def, 'Выберите сумму:').value = '-';
	select_cost.appendChild(def);
	for(var i=0;i<json.length;++i){
		if(json[i].country_name == data.country_name && json[i].name == data.name){
			var opt = document.createElement('option');
			if (json[i].vat == 1) var vat=5/6;
			else var vat = 1;
			update(opt, json[i].price +" "+ json[i].currency +" "+(parseInt(json[i].vat)? '(+НДС)': '(без учета НДС)') + ' -> ' + round2(json[i].usd*courses[$('moneyOut').value]*vat*json[i].profit/100) + ' ' + $('moneyOut').options[$('moneyOut').options.selectedIndex].text).value = i;
			select_cost.appendChild(opt);
			if (json[i].special) show(update(clear$('notes'), json[i].special));
			else hide(clear$('notes'));
		}
	}
	select_cost.onchange = function() {
		if (json[this.value].vat == 1) var vat=5/6;
		else var vat = 1;
		var cost = this.value == '-'?0:Math.floor((json[this.value].usd*vat*json[this.value].profit/100)*100)/100;
		selectCost(cost);
	}
}

function selectProvider(i,DATA) {
	if (i == '-') {
		hide$('instructions');
		return;
	}
	hide$('sub');
	updateInstructions(DATA.providers,DATA.providers[i]);
}

function selectCountry(i) {
	if (i == '-') {
		hide$('providers');
		hide$('instructions');
		hide$('sub');
		return;
	}
	if (JSONResponse[i].providers && JSONResponse[i].providers.length) {
		hide$('instructions');
		hide$('sub');
		show$('providers');
		DATA = JSONResponse[i];
		var select_provider = clear$('select_provider');
		var def = document.createElement('option');
		update(def, 'Выберите оператора').value = '-';
		var oldprovider='';
		select_provider.appendChild(def);
		for (var j = 0; j < DATA.providers.length; ++j) {
			var opt = document.createElement('option');
			update(opt, DATA.providers[j].name).value = j;
			if(oldprovider != DATA.providers[j].name)
				select_provider.appendChild(opt);
			oldprovider = DATA.providers[j].name;
		}
		select_provider.onchange = function() {
			selectProvider(this.value,DATA);
		}
	}
	else {
		hide$('providers');
		hide$('sub');
		updateInstructions(JSONResponse,JSONResponse[i]);
	}
}

function JSONHandleResponse() {
	var n = 0;
	document.body.style.backgroundImage = 'none';
	if (!window.JSONResponse) {
		show$('fail');
		return;
	}
	for (var i = 0; i < JSONResponse.length; i++) {
		for(var c= 0 ; c < first_countries.length; c++)
			if(JSONResponse[i].country == first_countries[c]) n++;
	}
	for (var i = 0; i < (JSONResponse.length-1); i++) {
		for (var j = (i+1); j < JSONResponse.length; j++) {
			for(var c = first_countries.length-1; c >= 0; c--) {
				if(JSONResponse[j].country == first_countries[c]){
					var temp = JSONResponse[i];
					JSONResponse[i] = JSONResponse[j];
					JSONResponse[j] = temp;
				}
			}
		}
	}
	for (var i = n; i < (JSONResponse.length-1); i++) {
		for (var j = (i+1); j < JSONResponse.length; j++) {
			if (JSONResponse[i].country_name > JSONResponse[j].country_name){
				var temp = JSONResponse[i];
				JSONResponse[i] = JSONResponse[j];
				JSONResponse[j] = temp;
			}
		}
	}
	show$('ui');
	var select_country = $('select_country');
	var oldcountry='';
	for (var i = 0; i < JSONResponse.length; ++i) {
			var opt = document.createElement('option');
			update(opt, JSONResponse[i].country_name).value = i;
		if(oldcountry != JSONResponse[i].country_name)
			select_country.appendChild(opt);
		oldcountry = JSONResponse[i].country_name; 
	}
	select_country.onchange = function() {
		selectCountry(this.value);
	}
}

function JSONSendRequest() {
	var head_node = document.getElementsByTagName('head').item(0);
	var js_node = document.createElement('script');
	js_node.src = JSON_URL;
	js_node.type = 'text/javascript';
	js_node.charset = 'utf-8';
	if (navigator.product == 'Gecko') {
		js_node.onload = JSONHandleResponse;
	}
	else {
		js_node.onreadystatechange = function(evt) {
			evt? 1: evt = window.event;
			var rs = (evt.target || evt.srcElement).readyState;
			if (rs == 'loaded' || rs == 'complete') {
				JSONHandleResponse();
			}
		}
	}
	head_node.appendChild(js_node);
}

if (window.addEventListener) {
	window.addEventListener('load', JSONSendRequest, false);
}
else if (window.attachEvent) {
	window.attachEvent('onload', JSONSendRequest);
}
