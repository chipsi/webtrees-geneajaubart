(function(e,b,a){function i(d){return d}function f(d){d=decodeURIComponent(d.replace(c," "));0===d.indexOf('"')&&(d=d.slice(1,-1).replace(/\\"/g,'"').replace(/\\\\/g,"\\"));return d}var c=/\+/g,h=e.cookie=function(k,q,d){if(q!==a){d=e.extend({},h.defaults,d);null===q&&(d.expires=-1);if("number"===typeof d.expires){var n=d.expires,o=d.expires=new Date;o.setDate(o.getDate()+n)}q=h.json?JSON.stringify(q):String(q);return b.cookie=[encodeURIComponent(k),"=",h.raw?q:encodeURIComponent(q),d.expires?"; expires="+d.expires.toUTCString():"",d.path?"; path="+d.path:"",d.domain?"; domain="+d.domain:"",d.secure?"; secure":""].join("")}q=h.raw?i:f;d=b.cookie.split("; ");for(var n=k?null:{},o=0,m=d.length;o<m;o++){var p=d[o].split("="),g=q(p.shift()),p=q(p.join("="));if(k&&k===g){n=h.json?JSON.parse(p):p;break}k||(n[g]=h.json?JSON.parse(p):p)}return n};h.defaults={};e.removeCookie=function(d,g){return null!==e.cookie(d)?(e.cookie(d,null,g),!0):!1}})(jQuery,document);