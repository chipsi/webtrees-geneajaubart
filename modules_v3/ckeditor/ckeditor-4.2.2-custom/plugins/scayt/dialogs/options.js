CKEDITOR.dialog.add("scaytcheck",function(N){function c(){return"undefined"!=typeof document.forms["optionsbar_"+R]?document.forms["optionsbar_"+R].options:[]}function b(f,g){if(f){var h=f.length;if(void 0==h){f.checked=f.value==g.toString()}else{for(var k=0;k<h;k++){f[k].checked=!1,f[k].value==g.toString()&&(f[k].checked=!0)}}}}function K(a){Q.getById("dic_message_"+R).setHtml('<span style="color:red;">'+a+"</span>")}function I(a){Q.getById("dic_message_"+R).setHtml('<span style="color:blue;">'+a+"</span>")}function G(f){f=(""+f).split(",");for(var g=0,h=f.length;g<h;g+=1){Q.getById(f[g]).$.style.display="inline"}}function E(f){f=(""+f).split(",");for(var g=0,h=f.length;g<h;g+=1){Q.getById(f[g]).$.style.display="none"}}function l(a){Q.getById("dic_name_"+R).$.value=a}var j=!0,O,Q=CKEDITOR.document,R=N.name,M=CKEDITOR.plugins.scayt.getUiTabs(N),P,i=[],e=0,L=["dic_create_"+R+",dic_restore_"+R,"dic_rename_"+R+",dic_delete_"+R],d=["mixedCase","mixedWithDigits","allCaps","ignoreDomainNames"];P=N.lang.scayt;var H=[{id:"options",label:P.optionsTab,elements:[{type:"html",id:"options",html:'<form name="optionsbar_'+R+'"><div class="inner_options">\t<div class="messagebox"></div>\t<div style="display:none;">\t\t<input type="checkbox" name="options"  id="allCaps_'+R+'" />\t\t<label style = "display: inline" for="allCaps" id="label_allCaps_'+R+'"></label>\t</div>\t<div style="display:none;">\t\t<input name="options" type="checkbox"  id="ignoreDomainNames_'+R+'" />\t\t<label style = "display: inline" for="ignoreDomainNames" id="label_ignoreDomainNames_'+R+'"></label>\t</div>\t<div style="display:none;">\t<input name="options" type="checkbox"  id="mixedCase_'+R+'" />\t\t<label style = "display: inline" for="mixedCase" id="label_mixedCase_'+R+'"></label>\t</div>\t<div style="display:none;">\t\t<input name="options" type="checkbox"  id="mixedWithDigits_'+R+'" />\t\t<label style = "display: inline" for="mixedWithDigits" id="label_mixedWithDigits_'+R+'"></label>\t</div></div></form>'}]},{id:"langs",label:P.languagesTab,elements:[{type:"html",id:"langs",html:'<form name="languagesbar_'+R+'"><div class="inner_langs">\t<div class="messagebox"></div>\t   <div style="float:left;width:45%;margin-left:5px;" id="scayt_lcol_'+R+'" ></div>   <div style="float:left;width:45%;margin-left:15px;" id="scayt_rcol_'+R+'"></div></div></form>'}]},{id:"dictionaries",label:P.dictionariesTab,elements:[{type:"html",style:"",id:"dictionaries",html:'<form name="dictionarybar_'+R+'"><div class="inner_dictionary" style="text-align:left; white-space:normal; width:320px; overflow: hidden;">\t<div style="margin:5px auto; width:95%;white-space:normal; overflow:hidden;" id="dic_message_'+R+'"> </div>\t<div style="margin:5px auto; width:95%;white-space:normal;">        <span class="cke_dialog_ui_labeled_label" >Dictionary name</span><br>\t\t<span class="cke_dialog_ui_labeled_content" >\t\t\t<div class="cke_dialog_ui_input_text">\t\t\t\t<input id="dic_name_'+R+'" type="text" class="cke_dialog_ui_input_text" style = "height: 25px; background: none; padding: 0;"/>\t\t</div></span></div>\t\t<div style="margin:5px auto; width:95%;white-space:normal;">\t\t\t<a style="display:none;" class="cke_dialog_ui_button" href="javascript:void(0)" id="dic_create_'+R+'">\t\t\t\t</a>\t\t\t<a  style="display:none;" class="cke_dialog_ui_button" href="javascript:void(0)" id="dic_delete_'+R+'">\t\t\t\t</a>\t\t\t<a  style="display:none;" class="cke_dialog_ui_button" href="javascript:void(0)" id="dic_rename_'+R+'">\t\t\t\t</a>\t\t\t<a  style="display:none;" class="cke_dialog_ui_button" href="javascript:void(0)" id="dic_restore_'+R+'">\t\t\t\t</a>\t\t</div>\t<div style="margin:5px auto; width:95%;white-space:normal;" id="dic_info_'+R+'"></div></div></form>'}]},{id:"about",label:P.aboutTab,elements:[{type:"html",id:"about",style:"margin: 5px 5px;",html:'<div id="scayt_about_'+R+'"></div>'}]}],o={title:P.title,minWidth:360,minHeight:220,onShow:function(){var f=this;f.data=N.fire("scaytDialog",{});f.options=f.data.scayt_control.option();f.chosed_lang=f.sLang=f.data.scayt_control.sLang;if(!f.data||!f.data.scayt||!f.data.scayt_control){alert("Error loading application service"),f.hide()}else{var g=0;j?f.data.scayt.getCaption(N.langCode||"en",function(a){0<g++||(O=a,F.apply(f),J.apply(f),j=!1)}):J.apply(f);f.selectPage(f.data.tab)}},onOk:function(){var f=this.data.scayt_control;f.option(this.options);f.setLang(this.chosed_lang);f.refresh()},onCancel:function(){var a=c(),g;for(g in a){a[g].checked=!1}a="undefined"!=typeof document.forms["languagesbar_"+R]?document.forms["languagesbar_"+R].scayt_lang:[];b(a,"")},contents:i};CKEDITOR.plugins.scayt.getScayt(N);for(P=0;P<M.length;P++){1==M[P]&&(i[i.length]=H[P])}1==M[2]&&(e=1);var F=function(){function a(g){var u=Q.getById("dic_name_"+R).getValue();if(!u){return K(" Dictionary name should not be empty. "),!1}try{var t=g.data.getTarget().getParent(),s=/(dic_\w+)_[\w\d]+/.exec(t.getId())[1];h[s].apply(null,[t,u,L])}catch(k){K(" Dictionary error. ")}return !0}var f=this,p=f.data.scayt.getLangList(),q=["dic_create","dic_delete","dic_rename","dic_restore"],n=[],m=[],r;if(e){for(r=0;r<q.length;r++){n[r]=q[r]+"_"+R,Q.getById(n[r]).setHtml('<span class="cke_dialog_ui_button">'+O["button_"+q[r]]+"</span>")}Q.getById("dic_info_"+R).setHtml(O.dic_info)}if(1==M[0]){for(r in d){q="label_"+d[r],n=Q.getById(q+"_"+R),"undefined"!=typeof n&&"undefined"!=typeof O[q]&&"undefined"!=typeof f.options[d[r]]&&(n.setHtml(O[q]),n.getParent().$.style.display="block")}}q='<p><img src="'+window.scayt.getAboutInfo().logoURL+'" /></p><p>'+O.version+window.scayt.getAboutInfo().version.toString()+"</p><p>"+O.about_throwt_copy+"</p>";Q.getById("scayt_about_"+R).setHtml(q);q=function(k,g){var u=Q.createElement("label");u.setAttribute("for","cke_option"+k);u.setStyle("display","inline");u.setHtml(g[k]);f.sLang==k&&(f.chosed_lang=k);var t=Q.createElement("div"),s=CKEDITOR.dom.element.createFromHtml('<input class = "cke_dialog_ui_radio_input" id="cke_option'+k+'" type="radio" '+(f.sLang==k?'checked="checked"':"")+' value="'+k+'" name="scayt_lang" />');s.on("click",function(){this.$.checked=!0;f.chosed_lang=k});t.append(s);t.append(u);return{lang:g[k],code:k,radio:t}};if(1==M[1]){for(r in p.rtl){m[m.length]=q(r,p.ltr)}for(r in p.ltr){m[m.length]=q(r,p.ltr)}m.sort(function(k,g){return g.lang>k.lang?-1:1});p=Q.getById("scayt_lcol_"+R);q=Q.getById("scayt_rcol_"+R);for(r=0;r<m.length;r++){(r<m.length/2?p:q).append(m[r].radio)}}var h={dic_create:function(k,g,v){var u=v[0]+","+v[1],t=O.err_dic_create,s=O.succ_dic_create;window.scayt.createUserDictionary(g,function(w){E(u);G(v[1]);s=s.replace("%s",w.dname);I(s)},function(w){t=t.replace("%s",w.dname);K(t+"( "+(w.message||"")+")")})},dic_rename:function(k,g){var t=O.err_dic_rename||"",s=O.succ_dic_rename||"";window.scayt.renameUserDictionary(g,function(u){s=s.replace("%s",u.dname);l(g);I(s)},function(u){t=t.replace("%s",u.dname);l(g);K(t+"( "+(u.message||"")+" )")})},dic_delete:function(k,g,v){var u=v[0]+","+v[1],t=O.err_dic_delete,s=O.succ_dic_delete;window.scayt.deleteUserDictionary(function(w){s=s.replace("%s",w.dname);E(u);G(v[0]);l("");I(s)},function(w){t=t.replace("%s",w.dname);K(t)})}};h.dic_restore=f.dic_restore||function(k,g,v){var u=v[0]+","+v[1],t=O.err_dic_restore,s=O.succ_dic_restore;window.scayt.restoreUserDictionary(g,function(w){s=s.replace("%s",w.dname);E(u);G(v[1]);I(s)},function(w){t=t.replace("%s",w.dname);K(t)})};m=(L[0]+","+L[1]).split(",");r=0;for(p=m.length;r<p;r+=1){if(q=Q.getById(m[r])){q.on("click",a,this)}}},J=function(){var a=this;if(1==M[0]){for(var m=c(),n=0,p=m.length;n<p;n++){var k=m[n].id,f=Q.getById(k);if(f&&(m[n].checked=!1,1==a.options[k.split("_")[0]]&&(m[n].checked=!0),j)){f.on("click",function(){a.options[this.getId().split("_")[0]]=this.$.checked?1:0})}}}1==M[1]&&(m=Q.getById("cke_option"+a.sLang),b(m.$,a.sLang));e&&(window.scayt.getNameUserDictionary(function(g){g=g.dname;E(L[0]+","+L[1]);g?(Q.getById("dic_name_"+R).setValue(g),G(L[1])):G(L[0])},function(){Q.getById("dic_name_"+R).setValue("")}),I(""))};return o});