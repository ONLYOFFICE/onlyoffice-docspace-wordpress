!function(){"use strict";var e={d:function(t,o){for(var l in o)e.o(o,l)&&!e.o(t,l)&&Object.defineProperty(t,l,{enumerable:!0,get:o[l]})},o:function(e,t){return Object.prototype.hasOwnProperty.call(e,t)}};e.d({},{l:function(){return c},i:function(){return r}});var t=window.wp.element,o=window.wp.blocks,l=JSON.parse('{"$schema":"https://schemas.wp.org/trunk/block.json","apiVersion":2,"name":"onlyoffice-docspace/onlyoffice-wordpress-docspace-block","title":"ONLYOFFICE DocSpace","category":"media","editorScript":"file:./build/index.js","keywords":["onlyoffice","docspace"],"textdomain":"onlyoffice-docspace-plugin","attributes":{"selectedAttachment":{"type":"object","default":null},"frameConfig":{"type":"object","default":null},"frameId":{"type":"string","default":null},"width":{"type":"string","default":null},"height":{"type":"string","default":null},"mode":{"type":"string","default":null},"showHeader":{"type":"boolean"},"showTitle":{"type":"boolean"},"showMenu":{"type":"boolean"},"showFilter":{"type":"boolean"},"showAction":{"type":"boolean"}}}'),n=window.wp.blockEditor,a=window.wp.components;const c={},r=(0,t.createElement)("svg",{width:"66",height:"60",viewBox:"0 0 66 60",fill:"black",xmlns:"http://www.w3.org/2000/svg"},(0,t.createElement)("path",{opacity:"0.5",fillRule:"evenodd",clipRule:"evenodd",d:"M28.9406 59.2644L2.20792 46.9066C-0.0693069 45.8277 -0.0693069 44.1604 2.20792 43.1796L11.5148 38.8642L28.8416 46.9066C31.1188 47.9854 34.7822 47.9854 36.9604 46.9066L54.2871 38.8642L63.5941 43.1796C65.8713 44.2585 65.8713 45.9258 63.5941 46.9066L36.8614 59.2644C34.7822 60.2452 31.1188 60.2452 28.9406 59.2644Z",fill:"black"}),(0,t.createElement)("path",{opacity:"0.75",fillRule:"evenodd",clipRule:"evenodd",d:"M28.9406 44.0606L2.20792 31.7028C-0.069307 30.6239 -0.069307 28.9566 2.20792 27.9758L11.3168 23.7584L28.9406 31.8989C31.2178 32.9778 34.8812 32.9778 37.0594 31.8989L54.6832 23.7584L63.7921 27.9758C66.0693 29.0547 66.0693 30.722 63.7921 31.7028L37.0594 44.0606C34.7822 45.1395 31.1188 45.1395 28.9406 44.0606Z",fill:"black"}),(0,t.createElement)("path",{fillRule:"evenodd",clipRule:"evenodd",d:"M28.9406 29.2518L2.20792 16.8939C-0.069307 15.8151 -0.069307 14.1478 2.20792 13.167L28.9406 0.809144C31.2178 -0.269715 34.8812 -0.269715 37.0594 0.809144L63.7921 13.167C66.0693 14.2458 66.0693 15.9132 63.7921 16.8939L37.0594 29.2518C34.7822 30.2325 31.1188 30.2325 28.9406 29.2518Z",fill:"black"})),{name:i}=l;(0,o.registerBlockType)(i,{icon:r,edit:e=>{let{attributes:o,setAttributes:l}=e;const i=(0,n.useBlockProps)({style:c}),[s,d]=(0,t.useState)(!1),[h,p]=(0,t.useState)({});(0,t.useEffect)((()=>{DocSpaceComponent.initScript().then((function(){s&&(console.log(h.docspaceConfig),DocSpace.initFrame(h.docspaceConfig))}))}),[s]);const m=e=>{var t={frameId:"ds-frame-select",height:"500px",mode:e.target.dataset.mode||null};p({title:e.target.dataset.title||"",docspaceConfig:t}),d(!0)};return(0,t.createElement)("div",i,(0,t.createElement)(n.InspectorControls,{key:"setting"},(0,t.createElement)(a.PanelBody,{title:"Settings"},(0,t.createElement)(a.__experimentalInputControl,{label:" Width",value:o.width,onChange:e=>l({width:e})}),(0,t.createElement)(a.__experimentalInputControl,{label:"Height",value:o.height,onChange:e=>l({height:e})}),(0,t.createElement)(a.__experimentalInputControl,{label:"src",value:o.src,onChange:e=>l({src:e})}),(0,t.createElement)(a.__experimentalInputControl,{label:"rootPath",value:o.rootPath,onChange:e=>l({rootPath:e})}),(0,t.createElement)(a.__experimentalInputControl,{label:"mode",value:o.mode,onChange:e=>l({mode:e})}),(0,t.createElement)(a.CheckboxControl,{label:"showHeader",checked:o.showHeader,onChange:e=>l({showHeader:e})}),(0,t.createElement)(a.CheckboxControl,{label:"showTitle",checked:o.showTitle,onChange:e=>l({showTitle:e})}),(0,t.createElement)(a.CheckboxControl,{label:"showMenu",checked:o.showMenu,onChange:e=>l({showMenu:e})}),(0,t.createElement)(a.CheckboxControl,{label:"showFilter",checked:o.showFilter,onChange:e=>l({showFilter:e})}),(0,t.createElement)(a.CheckboxControl,{label:"showAction",checked:o.showAction,onChange:e=>l({showAction:e})}))),(0,t.createElement)(a.Placeholder,{icon:r,label:"ONLYOFFICE DocSpace",instructions:"Pick room or media file from your DocSpace "},(0,t.createElement)(a.Button,{variant:"primary","data-title":"Select room","data-mode":"room selector",onClick:m},"Select room"),(0,t.createElement)(a.Button,{variant:"primary","data-title":"Select file","data-mode":"manager",onClick:m},"Select file")),s&&(0,t.createElement)(a.Modal,{onRequestClose:e=>{"onBlur"!=e._reactName&&d(!1)},title:h.title},(0,t.createElement)("div",{id:"ds-frame-select"},"Fallback text")))},save:e=>{let{attributes:o}=e;return(0,t.createElement)(t.RawHTML,null,"[onlyoffice-docspace  /]")}})}();