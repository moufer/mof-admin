/*! UEditorPlus v2.0.0*/
function preg_quote(a,b){return(a+"").replace(new RegExp("[.\\\\+*?\\[\\^\\]$(){}=!<>|:\\"+(b||"")+"-]","g"),"\\$&")}function loadScript(a,b){var c;c=document.createElement("script"),c.src=a,c.onload=function(){b&&b({isNew:!0})},document.getElementsByTagName("head")[0].appendChild(c)}var Formula={mode:"plain",latexeasy:null,init:function(){Formula.initMode(),Formula.initEvent(),Formula.initSubmit()},renderPlain:function(){var a=$("#preview"),b=$("#editor").val();if(!b)return void a.hide();b=encodeURIComponent(b);var c=editor.getOpt("formulaConfig"),d=c.imageUrlTemplate.replace(/\{\}/,b);$("#previewImage").attr("src",d),a.show()},setValuePlain:function(a){$("#editor").val(a),Formula.renderPlain()},setValueLive:function(a){return Formula.latexeasy?void Formula.latexeasy.call("set.latex",{latex:a}):void setTimeout(function(){Formula.setValueLive(a)},100)},initMode:function(){var a=editor.getOpt("formulaConfig");"live"===a.editorMode?($("#liveEditor").attr("src",a.editorLiveServer+"/editor"),$("#modeLive").show(),Formula.mode="live"):($("#modePlain").show(),Formula.mode="plain");var b=editor.selection.getRange().getClosedNode();if(b&&null!==b.getAttribute("data-formula-image")){var c=b.getAttribute("data-formula-image");c&&Formula.setValue(decodeURIComponent(c))}},setValue:function(a){switch(Formula.mode){case"plain":Formula.setValuePlain(a);break;case"live":Formula.setValueLive(a)}},getValue:function(a){switch(Formula.mode){case"plain":a($.trim($("#editor").val()));break;case"live":Formula.latexeasy.call("get.latex",{},function(b){a(b.latex)})}},initEvent:function(){var a,b=null;switch(Formula.mode){case"plain":$("#editor").on("change keypress",function(){b&&clearTimeout(b),b=setTimeout(function(){Formula.renderPlain()},1e3)}),$("#inputDemo").on("click",function(){$("#editor").val("f(a) = \\frac{1}{2\\pi i} \\oint\\frac{f(z)}{z-a}dz"),Formula.renderPlain()});break;case"live":var c=editor.getOpt("formulaConfig");loadScript(c.editorLiveServer+"/vendor/LatexEasyEditor/editor/sdk.js",function(){a=new window.LatexEasy(document.getElementById("liveEditor")),a.on("ready",function(){Formula.latexeasy=a}),a.init()})}},initSubmit:function(){dialog.onclose=function(a,b){return!b||(Formula.getValue(function(a){editor.execCommand("formula",a),editor.fireEvent("saveScene"),dialog.close(!1)}),!1)}}};