!function(){"use strict";var e={d:function(t,a){for(var o in a)e.o(a,o)&&!e.o(t,o)&&Object.defineProperty(t,o,{enumerable:!0,get:a[o]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}};e.d({},{l:function(){return l}});var t=window.wp.element,a=window.wp.blocks,o=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"onlyoffice-docspace/onlyoffice-wordpress-docspace-block-manager","title":"ONLYOFFICE DocSpace Manager","category":"media","editorScript":"file:./build/index.js","keywords":["onlyoffice","docspace"],"textdomain":"onlyoffice-docspace-plugin","attributes":{"selectedAttachment":{"type":"object","default":null},"frameId":{"type":"string","default":null},"frameConfig":{"type":"object","default":null}}}'),n=window.wp.blockEditor;const l={padding:"20px"},i=(0,t.createElement)("svg",{width:"66",height:"60",viewBox:"0 0 66 60",fill:"black",xmlns:"http://www.w3.org/2000/svg"},(0,t.createElement)("path",{opacity:"0.5",fillRule:"evenodd",clipRule:"evenodd",d:"M28.9406 59.2644L2.20792 46.9066C-0.0693069 45.8277 -0.0693069 44.1604 2.20792 43.1796L11.5148 38.8642L28.8416 46.9066C31.1188 47.9854 34.7822 47.9854 36.9604 46.9066L54.2871 38.8642L63.5941 43.1796C65.8713 44.2585 65.8713 45.9258 63.5941 46.9066L36.8614 59.2644C34.7822 60.2452 31.1188 60.2452 28.9406 59.2644Z",fill:"black"}),(0,t.createElement)("path",{opacity:"0.75",fillRule:"evenodd",clipRule:"evenodd",d:"M28.9406 44.0606L2.20792 31.7028C-0.069307 30.6239 -0.069307 28.9566 2.20792 27.9758L11.3168 23.7584L28.9406 31.8989C31.2178 32.9778 34.8812 32.9778 37.0594 31.8989L54.6832 23.7584L63.7921 27.9758C66.0693 29.0547 66.0693 30.722 63.7921 31.7028L37.0594 44.0606C34.7822 45.1395 31.1188 45.1395 28.9406 44.0606Z",fill:"black"}),(0,t.createElement)("path",{fillRule:"evenodd",clipRule:"evenodd",d:"M28.9406 29.2518L2.20792 16.8939C-0.069307 15.8151 -0.069307 14.1478 2.20792 13.167L28.9406 0.809144C31.2178 -0.269715 34.8812 -0.269715 37.0594 0.809144L63.7921 13.167C66.0693 14.2458 66.0693 15.9132 63.7921 16.8939L37.0594 29.2518C34.7822 30.2325 31.1188 30.2325 28.9406 29.2518Z",fill:"black"})),{name:r}=o;(0,a.registerBlockType)(r,{icon:i,edit:e=>{let{attributes:a,setAttributes:o}=e;const i=(0,n.useBlockProps)({style:l});a.frameId||o({frameId:DocSpaceComponent.generateId()}),!a.frameConfig&&a.frameId&&o({frameConfig:{height:"400px",frameId:"ds-frame-"+a.frameId,mode:"manager",showHeader:!1,showTitle:!0,showMenu:!1,showFilter:!1,showAction:!1}});var r=!1;return(0,t.useEffect)((async()=>{await DocSpaceComponent.initScript(),window.DocSpace&&!r&&(r=!0,DocSpace.initFrame(a.frameConfig))})),(0,t.createElement)("div",i,(0,t.createElement)("div",{id:"ds-frame-"+a.frameId},"Fallback text"))},save:e=>{let{attributes:a}=e;return(0,t.createElement)("div",null,(0,t.createElement)("div",{class:"ds-frame-view","data-config":JSON.stringify(a.frameConfig),id:"ds-frame-"+a.frameId},"Fallback text"))}})}();