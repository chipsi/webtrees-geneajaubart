CKEDITOR.dialog.add("uicolor",function(a){function k(b){/^#/.test(b)&&(b=window.YAHOO.util.Color.hex2rgb(b.substr(1)));n.setValue(b,!0);n.refresh(l)}function j(b){a.setUiColor(b);m._.contents.tab1.configBox.setValue('config.uiColor = "#'+n.get("hex")+'"')}var m,n,i=a.getUiColor(),l="cke_uicolor_picker"+CKEDITOR.tools.getNextNumber();return{title:a.lang.uicolor.title,minWidth:360,minHeight:320,onLoad:function(){m=this;this.setupContent();CKEDITOR.env.ie7Compat&&m.parts.contents.setStyle("overflow","hidden")},contents:[{id:"tab1",label:"",title:"",expand:!0,padding:0,elements:[{id:"yuiColorPicker",type:"html",html:"<div id='"+l+"' class='cke_uicolor_picker' style='width: 360px; height: 200px; position: relative;'></div>",onLoad:function(){var d=CKEDITOR.getUrl("plugins/uicolor/yui/");this.picker=n=new window.YAHOO.widget.ColorPicker(l,{showhsvcontrols:!0,showhexcontrols:!0,images:{PICKER_THUMB:d+"assets/picker_thumb.png",HUE_THUMB:d+"assets/hue_thumb.png"}});i&&k(i);n.on("rgbChange",function(){m._.contents.tab1.predefined.setValue("");j("#"+n.get("hex"))});for(var d=new CKEDITOR.dom.nodeList(n.getElementsByTagName("input")),c=0;c<d.count();c++){d.getItem(c).addClass("cke_dialog_ui_input_text")}}},{id:"tab1",type:"vbox",children:[{type:"hbox",children:[{id:"predefined",type:"select","default":"",label:a.lang.uicolor.predefined,items:[[""],["Light blue","#9AB8F3"],["Sand","#D2B48C"],["Metallic","#949AAA"],["Purple","#C2A3C7"],["Olive","#A2C980"],["Happy green","#9BD446"],["Jezebel Blue","#14B8C4"],["Burn","#FF893A"],["Easy red","#FF6969"],["Pisces 3","#48B4F2"],["Aquarius 5","#487ED4"],["Absinthe","#A8CF76"],["Scrambled Egg","#C7A622"],["Hello monday","#8E8D80"],["Lovely sunshine","#F1E8B1"],["Recycled air","#B3C593"],["Down","#BCBCA4"],["Mark Twain","#CFE91D"],["Specks of dust","#D1B596"],["Lollipop","#F6CE23"]],onChange:function(){var b=this.getValue();b?(k(b),j(b),CKEDITOR.document.getById("predefinedPreview").setStyle("background",b)):CKEDITOR.document.getById("predefinedPreview").setStyle("background","")},onShow:function(){var b=a.getUiColor();b&&this.setValue(b)}},{id:"predefinedPreview",type:"html",html:'<div id="cke_uicolor_preview" style="border: 1px solid black; padding: 3px; width: 30px;"><div id="predefinedPreview" style="width: 30px; height: 30px;">&nbsp;</div></div>'}]},{id:"configBox",type:"text",label:a.lang.uicolor.config,onShow:function(){var b=a.getUiColor();b&&this.setValue('config.uiColor = "'+b+'"')}}]}]}],buttons:[CKEDITOR.dialog.okButton]}});