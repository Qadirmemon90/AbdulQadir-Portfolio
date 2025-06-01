const menu = document.querySelector('#menu');
const nav = document.querySelector('.links');

menu.onclick = () => {
    menu.classList.toggle('bx-x');
    nav.classList.toggle('active');
}

var url = 'https://cdn.waplus.io/waplus-crm/settings/ossembed.js';
var s = document.createElement('script');
s.type = 'text/javascript';
s.async = true;
s.src = url;
var options = {
"enabled": true,
"chatButtonSetting": {
"backgroundColor": "#16BE45",
"ctaText": "Message Us",
"borderRadius": "8",
"marginLeft": "20",
"marginBottom": "20",
"marginRight": "20",
"position": "right",
"textColor": "#ffffff",
"phoneNumber": "+923392006182",
"messageText": "👋🏻Hello",
"trackClick": true
}
}
s.onload = function() {
CreateWhatsappBtn(options);
};
var x = document.getElementsByTagName('script')[0];
x.parentNode.insertBefore(s, x);