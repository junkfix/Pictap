/*! Pictap Gallery 2.0.2
https://github.com/junkfix/Pictap */


"use strict";

/*!
  * PhotoSwipe 5.4.4 - https://photoswipe.com
  * (c) 2024 Dmytro Semenov
  */
!function(t,i){"object"==typeof exports&&"undefined"!=typeof module?module.exports=i():"function"==typeof define&&define.amd?define(i):(t="undefined"!=typeof globalThis?globalThis:t||self).PhotoSwipe=i()}(this,(function(){"use strict";function t(t,i,s){const h=document.createElement(i);return t&&(h.className=t),s&&s.appendChild(h),h}function i(t,i){return t.x=i.x,t.y=i.y,void 0!==i.id&&(t.id=i.id),t}function s(t){t.x=Math.round(t.x),t.y=Math.round(t.y)}function h(t,i){const s=Math.abs(t.x-i.x),h=Math.abs(t.y-i.y);return Math.sqrt(s*s+h*h)}function e(t,i){return t.x===i.x&&t.y===i.y}function n(t,i,s){return Math.min(Math.max(t,i),s)}function o(t,i,s){let h=`translate3d(${t}px,${i||0}px,0)`;return void 0!==s&&(h+=` scale3d(${s},${s},1)`),h}function r(t,i,s,h){t.style.transform=o(i,s,h)}function a(t,i,s,h){t.style.transition=i?`${i} ${s}ms ${h||"cubic-bezier(.4,0,.22,1)"}`:"none"}function l(t,i,s){t.style.width="number"==typeof i?`${i}px`:i,t.style.height="number"==typeof s?`${s}px`:s}const c="idle",d="loading",u="loaded",p="error";function m(){return!(!navigator.vendor||!navigator.vendor.match(/apple/i))}let v=!1;try{window.addEventListener("test",null,Object.defineProperty({},"passive",{get:()=>{v=!0}}))}catch(t){}class f{constructor(){this.t=[]}add(t,i,s,h){this.i(t,i,s,h)}remove(t,i,s,h){this.i(t,i,s,h,!0)}removeAll(){this.t.forEach((t=>{this.i(t.target,t.type,t.listener,t.passive,!0,!0)})),this.t=[]}i(t,i,s,h,e,n){if(!t)return;const o=e?"removeEventListener":"addEventListener";i.split(" ").forEach((i=>{if(i){n||(e?this.t=this.t.filter((h=>h.type!==i||h.listener!==s||h.target!==t)):this.t.push({target:t,type:i,listener:s,passive:h}));const r=!!v&&{passive:h||!1};t[o](i,s,r)}}))}}function w(t,i){if(t.getViewportSizeFn){const s=t.getViewportSizeFn(t,i);if(s)return s}return{x:document.documentElement.clientWidth,y:window.innerHeight}}function g(t,i,s,h,e){let n=0;if(i.paddingFn)n=i.paddingFn(s,h,e)[t];else if(i.padding)n=i.padding[t];else{const s="padding"+t[0].toUpperCase()+t.slice(1);i[s]&&(n=i[s])}return Number(n)||0}function y(t,i,s,h){return{x:i.x-g("left",t,i,s,h)-g("right",t,i,s,h),y:i.y-g("top",t,i,s,h)-g("bottom",t,i,s,h)}}class _{constructor(t){this.slide=t,this.currZoomLevel=1,this.center={x:0,y:0},this.max={x:0,y:0},this.min={x:0,y:0}}update(t){this.currZoomLevel=t,this.slide.width?(this.o("x"),this.o("y"),this.slide.pswp.dispatch("calcBounds",{slide:this.slide})):this.reset()}o(t){const{pswp:i}=this.slide,s=this.slide["x"===t?"width":"height"]*this.currZoomLevel,h=g("x"===t?"left":"top",i.options,i.viewportSize,this.slide.data,this.slide.index),e=this.slide.panAreaSize[t];this.center[t]=Math.round((e-s)/2)+h,this.max[t]=s>e?Math.round(e-s)+h:this.center[t],this.min[t]=s>e?h:this.center[t]}reset(){this.center.x=0,this.center.y=0,this.max.x=0,this.max.y=0,this.min.x=0,this.min.y=0}correctPan(t,i){return n(i,this.max[t],this.min[t])}}class x{constructor(t,i,s,h){this.pswp=h,this.options=t,this.itemData=i,this.index=s,this.panAreaSize=null,this.elementSize=null,this.fit=1,this.fill=1,this.vFill=1,this.initial=1,this.secondary=1,this.max=1,this.min=1}update(t,i,s){const h={x:t,y:i};this.elementSize=h,this.panAreaSize=s;const e=s.x/h.x,n=s.y/h.y;this.fit=Math.min(1,e<n?e:n),this.fill=Math.min(1,e>n?e:n),this.vFill=Math.min(1,n),this.initial=this.l(),this.secondary=this.u(),this.max=Math.max(this.initial,this.secondary,this.p()),this.min=Math.min(this.fit,this.initial,this.secondary),this.pswp&&this.pswp.dispatch("zoomLevelsUpdate",{zoomLevels:this,slideData:this.itemData})}m(t){const i=t+"ZoomLevel",s=this.options[i];if(s)return"function"==typeof s?s(this):"fill"===s?this.fill:"fit"===s?this.fit:Number(s)}u(){let t=this.m("secondary");return t||(t=Math.min(1,3*this.fit),this.elementSize&&t*this.elementSize.x>4e3&&(t=4e3/this.elementSize.x),t)}l(){return this.m("initial")||this.fit}p(){return this.m("max")||Math.max(1,4*this.fit)}}class b{constructor(i,s,h){this.data=i,this.index=s,this.pswp=h,this.isActive=s===h.currIndex,this.currentResolution=0,this.panAreaSize={x:0,y:0},this.pan={x:0,y:0},this.isFirstSlide=this.isActive&&!h.opener.isOpen,this.zoomLevels=new x(h.options,i,s,h),this.pswp.dispatch("gettingData",{slide:this,data:this.data,index:s}),this.content=this.pswp.contentLoader.getContentBySlide(this),this.container=t("pswp__zoom-wrap","div"),this.holderElement=null,this.currZoomLevel=1,this.width=this.content.width,this.height=this.content.height,this.heavyAppended=!1,this.bounds=new _(this),this.prevDisplayedWidth=-1,this.prevDisplayedHeight=-1,this.pswp.dispatch("slideInit",{slide:this})}setIsActive(t){t&&!this.isActive?this.activate():!t&&this.isActive&&this.deactivate()}append(t){this.holderElement=t,this.container.style.transformOrigin="0 0",this.data&&(this.calculateSize(),this.load(),this.updateContentSize(),this.appendHeavy(),this.holderElement.appendChild(this.container),this.zoomAndPanToInitial(),this.pswp.dispatch("firstZoomPan",{slide:this}),this.applyCurrentZoomPan(),this.pswp.dispatch("afterSetContent",{slide:this}),this.isActive&&this.activate())}load(){this.content.load(!1),this.pswp.dispatch("slideLoad",{slide:this})}appendHeavy(){const{pswp:t}=this;!this.heavyAppended&&t.opener.isOpen&&!t.mainScroll.isShifted()&&(this.isActive,1)&&(this.pswp.dispatch("appendHeavy",{slide:this}).defaultPrevented||(this.heavyAppended=!0,this.content.append(),this.pswp.dispatch("appendHeavyContent",{slide:this})))}activate(){this.isActive=!0,this.appendHeavy(),this.content.activate(),this.pswp.dispatch("slideActivate",{slide:this})}deactivate(){this.isActive=!1,this.content.deactivate(),this.currZoomLevel!==this.zoomLevels.initial&&this.calculateSize(),this.currentResolution=0,this.zoomAndPanToInitial(),this.applyCurrentZoomPan(),this.updateContentSize(),this.pswp.dispatch("slideDeactivate",{slide:this})}destroy(){this.content.hasSlide=!1,this.content.remove(),this.container.remove(),this.pswp.dispatch("slideDestroy",{slide:this})}resize(){this.currZoomLevel!==this.zoomLevels.initial&&this.isActive?(this.calculateSize(),this.bounds.update(this.currZoomLevel),this.panTo(this.pan.x,this.pan.y)):(this.calculateSize(),this.currentResolution=0,this.zoomAndPanToInitial(),this.applyCurrentZoomPan(),this.updateContentSize())}updateContentSize(t){const i=this.currentResolution||this.zoomLevels.initial;if(!i)return;const s=Math.round(this.width*i)||this.pswp.viewportSize.x,h=Math.round(this.height*i)||this.pswp.viewportSize.y;(this.sizeChanged(s,h)||t)&&this.content.setDisplayedSize(s,h)}sizeChanged(t,i){return(t!==this.prevDisplayedWidth||i!==this.prevDisplayedHeight)&&(this.prevDisplayedWidth=t,this.prevDisplayedHeight=i,!0)}getPlaceholderElement(){var t;return null===(t=this.content.placeholder)||void 0===t?void 0:t.element}zoomTo(t,i,h,e){const{pswp:o}=this;if(!this.isZoomable()||o.mainScroll.isShifted())return;o.dispatch("beforeZoomTo",{destZoomLevel:t,centerPoint:i,transitionDuration:h}),o.animations.stopAllPan();const r=this.currZoomLevel;e||(t=n(t,this.zoomLevels.min,this.zoomLevels.max)),this.setZoomLevel(t),this.pan.x=this.calculateZoomToPanOffset("x",i,r),this.pan.y=this.calculateZoomToPanOffset("y",i,r),s(this.pan);const a=()=>{this.v(t),this.applyCurrentZoomPan()};h?o.animations.startTransition({isPan:!0,name:"zoomTo",target:this.container,transform:this.getCurrentTransform(),onComplete:a,duration:h,easing:o.options.easing}):a()}toggleZoom(t){this.zoomTo(this.currZoomLevel===this.zoomLevels.initial?this.zoomLevels.secondary:this.zoomLevels.initial,t,this.pswp.options.zoomAnimationDuration)}setZoomLevel(t){this.currZoomLevel=t,this.bounds.update(this.currZoomLevel)}calculateZoomToPanOffset(t,i,s){if(0===this.bounds.max[t]-this.bounds.min[t])return this.bounds.center[t];i||(i=this.pswp.getViewportCenterPoint()),s||(s=this.zoomLevels.initial);const h=this.currZoomLevel/s;return this.bounds.correctPan(t,(this.pan[t]-i[t])*h+i[t])}panTo(t,i){this.pan.x=this.bounds.correctPan("x",t),this.pan.y=this.bounds.correctPan("y",i),this.applyCurrentZoomPan()}isPannable(){return Boolean(this.width)&&this.currZoomLevel>this.zoomLevels.fit}isZoomable(){return Boolean(this.width)&&this.content.isZoomable()}applyCurrentZoomPan(){this.g(this.pan.x,this.pan.y,this.currZoomLevel),this===this.pswp.currSlide&&this.pswp.dispatch("zoomPanUpdate",{slide:this})}zoomAndPanToInitial(){this.currZoomLevel=this.zoomLevels.initial,this.bounds.update(this.currZoomLevel),i(this.pan,this.bounds.center),this.pswp.dispatch("initialZoomPan",{slide:this})}g(t,i,s){s/=this.currentResolution||this.zoomLevels.initial,r(this.container,t,i,s)}calculateSize(){const{pswp:t}=this;i(this.panAreaSize,y(t.options,t.viewportSize,this.data,this.index)),this.zoomLevels.update(this.width,this.height,this.panAreaSize),t.dispatch("calcSlideSize",{slide:this})}getCurrentTransform(){const t=this.currZoomLevel/(this.currentResolution||this.zoomLevels.initial);return o(this.pan.x,this.pan.y,t)}v(t){t!==this.currentResolution&&(this.currentResolution=t,this.updateContentSize(),this.pswp.dispatch("resolutionChanged"))}}class S{constructor(t){this.gestures=t,this.pswp=t.pswp,this.startPan={x:0,y:0}}start(){this.pswp.currSlide&&i(this.startPan,this.pswp.currSlide.pan),this.pswp.animations.stopAll()}change(){const{p1:t,prevP1:i,dragAxis:h}=this.gestures,{currSlide:e}=this.pswp;if("y"===h&&this.pswp.options.closeOnVerticalDrag&&e&&e.currZoomLevel<=e.zoomLevels.fit&&!this.gestures.isMultitouch){const s=e.pan.y+(t.y-i.y);if(!this.pswp.dispatch("verticalDrag",{panY:s}).defaultPrevented){this._("y",s,.6);const t=1-Math.abs(this.S(e.pan.y));this.pswp.applyBgOpacity(t),e.applyCurrentZoomPan()}}else{this.M("x")||(this.M("y"),e&&(s(e.pan),e.applyCurrentZoomPan()))}}end(){const{velocity:t}=this.gestures,{mainScroll:i,currSlide:s}=this.pswp;let h=0;if(this.pswp.animations.stopAll(),i.isShifted()){const s=(i.x-i.getCurrSlideX())/this.pswp.viewportSize.x;t.x<-.5&&s<0||t.x<.1&&s<-.5?(h=1,t.x=Math.min(t.x,0)):(t.x>.5&&s>0||t.x>-.1&&s>.5)&&(h=-1,t.x=Math.max(t.x,0)),i.moveIndexBy(h,!0,t.x)}s&&s.currZoomLevel>s.zoomLevels.max||this.gestures.isMultitouch?this.gestures.zoomLevels.correctZoomPan(!0):(this.P("x"),this.P("y"))}P(t){const{velocity:i}=this.gestures,{currSlide:s}=this.pswp;if(!s)return;const{pan:h,bounds:e}=s,o=h[t],r=this.pswp.bgOpacity<1&&"y"===t,a=o+function(t,i){return t*i/(1-i)}(i[t],.995);if(r){const t=this.S(o),i=this.S(a);if(t<0&&i<-.4||t>0&&i>.4)return void this.pswp.close()}const l=e.correctPan(t,a);if(o===l)return;const c=l===a?1:.82,d=this.pswp.bgOpacity,u=l-o;this.pswp.animations.startSpring({name:"panGesture"+t,isPan:!0,start:o,end:l,velocity:i[t],dampingRatio:c,onUpdate:i=>{if(r&&this.pswp.bgOpacity<1){const t=1-(l-i)/u;this.pswp.applyBgOpacity(n(d+(1-d)*t,0,1))}h[t]=Math.floor(i),s.applyCurrentZoomPan()}})}M(t){const{p1:i,dragAxis:s,prevP1:h,isMultitouch:e}=this.gestures,{currSlide:n,mainScroll:o}=this.pswp,r=i[t]-h[t],a=o.x+r;if(!r||!n)return!1;if("x"===t&&!n.isPannable()&&!e)return o.moveTo(a,!0),!0;const{bounds:l}=n,c=n.pan[t]+r;if(this.pswp.options.allowPanToNext&&"x"===s&&"x"===t&&!e){const i=o.getCurrSlideX(),s=o.x-i,h=r>0,e=!h;if(c>l.min[t]&&h){if(l.min[t]<=this.startPan[t])return o.moveTo(a,!0),!0;this._(t,c)}else if(c<l.max[t]&&e){if(this.startPan[t]<=l.max[t])return o.moveTo(a,!0),!0;this._(t,c)}else if(0!==s){if(s>0)return o.moveTo(Math.max(a,i),!0),!0;if(s<0)return o.moveTo(Math.min(a,i),!0),!0}else this._(t,c)}else"y"===t&&(o.isShifted()||l.min.y===l.max.y)||this._(t,c);return!1}S(t){var i,s;return(t-(null!==(i=null===(s=this.pswp.currSlide)||void 0===s?void 0:s.bounds.center.y)&&void 0!==i?i:0))/(this.pswp.viewportSize.y/3)}_(t,i,s){const{currSlide:h}=this.pswp;if(!h)return;const{pan:e,bounds:n}=h;if(n.correctPan(t,i)!==i||s){const h=Math.round(i-e[t]);e[t]+=h*(s||.35)}else e[t]=i}}function z(t,i,s){return t.x=(i.x+s.x)/2,t.y=(i.y+s.y)/2,t}class M{constructor(t){this.gestures=t,this.C={x:0,y:0},this.T={x:0,y:0},this.A={x:0,y:0},this.D=!1,this.I=1}start(){const{currSlide:t}=this.gestures.pswp;t&&(this.I=t.currZoomLevel,i(this.C,t.pan)),this.gestures.pswp.animations.stopAllPan(),this.D=!1}change(){const{p1:t,startP1:i,p2:s,startP2:e,pswp:n}=this.gestures,{currSlide:o}=n;if(!o)return;const r=o.zoomLevels.min,a=o.zoomLevels.max;if(!o.isZoomable()||n.mainScroll.isShifted())return;z(this.T,i,e),z(this.A,t,s);let l=1/h(i,e)*h(t,s)*this.I;if(l>o.zoomLevels.initial+o.zoomLevels.initial/15&&(this.D=!0),l<r)if(n.options.pinchToClose&&!this.D&&this.I<=o.zoomLevels.initial){const t=1-(r-l)/(r/1.2);n.dispatch("pinchClose",{bgOpacity:t}).defaultPrevented||n.applyBgOpacity(t)}else l=r-.15*(r-l);else l>a&&(l=a+.05*(l-a));o.pan.x=this.L("x",l),o.pan.y=this.L("y",l),o.setZoomLevel(l),o.applyCurrentZoomPan()}end(){const{pswp:t}=this.gestures,{currSlide:i}=t;(!i||i.currZoomLevel<i.zoomLevels.initial)&&!this.D&&t.options.pinchToClose?t.close():this.correctZoomPan()}L(t,i){const s=i/this.I;return this.A[t]-(this.T[t]-this.C[t])*s}correctZoomPan(t){const{pswp:s}=this.gestures,{currSlide:h}=s;if(null==h||!h.isZoomable())return;0===this.A.x&&(t=!0);const o=h.currZoomLevel;let r,a=!0;o<h.zoomLevels.initial?r=h.zoomLevels.initial:o>h.zoomLevels.max?r=h.zoomLevels.max:(a=!1,r=o);const l=s.bgOpacity,c=s.bgOpacity<1,d=i({x:0,y:0},h.pan);let u=i({x:0,y:0},d);t&&(this.A.x=0,this.A.y=0,this.T.x=0,this.T.y=0,this.I=o,i(this.C,d)),a&&(u={x:this.L("x",r),y:this.L("y",r)}),h.setZoomLevel(r),u={x:h.bounds.correctPan("x",u.x),y:h.bounds.correctPan("y",u.y)},h.setZoomLevel(o);const p=!e(u,d);if(!p&&!a&&!c)return h.v(r),void h.applyCurrentZoomPan();s.animations.stopAllPan(),s.animations.startSpring({isPan:!0,start:0,end:1e3,velocity:0,dampingRatio:1,naturalFrequency:40,onUpdate:t=>{if(t/=1e3,p||a){if(p&&(h.pan.x=d.x+(u.x-d.x)*t,h.pan.y=d.y+(u.y-d.y)*t),a){const i=o+(r-o)*t;h.setZoomLevel(i)}h.applyCurrentZoomPan()}c&&s.bgOpacity<1&&s.applyBgOpacity(n(l+(1-l)*t,0,1))},onComplete:()=>{h.v(r),h.applyCurrentZoomPan()}})}}function P(t){return!!t.target.closest(".pswp__container")}class C{constructor(t){this.gestures=t}click(t,i){const s=i.target.classList,h=s.contains("pswp__img"),e=s.contains("pswp__item")||s.contains("pswp__zoom-wrap");h?this.k("imageClick",t,i):e&&this.k("bgClick",t,i)}tap(t,i){P(i)&&this.k("tap",t,i)}doubleTap(t,i){P(i)&&this.k("doubleTap",t,i)}k(t,i,s){var h;const{pswp:e}=this.gestures,{currSlide:n}=e,o=t+"Action",r=e.options[o];if(!e.dispatch(o,{point:i,originalEvent:s}).defaultPrevented)if("function"!=typeof r)switch(r){case"close":case"next":e[r]();break;case"zoom":null==n||n.toggleZoom(i);break;case"zoom-or-close":null!=n&&n.isZoomable()&&n.zoomLevels.secondary!==n.zoomLevels.initial?n.toggleZoom(i):e.options.clickToCloseNonZoomable&&e.close();break;case"toggle-controls":null===(h=this.gestures.pswp.element)||void 0===h||h.classList.toggle("pswp--ui-visible")}else r.call(e,i,s)}}class T{constructor(t){this.pswp=t,this.dragAxis=null,this.p1={x:0,y:0},this.p2={x:0,y:0},this.prevP1={x:0,y:0},this.prevP2={x:0,y:0},this.startP1={x:0,y:0},this.startP2={x:0,y:0},this.velocity={x:0,y:0},this.Z={x:0,y:0},this.B={x:0,y:0},this.F=0,this.O=[],this.R="ontouchstart"in window,this.N=!!window.PointerEvent,this.supportsTouch=this.R||this.N&&navigator.maxTouchPoints>1,this.F=0,this.U=0,this.V=!1,this.isMultitouch=!1,this.isDragging=!1,this.isZooming=!1,this.raf=null,this.G=null,this.supportsTouch||(t.options.allowPanToNext=!1),this.drag=new S(this),this.zoomLevels=new M(this),this.tapHandler=new C(this),t.on("bindEvents",(()=>{t.events.add(t.scrollWrap,"click",this.$.bind(this)),this.N?this.q("pointer","down","up","cancel"):this.R?(this.q("touch","start","end","cancel"),t.scrollWrap&&(t.scrollWrap.ontouchmove=()=>{},t.scrollWrap.ontouchend=()=>{})):this.q("mouse","down","up")}))}q(t,i,s,h){const{pswp:e}=this,{events:n}=e,o=h?t+h:"";n.add(e.scrollWrap,t+i,this.onPointerDown.bind(this)),n.add(window,t+"move",this.onPointerMove.bind(this)),n.add(window,t+s,this.onPointerUp.bind(this)),o&&n.add(e.scrollWrap,o,this.onPointerUp.bind(this))}onPointerDown(t){const s="mousedown"===t.type||"mouse"===t.pointerType;if(s&&t.button>0)return;const{pswp:h}=this;h.opener.isOpen?h.dispatch("pointerDown",{originalEvent:t}).defaultPrevented||(s&&(h.mouseDetected(),this.H(t,"down")),h.animations.stopAll(),this.K(t,"down"),1===this.F&&(this.dragAxis=null,i(this.startP1,this.p1)),this.F>1?(this.W(),this.isMultitouch=!0):this.isMultitouch=!1):t.preventDefault()}onPointerMove(t){this.H(t,"move"),this.F&&(this.K(t,"move"),this.pswp.dispatch("pointerMove",{originalEvent:t}).defaultPrevented||(1!==this.F||this.isDragging?this.F>1&&!this.isZooming&&(this.j(),this.isZooming=!0,this.X(),this.zoomLevels.start(),this.Y(),this.J()):(this.dragAxis||this.tt(),this.dragAxis&&!this.isDragging&&(this.isZooming&&(this.isZooming=!1,this.zoomLevels.end()),this.isDragging=!0,this.W(),this.X(),this.U=Date.now(),this.V=!1,i(this.B,this.p1),this.velocity.x=0,this.velocity.y=0,this.drag.start(),this.Y(),this.J()))))}j(){this.isDragging&&(this.isDragging=!1,this.V||this.it(!0),this.drag.end(),this.dragAxis=null)}onPointerUp(t){this.F&&(this.K(t,"up"),this.pswp.dispatch("pointerUp",{originalEvent:t}).defaultPrevented||(0===this.F&&(this.Y(),this.isDragging?this.j():this.isZooming||this.isMultitouch||this.st(t)),this.F<2&&this.isZooming&&(this.isZooming=!1,this.zoomLevels.end(),1===this.F&&(this.dragAxis=null,this.X()))))}J(){(this.isDragging||this.isZooming)&&(this.it(),this.isDragging?e(this.p1,this.prevP1)||this.drag.change():e(this.p1,this.prevP1)&&e(this.p2,this.prevP2)||this.zoomLevels.change(),this.ht(),this.raf=requestAnimationFrame(this.J.bind(this)))}it(t){const s=Date.now(),h=s-this.U;h<50&&!t||(this.velocity.x=this.et("x",h),this.velocity.y=this.et("y",h),this.U=s,i(this.B,this.p1),this.V=!0)}st(t){const{mainScroll:s}=this.pswp;if(s.isShifted())return void s.moveIndexBy(0,!0);if(t.type.indexOf("cancel")>0)return;if("mouseup"===t.type||"mouse"===t.pointerType)return void this.tapHandler.click(this.startP1,t);const e=this.pswp.options.doubleTapAction?300:0;this.G?(this.W(),h(this.Z,this.startP1)<25&&this.tapHandler.doubleTap(this.startP1,t)):(i(this.Z,this.startP1),this.G=setTimeout((()=>{this.tapHandler.tap(this.startP1,t),this.W()}),e))}W(){this.G&&(clearTimeout(this.G),this.G=null)}et(t,i){const s=this.p1[t]-this.B[t];return Math.abs(s)>1&&i>5?s/i:0}Y(){this.raf&&(cancelAnimationFrame(this.raf),this.raf=null)}H(t,i){this.pswp.applyFilters("preventPointerEvent",!0,t,i)&&t.preventDefault()}K(t,s){if(this.N){const h=t,e=this.O.findIndex((t=>t.id===h.pointerId));"up"===s&&e>-1?this.O.splice(e,1):"down"===s&&-1===e?this.O.push(this.nt(h,{x:0,y:0})):e>-1&&this.nt(h,this.O[e]),this.F=this.O.length,this.F>0&&i(this.p1,this.O[0]),this.F>1&&i(this.p2,this.O[1])}else{const i=t;this.F=0,i.type.indexOf("touch")>-1?i.touches&&i.touches.length>0&&(this.nt(i.touches[0],this.p1),this.F++,i.touches.length>1&&(this.nt(i.touches[1],this.p2),this.F++)):(this.nt(t,this.p1),"up"===s?this.F=0:this.F++)}}ht(){i(this.prevP1,this.p1),i(this.prevP2,this.p2)}X(){i(this.startP1,this.p1),i(this.startP2,this.p2),this.ht()}tt(){if(this.pswp.mainScroll.isShifted())this.dragAxis="x";else{const t=Math.abs(this.p1.x-this.startP1.x)-Math.abs(this.p1.y-this.startP1.y);if(0!==t){const i=t>0?"x":"y";Math.abs(this.p1[i]-this.startP1[i])>=10&&(this.dragAxis=i)}}}nt(t,i){return i.x=t.pageX-this.pswp.offset.x,i.y=t.pageY-this.pswp.offset.y,"pointerId"in t?i.id=t.pointerId:void 0!==t.identifier&&(i.id=t.identifier),i}$(t){this.pswp.mainScroll.isShifted()&&(t.preventDefault(),t.stopPropagation())}}class A{constructor(t){this.pswp=t,this.x=0,this.slideWidth=0,this.ot=0,this.rt=0,this.lt=-1,this.itemHolders=[]}resize(t){const{pswp:i}=this,s=Math.round(i.viewportSize.x+i.viewportSize.x*i.options.spacing),h=s!==this.slideWidth;h&&(this.slideWidth=s,this.moveTo(this.getCurrSlideX())),this.itemHolders.forEach(((i,s)=>{h&&r(i.el,(s+this.lt)*this.slideWidth),t&&i.slide&&i.slide.resize()}))}resetPosition(){this.ot=0,this.rt=0,this.slideWidth=0,this.lt=-1}appendHolders(){this.itemHolders=[];for(let i=0;i<3;i++){const s=t("pswp__item","div",this.pswp.container);s.setAttribute("role","group"),s.setAttribute("aria-roledescription","slide"),s.setAttribute("aria-hidden","true"),s.style.display=1===i?"block":"none",this.itemHolders.push({el:s})}}canBeSwiped(){return this.pswp.getNumItems()>1}moveIndexBy(t,i,s){const{pswp:h}=this;let e=h.potentialIndex+t;const n=h.getNumItems();if(h.canLoop()){e=h.getLoopedIndex(e);const i=(t+n)%n;t=i<=n/2?i:i-n}else e<0?e=0:e>=n&&(e=n-1),t=e-h.potentialIndex;h.potentialIndex=e,this.ot-=t,h.animations.stopMainScroll();const o=this.getCurrSlideX();if(i){h.animations.startSpring({isMainScroll:!0,start:this.x,end:o,velocity:s||0,naturalFrequency:30,dampingRatio:1,onUpdate:t=>{this.moveTo(t)},onComplete:()=>{this.updateCurrItem(),h.appendHeavy()}});let t=h.potentialIndex-h.currIndex;if(h.canLoop()){const i=(t+n)%n;t=i<=n/2?i:i-n}Math.abs(t)>1&&this.updateCurrItem()}else this.moveTo(o),this.updateCurrItem();return Boolean(t)}getCurrSlideX(){return this.slideWidth*this.ot}isShifted(){return this.x!==this.getCurrSlideX()}updateCurrItem(){var t;const{pswp:i}=this,s=this.rt-this.ot;if(!s)return;this.rt=this.ot,i.currIndex=i.potentialIndex;let h,e=Math.abs(s);e>=3&&(this.lt+=s+(s>0?-3:3),e=3,this.itemHolders.forEach((t=>{var i;null===(i=t.slide)||void 0===i||i.destroy(),t.slide=void 0})));for(let t=0;t<e;t++)s>0?(h=this.itemHolders.shift(),h&&(this.itemHolders[2]=h,this.lt++,r(h.el,(this.lt+2)*this.slideWidth),i.setContent(h,i.currIndex-e+t+2))):(h=this.itemHolders.pop(),h&&(this.itemHolders.unshift(h),this.lt--,r(h.el,this.lt*this.slideWidth),i.setContent(h,i.currIndex+e-t-2)));Math.abs(this.lt)>50&&!this.isShifted()&&(this.resetPosition(),this.resize()),i.animations.stopAllPan(),this.itemHolders.forEach(((t,i)=>{t.slide&&t.slide.setIsActive(1===i)})),i.currSlide=null===(t=this.itemHolders[1])||void 0===t?void 0:t.slide,i.contentLoader.updateLazy(s),i.currSlide&&i.currSlide.applyCurrentZoomPan(),i.dispatch("change")}moveTo(t,i){if(!this.pswp.canLoop()&&i){let i=(this.slideWidth*this.ot-t)/this.slideWidth;i+=this.pswp.currIndex;const s=Math.round(t-this.x);(i<0&&s>0||i>=this.pswp.getNumItems()-1&&s<0)&&(t=this.x+.35*s)}this.x=t,this.pswp.container&&r(this.pswp.container,t),this.pswp.dispatch("moveMainScroll",{x:t,dragging:null!=i&&i})}}const D={Escape:27,z:90,ArrowLeft:37,ArrowUp:38,ArrowRight:39,ArrowDown:40,Tab:9},I=(t,i)=>i?t:D[t];class E{constructor(t){this.pswp=t,this.ct=!1,t.on("bindEvents",(()=>{t.options.trapFocus&&(t.options.initialPointerPos||this.dt(),t.events.add(document,"focusin",this.ut.bind(this))),t.events.add(document,"keydown",this.vt.bind(this))}));const i=document.activeElement;t.on("destroy",(()=>{t.options.returnFocus&&i&&this.ct&&i.focus()}))}dt(){!this.ct&&this.pswp.element&&(this.pswp.element.focus(),this.ct=!0)}vt(t){const{pswp:i}=this;if(i.dispatch("keydown",{originalEvent:t}).defaultPrevented)return;if(function(t){return"button"in t&&1===t.button||t.ctrlKey||t.metaKey||t.altKey||t.shiftKey}(t))return;let s,h,e=!1;const n="key"in t;switch(n?t.key:t.keyCode){case I("Escape",n):i.options.escKey&&(s="close");break;case I("z",n):s="toggleZoom";break;case I("ArrowLeft",n):h="x";break;case I("ArrowUp",n):h="y";break;case I("ArrowRight",n):h="x",e=!0;break;case I("ArrowDown",n):e=!0,h="y";break;case I("Tab",n):this.dt()}if(h){t.preventDefault();const{currSlide:n}=i;i.options.arrowKeys&&"x"===h&&i.getNumItems()>1?s=e?"next":"prev":n&&n.currZoomLevel>n.zoomLevels.fit&&(n.pan[h]+=e?-80:80,n.panTo(n.pan.x,n.pan.y))}s&&(t.preventDefault(),i[s]())}ut(t){const{template:i}=this.pswp;i&&document!==t.target&&i!==t.target&&!i.contains(t.target)&&i.focus()}}const L="cubic-bezier(.4,0,.22,1)";class k{constructor(t){var i;this.props=t;const{target:s,onComplete:h,transform:e,onFinish:n=(()=>{}),duration:o=333,easing:r=L}=t;this.onFinish=n;const l=e?"transform":"opacity",c=null!==(i=t[l])&&void 0!==i?i:"";this.ft=s,this.wt=h,this.gt=!1,this.yt=this.yt.bind(this),this._t=setTimeout((()=>{a(s,l,o,r),this._t=setTimeout((()=>{s.addEventListener("transitionend",this.yt,!1),s.addEventListener("transitioncancel",this.yt,!1),this._t=setTimeout((()=>{this.xt()}),o+500),s.style[l]=c}),30)}),0)}yt(t){t.target===this.ft&&this.xt()}xt(){this.gt||(this.gt=!0,this.onFinish(),this.wt&&this.wt())}destroy(){this._t&&clearTimeout(this._t),a(this.ft),this.ft.removeEventListener("transitionend",this.yt,!1),this.ft.removeEventListener("transitioncancel",this.yt,!1),this.gt||this.xt()}}class Z{constructor(t,i,s){this.velocity=1e3*t,this.bt=i||.75,this.St=s||12,this.zt=this.St,this.bt<1&&(this.zt*=Math.sqrt(1-this.bt*this.bt))}easeFrame(t,i){let s,h=0;i/=1e3;const e=Math.E**(-this.bt*this.St*i);if(1===this.bt)s=this.velocity+this.St*t,h=(t+s*i)*e,this.velocity=h*-this.St+s*e;else if(this.bt<1){s=1/this.zt*(this.bt*this.St*t+this.velocity);const n=Math.cos(this.zt*i),o=Math.sin(this.zt*i);h=e*(t*n+s*o),this.velocity=h*-this.St*this.bt+e*(-this.zt*t*o+this.zt*s*n)}return h}}class B{constructor(t){this.props=t,this.Mt=0;const{start:i,end:s,velocity:h,onUpdate:e,onComplete:n,onFinish:o=(()=>{}),dampingRatio:r,naturalFrequency:a}=t;this.onFinish=o;const l=new Z(h,r,a);let c=Date.now(),d=i-s;const u=()=>{this.Mt&&(d=l.easeFrame(d,Date.now()-c),Math.abs(d)<1&&Math.abs(l.velocity)<50?(e(s),n&&n(),this.onFinish()):(c=Date.now(),e(d+s),this.Mt=requestAnimationFrame(u)))};this.Mt=requestAnimationFrame(u)}destroy(){this.Mt>=0&&cancelAnimationFrame(this.Mt),this.Mt=0}}class F{constructor(){this.activeAnimations=[]}startSpring(t){this.Pt(t,!0)}startTransition(t){this.Pt(t)}Pt(t,i){const s=i?new B(t):new k(t);return this.activeAnimations.push(s),s.onFinish=()=>this.stop(s),s}stop(t){t.destroy();const i=this.activeAnimations.indexOf(t);i>-1&&this.activeAnimations.splice(i,1)}stopAll(){this.activeAnimations.forEach((t=>{t.destroy()})),this.activeAnimations=[]}stopAllPan(){this.activeAnimations=this.activeAnimations.filter((t=>!t.props.isPan||(t.destroy(),!1)))}stopMainScroll(){this.activeAnimations=this.activeAnimations.filter((t=>!t.props.isMainScroll||(t.destroy(),!1)))}isPanRunning(){return this.activeAnimations.some((t=>t.props.isPan))}}class O{constructor(t){this.pswp=t,t.events.add(t.element,"wheel",this.Ct.bind(this))}Ct(t){t.preventDefault();const{currSlide:i}=this.pswp;let{deltaX:s,deltaY:h}=t;if(i&&!this.pswp.dispatch("wheel",{originalEvent:t}).defaultPrevented)if(t.ctrlKey||this.pswp.options.wheelToZoom){if(i.isZoomable()){let s=-h;1===t.deltaMode?s*=.05:s*=t.deltaMode?1:.002,s=2**s;const e=i.currZoomLevel*s;i.zoomTo(e,{x:t.clientX,y:t.clientY})}}else i.isPannable()&&(1===t.deltaMode&&(s*=18,h*=18),i.panTo(i.pan.x-s,i.pan.y-h))}}class R{constructor(i,s){var h;const e=s.name||s.className;let n=s.html;if(!1===i.options[e])return;"string"==typeof i.options[e+"SVG"]&&(n=i.options[e+"SVG"]),i.dispatch("uiElementCreate",{data:s});let o="";s.isButton?(o+="pswp__button ",o+=s.className||`pswp__button--${s.name}`):o+=s.className||`pswp__${s.name}`;let r=s.isButton?s.tagName||"button":s.tagName||"div";r=r.toLowerCase();const a=t(o,r);if(s.isButton){"button"===r&&(a.type="button");let{title:t}=s;const{ariaLabel:h}=s;"string"==typeof i.options[e+"Title"]&&(t=i.options[e+"Title"]),t&&(a.title=t);const n=h||t;n&&a.setAttribute("aria-label",n)}a.innerHTML=function(t){if("string"==typeof t)return t;if(!t||!t.isCustomSVG)return"";const i=t;let s='<svg aria-hidden="true" class="pswp__icn" viewBox="0 0 %d %d" width="%d" height="%d">';return s=s.split("%d").join(i.size||32),i.outlineID&&(s+='<use class="pswp__icn-shadow" xlink:href="#'+i.outlineID+'"/>'),s+=i.inner,s+="</svg>",s}(n),s.onInit&&s.onInit(a,i),s.onClick&&(a.onclick=t=>{"string"==typeof s.onClick?i[s.onClick]():"function"==typeof s.onClick&&s.onClick(t,a,i)});const l=s.appendTo||"bar";let c=i.element;"bar"===l?(i.topBar||(i.topBar=t("pswp__top-bar pswp__hide-on-close","div",i.scrollWrap)),c=i.topBar):(a.classList.add("pswp__hide-on-close"),"wrapper"===l&&(c=i.scrollWrap)),null===(h=c)||void 0===h||h.appendChild(i.applyFilters("uiElement",a,s))}}function N(t,i,s){t.classList.add("pswp__button--arrow"),t.setAttribute("aria-controls","pswp__items"),i.on("change",(()=>{i.options.loop||(t.disabled=s?!(i.currIndex<i.getNumItems()-1):!(i.currIndex>0))}))}const U={name:"arrowPrev",className:"pswp__button--arrow--prev",title:"Previous",order:10,isButton:!0,appendTo:"wrapper",html:{isCustomSVG:!0,size:60,inner:'<path d="M29 43l-3 3-16-16 16-16 3 3-13 13 13 13z" id="pswp__icn-arrow"/>',outlineID:"pswp__icn-arrow"},onClick:"prev",onInit:N},V={name:"arrowNext",className:"pswp__button--arrow--next",title:"Next",order:11,isButton:!0,appendTo:"wrapper",html:{isCustomSVG:!0,size:60,inner:'<use xlink:href="#pswp__icn-arrow"/>',outlineID:"pswp__icn-arrow"},onClick:"next",onInit:(t,i)=>{N(t,i,!0)}},G={name:"close",title:"Close",order:20,isButton:!0,html:{isCustomSVG:!0,inner:'<path d="M24 10l-2-2-6 6-6-6-2 2 6 6-6 6 2 2 6-6 6 6 2-2-6-6z" id="pswp__icn-close"/>',outlineID:"pswp__icn-close"},onClick:"close"},$={name:"zoom",title:"Zoom",order:10,isButton:!0,html:{isCustomSVG:!0,inner:'<path d="M17.426 19.926a6 6 0 1 1 1.5-1.5L23 22.5 21.5 24l-4.074-4.074z" id="pswp__icn-zoom"/><path fill="currentColor" class="pswp__zoom-icn-bar-h" d="M11 16v-2h6v2z"/><path fill="currentColor" class="pswp__zoom-icn-bar-v" d="M13 12h2v6h-2z"/>',outlineID:"pswp__icn-zoom"},onClick:"toggleZoom"},q={name:"preloader",appendTo:"bar",order:7,html:{isCustomSVG:!0,inner:'<path fill-rule="evenodd" clip-rule="evenodd" d="M21.2 16a5.2 5.2 0 1 1-5.2-5.2V8a8 8 0 1 0 8 8h-2.8Z" id="pswp__icn-loading"/>',outlineID:"pswp__icn-loading"},onInit:(t,i)=>{let s,h=null;const e=i=>{var h,e;s!==i&&(s=i,h="active",e=i,t.classList.toggle("pswp__preloader--"+h,e))},n=()=>{var t;if(null===(t=i.currSlide)||void 0===t||!t.content.isLoading())return e(!1),void(h&&(clearTimeout(h),h=null));h||(h=setTimeout((()=>{var t;e(Boolean(null===(t=i.currSlide)||void 0===t?void 0:t.content.isLoading())),h=null}),i.options.preloaderDelay))};i.on("change",n),i.on("loadComplete",(t=>{i.currSlide===t.slide&&n()})),i.ui&&(i.ui.updatePreloaderVisibility=n)}},H={name:"counter",order:5,onInit:(t,i)=>{i.on("change",(()=>{t.innerText=i.currIndex+1+i.options.indexIndicatorSep+i.getNumItems()}))}};function K(t,i){t.classList.toggle("pswp--zoomed-in",i)}class W{constructor(t){this.pswp=t,this.isRegistered=!1,this.uiElementsData=[],this.items=[],this.updatePreloaderVisibility=()=>{},this.Tt=void 0}init(){const{pswp:t}=this;this.isRegistered=!1,this.uiElementsData=[G,U,V,$,q,H],t.dispatch("uiRegister"),this.uiElementsData.sort(((t,i)=>(t.order||0)-(i.order||0))),this.items=[],this.isRegistered=!0,this.uiElementsData.forEach((t=>{this.registerElement(t)})),t.on("change",(()=>{var i;null===(i=t.element)||void 0===i||i.classList.toggle("pswp--one-slide",1===t.getNumItems())})),t.on("zoomPanUpdate",(()=>this.At()))}registerElement(t){this.isRegistered?this.items.push(new R(this.pswp,t)):this.uiElementsData.push(t)}At(){const{template:t,currSlide:i,options:s}=this.pswp;if(this.pswp.opener.isClosing||!t||!i)return;let{currZoomLevel:h}=i;if(this.pswp.opener.isOpen||(h=i.zoomLevels.initial),h===this.Tt)return;this.Tt=h;const e=i.zoomLevels.initial-i.zoomLevels.secondary;if(Math.abs(e)<.01||!i.isZoomable())return K(t,!1),void t.classList.remove("pswp--zoom-allowed");t.classList.add("pswp--zoom-allowed");K(t,(h===i.zoomLevels.initial?i.zoomLevels.secondary:i.zoomLevels.initial)<=h),"zoom"!==s.imageClickAction&&"zoom-or-close"!==s.imageClickAction||t.classList.add("pswp--click-to-zoom")}}class j{constructor(t,i){this.type=t,this.defaultPrevented=!1,i&&Object.assign(this,i)}preventDefault(){this.defaultPrevented=!0}}class X{constructor(i,s){if(this.element=t("pswp__img pswp__img--placeholder",i?"img":"div",s),i){const t=this.element;t.decoding="async",t.alt="",t.src=i,t.setAttribute("role","presentation")}this.element.setAttribute("aria-hidden","true")}setDisplayedSize(t,i){this.element&&("IMG"===this.element.tagName?(l(this.element,250,"auto"),this.element.style.transformOrigin="0 0",this.element.style.transform=o(0,0,t/250)):l(this.element,t,i))}destroy(){var t;null!==(t=this.element)&&void 0!==t&&t.parentNode&&this.element.remove(),this.element=null}}class Y{constructor(t,i,s){this.instance=i,this.data=t,this.index=s,this.element=void 0,this.placeholder=void 0,this.slide=void 0,this.displayedImageWidth=0,this.displayedImageHeight=0,this.width=Number(this.data.w)||Number(this.data.width)||0,this.height=Number(this.data.h)||Number(this.data.height)||0,this.isAttached=!1,this.hasSlide=!1,this.isDecoding=!1,this.state=c,this.data.type?this.type=this.data.type:this.data.src?this.type="image":this.type="html",this.instance.dispatch("contentInit",{content:this})}removePlaceholder(){this.placeholder&&!this.keepPlaceholder()&&setTimeout((()=>{this.placeholder&&(this.placeholder.destroy(),this.placeholder=void 0)}),1e3)}load(i,s){if(this.slide&&this.usePlaceholder())if(this.placeholder){const t=this.placeholder.element;t&&!t.parentElement&&this.slide.container.prepend(t)}else{const t=this.instance.applyFilters("placeholderSrc",!(!this.data.msrc||!this.slide.isFirstSlide)&&this.data.msrc,this);this.placeholder=new X(t,this.slide.container)}this.element&&!s||this.instance.dispatch("contentLoad",{content:this,isLazy:i}).defaultPrevented||(this.isImageContent()?(this.element=t("pswp__img","img"),this.displayedImageWidth&&this.loadImage(i)):(this.element=t("pswp__content","div"),this.element.innerHTML=this.data.html||""),s&&this.slide&&this.slide.updateContentSize(!0))}loadImage(t){var i,s;if(!this.isImageContent()||!this.element||this.instance.dispatch("contentLoadImage",{content:this,isLazy:t}).defaultPrevented)return;const h=this.element;this.updateSrcsetSizes(),this.data.srcset&&(h.srcset=this.data.srcset),h.src=null!==(i=this.data.src)&&void 0!==i?i:"",h.alt=null!==(s=this.data.alt)&&void 0!==s?s:"",this.state=d,h.complete?this.onLoaded():(h.onload=()=>{this.onLoaded()},h.onerror=()=>{this.onError()})}setSlide(t){this.slide=t,this.hasSlide=!0,this.instance=t.pswp}onLoaded(){this.state=u,this.slide&&this.element&&(this.instance.dispatch("loadComplete",{slide:this.slide,content:this}),this.slide.isActive&&this.slide.heavyAppended&&!this.element.parentNode&&(this.append(),this.slide.updateContentSize(!0)),this.state!==u&&this.state!==p||this.removePlaceholder())}onError(){this.state=p,this.slide&&(this.displayError(),this.instance.dispatch("loadComplete",{slide:this.slide,isError:!0,content:this}),this.instance.dispatch("loadError",{slide:this.slide,content:this}))}isLoading(){return this.instance.applyFilters("isContentLoading",this.state===d,this)}isError(){return this.state===p}isImageContent(){return"image"===this.type}setDisplayedSize(t,i){if(this.element&&(this.placeholder&&this.placeholder.setDisplayedSize(t,i),!this.instance.dispatch("contentResize",{content:this,width:t,height:i}).defaultPrevented&&(l(this.element,t,i),this.isImageContent()&&!this.isError()))){const s=!this.displayedImageWidth&&t;this.displayedImageWidth=t,this.displayedImageHeight=i,s?this.loadImage(!1):this.updateSrcsetSizes(),this.slide&&this.instance.dispatch("imageSizeChange",{slide:this.slide,width:t,height:i,content:this})}}isZoomable(){return this.instance.applyFilters("isContentZoomable",this.isImageContent()&&this.state!==p,this)}updateSrcsetSizes(){if(!this.isImageContent()||!this.element||!this.data.srcset)return;const t=this.element,i=this.instance.applyFilters("srcsetSizesWidth",this.displayedImageWidth,this);(!t.dataset.largestUsedSize||i>parseInt(t.dataset.largestUsedSize,10))&&(t.sizes=i+"px",t.dataset.largestUsedSize=String(i))}usePlaceholder(){return this.instance.applyFilters("useContentPlaceholder",this.isImageContent(),this)}lazyLoad(){this.instance.dispatch("contentLazyLoad",{content:this}).defaultPrevented||this.load(!0)}keepPlaceholder(){return this.instance.applyFilters("isKeepingPlaceholder",this.isLoading(),this)}destroy(){this.hasSlide=!1,this.slide=void 0,this.instance.dispatch("contentDestroy",{content:this}).defaultPrevented||(this.remove(),this.placeholder&&(this.placeholder.destroy(),this.placeholder=void 0),this.isImageContent()&&this.element&&(this.element.onload=null,this.element.onerror=null,this.element=void 0))}displayError(){if(this.slide){var i,s;let h=t("pswp__error-msg","div");h.innerText=null!==(i=null===(s=this.instance.options)||void 0===s?void 0:s.errorMsg)&&void 0!==i?i:"",h=this.instance.applyFilters("contentErrorElement",h,this),this.element=t("pswp__content pswp__error-msg-container","div"),this.element.appendChild(h),this.slide.container.innerText="",this.slide.container.appendChild(this.element),this.slide.updateContentSize(!0),this.removePlaceholder()}}append(){if(this.isAttached||!this.element)return;if(this.isAttached=!0,this.state===p)return void this.displayError();if(this.instance.dispatch("contentAppend",{content:this}).defaultPrevented)return;const t="decode"in this.element;this.isImageContent()?t&&this.slide&&(!this.slide.isActive||m())?(this.isDecoding=!0,this.element.decode().catch((()=>{})).finally((()=>{this.isDecoding=!1,this.appendImage()}))):this.appendImage():this.slide&&!this.element.parentNode&&this.slide.container.appendChild(this.element)}activate(){!this.instance.dispatch("contentActivate",{content:this}).defaultPrevented&&this.slide&&(this.isImageContent()&&this.isDecoding&&!m()?this.appendImage():this.isError()&&this.load(!1,!0),this.slide.holderElement&&this.slide.holderElement.setAttribute("aria-hidden","false"))}deactivate(){this.instance.dispatch("contentDeactivate",{content:this}),this.slide&&this.slide.holderElement&&this.slide.holderElement.setAttribute("aria-hidden","true")}remove(){this.isAttached=!1,this.instance.dispatch("contentRemove",{content:this}).defaultPrevented||(this.element&&this.element.parentNode&&this.element.remove(),this.placeholder&&this.placeholder.element&&this.placeholder.element.remove())}appendImage(){this.isAttached&&(this.instance.dispatch("contentAppendImage",{content:this}).defaultPrevented||(this.slide&&this.element&&!this.element.parentNode&&this.slide.container.appendChild(this.element),this.state!==u&&this.state!==p||this.removePlaceholder()))}}function J(t,i,s){const h=i.createContentFromData(t,s);let e;const{options:n}=i;if(n){let o;e=new x(n,t,-1),o=i.pswp?i.pswp.viewportSize:w(n,i);const r=y(n,o,t,s);e.update(h.width,h.height,r)}return h.lazyLoad(),e&&h.setDisplayedSize(Math.ceil(h.width*e.initial),Math.ceil(h.height*e.initial)),h}class Q{constructor(t){this.pswp=t,this.limit=Math.max(t.options.preload[0]+t.options.preload[1]+1,5),this.Dt=[]}updateLazy(t){const{pswp:i}=this;if(i.dispatch("lazyLoad").defaultPrevented)return;const{preload:s}=i.options,h=void 0===t||t>=0;let e;for(e=0;e<=s[1];e++)this.loadSlideByIndex(i.currIndex+(h?e:-e));for(e=1;e<=s[0];e++)this.loadSlideByIndex(i.currIndex+(h?-e:e))}loadSlideByIndex(t){const i=this.pswp.getLoopedIndex(t);let s=this.getContentByIndex(i);s||(s=function(t,i){const s=i.getItemData(t);if(!i.dispatch("lazyLoadSlide",{index:t,itemData:s}).defaultPrevented)return J(s,i,t)}(i,this.pswp),s&&this.addToCache(s))}getContentBySlide(t){let i=this.getContentByIndex(t.index);return i||(i=this.pswp.createContentFromData(t.data,t.index),this.addToCache(i)),i.setSlide(t),i}addToCache(t){if(this.removeByIndex(t.index),this.Dt.push(t),this.Dt.length>this.limit){const t=this.Dt.findIndex((t=>!t.isAttached&&!t.hasSlide));if(-1!==t){this.Dt.splice(t,1)[0].destroy()}}}removeByIndex(t){const i=this.Dt.findIndex((i=>i.index===t));-1!==i&&this.Dt.splice(i,1)}getContentByIndex(t){return this.Dt.find((i=>i.index===t))}destroy(){this.Dt.forEach((t=>t.destroy())),this.Dt=[]}}const tt=.003;class it{constructor(t){this.pswp=t,this.isClosed=!0,this.isOpen=!1,this.isClosing=!1,this.isOpening=!1,this.It=void 0,this.Et=!1,this.Lt=!1,this.kt=!1,this.Zt=!1,this.Bt=void 0,this.Ft=void 0,this.Ot=void 0,this.Rt=void 0,this.Nt=void 0,this.Ut=this.Ut.bind(this),t.on("firstZoomPan",this.Ut)}open(){this.Ut(),this.Pt()}close(){if(this.isClosed||this.isClosing||this.isOpening)return;const t=this.pswp.currSlide;this.isOpen=!1,this.isOpening=!1,this.isClosing=!0,this.It=this.pswp.options.hideAnimationDuration,t&&t.currZoomLevel*t.width>=this.pswp.options.maxWidthToAnimate&&(this.It=0),this.Vt(),setTimeout((()=>{this.Pt()}),this.Lt?30:0)}Ut(){if(this.pswp.off("firstZoomPan",this.Ut),!this.isOpening){const t=this.pswp.currSlide;this.isOpening=!0,this.isClosing=!1,this.It=this.pswp.options.showAnimationDuration,t&&t.zoomLevels.initial*t.width>=this.pswp.options.maxWidthToAnimate&&(this.It=0),this.Vt()}}Vt(){const{pswp:t}=this,i=this.pswp.currSlide,{options:s}=t;var h,e;("fade"===s.showHideAnimationType?(s.showHideOpacity=!0,this.Nt=void 0):"none"===s.showHideAnimationType?(s.showHideOpacity=!1,this.It=0,this.Nt=void 0):this.isOpening&&t.Gt?this.Nt=t.Gt:this.Nt=this.pswp.getThumbBounds(),this.Bt=null==i?void 0:i.getPlaceholderElement(),t.animations.stopAll(),this.Et=Boolean(this.It&&this.It>50),this.$t=Boolean(this.Nt)&&(null==i?void 0:i.content.usePlaceholder())&&(!this.isClosing||!t.mainScroll.isShifted()),this.$t)?this.kt=null!==(h=s.showHideOpacity)&&void 0!==h&&h:(this.kt=!0,this.isOpening&&i&&(i.zoomAndPanToInitial(),i.applyCurrentZoomPan()));if(this.Zt=!this.kt&&this.pswp.options.bgOpacity>tt,this.Ft=this.kt?t.element:t.bg,!this.Et)return this.It=0,this.$t=!1,this.Zt=!1,this.kt=!0,void(this.isOpening&&(t.element&&(t.element.style.opacity=String(tt)),t.applyBgOpacity(1)));this.$t&&this.Nt&&this.Nt.innerRect?(this.Lt=!0,this.Ot=this.pswp.container,this.Rt=null===(e=this.pswp.currSlide)||void 0===e?void 0:e.holderElement,t.container&&(t.container.style.overflow="hidden",t.container.style.width=t.viewportSize.x+"px")):this.Lt=!1;this.isOpening?(this.kt?(t.element&&(t.element.style.opacity=String(tt)),t.applyBgOpacity(1)):(this.Zt&&t.bg&&(t.bg.style.opacity=String(tt)),t.element&&(t.element.style.opacity="1")),this.$t&&(this.qt(),this.Bt&&(this.Bt.style.willChange="transform",this.Bt.style.opacity=String(tt)))):this.isClosing&&(t.mainScroll.itemHolders[0]&&(t.mainScroll.itemHolders[0].el.style.display="none"),t.mainScroll.itemHolders[2]&&(t.mainScroll.itemHolders[2].el.style.display="none"),this.Lt&&0!==t.mainScroll.x&&(t.mainScroll.resetPosition(),t.mainScroll.resize()))}Pt(){this.isOpening&&this.Et&&this.Bt&&"IMG"===this.Bt.tagName?new Promise((t=>{let i=!1,s=!0;var h;(h=this.Bt,"decode"in h?h.decode().catch((()=>{})):h.complete?Promise.resolve(h):new Promise(((t,i)=>{h.onload=()=>t(h),h.onerror=i}))).finally((()=>{i=!0,s||t(!0)})),setTimeout((()=>{s=!1,i&&t(!0)}),50),setTimeout(t,250)})).finally((()=>this.Ht())):this.Ht()}Ht(){var t,i;null===(t=this.pswp.element)||void 0===t||t.style.setProperty("--pswp-transition-duration",this.It+"ms"),this.pswp.dispatch(this.isOpening?"openingAnimationStart":"closingAnimationStart"),this.pswp.dispatch("initialZoom"+(this.isOpening?"In":"Out")),null===(i=this.pswp.element)||void 0===i||i.classList.toggle("pswp--ui-visible",this.isOpening),this.isOpening?(this.Bt&&(this.Bt.style.opacity="1"),this.Kt()):this.isClosing&&this.Wt(),this.Et||this.jt()}jt(){const{pswp:t}=this;if(this.isOpen=this.isOpening,this.isClosed=this.isClosing,this.isOpening=!1,this.isClosing=!1,t.dispatch(this.isOpen?"openingAnimationEnd":"closingAnimationEnd"),t.dispatch("initialZoom"+(this.isOpen?"InEnd":"OutEnd")),this.isClosed)t.destroy();else if(this.isOpen){var i;this.$t&&t.container&&(t.container.style.overflow="visible",t.container.style.width="100%"),null===(i=t.currSlide)||void 0===i||i.applyCurrentZoomPan()}}Kt(){const{pswp:t}=this;this.$t&&(this.Lt&&this.Ot&&this.Rt&&(this.Xt(this.Ot,"transform","translate3d(0,0,0)"),this.Xt(this.Rt,"transform","none")),t.currSlide&&(t.currSlide.zoomAndPanToInitial(),this.Xt(t.currSlide.container,"transform",t.currSlide.getCurrentTransform()))),this.Zt&&t.bg&&this.Xt(t.bg,"opacity",String(t.options.bgOpacity)),this.kt&&t.element&&this.Xt(t.element,"opacity","1")}Wt(){const{pswp:t}=this;this.$t&&this.qt(!0),this.Zt&&t.bgOpacity>.01&&t.bg&&this.Xt(t.bg,"opacity","0"),this.kt&&t.element&&this.Xt(t.element,"opacity","0")}qt(t){if(!this.Nt)return;const{pswp:s}=this,{innerRect:h}=this.Nt,{currSlide:e,viewportSize:n}=s;if(this.Lt&&h&&this.Ot&&this.Rt){const i=-n.x+(this.Nt.x-h.x)+h.w,s=-n.y+(this.Nt.y-h.y)+h.h,e=n.x-h.w,a=n.y-h.h;t?(this.Xt(this.Ot,"transform",o(i,s)),this.Xt(this.Rt,"transform",o(e,a))):(r(this.Ot,i,s),r(this.Rt,e,a))}e&&(i(e.pan,h||this.Nt),e.currZoomLevel=this.Nt.w/e.width,t?this.Xt(e.container,"transform",e.getCurrentTransform()):e.applyCurrentZoomPan())}Xt(t,i,s){if(!this.It)return void(t.style[i]=s);const{animations:h}=this.pswp,e={duration:this.It,easing:this.pswp.options.easing,onComplete:()=>{h.activeAnimations.length||this.jt()},target:t};e[i]=s,h.startTransition(e)}}const st={allowPanToNext:!0,spacing:.1,loop:!0,pinchToClose:!0,closeOnVerticalDrag:!0,hideAnimationDuration:333,showAnimationDuration:333,zoomAnimationDuration:333,escKey:!0,arrowKeys:!0,trapFocus:!0,returnFocus:!0,maxWidthToAnimate:4e3,clickToCloseNonZoomable:!0,imageClickAction:"zoom-or-close",bgClickAction:"close",tapAction:"toggle-controls",doubleTapAction:"zoom",indexIndicatorSep:" / ",preloaderDelay:2e3,bgOpacity:.8,index:0,errorMsg:"The image cannot be loaded",preload:[1,2],easing:"cubic-bezier(.4,0,.22,1)"};return class extends class extends class{constructor(){this.Yt={},this.Jt={},this.pswp=void 0,this.options=void 0}addFilter(t,i,s=100){var h,e,n;this.Jt[t]||(this.Jt[t]=[]),null===(h=this.Jt[t])||void 0===h||h.push({fn:i,priority:s}),null===(e=this.Jt[t])||void 0===e||e.sort(((t,i)=>t.priority-i.priority)),null===(n=this.pswp)||void 0===n||n.addFilter(t,i,s)}removeFilter(t,i){this.Jt[t]&&(this.Jt[t]=this.Jt[t].filter((t=>t.fn!==i))),this.pswp&&this.pswp.removeFilter(t,i)}applyFilters(t,...i){var s;return null===(s=this.Jt[t])||void 0===s||s.forEach((t=>{i[0]=t.fn.apply(this,i)})),i[0]}on(t,i){var s,h;this.Yt[t]||(this.Yt[t]=[]),null===(s=this.Yt[t])||void 0===s||s.push(i),null===(h=this.pswp)||void 0===h||h.on(t,i)}off(t,i){var s;this.Yt[t]&&(this.Yt[t]=this.Yt[t].filter((t=>i!==t))),null===(s=this.pswp)||void 0===s||s.off(t,i)}dispatch(t,i){var s;if(this.pswp)return this.pswp.dispatch(t,i);const h=new j(t,i);return null===(s=this.Yt[t])||void 0===s||s.forEach((t=>{t.call(this,h)})),h}}{getNumItems(){var t;let i=0;const s=null===(t=this.options)||void 0===t?void 0:t.dataSource;s&&"length"in s?i=s.length:s&&"gallery"in s&&(s.items||(s.items=this.Qt(s.gallery)),s.items&&(i=s.items.length));const h=this.dispatch("numItems",{dataSource:s,numItems:i});return this.applyFilters("numItems",h.numItems,s)}createContentFromData(t,i){return new Y(t,this,i)}getItemData(t){var i;const s=null===(i=this.options)||void 0===i?void 0:i.dataSource;let h={};Array.isArray(s)?h=s[t]:s&&"gallery"in s&&(s.items||(s.items=this.Qt(s.gallery)),h=s.items[t]);let e=h;e instanceof Element&&(e=this.ti(e));const n=this.dispatch("itemData",{itemData:e||{},index:t});return this.applyFilters("itemData",n.itemData,t)}Qt(t){var i,s;return null!==(i=this.options)&&void 0!==i&&i.children||null!==(s=this.options)&&void 0!==s&&s.childSelector?function(t,i,s=document){let h=[];if(t instanceof Element)h=[t];else if(t instanceof NodeList||Array.isArray(t))h=Array.from(t);else{const e="string"==typeof t?t:i;e&&(h=Array.from(s.querySelectorAll(e)))}return h}(this.options.children,this.options.childSelector,t)||[]:[t]}ti(t){const i={element:t},s="A"===t.tagName?t:t.querySelector("a");if(s){i.src=s.dataset.pswpSrc||s.href,s.dataset.pswpSrcset&&(i.srcset=s.dataset.pswpSrcset),i.width=s.dataset.pswpWidth?parseInt(s.dataset.pswpWidth,10):0,i.height=s.dataset.pswpHeight?parseInt(s.dataset.pswpHeight,10):0,i.w=i.width,i.h=i.height,s.dataset.pswpType&&(i.type=s.dataset.pswpType);const e=t.querySelector("img");var h;if(e)i.msrc=e.currentSrc||e.src,i.alt=null!==(h=e.getAttribute("alt"))&&void 0!==h?h:"";(s.dataset.pswpCropped||s.dataset.cropped)&&(i.thumbCropped=!0)}return this.applyFilters("domItemData",i,t,s)}lazyLoadData(t,i){return J(t,this,i)}}{constructor(t){super(),this.options=this.ii(t||{}),this.offset={x:0,y:0},this.si={x:0,y:0},this.viewportSize={x:0,y:0},this.bgOpacity=1,this.currIndex=0,this.potentialIndex=0,this.isOpen=!1,this.isDestroying=!1,this.hasMouse=!1,this.hi={},this.Gt=void 0,this.topBar=void 0,this.element=void 0,this.template=void 0,this.container=void 0,this.scrollWrap=void 0,this.currSlide=void 0,this.events=new f,this.animations=new F,this.mainScroll=new A(this),this.gestures=new T(this),this.opener=new it(this),this.keyboard=new E(this),this.contentLoader=new Q(this)}init(){if(this.isOpen||this.isDestroying)return!1;this.isOpen=!0,this.dispatch("init"),this.dispatch("beforeOpen"),this.ei();let t="pswp--open";return this.gestures.supportsTouch&&(t+=" pswp--touch"),this.options.mainClass&&(t+=" "+this.options.mainClass),this.element&&(this.element.className+=" "+t),this.currIndex=this.options.index||0,this.potentialIndex=this.currIndex,this.dispatch("firstUpdate"),this.scrollWheel=new O(this),(Number.isNaN(this.currIndex)||this.currIndex<0||this.currIndex>=this.getNumItems())&&(this.currIndex=0),this.gestures.supportsTouch||this.mouseDetected(),this.updateSize(),this.offset.y=window.pageYOffset,this.hi=this.getItemData(this.currIndex),this.dispatch("gettingData",{index:this.currIndex,data:this.hi,slide:void 0}),this.Gt=this.getThumbBounds(),this.dispatch("initialLayout"),this.on("openingAnimationEnd",(()=>{const{itemHolders:t}=this.mainScroll;t[0]&&(t[0].el.style.display="block",this.setContent(t[0],this.currIndex-1)),t[2]&&(t[2].el.style.display="block",this.setContent(t[2],this.currIndex+1)),this.appendHeavy(),this.contentLoader.updateLazy(),this.events.add(window,"resize",this.ni.bind(this)),this.events.add(window,"scroll",this.oi.bind(this)),this.dispatch("bindEvents")})),this.mainScroll.itemHolders[1]&&this.setContent(this.mainScroll.itemHolders[1],this.currIndex),this.dispatch("change"),this.opener.open(),this.dispatch("afterInit"),!0}getLoopedIndex(t){const i=this.getNumItems();return this.options.loop&&(t>i-1&&(t-=i),t<0&&(t+=i)),n(t,0,i-1)}appendHeavy(){this.mainScroll.itemHolders.forEach((t=>{var i;null===(i=t.slide)||void 0===i||i.appendHeavy()}))}goTo(t){this.mainScroll.moveIndexBy(this.getLoopedIndex(t)-this.potentialIndex)}next(){this.goTo(this.potentialIndex+1)}prev(){this.goTo(this.potentialIndex-1)}zoomTo(...t){var i;null===(i=this.currSlide)||void 0===i||i.zoomTo(...t)}toggleZoom(){var t;null===(t=this.currSlide)||void 0===t||t.toggleZoom()}close(){this.opener.isOpen&&!this.isDestroying&&(this.isDestroying=!0,this.dispatch("close"),this.events.removeAll(),this.opener.close())}destroy(){var t;if(!this.isDestroying)return this.options.showHideAnimationType="none",void this.close();this.dispatch("destroy"),this.Yt={},this.scrollWrap&&(this.scrollWrap.ontouchmove=null,this.scrollWrap.ontouchend=null),null===(t=this.element)||void 0===t||t.remove(),this.mainScroll.itemHolders.forEach((t=>{var i;null===(i=t.slide)||void 0===i||i.destroy()})),this.contentLoader.destroy(),this.events.removeAll()}refreshSlideContent(t){this.contentLoader.removeByIndex(t),this.mainScroll.itemHolders.forEach(((i,s)=>{var h,e;let n=(null!==(h=null===(e=this.currSlide)||void 0===e?void 0:e.index)&&void 0!==h?h:0)-1+s;var o;(this.canLoop()&&(n=this.getLoopedIndex(n)),n===t)&&(this.setContent(i,t,!0),1===s&&(this.currSlide=i.slide,null===(o=i.slide)||void 0===o||o.setIsActive(!0)))})),this.dispatch("change")}setContent(t,i,s){if(this.canLoop()&&(i=this.getLoopedIndex(i)),t.slide){if(t.slide.index===i&&!s)return;t.slide.destroy(),t.slide=void 0}if(!this.canLoop()&&(i<0||i>=this.getNumItems()))return;const h=this.getItemData(i);t.slide=new b(h,i,this),i===this.currIndex&&(this.currSlide=t.slide),t.slide.append(t.el)}getViewportCenterPoint(){return{x:this.viewportSize.x/2,y:this.viewportSize.y/2}}updateSize(t){if(this.isDestroying)return;const s=w(this.options,this);!t&&e(s,this.si)||(i(this.si,s),this.dispatch("beforeResize"),i(this.viewportSize,this.si),this.oi(),this.dispatch("viewportSize"),this.mainScroll.resize(this.opener.isOpen),!this.hasMouse&&window.matchMedia("(any-hover: hover)").matches&&this.mouseDetected(),this.dispatch("resize"))}applyBgOpacity(t){this.bgOpacity=Math.max(t,0),this.bg&&(this.bg.style.opacity=String(this.bgOpacity*this.options.bgOpacity))}mouseDetected(){var t;this.hasMouse||(this.hasMouse=!0,null===(t=this.element)||void 0===t||t.classList.add("pswp--has_mouse"))}ni(){this.updateSize(),/iPhone|iPad|iPod/i.test(window.navigator.userAgent)&&setTimeout((()=>{this.updateSize()}),500)}oi(){this.setScrollOffset(0,window.pageYOffset)}setScrollOffset(t,i){this.offset.x=t,this.offset.y=i,this.dispatch("updateScrollOffset")}ei(){this.element=t("pswp","div"),this.element.setAttribute("tabindex","-1"),this.element.setAttribute("role","dialog"),this.template=this.element,this.bg=t("pswp__bg","div",this.element),this.scrollWrap=t("pswp__scroll-wrap","section",this.element),this.container=t("pswp__container","div",this.scrollWrap),this.scrollWrap.setAttribute("aria-roledescription","carousel"),this.container.setAttribute("aria-live","off"),this.container.setAttribute("id","pswp__items"),this.mainScroll.appendHolders(),this.ui=new W(this),this.ui.init(),(this.options.appendToEl||document.body).appendChild(this.element)}getThumbBounds(){return function(t,i,s){const h=s.dispatch("thumbBounds",{index:t,itemData:i,instance:s});if(h.thumbBounds)return h.thumbBounds;const{element:e}=i;let n,o;if(e&&!1!==s.options.thumbSelector){const t=s.options.thumbSelector||"img";o=e.matches(t)?e:e.querySelector(t)}return o=s.applyFilters("thumbEl",o,i,t),o&&(n=i.thumbCropped?function(t,i,s){const h=t.getBoundingClientRect(),e=h.width/i,n=h.height/s,o=e>n?e:n,r=(h.width-i*o)/2,a=(h.height-s*o)/2,l={x:h.left+r,y:h.top+a,w:i*o};return l.innerRect={w:h.width,h:h.height,x:r,y:a},l}(o,i.width||i.w||0,i.height||i.h||0):function(t){const i=t.getBoundingClientRect();return{x:i.left,y:i.top,w:i.width}}(o)),s.applyFilters("thumbBounds",n,i,t)}(this.currIndex,this.currSlide?this.currSlide.data:this.hi,this)}canLoop(){return this.options.loop&&this.getNumItems()>2}ii(t){return window.matchMedia("(prefers-reduced-motion), (update: slow)").matches&&(t.showHideAnimationType="none",t.zoomAnimationDuration=0),{...st,...t}}}}));
//umd














/*!
  * PhotoSwipe Lightbox 5.4.4 - https://photoswipe.com
  * (c) 2024 Dmytro Semenov
  */
!function(t,i){"object"==typeof exports&&"undefined"!=typeof module?module.exports=i():"function"==typeof define&&define.amd?define(i):(t="undefined"!=typeof globalThis?globalThis:t||self).PhotoSwipeLightbox=i()}(this,(function(){"use strict";function t(t,i,s){const h=document.createElement(i);return t&&(h.className=t),s&&s.appendChild(h),h}function i(t,i,s){t.style.width="number"==typeof i?`${i}px`:i,t.style.height="number"==typeof s?`${s}px`:s}const s="idle",h="loading",e="loaded",n="error";function o(t,i,s=document){let h=[];if(t instanceof Element)h=[t];else if(t instanceof NodeList||Array.isArray(t))h=Array.from(t);else{const e="string"==typeof t?t:i;e&&(h=Array.from(s.querySelectorAll(e)))}return h}function r(){return!(!navigator.vendor||!navigator.vendor.match(/apple/i))}class l{constructor(t,i){this.type=t,this.defaultPrevented=!1,i&&Object.assign(this,i)}preventDefault(){this.defaultPrevented=!0}}class a{constructor(i,s){if(this.element=t("pswp__img pswp__img--placeholder",i?"img":"div",s),i){const t=this.element;t.decoding="async",t.alt="",t.src=i,t.setAttribute("role","presentation")}this.element.setAttribute("aria-hidden","true")}setDisplayedSize(t,s){this.element&&("IMG"===this.element.tagName?(i(this.element,250,"auto"),this.element.style.transformOrigin="0 0",this.element.style.transform=function(t,i,s){let h=`translate3d(${t}px,${i||0}px,0)`;return void 0!==s&&(h+=` scale3d(${s},${s},1)`),h}(0,0,t/250)):i(this.element,t,s))}destroy(){var t;null!==(t=this.element)&&void 0!==t&&t.parentNode&&this.element.remove(),this.element=null}}class d{constructor(t,i,h){this.instance=i,this.data=t,this.index=h,this.element=void 0,this.placeholder=void 0,this.slide=void 0,this.displayedImageWidth=0,this.displayedImageHeight=0,this.width=Number(this.data.w)||Number(this.data.width)||0,this.height=Number(this.data.h)||Number(this.data.height)||0,this.isAttached=!1,this.hasSlide=!1,this.isDecoding=!1,this.state=s,this.data.type?this.type=this.data.type:this.data.src?this.type="image":this.type="html",this.instance.dispatch("contentInit",{content:this})}removePlaceholder(){this.placeholder&&!this.keepPlaceholder()&&setTimeout((()=>{this.placeholder&&(this.placeholder.destroy(),this.placeholder=void 0)}),1e3)}load(i,s){if(this.slide&&this.usePlaceholder())if(this.placeholder){const t=this.placeholder.element;t&&!t.parentElement&&this.slide.container.prepend(t)}else{const t=this.instance.applyFilters("placeholderSrc",!(!this.data.msrc||!this.slide.isFirstSlide)&&this.data.msrc,this);this.placeholder=new a(t,this.slide.container)}this.element&&!s||this.instance.dispatch("contentLoad",{content:this,isLazy:i}).defaultPrevented||(this.isImageContent()?(this.element=t("pswp__img","img"),this.displayedImageWidth&&this.loadImage(i)):(this.element=t("pswp__content","div"),this.element.innerHTML=this.data.html||""),s&&this.slide&&this.slide.updateContentSize(!0))}loadImage(t){var i,s;if(!this.isImageContent()||!this.element||this.instance.dispatch("contentLoadImage",{content:this,isLazy:t}).defaultPrevented)return;const e=this.element;this.updateSrcsetSizes(),this.data.srcset&&(e.srcset=this.data.srcset),e.src=null!==(i=this.data.src)&&void 0!==i?i:"",e.alt=null!==(s=this.data.alt)&&void 0!==s?s:"",this.state=h,e.complete?this.onLoaded():(e.onload=()=>{this.onLoaded()},e.onerror=()=>{this.onError()})}setSlide(t){this.slide=t,this.hasSlide=!0,this.instance=t.pswp}onLoaded(){this.state=e,this.slide&&this.element&&(this.instance.dispatch("loadComplete",{slide:this.slide,content:this}),this.slide.isActive&&this.slide.heavyAppended&&!this.element.parentNode&&(this.append(),this.slide.updateContentSize(!0)),this.state!==e&&this.state!==n||this.removePlaceholder())}onError(){this.state=n,this.slide&&(this.displayError(),this.instance.dispatch("loadComplete",{slide:this.slide,isError:!0,content:this}),this.instance.dispatch("loadError",{slide:this.slide,content:this}))}isLoading(){return this.instance.applyFilters("isContentLoading",this.state===h,this)}isError(){return this.state===n}isImageContent(){return"image"===this.type}setDisplayedSize(t,s){if(this.element&&(this.placeholder&&this.placeholder.setDisplayedSize(t,s),!this.instance.dispatch("contentResize",{content:this,width:t,height:s}).defaultPrevented&&(i(this.element,t,s),this.isImageContent()&&!this.isError()))){const i=!this.displayedImageWidth&&t;this.displayedImageWidth=t,this.displayedImageHeight=s,i?this.loadImage(!1):this.updateSrcsetSizes(),this.slide&&this.instance.dispatch("imageSizeChange",{slide:this.slide,width:t,height:s,content:this})}}isZoomable(){return this.instance.applyFilters("isContentZoomable",this.isImageContent()&&this.state!==n,this)}updateSrcsetSizes(){if(!this.isImageContent()||!this.element||!this.data.srcset)return;const t=this.element,i=this.instance.applyFilters("srcsetSizesWidth",this.displayedImageWidth,this);(!t.dataset.largestUsedSize||i>parseInt(t.dataset.largestUsedSize,10))&&(t.sizes=i+"px",t.dataset.largestUsedSize=String(i))}usePlaceholder(){return this.instance.applyFilters("useContentPlaceholder",this.isImageContent(),this)}lazyLoad(){this.instance.dispatch("contentLazyLoad",{content:this}).defaultPrevented||this.load(!0)}keepPlaceholder(){return this.instance.applyFilters("isKeepingPlaceholder",this.isLoading(),this)}destroy(){this.hasSlide=!1,this.slide=void 0,this.instance.dispatch("contentDestroy",{content:this}).defaultPrevented||(this.remove(),this.placeholder&&(this.placeholder.destroy(),this.placeholder=void 0),this.isImageContent()&&this.element&&(this.element.onload=null,this.element.onerror=null,this.element=void 0))}displayError(){if(this.slide){var i,s;let h=t("pswp__error-msg","div");h.innerText=null!==(i=null===(s=this.instance.options)||void 0===s?void 0:s.errorMsg)&&void 0!==i?i:"",h=this.instance.applyFilters("contentErrorElement",h,this),this.element=t("pswp__content pswp__error-msg-container","div"),this.element.appendChild(h),this.slide.container.innerText="",this.slide.container.appendChild(this.element),this.slide.updateContentSize(!0),this.removePlaceholder()}}append(){if(this.isAttached||!this.element)return;if(this.isAttached=!0,this.state===n)return void this.displayError();if(this.instance.dispatch("contentAppend",{content:this}).defaultPrevented)return;const t="decode"in this.element;this.isImageContent()?t&&this.slide&&(!this.slide.isActive||r())?(this.isDecoding=!0,this.element.decode().catch((()=>{})).finally((()=>{this.isDecoding=!1,this.appendImage()}))):this.appendImage():this.slide&&!this.element.parentNode&&this.slide.container.appendChild(this.element)}activate(){!this.instance.dispatch("contentActivate",{content:this}).defaultPrevented&&this.slide&&(this.isImageContent()&&this.isDecoding&&!r()?this.appendImage():this.isError()&&this.load(!1,!0),this.slide.holderElement&&this.slide.holderElement.setAttribute("aria-hidden","false"))}deactivate(){this.instance.dispatch("contentDeactivate",{content:this}),this.slide&&this.slide.holderElement&&this.slide.holderElement.setAttribute("aria-hidden","true")}remove(){this.isAttached=!1,this.instance.dispatch("contentRemove",{content:this}).defaultPrevented||(this.element&&this.element.parentNode&&this.element.remove(),this.placeholder&&this.placeholder.element&&this.placeholder.element.remove())}appendImage(){this.isAttached&&(this.instance.dispatch("contentAppendImage",{content:this}).defaultPrevented||(this.slide&&this.element&&!this.element.parentNode&&this.slide.container.appendChild(this.element),this.state!==e&&this.state!==n||this.removePlaceholder()))}}function c(t,i,s,h,e){let n=0;if(i.paddingFn)n=i.paddingFn(s,h,e)[t];else if(i.padding)n=i.padding[t];else{const s="padding"+t[0].toUpperCase()+t.slice(1);i[s]&&(n=i[s])}return Number(n)||0}class u{constructor(t,i,s,h){this.pswp=h,this.options=t,this.itemData=i,this.index=s,this.panAreaSize=null,this.elementSize=null,this.fit=1,this.fill=1,this.vFill=1,this.initial=1,this.secondary=1,this.max=1,this.min=1}update(t,i,s){const h={x:t,y:i};this.elementSize=h,this.panAreaSize=s;const e=s.x/h.x,n=s.y/h.y;this.fit=Math.min(1,e<n?e:n),this.fill=Math.min(1,e>n?e:n),this.vFill=Math.min(1,n),this.initial=this.t(),this.secondary=this.i(),this.max=Math.max(this.initial,this.secondary,this.o()),this.min=Math.min(this.fit,this.initial,this.secondary),this.pswp&&this.pswp.dispatch("zoomLevelsUpdate",{zoomLevels:this,slideData:this.itemData})}l(t){const i=t+"ZoomLevel",s=this.options[i];if(s)return"function"==typeof s?s(this):"fill"===s?this.fill:"fit"===s?this.fit:Number(s)}i(){let t=this.l("secondary");return t||(t=Math.min(1,3*this.fit),this.elementSize&&t*this.elementSize.x>4e3&&(t=4e3/this.elementSize.x),t)}t(){return this.l("initial")||this.fit}o(){return this.l("max")||Math.max(1,4*this.fit)}}function p(t,i,s){const h=i.createContentFromData(t,s);let e;const{options:n}=i;if(n){let o;e=new u(n,t,-1),o=i.pswp?i.pswp.viewportSize:function(t,i){if(t.getViewportSizeFn){const s=t.getViewportSizeFn(t,i);if(s)return s}return{x:document.documentElement.clientWidth,y:window.innerHeight}}(n,i);const r=function(t,i,s,h){return{x:i.x-c("left",t,i,s,h)-c("right",t,i,s,h),y:i.y-c("top",t,i,s,h)-c("bottom",t,i,s,h)}}(n,o,t,s);e.update(h.width,h.height,r)}return h.lazyLoad(),e&&h.setDisplayedSize(Math.ceil(h.width*e.initial),Math.ceil(h.height*e.initial)),h}return class extends class extends class{constructor(){this.u={},this.p={},this.pswp=void 0,this.options=void 0}addFilter(t,i,s=100){var h,e,n;this.p[t]||(this.p[t]=[]),null===(h=this.p[t])||void 0===h||h.push({fn:i,priority:s}),null===(e=this.p[t])||void 0===e||e.sort(((t,i)=>t.priority-i.priority)),null===(n=this.pswp)||void 0===n||n.addFilter(t,i,s)}removeFilter(t,i){this.p[t]&&(this.p[t]=this.p[t].filter((t=>t.fn!==i))),this.pswp&&this.pswp.removeFilter(t,i)}applyFilters(t,...i){var s;return null===(s=this.p[t])||void 0===s||s.forEach((t=>{i[0]=t.fn.apply(this,i)})),i[0]}on(t,i){var s,h;this.u[t]||(this.u[t]=[]),null===(s=this.u[t])||void 0===s||s.push(i),null===(h=this.pswp)||void 0===h||h.on(t,i)}off(t,i){var s;this.u[t]&&(this.u[t]=this.u[t].filter((t=>i!==t))),null===(s=this.pswp)||void 0===s||s.off(t,i)}dispatch(t,i){var s;if(this.pswp)return this.pswp.dispatch(t,i);const h=new l(t,i);return null===(s=this.u[t])||void 0===s||s.forEach((t=>{t.call(this,h)})),h}}{getNumItems(){var t;let i=0;const s=null===(t=this.options)||void 0===t?void 0:t.dataSource;s&&"length"in s?i=s.length:s&&"gallery"in s&&(s.items||(s.items=this.v(s.gallery)),s.items&&(i=s.items.length));const h=this.dispatch("numItems",{dataSource:s,numItems:i});return this.applyFilters("numItems",h.numItems,s)}createContentFromData(t,i){return new d(t,this,i)}getItemData(t){var i;const s=null===(i=this.options)||void 0===i?void 0:i.dataSource;let h={};Array.isArray(s)?h=s[t]:s&&"gallery"in s&&(s.items||(s.items=this.v(s.gallery)),h=s.items[t]);let e=h;e instanceof Element&&(e=this.m(e));const n=this.dispatch("itemData",{itemData:e||{},index:t});return this.applyFilters("itemData",n.itemData,t)}v(t){var i,s;return null!==(i=this.options)&&void 0!==i&&i.children||null!==(s=this.options)&&void 0!==s&&s.childSelector?o(this.options.children,this.options.childSelector,t)||[]:[t]}m(t){const i={element:t},s="A"===t.tagName?t:t.querySelector("a");if(s){i.src=s.dataset.pswpSrc||s.href,s.dataset.pswpSrcset&&(i.srcset=s.dataset.pswpSrcset),i.width=s.dataset.pswpWidth?parseInt(s.dataset.pswpWidth,10):0,i.height=s.dataset.pswpHeight?parseInt(s.dataset.pswpHeight,10):0,i.w=i.width,i.h=i.height,s.dataset.pswpType&&(i.type=s.dataset.pswpType);const e=t.querySelector("img");var h;if(e)i.msrc=e.currentSrc||e.src,i.alt=null!==(h=e.getAttribute("alt"))&&void 0!==h?h:"";(s.dataset.pswpCropped||s.dataset.cropped)&&(i.thumbCropped=!0)}return this.applyFilters("domItemData",i,t,s)}lazyLoadData(t,i){return p(t,this,i)}}{constructor(t){super(),this.options=t||{},this.g=0,this.shouldOpen=!1,this._=void 0,this.onThumbnailsClick=this.onThumbnailsClick.bind(this)}init(){o(this.options.gallery,this.options.gallerySelector).forEach((t=>{t.addEventListener("click",this.onThumbnailsClick,!1)}))}onThumbnailsClick(t){if(function(t){return"button"in t&&1===t.button||t.ctrlKey||t.metaKey||t.altKey||t.shiftKey}(t)||window.pswp)return;let i={x:t.clientX,y:t.clientY};i.x||i.y||(i=null);let s=this.getClickedIndex(t);s=this.applyFilters("clickedIndex",s,t,this);const h={gallery:t.currentTarget};s>=0&&(t.preventDefault(),this.loadAndOpen(s,h,i))}getClickedIndex(t){if(this.options.getClickedIndexFn)return this.options.getClickedIndexFn.call(this,t);const i=t.target,s=o(this.options.children,this.options.childSelector,t.currentTarget).findIndex((t=>t===i||t.contains(i)));return-1!==s?s:this.options.children||this.options.childSelector?-1:0}loadAndOpen(t,i,s){if(window.pswp||!this.options)return!1;if(!i&&this.options.gallery&&this.options.children){const t=o(this.options.gallery);t[0]&&(i={gallery:t[0]})}return this.options.index=t,this.options.initialPointerPos=s,this.shouldOpen=!0,this.preload(t,i),!0}preload(t,i){const{options:s}=this;i&&(s.dataSource=i);const h=[],e=typeof s.pswpModule;if("function"==typeof(n=s.pswpModule)&&n.prototype&&n.prototype.goTo)h.push(Promise.resolve(s.pswpModule));else{if("string"===e)throw new Error("pswpModule as string is no longer supported");if("function"!==e)throw new Error("pswpModule is not valid");h.push(s.pswpModule())}var n;"function"==typeof s.openPromise&&h.push(s.openPromise()),!1!==s.preloadFirstSlide&&t>=0&&(this._=function(t,i){const s=i.getItemData(t);if(!i.dispatch("lazyLoadSlide",{index:t,itemData:s}).defaultPrevented)return p(s,i,t)}(t,this));const o=++this.g;Promise.all(h).then((t=>{if(this.shouldOpen){const i=t[0];this.I(i,o)}}))}I(t,i){if(i!==this.g&&this.shouldOpen)return;if(this.shouldOpen=!1,window.pswp)return;const s="object"==typeof t?new t.default(this.options):new t(this.options);this.pswp=s,window.pswp=s,Object.keys(this.u).forEach((t=>{var i;null===(i=this.u[t])||void 0===i||i.forEach((i=>{s.on(t,i)}))})),Object.keys(this.p).forEach((t=>{var i;null===(i=this.p[t])||void 0===i||i.forEach((i=>{s.addFilter(t,i.fn,i.priority)}))})),this._&&(s.contentLoader.addToCache(this._),this._=void 0),s.on("destroy",(()=>{this.pswp=void 0,delete window.pswp})),s.init()}destroy(){var t;null===(t=this.pswp)||void 0===t||t.destroy(),this.shouldOpen=!1,this.u={},o(this.options.gallery,this.options.gallerySelector).forEach((t=>{t.removeEventListener("click",this.onThumbnailsClick,!1)}))}}}));
//umd












/*! Video plugin for the PhotoSwipe 1.0.2
https://github.com/dimsemenov/photoswipe-video-plugin */

function isVideoContent(e){return e&&e.data&&"video"===e.data.type}
class VideoContentSetup{constructor(e,t){this.options=t,this.initLightboxEvents(e),e.on("init",()=>{this.initPswpEvents(e.pswp)})}initLightboxEvents(e){e.on("contentLoad",this.onContentLoad.bind(this)),e.on("contentDestroy",this.onContentDestroy.bind(this)),e.on("contentActivate",this.onContentActivate.bind(this)),e.on("contentDeactivate",this.onContentDeactivate.bind(this)),e.on("contentAppend",this.onContentAppend.bind(this)),e.on("contentResize",this.onContentResize.bind(this)),e.addFilter("isKeepingPlaceholder",this.isKeepingPlaceholder.bind(this)),e.addFilter("isContentZoomable",this.isContentZoomable.bind(this)),e.addFilter("useContentPlaceholder",this.useContentPlaceholder.bind(this)),e.addFilter("domItemData",(e,t,o)=>("video"===e.type&&o&&(o.dataset.pswpVideoSources?e.videoSources=JSON.parse(pswpVideoSources):o.dataset.pswpVideoSrc?e.videoSrc=o.dataset.pswpVideoSrc:e.videoSrc=o.href),e))}initPswpEvents(e){e.on("pointerDown",t=>{let o=e.currSlide;if(isVideoContent(o)&&this.options.preventDragOffset){let n=t.originalEvent;if("pointerdown"===n.type){let i=Math.ceil(o.height*o.currZoomLevel),s=i+o.bounds.center.y,d=n.pageY-e.offset.y;d>s-this.options.preventDragOffset&&d<s&&t.preventDefault()}}}),e.on("appendHeavy",e=>{isVideoContent(e.slide)&&!e.slide.isActive&&e.preventDefault()}),e.on("close",()=>{isVideoContent(e.currSlide.content)&&(e.options.showHideAnimationType&&"zoom"!==e.options.showHideAnimationType||(e.options.showHideAnimationType="fade"),this.pauseVideo(e.currSlide.content))})}onContentDestroy({content:e}){isVideoContent(e)&&e._videoPosterImg&&(e._videoPosterImg.onload=e._videoPosterImg.onerror=null,e._videoPosterImg=null)}onContentResize(e){if(isVideoContent(e.content)){e.preventDefault();let t=e.width,o=e.height,n=e.content;if(n.element&&(n.element.style.width=t+"px",n.element.style.height=o+"px"),n.slide&&n.slide.placeholder){let i=n.slide.placeholder.element.style;i.transform="none",i.width=t+"px",i.height=o+"px"}}}isKeepingPlaceholder(e,t){return!isVideoContent(t)&&e}isContentZoomable(e,t){return!isVideoContent(t)&&e}onContentActivate({content:e}){isVideoContent(e)&&this.options.autoplay&&this.playVideo(e)}onContentDeactivate({content:e}){isVideoContent(e)&&this.pauseVideo(e)}onContentAppend(e){isVideoContent(e.content)&&(e.preventDefault(),e.content.isAttached=!0,e.content.appendImage())}onContentLoad(e){let t=e.content;if(isVideoContent(e.content)){if(e.preventDefault(),!t.element){if(t.state="loading",t.type="video",t.element=document.createElement("video"),this.options.videoAttributes)for(let o in this.options.videoAttributes)t.element.setAttribute(o,this.options.videoAttributes[o]||"");t.element.setAttribute("poster",t.data.msrc),this.preloadVideoPoster(t,t.data.msrc),t.element.style.position="absolute",t.element.style.left=0,t.element.style.top=0,t.data.videoSources?t.data.videoSources.forEach(e=>{let o=document.createElement("source");o.src=e.src,o.type=e.type,t.element.appendChild(o)}):t.data.videoSrc&&(t.element.src=t.data.videoSrc)}}}preloadVideoPoster(e,t){!e._videoPosterImg&&t&&(e._videoPosterImg=new Image,e._videoPosterImg.src=t,e._videoPosterImg.complete?e.onLoaded():e._videoPosterImg.onload=e._videoPosterImg.onerror=()=>{e.onLoaded()})}playVideo(e){e.element&&e.element.play()}pauseVideo(e){e.element&&e.element.pause()}useContentPlaceholder(e,t){return!!isVideoContent(t)||e}}class PhotoSwipeVideoPlugin{constructor(e,t){new VideoContentSetup(e,{videoAttributes:{controls:"",playsinline:"",preload:"auto"},autoplay:!0,preventDragOffset:40,...t})}}
//export { PhotoSwipeVideoPlugin as default };





/*! Auto hide UI for PhotoSwipe 1.0.1
https://github.com/arnowelzel/photoswipe-auto-hide-ui */


class PhotoSwipeAutoHideUI{constructor(lightbox,options){this.options={...{idleTime:4000},...options};this.captionTimer=!1;this.lightbox=lightbox;this.hasTouch=!1;this.lightbox.on('change',()=>{document.addEventListener('touchstart',()=>{this.stopHideTimer();this.hasTouch=!0},{once:!0})
document.addEventListener('mousemove',()=>{this.startHideTimer()},{once:!0})});this.lightbox.on('destroy',()=>{this.stopHideTimer()})}
showUI(){if(this.lightbox&&this.lightbox.pswp&&this.lightbox.pswp.element){this.lightbox.pswp.element.classList.add('pswp--ui-visible')}}
hideUI(){if(this.lightbox&&this.lightbox.pswp&&this.lightbox.pswp.element){this.lightbox.pswp.element.classList.remove('pswp--ui-visible')}}
mouseMove(){this.stopHideTimer();if(this.lightbox){this.showUI();this.startHideTimer()}}
startHideTimer(){if(this.hasTouch){return}
this.stopHideTimer();this.captionTimer=window.setTimeout(()=>{this.hideUI()},this.options.idleTime);document.addEventListener('mousemove',()=>{this.mouseMove()},{once:!0})}
stopHideTimer(){if(this.captionTimer){window.clearTimeout(this.captionTimer);this.captionTimer=!1}}}








/*! PhotoSwipe slideshow plugin v2.0
https://github.com/junkfix/photoswipe-slideshow */

class PhotoSwipeSlideshow{constructor(e,s){this.lightbox=e,this.options=Object.assign({defaultDelayMs:4e3,playPauseButtonOrder:6,progressBarPosition:"top",progressBarTransition:"ease",restartOnSlideChange:!1,autoHideProgressBar:!0},s),this.setSlideshowLength(Number(localStorage.getItem("pswp_delay"))||this.options.defaultDelayMs),document.head.insertAdjacentHTML("beforeend",`<style>.pswp__progress-bar{position:fixed;${this.options.progressBarPosition}:0;width:0;height:0}.pswp__progress-bar.running{width:100%;height:3px;transition-property:width;background:#c00}</style>`),this.slideshowIsRunning=!1,this.slideshowTimerID=null,this.wakeLockIsRunning=!1,this.wakeLockSentinel=null,this.lightbox.on("init",(()=>{this.init(this.lightbox.pswp)}))}init(e){e.on("uiRegister",(()=>{e.ui.registerElement({name:"playpause-button",title:"Toggle slideshow (Space)\nChange delay with +/- while running",order:this.options.playPauseButtonOrder,isButton:!0,html:'<svg aria-hidden="true" class="pswp__icn" viewBox="0 0 32 32"><use class="pswp__icn-shadow" xlink:href="#pswp__icn-play"/><use class="pswp__icn-shadow" xlink:href="#pswp__icn-stop"/><path id="pswp__icn-play" d="M9.5 6.4c-.7-.4-1.6-.4-2.3-0S6 7.5 6 8.2V23.9c0 .8.5 1.5 1.2 1.9s1.6.4 2.3-0l13.8-7.8a2.3 2.1 0 000-3.7z"/><path id="pswp__icn-stop" style="display:none" d="M6 9A3 3 90 019 6H23A3 3 90 0126 9V23a3 3 90 01-3 3H9A3 3 90 016 23z"/></svg>',onClick:(e,s)=>{this.setSlideshowState()}}),e.ui.registerElement({name:"playtime",appendTo:"wrapper",tagName:"div",className:"pswp__progress-bar"}),e.events.add(document,"keydown",(e=>{switch(e.code){case"Space":this.setSlideshowState(),e.preventDefault();break;case"ArrowUp":case"NumpadAdd":case"Equal":this.changeSlideshowLength(1e3),e.preventDefault();break;case"ArrowDown":case"NumpadSubtract":case"Minus":this.changeSlideshowLength(-1e3),e.preventDefault()}}))})),this.lightbox.on("change",(()=>{this.slideshowIsRunning&&this.options.restartOnSlideChange&&this.goToNextSlideAfterTimeout()})),this.lightbox.on("close",(()=>{this.slideshowIsRunning&&this.setSlideshowState()}))}setSlideshowState(){this.slideshowIsRunning=!this.slideshowIsRunning,this.slideshowIsRunning?this.goToNextSlideAfterTimeout():this.resetSlideshow(),document.querySelector("#pswp__icn-stop").style.display=this.slideshowIsRunning?"inline":"none",document.querySelector("#pswp__icn-play").style.display=this.slideshowIsRunning?"none":"inline",document.querySelector(".pswp__progress-bar").style.opacity=this.options.autoHideProgressBar?null:1,this.toggleWakeLock()}setSlideshowLength(e){this.options.defaultDelayMs=Math.min(Math.max(e,1e3),2147483647),this.options.defaultDelayMs!=e&&localStorage.setItem("pswp_delay",this.options.defaultDelayMs)}changeSlideshowLength(e){if(!this.slideshowIsRunning)return;this.setSlideshowLength(this.options.defaultDelayMs+e),localStorage.setItem("pswp_delay",this.options.defaultDelayMs);const s=document.querySelector(".pswp__counter");s&&(s.innerHTML=this.options.defaultDelayMs/1e3+"s"),this.goToNextSlideAfterTimeout()}isVideoContent(e){return"video"===e?.data?.type}getSlideTimeout(){const e=pswp.currSlide.content;if(this.isVideoContent(e)){const s=e.element;if(s.paused)return this.options.defaultDelayMs;const t=s.duration,i=s.currentTime;return isNaN(t)||isNaN(i)?this.options.defaultDelayMs:1e3*(t-i)}return this.options.defaultDelayMs}slideContentHasLoaded(){const e=pswp.currSlide.content;if(this.isVideoContent(e)){const s=e.element;return s.ended||s.readyState===HTMLMediaElement.HAVE_ENOUGH_DATA&&[HTMLMediaElement.NETWORK_IDLE,HTMLMediaElement.NETWORK_LOADING].includes(s.networkState)}return!e.isLoading()}goToNextSlideAfterTimeout(){if(this.resetSlideshow(),this.slideContentHasLoaded()){const e=this.getSlideTimeout();this.slideshowTimerID=setTimeout((()=>{pswp.next(),this.options.restartOnSlideChange||this.goToNextSlideAfterTimeout()}),e),setTimeout((()=>{this.slideshowIsRunning&&this.toggleProgressBar(e)}),100)}else this.slideshowTimerID=setTimeout((()=>{this.goToNextSlideAfterTimeout()}),200)}getSlideTransition(){return this.isVideoContent(pswp.currSlide.content)?"linear":this.options.progressBarTransition}toggleProgressBar(e){const s=document.querySelector(".pswp__progress-bar");e?(s.style.transitionTimingFunction=this.getSlideTransition(),s.style.transitionDuration=`${e}ms`,s.classList.add("running")):s.classList.remove("running")}toggleWakeLock(){this.wakeLockIsRunning!=this.slideshowIsRunning&&("keepAwake"in screen?screen.keepAwake=this.slideshowIsRunning:"wakeLock"in navigator&&(this.wakeLockSentinel?this.wakeLockSentinel.release().then((()=>{this.wakeLockSentinel=null})):navigator.wakeLock.request("screen").then((e=>{this.wakeLockSentinel=e,this.wakeLockSentinel.addEventListener("release",(()=>{this.wakeLockSentinel=null,this.wakeLockIsRunning=!1}))})).catch((e=>{}))),this.wakeLockIsRunning=this.slideshowIsRunning)}resetSlideshow(){this.toggleProgressBar(),this.slideshowTimerID&&(clearTimeout(this.slideshowTimerID),this.slideshowTimerID=null)}}




/*! Fullscreen for PhotoSwipe 1.0.5
https://github.com/arnowelzel/photoswipe-fullscreen */

class PhotoSwipeFullscreen{constructor(e){this.lightbox=e,this.lightbox.on("init",()=>{this.initPlugin(this.lightbox.pswp)})}initPlugin(e){this.fullscreenAPI=this.getFullscreenAPI(),this.fullscreenAPI&&e.on("uiRegister",()=>{e.ui.registerElement({name:"fullscreen-button",title:"Fullscreen [F]",order:19,isButton:!0,html:'<svg aria-hidden="true" class="pswp__icn" viewBox="0 0 32 32" width="32" height="32"><use class="pswp__icn-shadow" xlink:href="#pswp__icn-fullscreen-exit"/><use class="pswp__icn-shadow" xlink:href="#pswp__icn-fullscreen-request"/><path id="pswp__icn-fullscreen-request" transform="translate(4,4)" d="M20 3h2v6h-2V5h-4V3h4zM4 3h4v2H4v4H2V3h2zm16 16v-4h2v6h-6v-2h4zM4 19h4v2H2v-6h2v4z" /></g><path id="pswp__icn-fullscreen-exit" style="display:none" transform="translate(4,4)" d="M18 7h4v2h-6V3h2v4zM8 9H2V7h4V3h2v6zm10 8v4h-2v-6h6v2h-4zM8 15v6H6v-4H2v-2h6z"/></svg>',onClick:(e,l)=>{this.toggleFullscreen()}}),e.events.add(document,"keydown",e=>{70==e.keyCode&&(this.toggleFullscreen(),e.preventDefault())})}),this.lightbox.on("close",()=>{this.fullscreenAPI&&this.fullscreenAPI.isFullscreen()&&this.fullscreenAPI.exit()})}toggleFullscreen(){this.fullscreenAPI&&(this.fullscreenAPI.isFullscreen()?(this.fullscreenAPI.exit(),setTimeout(function(){document.getElementById("pswp__icn-fullscreen-exit").style.display="none",document.getElementById("pswp__icn-fullscreen-request").style.display="inline"},300)):(this.fullscreenAPI.request(document.querySelector(".pswp")),setTimeout(function(){document.getElementById("pswp__icn-fullscreen-exit").style.display="inline",document.getElementById("pswp__icn-fullscreen-request").style.display="none"},300)))}getFullscreenAPI(){let e,l,n,s,t,i;return document.fullscreenEnabled?(l="requestFullscreen",n="exitFullscreen",s="fullscreenElement",t="fullscreenchange",i="fullscreenerror"):document.webkitFullscreenEnabled&&(l="webkitRequestFullscreen",n="webkitExitFullscreen",s="webkitFullscreenElement",t="webkitfullscreenchange",i="webkitfullscreenerror"),l&&(e={request:function(e){"webkitRequestFullscreen"===l?e[l](Element.ALLOW_KEYBOARD_INPUT):e[l]()},exit:function(){return document[n]()},isFullscreen:function(){return document[s]},change:t,error:i}),e}}











const _id = (id) => document.getElementById(id);
const _qs = (q, el) => (el || document).querySelector(q);
const _qsa = (q, el) => Array.from((el || document).querySelectorAll(q));

//here p = innerHTML/textContent =..., m = appendChild(...)
const _ce = (t, att, p, m) => {
	t = document.createElement(t);
	if(att){
		for(const k in att){_att(t, k, att[k]);}
	}
	const pp = (e,x) => {
		for (const k in e) {
			if(x){
				t[k] = e[k];
			}else{
				(Array.isArray(e[k]) ? e[k] : [e[k]]).forEach(v =>{
					t[k](v);
				});
			}
		}
	};
	if(p){pp(p,1);}
	if(m){pp(m);}
	return t;
};

const _on = (el, ev, fn, opt) => ev.split(' ').forEach(e => el.addEventListener(e, fn, opt));

const _off = (el, ev, fn) => ev.split(' ').forEach(e => el.removeEventListener(e, fn));

const wait = (ms, fn) => {setTimeout(fn, ms);};

const _att = (el, k, v) => {
	if(v === undefined){
		return el.getAttribute(k);
	}
	el.setAttribute(k, v);
};

function debounce(fn, ms = 100){
	let tout;
	return (...args) => {
		const c = this;
		if(tout){clearTimeout(tout);}
		tout = setTimeout(() => fn.apply(c, args), ms);
	};
}

function throttle(fn, mx){
	if(!mx) return fn;
	let calm;
	return (...args)=>{
		if(calm){return;}
		fn.apply(this, args);
		calm = setTimeout(()=>{calm = 0;}, mx);
	};
}

const ucfirst = (s) => (s[0].toUpperCase() + s.slice(1));

const inRange = (x, min, max) => ((x - min) * (x - max) <= 0);

const getMonth = (m,n='short') => {
	const d = new Date();
	d.setDate(1);
	d.setMonth(parseInt(m,10)-1);
	return d.toLocaleString('default',{month: n});
};

const slideUp = (el, ms = 500) => {
	let s = el.style;
	s.transitionProperty = 'height';
	s.transitionDuration = ms + 'ms';
	s.height = el.offsetHeight + 'px';
	el.offsetHeight;
	s.overflow = 'hidden';
	s.height = 0;
	wait(ms, () => {
		s.display = 'none';
		s.removeProperty('height');
		s.removeProperty('overflow');
		s.removeProperty('transition-duration');
		s.removeProperty('transition-property');
	});
};

const slideDown = (el, ms = 500) => {
	let s = el.style;
	s.removeProperty('display');
	let display = window.getComputedStyle(el).display;
	if(display === 'none'){display = 'block';}
	s.display = display;
	let height = el.offsetHeight;
	s.overflow = 'hidden';
	s.height = 0;
	el.offsetHeight;
	s.transitionProperty = "height";
	s.transitionDuration = ms + 'ms';
	s.height = height + 'px';
	wait(ms, () => {
		s.removeProperty('height');
		s.removeProperty('overflow');
		s.removeProperty('transition-duration');
		s.removeProperty('transition-property');
	});
};


function filesize(bytes){
	if(!bytes){return '0 B';}
	const k = 1024;
	const sizes = ['B','KB','MB','GB','TB','PB'];
	const i = Math.floor(Math.log(bytes) / Math.log(k));
	const s = parseFloat(bytes / Math.pow(k, i));
	let dm = i>1 ? Math.min(i - 2, 2) : 0;
	if(i == 2 && s>10){dm = 0;}
	return s.toFixed(dm) + ' ' + sizes[i];
}


function relativeDate(t){
	if(!t){return '';}
	const diff = t - (Date.now() / 1000);

	const units = [
		["year", 31536000],
		["month", 2592000],
		["week", 604800],
		["day", 86400],
		["hour", 3600],
		["minute", 60]
	];

	for(const [label, seconds] of units){
		const f = Math.floor(Math.abs(diff) / seconds);
		if(f >= 1){
			try{
				const rtf = new Intl.RelativeTimeFormat(navigator.language,{numeric:'auto'});
				return rtf.format(diff<0 ? -f : f, label);
			}catch(e){
				return (diff<0?'':'in ')+''+f+' '+label+(f===1?'':'s')+(diff<0?' ago':'');
			}
		}
	}
	return "just now";
}

function fullDate(t){
	if(!t){return '';}
	return new Intl.DateTimeFormat(undefined, {year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric'}).format(new Date(t*1000));
}


function getFilename(p){
	return p.split('/').reverse()[0];
}

function removeExt(url){
	const file = url.split('/').pop();
	const n = file.lastIndexOf('.');
	if(n === -1){return url;}
	return url.substring(0, url.lastIndexOf('.'));
}

function getExt(url){
	const file = url.split('/').pop();
	if(file === undefined || file === '' || file === '.'){
		return '';
	}
	const ext = file.split('.').pop().toLowerCase();
	return ext === file ? '' : ext;
}


const db = {conf: 0, files: 0, d: 0, t: 0, s: 0, k: 0, a: 0, zb: 0};


function mydb(tb){
	return new Promise((resolve, reject) => {
		const dn = window.location.pathname.replace(/\//g,'-');
		const init = ()=>{
			const dbo = window.indexedDB.open(dn, 2);
			dbo.onsuccess = () => {
				db.zb = dbo.result;
				resolve(act);
			};
			dbo.onerror = (e) => {
				const et = e.target.error;
				if(et && et.name === 'VersionError'){
					window.indexedDB.deleteDatabase(dn);
					return init();
				}
				resolve(); //reject(new Error(et));
			};
			dbo.onupgradeneeded = (e) => {
				db.zb = dbo.result;
				for(const i in db){
					if(i=='zb'){continue;}
					if(e.oldVersion){try{db.zb.deleteObjectStore(i);}catch(e){}}
					db.zb.createObjectStore(i, {keyPath: 'i'});
				}
			};
		};

		const act = {
			get: (k,v) => qu('get', k, v),
			put: (k,v) => qu('put', k, v),
			del: k => qu('delete', k),
			all: () => qu('getAll'),
			wipe: () => qu('wipe'),
		};

		const qu = (m, param, pval) => new Promise((resolveq, rejectq) => {
			if(m=='wipe'){db.zb.close();window.indexedDB.deleteDatabase(dn);db.zb = 0; resolveq();return;}
			const tx = db.zb.transaction(tb, 'read'+(m.includes('get')? 'only' : 'write'));
			const store = tx.objectStore(tb);
			if(typeof param === 'number'){param = param+='';}
			if(m === 'put'){
				if(typeof param !== 'object'){
					param = {[param]: pval};
				}
				let pp = [];
				for(const i of Object.keys(param)){
					pp.push({i:i, v:param[i]});
				}
				param = pp;
			}
			const multi = param && Array.isArray(param);
			if(multi){
				Promise.all(param.map(key => {
					return new Promise((resolve, reject) => {
						if(m === 'get'){key+='';}
						const r = store[m](key);
						r.onsuccess = e => resolve(e.target.result);
						r.onerror = e => reject(e.target.error);
					});
				})).then(r => {
					if(m === 'get'){
						const s = r.findIndex(i => i === undefined);
						if(s !== -1){
							console.log('Missing: ' + param[s]);
							r = undefined;
						}else if(r){
							const data = {};
							r.forEach((d, i) => {
								data[d.i] = d.v;
							});
							r = data;
						}
					}

					resolveq(r);
				}).catch(e => rejectq(e));
			}else{
				const r = store[m](param);
				r.onsuccess = (e) => {
					let j = e.target.result;
					if(m === 'get'){
						if(j && j.hasOwnProperty('v')){
							j = j.v;
						}else if(pval !== undefined){
							j = pval;
						}
					}
					resolveq(j);
				};
				r.onerror = (e) => {rejectq(e);};
			}
		});
		if(db.zb){resolve(act);}else{init();}
	});
}


function sharedView(f){
	const g = _qs('.gallery');
	f.items.forEach(v=>{
		const ah = _ce('a', {'class': 'file', style: '--ratio:'+( (v.h) ? v.w + '/' + v.h : '4/3')});
		if(v.ft){
			_att(ah,'data-pswp-width', v.w);
			_att(ah,'data-pswp-height', v.h);
			const im = _ce('img',{loading:'lazy',src: f.url + encodeURIComponent(v.fileid + 't-'+removeExt(v.file))+'.webp?mt='+v.mt});
			im.onload = imld;
			ah.appendChild(im);
			ah.appendChild(_ce('i-con'));
		}else{
			const ext = getExt(v.file).toLowerCase();
			_att(ah,'data-ext', ext);
			ah.innerHTML = svgExt(ext);
			ah.classList.add('loaded');
		}
		const fo = _ce('div',{'class':'info'});
		fo.appendChild(_ce('f-nm',0,{textContent: v.file}));
		if(v.ft>1){
			_att(ah,'data-pswp-type', 'video');
			fo.appendChild(_ce('v-dur',0,{textContent: vduration(v.dur)}));
		}
		ah.appendChild(fo);
		_att(ah,'href', f.url + v.fileid + 'f-'+encodeURIComponent(v.file)+'?mt='+v.mt);
		g.appendChild(ah);
	});
	const lbox = new PhotoSwipeLightbox({
		bgOpacity: 1,
		zoom: false,
		gallery: '.gallery',
		children: 'a[data-pswp-width]',
		pswpModule: PhotoSwipe
	});

	lbox.on('contentInit', ({content}) => {
		if(lbox.pswp && lbox.pswp.currSlide){
			lbox.pswp.currSlide.data.element.scrollIntoView({behavior: 'smooth', block: 'center'});
		}
	});

	const fs = new PhotoSwipeFullscreen(lbox);
	const ss = new PhotoSwipeSlideshow(lbox,4);
	const vp = new PhotoSwipeVideoPlugin(lbox, {});
	lbox.init();

	//fix back btn
	if(!history.state||!history.state.pswp||history.state.path!==location.href){history.pushState({pswp:true,path:location.href},null,location.href);}
	window.addEventListener('popstate',function(e){if(typeof pswp==="object"&&pswp&&pswp.isOpen){history.pushState({pswp:true,path:location.href},null,location.href);pswp.close();}else{history.go(-1)}});
}


function lsclear(){
	navi.sort={};
	navi.scroll={};
	db.d.wipe();
}

function lsscroll(){
	db.conf.put('scroll', navi.scroll).catch(e=>{});
}


function post(opt){
	const url = opt.url ? opt.url : window.location.pathname;
	fetch(url, {
		method: 'POST',
		headers: {
			'Accept': 'application/json',
			'Content-Type': 'application/x-www-form-urlencoded'
		},
		body: opt.params
	})
	.then(response => {
		if(response.status === 200){
			return response.json();
		}else if(response.status === 401){
			popup('Please Login','Error');
			wait(1000,()=>{ location.reload(); });
			throw new Error('Not Logged in');
		}else{
			throw new Error('Server error');
		}
	})
	.then(json => {
		if(typeof json === 'object'){
			if(json.msg === 'login'){
				wait(1000,()=>{ location.reload(); });
			}
			if(json.Dir){
				Dir = json.Dir;
				updateDirSize();
				delete json.Dir;
				buildMenu();
			}
			if(opt.complete ){
				opt.complete(json);
			}
		}
	})
	.finally(() => {
		if(opt.always){
			opt.always();
		}
	})
	.catch(e => {
		toast(e.message,{theme: 'red',timeout:0,close:1});
	});
}



function toast(message, opts = {}){
	opts = Object.assign({
		timeout: 4,
		theme: 'green',
		close: 0,
		prep: 0,
		click: null
	}, opts);

	const dismiss = () => {
		if(el){
			el.style.opacity = 0;
			wait(300,() => {el.remove();});
		}
	};

	const el = _ce('div',{'class':'toast ' + opts.theme},{innerHTML: '<p>' + message + '</p>'});
	if(typeof opts.click === 'function'){
		_on(el, 'click', opts.click);
	}else{
		el.onclick = dismiss.bind(this,el);
	}

	if(opts.close){
		el.classList.add('close');
		const closeBtn = _ce('i',{'class':'rbtn ico-x'});
		closeBtn.onclick = dismiss;
		el.insertBefore(closeBtn, el.firstChild);
	}

	const nav = _ce('nav');
	el.appendChild(nav);
	if(opts.prep){
		_id('toast').prepend(el);
	}else{
		_id('toast').appendChild(el);
	}
	el.offsetHeight;
	el.style.opacity = 1;

	if(opts.timeout > 0){
		wait(1000 * opts.timeout, () => {
			dismiss();
		});
	}

	return {
		el,
		bar: (c, t) => { nav.style.width = ((c / t) * 100) + '%';},
		update: (msg) => {_qs('p',el).innerHTML = msg;},
		dismiss
	};
}


function popup(html='', head, options={}){
	options = Object.assign({
		buttons: [{
				text: "Close",
				key: "Enter",
				def: 1
			}
		],
		selects: 0,
		selected: '',
		selectt: 'Please Select',
		bgclose: 1,
	}, options);

	const anim = () => {
		pop.innerHTML = '';
	};
	const close = () => {
		_off(window, 'resize', resz);
		_off(document,'keydown', kbd);
		_off(document,'click', bgclick);
		pop_el.style.removeProperty('top');
		_on(pop_el, 'transitionend', anim);
		pop.classList.remove('popfade');

	};

	const bgclick = (e) => {
		if(options.bgclose && !pop_el.contains(e.target)){
			e.preventDefault();
			e.stopPropagation();
			close();
		}
	};

	const pressed = (btn, html) => {
		if( !btn.click || btn.click(html, pp)){
			close();
		}
	};

	const kbd = (e) => {
		if(e.key === undefined){return;}
		const key = e.key.toLowerCase();
		const d = options.buttons.find(btn => {return btn.key && btn.key.toLowerCase() === key;});
		if(d){
			throttle(()=>{
				pressed(d, section);
				},500)();
			return;
		}
		if(key === 'escape'){
			close();
		}
	};

	const fbtn = () => {
		options.buttons.forEach(bn => {
			const bt = _ce('button',{'class':'btn' + (bn.def ? ' default' : '')},{innerHTML: bn.text});
			bt.onclick = pressed.bind(this, bn, section);
			ft.appendChild(bt);
		});
	};


	const pop = _id('popup');
	const popl = _ce('div',{id: 'poplayer'});
	pop.appendChild(popl);
	const pop_el = _ce('div',{id: 'popbox'});

	const hd = _ce('header');
	hd.innerHTML = head;
	pop_el.appendChild(hd);

	pop.appendChild(pop_el);

	void popl.offsetWidth;

	const section = _ce('section');
	if(html instanceof Element){
		section.appendChild(html);
	}else{
		section.innerHTML = html;
	}
	ddGen(section, options.selects, options.selected, 'popselect', options.selectt);
	section.appendChild(_ce('cite'));
	pop_el.appendChild(section);
	const nav = _ce('nav');
	pop_el.appendChild(nav);
	const ft = _ce('footer');
	fbtn();
	pop_el.appendChild(ft);

	const resz = () => {
		pop_el.style.top = (window.pageYOffset + Math.max(100,(window.innerHeight - pop_el.offsetHeight) / 2))+'px';
	};
	const pp = {
		head: (h) => { hd.innerHTML = h;},
		msg: (msg) => { section.innerHTML = msg;},
		btn: (bx) => { options.buttons = bx; ft.innerHTML = ''; fbtn();},
		bar: (c, t) => { nav.style.width = ((c / t) * 100) + '%';},
		close
	};

	resz();
	pop.classList.add('popfade');
	resz();
	wait(300,()=>{
		_on(window, 'resize', resz);
		_on(document,'keydown', kbd);
		_on(document,'click', bgclick);
		resz();
	});

	return pp;

}


const ddGen = (el, op, s='', id='', t='Please Select')=>{
	const sel_el = _ce('select');
	if(op){
		sel_el.id = id;
		const selv = s+'';
		const dh = _ce('option',{disabled:''},{textContent: t});
		if( selv === ''){
			_att(dh,'selected','');
		}
		sel_el.appendChild(dh);
		for(const i in op){
			const op_el = _ce('option',{value: v});
			const v = op[i][0]+'';
			if(selv === v){_att(op_el,'selected','');}
			const q =op[i].length>1? op[i][1] : op[i][0];
			if(q.includes('<')){
				op_el.innerHTML = q;
			}else{
				op_el.textContent = q;
			}
			sel_el.appendChild(op_el);
		}
		el.appendChild(sel_el);
	}
	return sel_el;
};





async function act_upload(hide,appup){
	navi.noroll = 1;
	window.scrollTo(0,0);

	const u = _id('uploader');
	u.innerHTML = '';
	if(hide){
		navi.noroll = 0;
		return false;
	}

	const up = {
		List: [],
		Mode: 0,
		Toast: null,
		Total: 0,
		xhr: null,
		cancel: 0,
		lmsg: '',
		maxthm: 40,
		dir: navi.dir || Dir.home
	};


	const addItem = (items,input) => {
		for(const item of items){
			if(input){
				upThumb(item);
			}else{
				const i = item.webkitGetAsEntry();
				if(i.isFile){
					i.file((file) => {
						upThumb(file);
					});
				}else if(i.isDirectory){
					dirScan(i.createReader());
				}
			}
		}
	};

	const dirScan = (rdr) => {
		rdr.readEntries((entries) => {
			for(const i of entries){
				if(i.isFile){
					i.file((file) => {
						upThumb(file);
					});
				}else if(i.isDirectory){
					dirScan(i.createReader());
				}
			}
			if(entries.length > 0){
				dirScan(rdr);
			}
		});
	};

	const upThumb = (file) => {
		const ext = getExt(file.name);
		const mtype = _p.ext_images.includes(ext)? 1 : ( _p.ext_videos.includes(ext)? 2 : 0 );
		if(mtype || (_p.ext_uploads.includes(ext) || _p.ext_uploads.includes('*'))){
			const t = ['M8 6H1V1H8M2 5H7V2H2Z','M8 6H1V1h7M2 5h5L5 2 4 4 3.2 3Z','M6 1H1v5h5V4l2 2V1L6 3Z'];
			let src = (mtype === 1 && up.maxthm>0) ? URL.createObjectURL(file) : "data:image/svg+xml,%3Csvg fill='%23666' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 9 7'%3E%3Cpath d='"+t[mtype]+"'%3E%3C/path%3E%3C/svg%3E";
			const f = _ce('div',{'class': 'uploadthumb'});
			const im = _ce('img',{src: src});
			f.appendChild(im);
			const pg = _ce('nav');
			f.appendChild(pg);
			const lb = _ce('label');
			lb.textContent = '0%';
			f.appendChild(lb);
			f.appendChild(document.createTextNode(' of ' + filesize(file.size)));
			const dv = _ce('div');
			dv.textContent = file.name;
			f.appendChild(dv);

			const b = _ce('button',{'class':'uploadclose',title: "Close"});
			b.onclick = () => {
				up.List = up.List.filter(w => w.f.name !== file.name);
				f.remove();
				up.maxthm++;
			};
			b.appendChild(_ce('i',{'class':'ico-x'}));
			f.appendChild(b);

			_id('uploadlist').appendChild(f);
			up.List.push({f:file,el:pg,cl:b,im:im});
			up.maxthm--;
		}
	};


	const upBar = (el,p)=>{
		el.style.width = p + '%';
		el.nextElementSibling.innerText = Math.floor(p) + '%';
	};

	const upNext = (failed,curr) => {
		if(up.cancel || !up.List.length){
			up.List = failed;
			up.lmsg = '';
			if(up.cancel){
				toast('Upload aborted!',{timeout: 0,close: 1, theme:'red'});
			}else{
				toast('All uploads completed!',{timeout: 5,theme:'black'});
			}
			if(up.Toast){up.Toast.dismiss();}
			db.d.del(up.dir).then(r=>genPage());
			return;
		}

		if(curr===1){
			if(up.Toast){up.Toast.el.remove();}
			up.Toast = toast('',{timeout:0,theme:'black',close:1,click:()=>{up.cancel=1;up.xhr.abort();}});
		}
		up.Toast.update(curr + ' of '+up.Total+' Uploading'+up.lmsg);
		up.Toast.bar(curr, up.Total);
		const fobj = up.List[0];
		const el = fobj.el;
		const file = fobj.f;
		const fd = new FormData();
		fd.append('media[]', file);
		up.xhr = new XMLHttpRequest();
		up.xhr.open('POST', location.pathname+'?upload=js&mode='+up.Mode+'&mtime='+file.lastModified+'&updir='+up.dir, true);
		up.xhr.responseType = 'json';
		up.xhr.upload.onprogress = function (e){
			if(e.lengthComputable){
				upBar(el,(e.loaded / e.total) * 100);
			}
		};
		up.xhr.onload = function (){
			let p=0
			if(up.xhr.status >= 200 && up.xhr.status < 300){
				p=100;
				const res = up.xhr.response;
				if(res.ok){
					up.lmsg = '<br>'+res.msg;
					fobj.cl.remove();
					fobj.im.style.opacity = "0.4";
				}else{
					toast(res.msg,{theme:'red',timeout:0,close:1});

				}
			}else{
				failed.push(up.List[0]);
				toast("Error "+up.xhr.status,{theme:'red',timeout:0,close:1});
			}
			upBar(el,p);
			up.List.shift();
			upNext(failed,curr+1);
		};

		up.xhr.onerror = function (){
			toast('Error uploading '+file.name,{theme:'red',timeout:0,close:1});
			failed.push(up.List[0]);
			up.List.shift();
			upNext(failed,curr+1);
			upBar(el,0);
		};
		up.xhr.send(fd);
	};


	const h = _ce('div',{id: 'uploadbox'});
	h.ondrop = (e) => {
		e.preventDefault();
		h.classList.remove('drag');
		const files = e.dataTransfer.items;
		addItem(files);
	};
	h.ondragover = (e) => {
		e.preventDefault();
		e.dataTransfer.dropEffect = 'copy';
		h.classList.add('drag');
	};
	h.innerHTML = '<div id="uploadhead">\
	<span id="uploadtitle">File Uploader </span>\
	<span onclick="act_upload(1)" id="uploadclose">&times;</span>\
	</div>\
	<p>Drag and drop folder/files here</p>\
	';
	const p1 = _ce('p');
	const f = _ce('input',{multiple: '', type: 'file', id: 'uploadinput', style: 'display:none'});
	//_att(f, 'accept', 'image/*, video/*');
	//_att(f, 'webkitdirectory', '');
	_on(f,'change', (e) => {addItem(e.target.files,1);});
	p1.appendChild(f);

	p1.appendChild(_ce('label',{'class':'btn', 'for': 'uploadinput'},{textContent: 'Select Files'}));

	const bt = _ce('span',{'class':'btn default'},{textContent: 'Upload Now'});
	bt.onclick = () => {
		if(up.List.length === 0){
			toast('No files selected',{theme:'red'});
			return;
		}
		up.cancel=0;
		up.Total = up.List.length;

		upNext([],1);
	};
	p1.appendChild(bt);

	h.appendChild(p1);

	const p2 = _ce('p');

	const dl = _ce('label');
	const dr = ddGen(dl, dirArr(), up.dir);
	dr.onchange = () => {up.dir = parseInt(dr.value, 10);}
	p2.appendChild(dl);

	p2.appendChild(_ce('br'));

	const sl = _ce('label');
	const sd = ddGen(sl, [[0,'Skip Duplicates'],[1,'Auto Rename'],[2,'Overwrite']], up.Mode);
	sd.onchange = () => {up.Mode = parseInt(sd.value, 10);};

	p2.appendChild(sl);


	h.appendChild(p2);

	h.appendChild(_ce('div',{id: 'uploadlist'}));

	u.appendChild(h);
	if(appup){
		for(const file of appup){
			upThumb(file);
		}
	}
}





function act_exit(i){
	const t = {1:'',2:' from ALL Devices'};
	popup('Log Out'+t[i]+'?','Confirmation', {
		buttons: [
			{
				text: "No"
			},{
				text: "Yes",
				click: (html, pp) => {
					lsclear();
					post({params:'logout='+i});
					return true;
				},
				key: "Enter",
				def: 1
			}
		],
	});
}



function act_edit(atag){
	const [did, file, fileid, itype] = gensid(atag).split('/');
	const src = _att(atag,'href');
	const ext = getExt(file);
	let mtype = 0;
	const pics = ['jpg','jpeg'];
	const vids = ['mp4','mov','3gp'];

	let image = _ce('img');

	let loaded = 0;
	let imxy;
	const vcut = {start: 0, stop: 0, pos: 0, dur: 0, audio: 1, el: {}};
	if(pics.includes(ext)){
		mtype = 1;
	}

	if(vids.includes(ext)){
		mtype = 2;
	}

	if(!mtype){toast(ext+' Not Supported');return;}

	const orientation = parseInt(_att(atag,'data-ori'),10) || ((mtype === 1)? 1 : 0);
	let curot = (mtype === 1)? orientation : 0;

	let mu = getUrlID(); mu.url +='#edit/'+fileid+'/'+encodeURIComponent(file);
	updHist(1,mu);
	navi.backbtn = 0;
	navi.edit = 1;
	window.scrollTo(0,0);

	const rotate = {
		r:{1:6,2:5,3:8,4:7,5:4,6:3,7:2,8:1},
		l:{1:8,2:7,3:6,4:5,5:2,6:1,7:4,8:3},
		h:{1:2,2:1,3:4,4:3,5:6,6:5,7:8,8:7},
		v:{1:4,2:3,3:2,4:1,5:6,6:5,7:8,8:7},
		d:{1:0,2:0,3:180,4:0,5:90,6:90,7:90,8:270},
	};
	const tclose = _id("tclose");
	const body = _qs("body");
	body.classList.add('edit','rotate');
	const selectbar = _id("selectbar");
	const editor = _id("editor");
	const nav = _ce('nav');
	selectbar.appendChild(nav);
	const editwrap = _ce('div',{id: "editwrap"});
	const imgdiv = _ce('div',{id: "editimgdiv"});
	let icons;
	const canvas = _ce('canvas');
	const video = _ce('video');
	if(mtype === 1){
		icons = {l:'left',r:'right',h:'hoz',v:'vert',c:'crop'};
		imgdiv.appendChild(canvas);
	}else if(mtype === 2){
		icons = {l:'left',r:'right',c: 'cut'};
		_att(video, 'controls', '');
		_att(video, 'playsinline', '');
		_att(video, 'src', src);
		imgdiv.appendChild(video);
		imgdiv.className = "vwrap loader";
		_on(video, 'loadedmetadata', _=> {
			loaded = 1;
			let d = video.duration;
			vcut.dur = d;
			vcut.stop = d;
			imgdiv.classList.remove('loader');
			imgRot();
		});
		_on(video, 'timeupdate', _=> {vcut.pos = video.currentTime;});
		imgdiv.appendChild(_ce('div',{'class': 'ctxt vtrim'},0,{appendChild:_ce('div')}));
	}


	editwrap.appendChild(imgdiv);
	editor.appendChild(editwrap);
	const ctitle = _id('ctitle');
	ctitle.textContent = file;

	const points =['top','left','width','height'];

	const save = _id('tsave');
	save.onclick = () => {
		let u = '&r='+curot;
		if(mtype === 1){
			if(orientation === curot){u = '';}

			const ratio = crop.orgw / crop.boxw;

			let X = Math.round(Math.max(0,crop.left * ratio));
			let Y = Math.round(Math.max(0,crop.top * ratio));
			let W = Math.min(crop.orgw - X, Math.round(crop.width * ratio));
			let H = Math.min(crop.orgh - Y, Math.round(crop.height * ratio));

			let orgw = crop.orgw, orgh = crop.orgh;

			switch (rotate.d[curot]){
				case 90:
					[X, Y, W, H, orgw, orgh] = [Y, orgw - X - W, H, W, orgh, orgw];
					break;

				case 180:
					[X, Y] = [orgw - X - W, orgh - Y - H];
					break;

				case 270:
					[X, Y, W, H, orgw, orgh] = [orgh - Y - H, X, H, W, orgh, orgw];
					break;
			}

			if( curot === 2 || curot === 7 ){
				X = orgw - X - W;
			}

			if( curot === 4 || curot === 5 ){
				Y = orgh - Y - H;
			}

			if(crop.orgw !== W || crop.orgh !== H){
				u += '&w='+W+'&h='+H+'&x='+X+'&y='+Y;
			}
		}else{
			let d = (orientation + curot) % 360;
			u = (orientation == d)? '' : '&r='+d+'&vrot=1';
			if(vcut.trim){
				u += '&vcut=1&ss='+vcut.start+'&to='+vcut.stop+'&aud='+vcut.audio;
			}
		}

		if(!u){toast('No Changes'); tclose.click();return;}
		popup('Save '+file+'?<p><label><input id="tow" style="width:auto" name="tow" type="checkbox" checked> Overwrite</label></p>','Confirmation',{
			buttons: [
				{
					text: "No"
				}, {
					text: "Yes",
					click: (html, pp) => {
						if(_id('tow').checked){
							u += '&o=1';
						}
						let j = toast('Waiting',{timeout:0});

						j.el.classList.add('loader');
						post({
							params: 'task=edit&id='+ did +'&fid='+ fileid+'&name=' + encodeURIComponent(file)+ u,
							complete: function(res){
								if(res.ok){
									toast(res.msg);
									db.files.del(fileid).then(r=>{
										tclose.click();
										genPage();
									}).catch(e=>{});
								}else{
									toast(res.msg,{theme:'red',timeout:0,close:1});
								}
							},
							always: function(){
								j.dismiss();
							}
						});
						return true;
					},
					key: "Enter",
					def: 1
				}
			],
		});
	};


	const pointDown = document.ontouchstart !== null ? 'pointerdown' : 'touchstart';
	const pointMove = document.ontouchmove !== null ? 'pointermove' : 'touchmove';
	const pointUp = document.ontouchmove !== null ? 'pointerup' : 'touchend';
	const pxy = (e) => {
		const t = (window.TouchEvent && e instanceof TouchEvent)? e.touches[0] : e;
		crop.nX = t.pageX;
		crop.nY = t.pageY;
	};
	const DRAG = 1; const RESIZE = 2;


	const aspects = {
		'Free' : 0,
		'Original': 0,
		'1:1': 1,
		'16:9': 16/9,
		'9:16': 9/16,
		'4:3': 4/3,
		'3:4': 3/4,
		'3:2':3/2,
		'2:3':2/3
	};
	const crop = {action: 0, corner: '', boxw: 0, boxh: 0, x: 0, y: 0, nX: 0, nY: 0, top: 0, left: 0, orgw: 0, orgh: 0, width: 0, height: 0, iw: 0, ih: 0};

	let curasp = 'Free';

	const pDown = (e,el) => {
		crop.corner = el.id === 'cropsel' ? '' : el.id;
		crop.action = crop.corner ? RESIZE : DRAG;
		pxy(e);
		crop.x = crop.nX;
		crop.y = crop.nY;
	};

	const bound = (n,r) => {
		if(!n){
			n = {};
			for(const i of points){
				n[i] = crop[i];
			}
		}
		const asp = aspects[curasp] || n.width / n.height;
		const MIN_WIDTH = 50;

		if(n.top<0){
			n.height += Math.abs(n.top);
			n.top=0;
		}
		if(n.left<0){
			n.width += Math.abs(n.width);
			n.left=0;
		}
		if(n.width < MIN_WIDTH){
			n.width = MIN_WIDTH;
			n.height = n.width / asp;
		}

		if(n.height < MIN_WIDTH){
			n.height = MIN_WIDTH;
			n.width = n.height * asp;
		}

		if(aspects[curasp]){
			if(n.width / n.height < aspects[curasp]){
				n.height = n.width / aspects[curasp];
			}else{
				n.width = n.height * aspects[curasp];
			}
		}
		n.width = Math.min(crop.boxw, n.width);
		n.height = Math.min(crop.boxh, n.height);
		if(r){
			return n;
		}

		const c = {};
		for(const i of points){
			crop[i] = n[i];
			c[i] = n[i] + 'px';
		}

		Object.assign(cropsel.style, c);

		cropbg.style.borderWidth = n.top + "px "+ (crop.boxw - n.left - n.width) + "px " + (crop.boxh - n.top - n.height) + "px " + n.left + "px";
	};


	const pMove = (force) => {
		if(!force && (!inRange(crop.nX, imxy.x, imxy.xx) || !inRange(crop.nY, imxy.y, imxy.yy))){
			crop.action = 0;
			return;
		}

		const X = crop.nX - crop.x;
		const Y = crop.nY - crop.y;
		crop.x = crop.nX;
		crop.y = crop.nY;
		let n = {...crop};

		if(crop.action === DRAG){
			n.left = Math.max(0, Math.min(n.left+X, crop.boxw - crop.width));
			n.top = Math.max(0, Math.min(n.top+Y, crop.boxh - crop.height));

		}else{
			if(crop.corner.includes("top")){
				n.height -= Y;
				n.top += Y;
			}
			if(crop.corner.includes("bottom")){
				n.height += Y;
			}
			if(crop.corner.includes("right")){
				n.width += X;
			}
			if(crop.corner.includes("left")){
				n.width -= X;
				n.left += X;
			}
			if(aspects[curasp]){
				if(n.width>crop.width){
					n.height = crop.height + Math.abs(X);
				}else if(n.height>crop.height){
					n.width = crop.width + Math.abs(Y);
				}
			}
			n = bound(n,1);
		}
		n.left = Math.min(Math.max(0, n.left), crop.boxw - n.width);
		n.width = Math.min(Math.max(0, n.width), crop.boxw);

		n.top = Math.min(Math.max(0, n.top), crop.boxh - n.height);
		n.height = Math.min(Math.max(0, n.height), crop.boxh);

		n = bound(n);
	};


	const pointerUp = (e) => {
		if(!crop.action){return;}
		crop.action = 0;
	};

	const pointerMove = (e) => {
		if(!crop.action){return;}
		pxy(e);
		pMove();
	};

	const cropbg = _ce('div',{id: "cropbg"});

	const cropsel = _ce('div',{id: "cropsel"});

	const close = () => {
		if(mtype === 1){
			_off(window, pointMove, pointerMove);
			_off(window, pointUp, pointerUp);
			_off(window, 'resize', winSz);
		}
		body.classList.remove('crop','edit','rotate');
		editor.innerHTML = '';
		ctitle.innerHTML = '';
		selectbar.innerHTML = '';
		image = null;
		tclose.onclick=null;
		if(!navi.backbtn){
			history.back();
		}
		navi.edit = 0;
		restoreScroll();
	};


	const winSz = () =>{

		if(crop.ww === window.innerWidth){return;}

		const ratio = Math.min((window.innerWidth - 50)/crop.orgw, (window.innerHeight - 110)/crop.orgh);
		const neww = crop.orgw * ratio;
		const newh = crop.orgh * ratio;

		const oratio = neww / crop.boxw;
		crop.left = Math.max(0, crop.left * oratio);
		crop.top = Math.max(0, crop.top * oratio);
		crop.width = Math.min(neww - crop.left, crop.width * oratio);
		crop.height = Math.min(newh - crop.top, crop.height * oratio);

		imgRot();
		if(body.classList.contains('crop')){bound();}
	};


	const imgRot = (c) => {
		if(!loaded){return;}

		if(c === 'c'){
			body.classList.remove('rotate');
			body.classList.add('crop');
			return;
		}

		if(mtype === 2){
			let r = {l: 270, r: 90};
			if(r[c]){
				curot += r[c];
				curot = curot % 360;
			}

			video.style.transform = 'rotate('+curot+'deg)';

			if(c == 'start' || c == 'stop'){
				vcut[c] = vcut.pos; vcut.trim = 1;
				let t = vcut.stop - vcut.start;
				if(t<0){vcut.start = 0; vcut.stop = t = vcut.dur; }
				vcut.el.dur.textContent = 'Dur: '+numTime(t);
			}
			if(c == 'sound'){
				vcut.audio = vcut.audio ? 0 : 1;
				vcut.trim = 1;
			}
			vcut.el.audio.className = 'cbtn ico-'+(vcut.audio?'':'no')+'sound';
			vcut.el.start.textContent = numTime(vcut.start);
			vcut.el.stop.textContent = numTime(vcut.stop);
			['start','stop','dur'].forEach(i=>{
				imgdiv.style.setProperty('--v'+i, vcut[i]);
			});
			return;
		}

		if(c){
			curot = rotate[c][curot];
			crop.width = 0;
		}


		let iw = crop.iw;
		let ih = crop.ih;


		let cw = iw;
		let ch = ih;

		if(curot > 4){
			[cw, ch] = [ih, iw];
		}

		crop.orgw = cw;
		crop.orgh = ch;


		crop.ww = window.innerWidth;

		aspects.Original = cw/ch;
		const ratio = Math.min((window.innerWidth - 50)/cw, (window.innerHeight - 110)/ch);

		iw *= ratio;
		ih *= ratio;

		canvas.width = cw * ratio;
		canvas.height = ch * ratio;

		crop.boxw = cw * ratio;
		crop.boxh = ch * ratio;
		if(!crop.width){
			crop.width = crop.boxw;
			crop.height = crop.boxh;
		}

		const ctx = canvas.getContext("2d");

		switch (curot){
			case 2: ctx.transform(-1, 0, 0, 1, iw, 0); break;
			case 3: ctx.transform(-1, 0, 0, -1, iw, ih); break;
			case 4: ctx.transform(1, 0, 0, -1, 0, ih); break;
			case 5: ctx.transform(0, 1, 1, 0, 0, 0); break;
			case 6: ctx.transform(0, 1, -1, 0, ih, 0); break;
			case 7: ctx.transform(0, -1, -1, 0, ih, iw); break;
			case 8: ctx.transform(0, -1, 1, 0, 0, iw); break;
			default: break;
		}

		ctx.drawImage(image, 0, 0 , iw, ih);
		const d=imgdiv.getBoundingClientRect();
		imxy = {x: d.x-50, y: d.y-50, xx: d.x + d.width+50, yy: d.y + d.height+50};
	};


	for(const [k, v] of Object.entries(icons)){
		const d = _ce('div',{'class':'rbtn'});
		const i = _ce('i',{'class':'ico-'+v});
		i.onclick = imgRot.bind(this,k);
		d.appendChild(i);
		nav.appendChild(d);
	}


	if(mtype === 1){

		for(const i of ['top','left','bottom','right','topleft','topright','bottomleft','bottomright']){
			const d = _ce('div',{id: i});
			_on(d,pointDown, (e) => {pDown(e,d);});
			cropsel.appendChild(d);
		}

		imgdiv.appendChild(cropbg);
		imgdiv.appendChild(cropsel);
		for(const [k, v] of Object.entries(aspects)){
			const b = _ce('button',0,{textContent: k});
			b.onclick = ()=>{
				curasp = k;
				[...nav.children].forEach((f) =>{
					f.classList.remove('active');
				});
				b.classList.add('active');

				imgRot();
				bound();
			}
			if(k === curasp){b.classList.add('active');}
			nav.appendChild(b);
		}
		_on(cropsel, pointDown, (e) => {pDown(e,e.target);});
		_on(window, pointMove, pointerMove);
		_on(window, pointUp, pointerUp);
		_on(window, 'resize', winSz);

		editor.prepend(image);
		editor.classList.add('loader');

		image.onload = ()=>{
			loaded = 1;
			editor.classList.remove('loader');
			wait(100,()=>{
				let iw = image.naturalWidth;
				let ih = image.naturalHeight;
				if(orientation > 4){
					[iw, ih] = [ih, iw];
				}
				crop.iw = iw;
				crop.ih = ih;
				imgRot();
				image.style.opacity = 0;
			});
		};
		image.onerror = (e) => {
			popup("Load error: "+file);
			tclose.click();
		};
		image.src = src;
	}else{
		//video
		const i = _ce('i',{'class': 'ico-start'});
		i.onclick = imgRot.bind(this,'start');
		nav.appendChild(_ce('div',{'class': 'cbtn'},0,{appendChild: i}));

		vcut.el.start = _ce('div',{'class': 'ctxt'},{textContent: numTime(0)});
		nav.appendChild(vcut.el.start);

		vcut.el.dur = _ce('i');
		vcut.el.audio = _ce('i',{'class': 'cbtn ico-sound'})
		vcut.el.audio.onclick = imgRot.bind(this,'sound');
		nav.appendChild(_ce('div',{'class': 'ctxt', style: 'flex:1;text-align:center'},0,{appendChild: [vcut.el.audio, vcut.el.dur]}));

		vcut.el.stop = _ce('div',{'class': 'ctxt'},{textContent: '-'});
		nav.appendChild(vcut.el.stop);

		const j = _ce('i',{'class': 'ico-stop'});
		j.onclick = imgRot.bind(this,'stop');
		nav.appendChild(_ce('div',{'class': 'cbtn'},0,{appendChild: j}));
		imgRot();
	}
	tclose.onclick = close;
}


function numTime(x,t){
	x=Math.round(x);
	t = (x>=3600)? Math.floor(x/3600).toString().padStart(2,'0') + ':' : '';
	t += (Math.floor(x/60)-Math.floor(x/3600)*60).toString().padStart(2,'0');
	t += ':' + Math.round(x%60).toString().padStart(2,'0');
	return t;
}





function updHist(ispush, m){
	if(!m){return;}
	if(!navi.hist && !ispush){
		navi.hist = 1;
	}
	ispush = ispush? 'push' : 'replace';
	var u = location.protocol+'//' + location.host + location.pathname + m.url;
	history[ispush + 'State'](m, document.title, u);
}


function getDirID(n){
	const o = Object.entries(Dir.d).find(([key, [d]]) => d === n)?.[0];
	return o ? parseInt(o,10) : null;
}


function updateDirSize(){
	const b = Object.entries(Dir.d).map(([i, v]) => ({i: parseInt(i), p: v[1], s: v[3], q: v[4]}));
	const pd = (i) => b.reduce((a, n) => n.p === i ? [...a, n, ...pd(n.i)] : a, []);
	b.forEach(r => {
		const g = pd(r.i);
		Dir.d[r.i][3]=g.reduce((a, c) => a + c.s, r.s);
		Dir.d[r.i][4]=g.reduce((a, c) => a + c.q, r.q);
	});
}


function thumbUrl(did, file, mt){
	let p = Dir.d[did][0];
	if(p !== ''){p+='/';}
	p = p + removeExt(file);
	p = p.split('/').map(f => encodeURIComponent(f)).join('/');
	return _p.url_thumbs + '/' + p + '.webp?'+mt;
}




async function buildMenu(){
	const rule = morderget();
	if(!rule.az){rule.az = msorter.default[rule.i];}
	const menuArr = (id) => {
		id = parseInt(id,10);
		const [path, pid, mt, sz, qt] = Dir.d[id];
		const name = getFilename(path);
		const cubs = Object.keys(Dir.d).filter(cid => Dir.d[cid][1] === id).map(menuArr).filter(ch => ch.pid !== 0);

		if(rule.by === 'size' || rule.by === 'date' ){
			const m = rule.by === 'size' ? 'sz' : 'mt';
			if(rule.az === 'a'){
				cubs.sort((a, b) => a[m] - b[m]);
			}else{
				cubs.sort((a, b) => b[m] - a[m]);
			}
		}else{
			if(rule.az === 'a'){
				cubs.sort((a, b) => {return a.name.toLowerCase().localeCompare(b.name.toLowerCase(), undefined, { sensitivity: 'base' });});
			}else{
				cubs.sort((a, b) => {return b.name.toLowerCase().localeCompare(a.name.toLowerCase(), undefined, { sensitivity: 'base' });});
			}
		}
		return {
			name, id, pid, path, mt, sz, qt,
			cubs: cubs || []
		};
	};
	_id('msorter').className = 'ico-' + rule.by + rule.az;

	const plus = (b) => {
		var nxtul = b.nextElementSibling;

		if(nxtul){
			nxtul.classList.toggle("down");
			b.closest("li").classList.toggle("open");

			if(nxtul.classList.contains("down")){
				slideDown(nxtul);
			}else{
				slideUp(nxtul);
			}
		}
	};

	const itag = (i) => {
		i.click();
	};

	const genLI = (folder) => {
		const li = _ce('li',{'data-id': folder.id, 'data-pid': folder.pid});

		let n = folder.name+''; let c ='';
		if(n === ''){n = 'Root';c = 'root';}
		const a = _ce('a',{'class': c, href: getUrlID(folder.id).url},{textContent: n});
		a.onclick = genPage.bind(this,folder.id, a);
		li.appendChild(a);
		if(folder.cubs.length > 0){
			const b = _ce('b');
			b.onclick = plus.bind(this,b);
			li.appendChild(b);
			li.classList.add('sub');

			const subUl = _ce('ul',{style: 'display:none'});
			folder.cubs.forEach((subFolder) => {
				const subLi = genLI(subFolder,'subul');
				subUl.appendChild(subLi);
			});

			li.appendChild(subUl);
		}else{
			const i = _ce('i');
			i.onclick = itag.bind(this,a);
			li.appendChild(i);
		}

		return li;
	};

	const menu = _id('menu');
	menu.innerHTML = '<ul id="premenu"><li><a href="?a=Albums"><u class="ico-album"></u>Albums</a></li><li><a href="?t=Timeline"><u class="ico-date"></u>Timeline</a></li><li><a href="?k=Tags"><u class="ico-tag"></u>Tags</a></li></ul>';

	menu.appendChild(_ce('ul',0,0,{appendChild: genLI(Object.keys(Dir.d).map(menuArr)[0])}));
}








const msorter = {
	names: ['name','size','date'],
	order: ['','a','d'],
	default: ['d','d','d']
};

function morderset(label,az){
	const i = msorter.names.indexOf(label);
	az = (msorter.default[i] === az) ? '' : az;
	const v = (i << 2) | (msorter.order.indexOf(az) & 0x03);
	navi.sort.menu = v;
	buildMenu();
	expandMenu();
	db.conf.put('sort', navi.sort).catch(e=>{});
}

function morderget(){
	let v = navi.sort.menu || 0;
	const i = ((v >> 2) & 0x03);
	const by = msorter.names[ i ];
	let az = msorter.order[ (v & 0x03) ];
	if(az === ''){az = msorter.default[i];}
	return {by,az,i};
}


const sorter = {
	names: ['name','size','date','dur','type','dim','best'],
	order: ['','a','d'],
	default: ['a','d','d','d','d','d','d']
};

function orderset(label,az){
	const m = navi.mode;
	const id = navi.mval;
	if(!navi.sort[m]){navi.sort[m] = {};}

	const i = sorter.names.indexOf(label);
	az = (sorter.default[i] === az) ? '' : az;

	const v = (i << 2) | (sorter.order.indexOf(az) & 0x03);
	if(v){
		navi.sort[m][id] = v;
	}else{
		delete navi.sort[m][id];
	}

	db.conf.put('sort', navi.sort).catch(e=>{});
}

function orderget(){
	const m = navi.mode;
	const id = navi.mval;
	if(!navi.sort[m]){navi.sort[m] = {};}

	const v = (navi.sort[m][id] || 0);
	const i = ((v >> 2) & 0x0F);
	const by = sorter.names[ i ];
	let az = sorter.order[ (v & 0x03) ];
	if(az === ''){az = sorter.default[i];}
	return {by,az,i};
}




const viewitms = {rows:[80,250,150],tiles:[80,220,140] /*,'grid','detail'*/};

function changeView(v){
	if(!v){
		v = navi.view.i || 'rows';
	}
	const m = _qs('main');
	for(const [i, a] of Object.entries(viewitms)){
		m.classList.remove(i);
	}
	m.classList.add(v);
	_id('viewbt').className = 'ico-'+v;

}
function changeRow(v,n){
	document.documentElement.style.setProperty('--'+n, v+'px');
}

async function cMenu(e,dhis,who,rpt){
	if(!rpt){
		e.preventDefault();
	}
	if(Date.now() - navi.cm.time < 1000){return 0;}
	if(navi.cm.open){
		hideContextMenu();
		return;
	}

	const cSelect = async (who,action,e,li) => {
		e.stopPropagation();
		e.preventDefault();
		console.log('cSelect ' + who +', '+ action, li, 'cel',dhis);
		switch (who){
			case 'exit':{
				act_exit(action);
				break;
			}
			case 'sort':{
				const rule = await orderget();
				const i = sorter.names.indexOf(action);
				let az;
				if(rule.by === action && rule.az !== ''){
					az = rule.az === 'a' ? 'd' : 'a';
				}else{
					az = sorter.default[i];
				}
				orderset(action, az);
				genPage( -1 );
				break;
			}
			case 'view':{
				navi.view.i = action;
				db.conf.put('view', navi.view).catch(e=>{});
				changeView(action);
				break;
			}
			case 'sels':{
				let newsel = {};
				for(const ah of _qsa('[data-tick]')){
					const fid = gensid(ah);
					newsel[fid] = parseInt(_att(ah,'data-s'),10);
				}
				if(action === 'none'){
					newsel = {};
				}
				if(action === 'invert'){
					for(const id in navi.ticked){
						delete newsel[id];
					}
				}
				navi.ticked = {...newsel};
				tickShow();
				break;
			}
			case 'msort':{
				const rule = morderget();
				const i = msorter.names.indexOf(action);
				let az;
				if(rule.by === action && rule.az !== ''){
					az = rule.az === 'a' ? 'd' : 'a';
				}else{
					az = msorter.default[i];
				}
				morderset(action, az);
				break;
			}
			case 'dir':{
				switch(action){
					case "delete":
						act_delete(dhis); break;
					case "move":
						act_move(dhis); break;
					case "info":
						act_info(dhis,1); break;
					case "rename":
						act_rename(dhis); break;
					case "refresh":
						let x = _att(dhis,'data-d')
						fsTask('refresh', [x+'//0/dir'], Dir.d[x][0], li);
						break;
				}
				break;
			}
			case 'alb':{
				switch(action){
					case "delete":
						remAlb(_att(dhis,'data-aid')); break;
					case "info":
						editAlb(_att(dhis,'data-aid')); break;
				}
				break;
			}
			case 'img':{
				if(lightbox.pswp && lightbox.pswp.isOpen){
					navi.backbtn = 1;
					lightbox.pswp.close();
				}
				switch(action){
					case "share":{
						act_share(dhis);
						break;
					}
					case "download":{
						act_download(dhis);
						break;
					}
					case "edit":{
						_id("tclose").click();
						act_edit(dhis);
						break;
					}
					case "delete":{
						act_delete(dhis);
						break;
					}
					case "eday":{
						genPage({t: new Date((dhis.href.split('=').pop()) * 1000).toISOString().split('T')[0]});
						break;
					}
					case "thumb":{
						set_thumb(dhis);
						break;
					}
					case "move":{
						act_move(dhis);
						break;
					}
					case "map":{
						act_map(dhis);
						break;
					}
					case "rename":{
						act_rename(dhis);
						break;
					}
					case "info":{
						act_info(dhis);
						break;
					}
					case "refresh":{
						act_refresh(dhis);
						break;
					}
				}
				break;
			}
			case 'dots':{
				switch (action){
					case "album":{
						imageAlb(1);
						break;
					}
					case "albumr":{
						imageAlb(0);
						break;
					}
					case "albumc":{
						makeAlb();
						break;
					}
					case "albumm":{
						editAlb(navi.mval);
						break;
					}
					case "share":{
						wait(1, act_share);
						break;
					}
					case "upload":{
						act_upload();
						break;
					}
					case "map":{
						act_map(0);
						break;
					}
					case "move":{
						act_move(0);
						break;
					}
					case "delete":{
						act_delete();
						break;
					}
					case "download":{
						act_download();
						break;
					}
					case "newdir":{
						popup('Create New folder<br/><input type="text" id="popinput" value="" autocomplete="off">','Confirmation',{
							buttons: [
								{
									text: "No"
								}, {
									text: "Yes",
									click: (html, pp) => {
										const sel = _qs('#popselect',html);
										const i = _qs('#popinput',html);
										if(!i.value){return true;}
										fsTask('newdir', [sel.value+'/'+i.value+'/dir'], i.value, li);
										return true;
									},
									key: "Enter",
									def: 1
								}
							],
							selects: dirArr(),
							selected: (navi.dir || Dir.home),
						});
						break;
					}
					case "refresh":{
						if(!Object.keys(navi.ticked).length){
							fsTask('refresh', [navi.dir+'//0/dir'], Dir.d[navi.dir][0], li);
						}else{
							act_refresh();
						}
						break;
					}
					case "left":
					case "right":{
						act_rot(action);
						break;
					}
				}
				break;
			}
		}
		hideContextMenu(e);
	};


	navi.cm.el = dhis;

	const clickX = e.clientX || e.touches[0].clientX;
	const clickY = e.clientY || e.touches[0].clientY;


	const el = e.target;

	if(!el || !el.getBoundingClientRect){
		return;
	}

	const items = {
		info: 'Info',
		rename: 'Rename',
		delete: 'Delete',
		upload: 'Upload',
		move: 'Move',
		download: 'Download',
		share: 'Share',
		name: 'Name',
		edit: 'Edit',
		size: 'Filesize',
		date: 'Date',
		dur: 'Duration',
		type: 'Filetype',
		best: 'Best',
		dim: 'Dimension',
		map: 'GPS Tag',
		newdir: 'New Folder',
		eday: 'Explore Day',
		album: 'Add to Album',
		albumc: 'Create Album',
		albumm: 'Manage Album',
		thumb: 'Set as Thumbnail',
		refresh: 'Rescan',
	};

	const cm = _id('cmenu');
	cm.innerHTML = '';

	const sels = Object.keys(navi.ticked);


	switch(who){

		case 'dots':{
			let t = 0;
			let a = sels.length === 0 ? 'upload,newdir,album'+((navi.mode == 'd')?',refresh':'') : 'left,refresh,move,album,map,share,download,delete';
			for(let i of a.split(',')){
				if( !_p.can[i] || (t && i === 'info')){continue;}

				if(!sels.length && i == 'album'){
					i = 'albumc';
					if(navi.mode == 'a' && navi.mval!='Albums'){
						i = 'albumm';
					}
				}

				const li = _ce('li');

				if(i=='left'){
					li.id = 'cmhd';
					for(const j of 'left,right'.split(',')){
						const w = _ce('i',{'class':'ico-'+j});
						w.onclick = (e)=>{cSelect(who,j,e,w);};
						li.appendChild(w);
					}
				}else{
					let u = items[i];
					if(navi.mode == 'a'){
						if(i == 'album'){continue;}
						if(i == 'delete'){u = 'Remove from Album';}
					}

					li.innerHTML = '<i class="ico-'+i+'"></i> '+u;
					li.onclick = (e)=>{cSelect(who,i,e,li);};
				}

				cm.appendChild(li);
				t++;
			}

			if(!t){
				cm.innerHTML = '<li>...</li>';
			}
			break;
		}
		case 'sels':{
			const c = {none: 'ring', invert: 'invert', all: 'tick'};
			for(const i of Object.keys(c)){
				const li = _ce('li');
				li.innerHTML = '<i class="ico-'+c[i]+'"></i> Select '+ucfirst(i);
				li.onclick = (e)=>{cSelect(who,i,e,li);};
				cm.appendChild(li);
			}
			break;
		}
		case 'img':{
			let l = 'info,eday,edit,map,thumb,rename,move,delete'.split(',');
			if(_att(dhis,'data-ext')){
				l = l.filter((i) => !['edit','map','thumb'].includes(i));
			}

			for(const i of l){
				if(!_p.can[i] || (i =='thumb' && navi.mode!='d')){continue;}
				const li = _ce('li');
				if(i=='info'){
					li.id = 'cmhd';
					for(const j of 'info,refresh,download,share'.split(',')){
						if(j=='refresh' && (_att(dhis,'data-ext') || !_p.can[j])){continue;}
						const w = _ce('i',{'class':'ico-'+j});
						w.onclick = (e)=>{cSelect(who,j,e,w);};
						li.appendChild(w);
					}
				}else{
					let u = items[i];
					if(i == 'delete' && navi.mode == 'a'){u = 'Remove from Album';}
					li.innerHTML = '<i class="ico-'+i+'"></i> '+u;
					li.onclick = (e)=>{cSelect(who,i,e,li);};
				}
				cm.appendChild(li);
			}
			break;
		}
		case 'dir':{
			for(const i of 'info,refresh,rename,move,delete'.split(',')){
				if(!_p.can[i]){continue;}
				const li = _ce('li');
				li.innerHTML = '<i class="ico-'+i+'"></i> '+items[i];
				li.onclick = (e)=>{cSelect(who,i,e,li);};
				cm.appendChild(li);
			}
			break;
		}
		case 'alb':{
			if(_p.can.edit){
				const li = _ce('li');
				li.innerHTML = '<i class="ico-info"></i> '+items.albumm;
				li.onclick = (e)=>{cSelect(who,'info',e,li);};
				cm.appendChild(li);

				const d = _ce('li');
				d.innerHTML = '<i class="ico-delete"></i> Remove Album';
				d.onclick = (e)=>{cSelect(who,'delete',e,d);};
				cm.appendChild(d);
			}
			break;
		}
		case 'sort':{
			if((navi.mode == 't' && navi.mval == 'Timeline') ||
				(navi.mode == 'a' && navi.mval == 'Albums') ||
				(navi.mode == 'k' && navi.mval == 'Tags')
			){
				cm.innerHTML = '<li>...</li>';break;
			}
			const rule = await orderget();
			for(const i of sorter.names){
				if(i=== 'best' && navi.mode!='s'){continue;}
				const li = _ce('li');

				let az;
				if(rule.by === i){
					az = rule.az === 'a' ? 'd' : 'a';
				}else{
					az = sorter.default[sorter.names.indexOf(i)];
				}
				if(i === rule.by){
					li.classList.add('sel');
				}

				li.innerHTML = '<i class="ico-'+i+az+'"></i> '+items[i];
				li.onclick = (e)=>{cSelect(who,i,e,li);};
				cm.appendChild(li);

			}
			break;
		}
		case 'msort':{
			const rule = await morderget();
			for(const i of msorter.names){
				const li = _ce('li');

				let az;
				if(rule.by === i){
					az = rule.az === 'a' ? 'd' : 'a';
				}else{
					az = msorter.default[msorter.names.indexOf(i)];
				}
				if(i === rule.by){
					li.classList.add('sel');
				}

				li.innerHTML = '<i class="ico-'+i+az+'"></i> '+items[i];
				li.onclick = (e)=>{cSelect(who,i,e,li);};
				cm.appendChild(li);
			}
			break;
		}
		case 'view':{
			const v = navi.view.i || 'rows';
			for(const [i, a] of Object.entries(viewitms)){
				const li = _ce('li');
				if(i === v){
					li.classList.add('sel');
				}

				li.innerHTML = '<i class="ico-'+i+'"></i> '+ucfirst(i);
				li.onclick = (e)=>{cSelect(who,i,e,li);};
				cm.appendChild(li);
			}
			const ip = _ce('input');
			_att(ip,'type','range');
			_att(ip,'min',viewitms[v][0]);
			_att(ip,'max',viewitms[v][1]);
			_att(ip,'list','szdd');

			ip.value = navi.view[v] || viewitms[v][2];


			ip.onchange = () => {
				changeRow(ip.value,v);
				navi.view[v] = ip.value;
				db.conf.put('view',navi.view).catch(e=>{});
			}
			const li = _ce('li');
			li.appendChild(ip);
			li.onclick = (e)=>{e.stopPropagation();};
			const dl = _ce('datalist');
			dl.id = 'szdd';
			const op = _ce('option');
			op.value = viewitms[v][2];
			dl.appendChild(op);
			li.appendChild(dl);
			cm.appendChild(li);
			break;
		}
		case 'exit':{
			const t = {1:'',2:' Everywhere'};
			for(const i in t){
				const li = _ce('li');
				li.innerHTML = '<i class="ico-exit"></i> Log out'+t[i];
				li.onclick = (e)=>{cSelect(who,i,e,li);};
				cm.appendChild(li);
			}
			break;
		}
	}

	cm.style.top = '-500px';
	cm.style.display = 'block';

	const rect = el.getBoundingClientRect();
	const scrollY = window.scrollY;
	const toploc = (el.clientHeight > 50 ? clickY : rect.top) - cm.clientHeight - 10 + scrollY;
	const bottomloc = (el.clientHeight > 50 ? clickY + 20 : rect.bottom + 10) + scrollY;
	const atbottom = bottomloc + cm.clientHeight <= window.innerHeight + scrollY;
	const top_pos = atbottom ? bottomloc : Math.max(scrollY, toploc);


	const left_loc = clickX - cm.clientWidth / 2;
	const left_pos = Math.max(10, Math.min(document.documentElement.clientWidth - cm.clientWidth - 10, left_loc + window.scrollX));


	navi.cm.pos = scrollY;
	cm.style.left = Math.round(left_pos) + 'px';
	cm.style.top = Math.round(top_pos) + 'px';
	cm.classList.add(atbottom ? 'top' : 'bottom');
	navi.cm.time = Date.now();

	dhis.classList.add('cm');
	cm.focus();
	wait(400,() => {
		navi.cm.open = 1;
		_on(document, 'click', hideContextMenu);
	});
}


function hideContextMenu(e){
	if(!navi.cm.open){return 0;}
	if(e){
		e.stopPropagation();
		e.preventDefault();
	}
	_off(document, 'click', hideContextMenu);

	const cm = _id('cmenu');
	cm.classList.add('chide');
	wait(200,() => {
		cm.style.top = '-500px';
		cm.style.display = 'block';
		cm.classList.remove('top', 'bottom','chide');
	});

	if(navi.cm.el){
		navi.cm.el.classList.remove('cm');
	}
	navi.cm.open=0;
	return 1;
}



function topbar(show){
	if(navi.pscroll<48){show = 1;}
	if(show == navi.top){return;}
	if(show){
		_id('header').classList.add('open');
		navi.top = 1;
	}else{
		_id('header').classList.remove('open');
		navi.top = 0;
	}
}


function sidebar(show){
	if(show>1){show = !navi.side;}
	if(show == navi.side){return;}
	if(show){
		hideContextMenu();
		topbar(1);
		_id('sidebar').classList.add('open');
		navi.side=1;

	}else{
		_id('sidebar').classList.remove('open');
		navi.side=0;
		topbar(0);
	}
}


const dirnameSrch = (kwds, allw) => {
	const r = [];
	if(kwds.length){
		const kwd = kwds.toLowerCase().split(' ');
		for(const key in Dir.d){
			const dn = Dir.d[key][0].split('/').pop().toLowerCase();
			const m = allw? kwd.every(w => dn.includes(w)) : kwd.some(w => dn.includes(w));

			if(m){
				r.push(key);
			}
		}
	}
	return r;
};


function scrollPos(){
	return (document.documentElement.scrollHeight - window.innerHeight);
}
const debScroll = debounce(lsscroll, 1000);
function backupScroll(){
	if(navi.edit || navi.noroll || !navi.mval){return;}
	const p = Math.round((window.scrollY / scrollPos()) * 100);
	const m = navi.mode;
	const id = navi.mval;
	if(!navi.scroll[m]){navi.scroll[m] = {};}
	if(p>0){
		navi.scroll[m][id]=p;
	}else{
		delete navi.scroll[m][id];
	}
	debScroll();
}


function restoreScroll(){
	let p = 0;
	const m = navi.mode;
	const id = navi.mval;
	if(!navi.scroll[m]){navi.scroll[m] = {};}
	if(!navi.noroll && navi.scroll[m][id]){
		p = (parseFloat(navi.scroll[m][id]) / 100) * scrollPos();
	}

	window.scrollTo({top: p});
}





function tickShow(e,el){
	if(this instanceof Element){el=this.parentElement;}

	if(e === 0){//clear all
		navi.ticked={};
		el=0;
	}

	if(el){
		let shf = 0; let action = 1;
		const imt = parseInt(el.id.replace('imt',''),10);
		if(e){
			e.stopPropagation();
			e.preventDefault();
			if(e.shiftKey){shf = 1;}
		}
		if(!shf){navi.lsel = imt;}
		const fid = gensid(el);
		if(parseInt(_att(el,'data-tick'),10)){
			delete navi.ticked[fid];
			action = -1;
		}else{
			navi.ticked[fid] = parseInt(_att(el,'data-s'),10);
		}
		if(shf && navi.lsel && navi.lsel !== imt){
			let mn = Math.min(navi.lsel, imt);
			const mx = Math.max(navi.lsel, imt);
			while(mn <= mx){
				const imid = _id('imt'+mn);
				if(imid){
					const idid = gensid(imid);
					if(action > 0){
						navi.ticked[idid] = parseInt(_att(imid,'data-s'),10);
					}else{
						delete navi.ticked[idid];
					}
				}
				mn++;
			}
		}
	}
	const count = Object.keys(navi.ticked).length;
	const mb = Object.values(navi.ticked).reduce((acc, val) => acc + val, 0);
	navi.ticksize = mb;
	_id('selected').innerHTML = count + ' file'+ (count>1 ? 's' : '') +', ' + filesize(mb);
	if(count){
		_id('topbar').classList.add('tick');
	}else{
		_id('topbar').classList.remove('tick');
	}

	for(const ah of _qsa('[data-tick]')){
		const id = gensid(ah);
		_att(ah,'data-tick', navi.ticked[id] ? '1' : '0');
	}

}


const cityDb = {};
function citySrch(e){
	let kwd = e.target.value;
	kwd = kwd.replace(/\s+/g, ' ').trim();
	const upcity = (d) => {
		const id = _id('clat');
		id.innerHTML = '';
		for(const [k, v] of Object.entries(d)){
			id.appendChild(_ce('option',{value: k}, {textContent: v}));
		}
	};
	if(kwd.length>2){
		if(cityDb[kwd]){
			upcity(cityDb[kwd]);
		}else{
			post({
				params: 'task=city&name='+encodeURIComponent(kwd),
				complete: function(j){
					if(!j.ok){toast(j.msg,{theme:'red',timeout:0,close:1}); return;}
					for(const [k, v] of Object.entries(j.msg)){
						cityDb[k] = {[k]: v};
					}
					cityDb[kwd] = {...j.msg};
					upcity(j.msg);
				}
			});
		}
	}
}


const act_share = async (id) => {
	if(!('canShare' in navigator)){
		toast('Share not supported');
		return;
	}
	const [fid,d,name,fileid,single] = getselected(id);
	if(!fid){return;}

	if(fid.length){
		const aborter = new AbortController();
		const sigbort = aborter.signal;

		const rstrmArr = async (rstrm, sigbort) => {
			const chunks = [];
			const reader = rstrm.getReader();

			while (true){
				const { done, value } = await reader.read();
				if(sigbort && sigbort.aborted){
					reader.cancel();
					toast('Download aborted.');
					throw new Error('Download aborted');
				}
				if(done){
					break;
				}

				chunks.push(value);
			}
			return new Blob(chunks);
		};
		let dBytes = 0;

		const fcontent = [];

		const pxp = popup('','0.00%',{buttons:[{text:'Stop',key: 'escape',def: 1,click: async (html, pp) => {
			aborter.abort();
			return true;
		}}],bgclose: 0});

		try{
			for(let i = 0; i < fid.length; i++){
				const [id, fileName, fileid]=fid[i].split('/');
				const el = _qs('[data-i="'+fileid+'"]');
				if(!el){continue;}

				pxp.msg('Downloading '+ (i+1)+' of '+fid.length+'<br>'+fileName);

				const res = await fetch(el.href, { cache: "force-cache", signal: sigbort });

				const rstrm = res.body.pipeThrough(new TransformStream({
					transform(chunk, controller){
						dBytes += chunk.length;
						pxp.head(((dBytes/navi.ticksize)*100).toFixed(2) + '%');
						pxp.bar(dBytes, navi.ticksize);
						controller.enqueue(chunk);
					}
				}));

				const blob = new Blob([await rstrmArr(rstrm, sigbort)], { type: res.headers.get('Content-Type') });
				fcontent.push({ blob, fileName });
			}
		}catch(error){
			toast('Download error: '+ error.message);
		} finally {
			aborter.abort();
		}

		pxp.bar(0, 1);
		const data = {
			files: fcontent.map(({ blob, fileName }) => {
				return new File([blob], fileName, {
					type: blob.type,
				});
			})
		};
		pxp.head('Confirmation');
		pxp.msg('Share?<br>'+name);
		pxp.btn([
			{
				text: "No"
			},{
				text: "Yes",
				click: async (html, pp) => {
					if(navigator.canShare(data)){
						try{
							await navigator.share(data);
						}catch(e){
							console.error(e);
							let e = JSON.stringify(e);
							if(e.length>3){
								wait(500,()=>{popup(,'Share failed');});
							}
						}
					}else{
						wait(500,()=>{popup('Not supported: navigator.canShare()','Share failed');});
					}
					return true;
				},
				key: "Enter",
				def: 1
			}
		]);
	}
};

function dirArr(){
	return Object.entries(Dir.d).map(([k, v]) => [k, '/' + v[0]]).sort((a, b) => a[1].localeCompare(b[1]));
}

function albObj(){
	return Dir.a.reduce((a,k,i) => {a[k[0]] = {name: k[1], qt: k[2], mt: k[3], own: k[4], family: k[5], share: k[6]};return a;},{});
}


function gensid(el){
	const t = el.classList.contains('dir')?'dir':'file';
	let fileid = [
		_att(el, 'data-d'),
		_qs('f-nm',el).textContent,
		_att(el, 'data-i'),
		t
	];
	return fileid.join('/');
}


function getselected(id){
	let fid,d,name,fileid=0,single=1;
	if(id){
		const x = gensid(id);
		const b = x.split('/');
		d = parseInt(b[0],10);
		name = b[1];
		fileid = parseInt(b[2],10);
		fid = [ x ];
	}else{
		fid = Object.keys(navi.ticked);
		if(fid.length === 0){
			return [0,0,0,0];
		}
		name = _id('selected').innerHTML;
		d = fid[0].split('/')[0];
		single=0;
	}
	return [fid,d,name,fileid,single];
}


function act_map(id){
	const [fid,d,name,fileid,single] = getselected(id);
	if(!fid){return;}

	let map='', city='';
	if(single){
		const i = _qs('[data-loc]',id);
		if(i){
			map = _att(i,'data-loc');
			cityDb[map] = {[map]: i.textContent};
			city = '<option value="'+map+'">'+i.textContent+'</option>';
		}
	}

	popup('New GPS for '+name+'<p><input type="text" id="map" value="'+map+'" list="clat"><datalist id="clat">'+city+'</datalist></p>','Confirmation',{
		buttons: [
			{
				text: "No"
			}, {
				text: "Yes",
				click: (html) => {
					const cord = _qs('#map', html).value;
					if(cord.split(',').length === 2){
						fsTask('gps-tag', fid, cord, 0);
						return true;
					}
					_qs('cite', html).innerHTML = 'Invalid';
					return false;
				},
				key: "Enter",
				def: 1
			}
		],
		bgclose: 0
	});
	_id('map').oninput = debounce(citySrch,1000);
	_id('map').focus();

}

function act_rename(id){

	const [fid,d,name,fileid,single] = getselected(id);
	if(!fid){return;}


	popup('Rename '+name+' to<br/><input type="text" id="popinput" value="'+name+'" required autocomplete="off">','Confirmation',{
		buttons: [
			{
				text: "No"
			}, {
				text: "Yes",
				click: (html) => {
					const i = _qs('#popinput',html);
					if(i.value === name){return true;}
					fsTask('rename', fid, i.value, id);
					return true;
				},
				key: "Enter",
				def: 1
			}
		],
		bgclose: 0
	});
	_id('popinput').focus();

}

function act_refresh(id){
	const [fid,d,name,fileid,single] = getselected(id);
	if(!fid){return;}
	fsTask('refresh', fid, '', id);
}

function act_info(el,dir){
	const d = Dir.d[_att(el, 'data-d')];
	let p = '/' + d[0]; let m;
	if(dir){
		p = p.substring(0, p.lastIndexOf('/'));
		m = d[2];
	}else{
		m = el.href.split('=').pop();
	}

	const n = {
		Name: _qs('f-nm',el).textContent,
		Size: _qs('f-sz',el).textContent,
	};

	let v;
	if(v = _qs('f-qt',el)){
		n.Files = v.textContent;
	}
	n.Path = p;
	n.Modified = fullDate(m);
	if(v = _qs('f-dev',el)){
		n.Device = v.textContent;
	}
	if(v = _qs('f-kw',el)){
		n.Keywords = v.textContent;
	}
	if(v = _qs('f-rs',el)){
		n.Resolution = v.textContent;
	}
	if(v = _qs('f-gps',el)){
		n.GPS = _att(v,'data-loc');
		n.Place = v.textContent;
	}

	const t = _ce('table',{'class':'inf'});
	for(const i in n){
		const r = _ce('tr');

		const a = _ce('td');
		a.textContent = i+':';
		r.appendChild(a);

		const b = _ce('td');
		b.textContent = n[i];
		r.appendChild(b);

		t.appendChild(r);
	}

	popup(t,'Info',{
		buttons: [
			{
				text: "Close",
				key: "Enter",
				def: 1
			}
		]
	});

}

function act_rot(act,id){
	const [fid,d,name,fileid,single] = getselected(id);
	if(!fid){return;}

	popup('Rotate '+act+' '+name+'?<p><label><input id="tow" style="width:auto" name="tow" type="checkbox" checked> Overwrite</label></p>','Confirmation',{
		buttons: [
			{
				text: "No"
			}, {
				text: "Yes",
				click: (html) => {
					if(_id('tow').checked){
						act += '-1';
					}
					fsTask('rotate', fid, act, id);
					return true;
				},
				key: "Enter",
				def: 1
			}
		],
		bgclose: 0
	});
}


function imageAlb(act,id,alb){
	const [fid,d,name,fileid,single] = getselected(id);
	if(!fid){return;}
	let i = [];
	for(const j of fid){
		i.push(j.split('/')[2]);
	}
	i = i.join('%2C',i);
	if(act){
		let alist = [...Dir.a];
		if(!alb && !alist.length){alb='c';}
		if(!alb){alb=alist[0][0];}
		alist.unshift(['c','-- Create a New Album --']);
		popup('Add '+name+' to Album','Confirmation',{
			buttons: [
				{
					text: "No"
				}, {
					text: "Yes",
					click: (html) => {
						const sel = _qs('#popselect',html);
						const sv = sel.value;
						if(!sv){return false;}
						if(sv == 'c'){
							wait(300,()=>{makeAlb(1);});
						}else{
							post({params:'task=album&act=add&aid='+sv+'&fids='+i,
								complete: function(j){
									let m = {theme:'red',timeout:0};
									if(j.ok){
										m={};
									}
									toast(j.msg,m);
								}
							});
						}
						return true;
					},
					key: "Enter",
					def: 1
				}
			],
			selects: alist,
			selectt: 'Select an Album',
			selected: alb,
			bgclose: 0
		});
	}else{
		const ab = albObj();
		popup('Remove '+name+' from '+ab[navi.mval].name+'?','Confirmation',{
			buttons: [
				{
					text: "No"
				}, {
					text: "Yes",
					click: (html) => {
						post({params:'task=album&act=rem&aid='+navi.mval+'&fids='+i,
							complete: function(j){
								let m = {theme:'red',timeout:0};
								if(j.ok){
									m={};
									genPage(0);
								}
								toast(j.msg,m);
							}
						});
						return true;
					},
					key: "Enter",
					def: 1
				}
			],
			bgclose: 0
		});
	}
}


function itick(n,t,c,ty='checkbox'){
	const l = _ce('label',{'class':'togg'});
	const i = _ce('input',{'type':ty,'name':n});
	if(c){_att(i,'checked','');}
	l.appendChild(i);
	l.appendChild(document.createTextNode(' ' + t));
	return {l,i};
}

function makeAlb(add){
	editAlb(0,{'0':{name:'',share:0,family:0,own:1}},add);
}
function editAlb(aid,make,add){
	const ab = make || albObj();
	const a = ab[aid];
	if(!a){toast('No Album');return;}
	const el = _ce('div');
	const p = '^(?![\\.]*?$)[^<>=\\|\\:\\*\\/\\\\\\?]*$';
	if(aid){
		const mt = _ce('div');
		mt.innerText = 'Last updated: '+relativeDate(a.mt);
		el.appendChild(mt);
	}
	const an = _ce('input',{pattern: p, value: a.name});

	const sd = _ce('div');
	const sc = _ce('code');
	sd.appendChild(sc);

	const slnk =(t)=>{
		if(sh.i.checked && t){
			sd.classList='';
			let l = window.location.href;
			l = l.substring(0, l.lastIndexOf('/'));
			l += '/share.php?a='+encodeURIComponent(t);
			sc.innerText = l;
		}else{
			sd.classList='hide';
		}
	};

	const sh = itick('s','Public', a.share);
	sh.i.onchange = ()=>{
		if(sh.i.checked){
			if(!sn.value){sn.value = a.name.replace(/ /g,'');}
			slnk(sn.value);
		}else{
			slnk(0);
		}
	};

	const sn = _ce('input',{pattern: p.replace(/>/,'> ')});
	if(a.share){
		sn.value=a.share;
		slnk(a.share);
	}else{
		sn.value = a.name.replace(/ /g,'');
		an.oninput = ()=>{
			sn.value = an.value.replace(/ /g,'');
			slnk(sn.value);
		};
		slnk(0);
	}


	sn.oninput = ()=>{
		slnk(sn.value);
	};
	sd.appendChild(sn);
	const fm = itick('f','Family', a.family);
	if(!a.own){fm.i.disabled = true;}


	el.appendChild(an);
	el.appendChild(_ce('br'));
	el.appendChild(sh.l);
	el.appendChild(fm.l);
	el.appendChild(sd);

	popup(el,(aid?'Edit':'Create')+' Album: '+a.name,{
		buttons: [
			{
				text: "Close"
			}, {
				text: "Save",
				click: (html) => {
					post({params:'task=album&act=edit&aid='+aid+'&fam='+((a.own && fm.i.checked)? 1 : 0)+'&name='+encodeURIComponent(an.value)+'&shr='+encodeURIComponent(sh.i.checked? sn.value : 0),
						complete: function(j){
							let m = {theme:'red',timeout:0};
							if(j.ok){
								m={};
								if(!aid){
									toast('Album Created: '+an.value);
									if(add){
										wait(300,()=>{imageAlb(1,0,j.msg);});
										return;
									}
								}
								navi.reload = 1;
								genPage(0);
							}
							toast(j.msg,m);
						}
					});
					return true;
				},
				key: "Enter",
				def: 1
			}
		],
		bgclose: 0
	});
}

function remAlb(aid){
	const a = albObj();
	if(!a[aid]){toast('No Album');return;}
	popup('','Remove Album "'+a[aid].name+'"?',{
		buttons: [
			{
				text: "No"
			}, {
				text: "Yes",
				click: (html) => {
					post({params:'task=album&act=rema&aid='+aid+'&name='+encodeURIComponent(a[aid].name),
						complete: function(j){
							let m = {theme:'red',timeout:0};
							if(j.ok){
								m={};
								navi.reload = 1;
								genPage(0);
							}
							toast(j.msg,m);
						}
					});
					return true;
				},
				key: "Enter",
				def: 1
			}
		],
		bgclose: 0
	});

}


function act_move(id){
	const [fid,d,name,fileid,single] = getselected(id);
	if(!fid){return;}

	popup('Move '+name+' to','Confirmation',{
		buttons: [
			{
				text: "No"
			}, {
				text: "Yes",
				click: (html) => {
					const sel = _qs('#popselect',html);
					if(sel.value === d){return true;}
					fsTask('move', fid, sel.value, id);
					return true;
				},
				key: "Enter",
				def: 1
			}
		],
		selects: dirArr(),
		selected: d,
		bgclose: 0
	});
}

function act_delete(id){
	if(navi.mode == 'a'){imageAlb(0,id);return;}
	const [fid,d,name,fileid,single] = getselected(id);
	if(!fid){return;}

	popup('Delete '+name+' ?','Confirmation',{
		buttons: [
			{
				text: "No"
			}, {
				text: "Yes",
				click: (html) => {
					fsTask('delete', fid, 0, id);
					return true;
				},
				key: "Enter",
				def: 1
			}
		],
		bgclose: 0
	});
}

function act_download(id){
	const [fid,d,name,fileid,single] = getselected(id);
	if(!fid){return;}
	fsTask('download', fid, 0, id);
}

function set_thumb(id){
	const [fid,d,name,fileid,single] = getselected(id);
	if(!fid){return;}

	popup(name+' to','Set Thumbnail', {
		buttons: [
			{
				text: "No"
			}, {
				text: "Yes",
				click: (html) => {
					const sel = _qs('#popselect',html);
					if(sel.value === d){return true;}
					fsTask('thumb', fid, sel.value, id);
					return true;
				},
				key: "Enter",
				def: 1
			}
		],
		selects: dirArr(),
		selected: d,
		bgclose: 0
	});
}


function fsTask(task, fid, newitm, el, tst, tot, lmsg, err){
	if(!tst){
		navi.tasks = fid;
		tot = navi.tasks.length;
		tst = toast(task,{timeout:0,theme:'black',click:()=>{if(navi.tasks.length){toast('Aborted');navi.tasks=[];}},close:1});
		tst.el.classList.add('loader');
		lmsg = '';
	}
	const tickid = navi.tasks.shift();
	const [id, name, fileid, itype] = tickid.split('/');
	if(!el){
		el = _qs('[data-i="'+fileid+'"]');
	}
	if(!el){
		toast(name+' missing',{theme:'red'});
		if(navi.tasks.length){
			fsTask(task, navi.tasks, newitm, 0, tst, tot, lmsg, err);
		}
		return;
	}
	const cur = tot - navi.tasks.length;
	const msg = cur +'/'+tot+' '+ucfirst(task)+' '+name;
	tst.update( msg + lmsg );
	tst.bar(cur, tot);

	if(task === 'download'){
		const ah = document.createElement("a");
		ah.href = el.href;
		ah.download = name;
		document.body.appendChild(ah);
		wait(1,()=>{ah.click();});
		wait(200,()=>{
			document.body.removeChild(ah);
			if(navi.tasks.length){
				fsTask(task, navi.tasks, newitm, 0, tst, tot, lmsg, err);
			}else{
				wait(3000,()=>{tst.dismiss();});
			}
		});
		return;
	}

	el.classList.add('loader');
	el.scrollIntoView({behavior:'smooth', block:'center'});
	post({
		params: 'task='+task+'&id='+ id +'&fid='+ fileid +'&name=' + encodeURIComponent(name)+ '&new='+encodeURIComponent(newitm)+ '&type='+itype,
		complete: function(res){
			if(res.ok){
				lmsg = '<br>'+res.msg;

				if('delete,gps-tag,rotate'.includes(task)){
					if(itype==='dir'){
						db.d.del(Dir.d[id][1]).catch(e=>{});
					}else{
						db.files.del(fileid).catch(e=>{});
					}
				}
				if('move,rename'.includes(task)){
					if(itype==='dir'){
						if(task==='rename'){
							let newn = Dir.d[id][0].split('/');
							newn[newn.length - 1] = newitm;
							Dir.d[id][0] = newn.join('/');
							buildMenu();
						}else if(task==='move'){
							navi.reload = 1;
							Dir.d[Dir.d[id][1]][2]=1;
							db.d.del([Dir.d[id][1],newitm]).catch(e=>{});
						}
					}else{
						db.d.del([id,newitm]).catch(e=>{});
					}
				}
				if('newdir,refresh'.includes(task)){
					db.d.del(id).catch(e=>{});
				}
				if(!('gps-tag,thumb'.includes(task))){
					_on(el, "transitionend", () => {
						el.remove();
					});
					el.classList.add('anim');
				}
				if(itype==='file'){
					try{delete navi.ticked[tickid];}catch(e){}
				}
				tickShow();
			}else{
				lmsg = '<br>Failed: '+name;
				el.scrollIntoView({behavior:'smooth', block:'center'});
				el.focus();
				toast(res.msg,{theme:'red',timeout:0,click:()=>{},close:1});
			}
			tst.update( msg + lmsg);
			if(navi.tasks.length){
				fsTask(task, navi.tasks, newitm, 0, tst, tot, lmsg, err);
			}else{
				if(!err){
					genPage();
					tst.el.classList.remove('loader');
					wait(4000,()=>{tst.dismiss();});
				}
			}

		},
		always: function(){
			el.classList.remove('loader');
		}
	});
}




function getUrlID(n){
	if(n === undefined){
		n = Object.fromEntries(new URLSearchParams(window.location.search));
	}
	if(typeof n !== 'object'){
		n = {d: n, n: Dir.d[n][0]};
	}else{
		if(!(['s','t','d','k','a'].some(k => Object.keys(n).includes(k)))){
			if(!Dir.home){return null;}
			n = {d: Dir.home};
		}
	}
	if(n.hasOwnProperty('d') && /^\d+$/.test(n.d)){
		n.d = parseInt(n.d,10); n.n = Dir.d[n.d][0];
	}
	n.url = '?'+ new URLSearchParams(n).toString();
	return n;

}


const vduration = (i) => {
	if(!i){i=0;}
	let t = Math.floor(i/3600).toString().padStart(2,'0') + ':';
	t += (Math.floor(i/60)-Math.floor(i/3600)*60).toString().padStart(2,'0');
	t += ':' + Math.round(i%60).toString().padStart(2,'0');
	return t;
};

function svgExt(ext){
	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M6 2c-1 0-2 1-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6H6Z" class="sbg"/><path class="sco" d="m14 6c0 1 1 2 2 2h4l-6-6z"/><text x="25%" y="70%" font-family="sans-serif" font-size=".4em" lengthAdjust="spacingAndGlyphs" textLength="50%" class="stx">'+ext.toUpperCase()+'</text></svg>';
}



function expandMenu(){
	const cr = _id("breadcrumbs");
	cr.innerHTML = '';
	let id = navi.dir;
	if(navi.mode=='s'){
		id = 0;
		const m = getUrlID();
		const s = _ce('a',{href: m.url});
		s.onclick = ()=>{searchList(m);return false;};
		s.innerHTML = 'Search: <i>'+navi.kwd+'</i>';
		cr.appendChild(s);
	}
	const bc = (id,home) => {
		let n = getFilename(Dir.d[id][0]);
		if(n === '' && !home){return;}
		const c = _ce('a',{href: getUrlID(id).url});
		c.onclick = genPage.bind(this, id, c);
		if(home){
			c.className = 'ico-home';
			c.title = 'Home';
			id = 0;
			n = '';
		}
		c.textContent = n;
		cr.prepend(c);
	};
	while ( id > 0 ){
		if(id !== Dir.home){
			bc(id);
		}
		id = Dir.d[id][1];
	}
	if(Dir.home){
		bc(Dir.home,1);
	}


	let f = _qsa("#menu li[data-id]");
	f.forEach(function(li){
		li.classList.remove("curr");
		if(_att(li,"data-id") == navi.dir){
			let p = li;
			while (p && p.tagName !== 'DIV'){
				if(p.tagName === 'UL'){
					p.classList.add('down');
					p.style.display = 'block';
				}
				if(p.tagName === 'LI'){
					p.classList.add('open');
				}
				p = p.parentNode;
			}
			for(const ul of li.children){
				if(ul.tagName === 'UL' && !ul.classList.contains("down")){
					ul.classList.add("down");
					slideDown(ul);
					li.classList.add("open");
					break;
				}
			}
			li.classList.add("curr");
			wait(200,()=>{
				li.scrollIntoView({behavior:'smooth', block:'center'});
			});
		}
	});

}


const srchOpts = () => {
	_id('selectbar').innerHTML = '<div style="display:none" id="advsrch"><label class="togg"><input type="checkbox" name="w" checked> Match All words</label><br/><label class="togg"><input type="checkbox" name="p" checked> Photos</label> <label class="togg"><input type="checkbox" name="v" checked> Videos</label></div>';
	_qs('body').classList.add('search');

};


const idbsave = async (j)=>{
	if(!j.mode){return;}
	if(j.ids.length){
		let fd;
		if(Array.isArray(j.fds)){
			fd = j.ids.reduce((a,k,i) => {a[k] = j.fds[i];return a;},{});
		}else{
			fd = j.fds;
		}
		await db.files.put(fd).catch(e=>{});
	}
	const s = {mt: j.mt, ids: j.ids};
	if(j.i){s.i = j.i;}
	await db[j.mode].put(j.key, s).catch(e=>{});
};

const idbload = async (mode,key)=>{
	let f = await db[mode].get(key).catch(e=>{});
	if(f){
		f.fds = {};
		f.mode = mode;
		f.key = key;
		if(f.ids.length){
			f.fds = await db.files.get(f.ids).catch(e=>{});
			if(!f.fds){f=0;}
		}
	}
	return f;
};


let thumbabrt = new AbortController();

const thumbLoader = async (updrefresh, tst, tot)=>{
	if(!navi.thumbs.length){
		if(tst){wait(500,()=>{tst.dismiss();});}
		if(updrefresh){genPage();}
		return;
	}

	if(!tst){
		tot = navi.thumbs.length;
		tst = toast('T',{timeout:0,theme:'black',click:()=>{if(navi.thumbs.length){toast('Aborted');navi.thumbs=[];}},close:1});
		tst.el.classList.add('loader');
	}

	const [el, src, fid, url, n] = navi.thumbs.shift();
	const a = el.parentElement;
	a.classList.add('loader');
	thumbabrt = new AbortController();
	const signal = thumbabrt.signal;

	const cur = tot - navi.thumbs.length;
	tst.update( cur +'/'+tot+' Generating Thumbnails<br/>'+n );
	tst.bar(cur, tot);

	try{
		const response = await fetch(location.pathname+url, { signal });
		const js = await response.json();
		if(js.ok){
			el.src = src;
			a.dataset.pswpWidth = js.msg.w;
			a.dataset.pswpHeight = js.msg.h;
			db.files.get(fid).then( f => {
				if(f){
					f.th = 0;
					f.w = js.msg.w;
					f.h = js.msg.h;
					db.files.put(fid, f).catch(e=>{});
				}
			}).catch(e=>{});
		}
	}catch(e){
		if(e.name === 'AbortError'){tst.dismiss();return;}
		updrefresh=0;
	}
	a.classList.remove('loader');
	thumbLoader(updrefresh, tst, tot);
};


const getFresh = (kwd, el, mt, n)=>{
	if(!el){el = _id('sorter');}
	el.classList.add('loader');
	const loadtoast = toast('Loading: '+(n||kwd),{timeout:0,theme:'black',click:()=>{}, prep:1});

	post({
		params: 'task=srch&m='+navi.mode+'&name='+encodeURIComponent(kwd),
		complete: function(json){
			if(json.mode){
				idbsave(json);
				navi.reload = 0;
				media(json, n);
			}
		},
		always: function(){
			loadtoast.dismiss();
			el.classList.remove('loader');
		}
	});

};

const albList = async (i,el,kwd) => {
	if(navi.mode != 'a'){
		navi.mode = 'a';
		navi.mval = -1;
	}
	kwd = i.a.toString().replace(/\s+/g, ' ').trim();
	if(kwd != navi.mval || navi.issort){
		if(!navi.issort){navi.ticked={};}
		navi.kwd = kwd;
		navi.dir = 0;

		if(kwd=='Albums'){
			return media({fds:{},key: kwd});
		}
		kwd = i.a.toString().replace(/\D/g, '');
		if(kwd.length){
			let mt = 0;
			const al = albObj();
			if(!navi.reload){
				const f = await idbload('a',kwd).catch(e=>{});
				mt = f? f.mt : 0;
				if(al[kwd] && mt === al[kwd].mt){return media(f);}
			}
			getFresh(kwd, el, mt, al[kwd].name);

		}
	}
	return false;
};

const timeList = async (i,el,kwd) => {
	if(navi.mode != 't'){
		navi.mode = 't';
		navi.mval = -1;
	}
	kwd = i.t.replace(/\s+/g, ' ').trim();
	if(kwd != navi.mval || navi.issort){
		if(!navi.issort){navi.ticked={};}
		navi.kwd = kwd;
		navi.dir = 0;
		if(kwd.length){
			let mt = 0;
			if(!navi.reload){
				const f = await idbload('t',kwd).catch(e=>{});
				mt = f? f.mt : 0;
				if(mt === Dir.m){return media(f);}
			}
			getFresh(kwd, el, mt);
		}
	}
	return false;
};

const keyList = async (i,el,kwd) => {
	if(navi.mode != 'k'){
		navi.mode = 'k';
		navi.mval = -1;
	}
	kwd = i.k.replace(/\s+/g, ' ').trim();
	if(kwd != navi.mval || navi.issort){
		if(!navi.issort){navi.ticked={};}
		navi.kwd = kwd;
		navi.dir = 0;
		if(kwd.length){
			let mt = 0;
			if(!navi.reload){
				const f = await idbload('k',kwd).catch(e=>{});
				mt = f? f.mt : 0;
				if(mt === Dir.m){return media(f);}
			}
			getFresh(kwd, el, mt);
		}
	}
	return false;

};


const searchList = async (i,el,kwd) => {
	kwd = i.s.replace(/\s+/g, ' ').trim();
	if(navi.mode != 's'){
		navi.mode = 's';
		navi.mval = -1;
		const v = _id('srch').value;
		if(v != kwd){_id('srch').value = kwd;}
	}
	if(kwd != navi.mval || navi.issort){
		if(!navi.issort){navi.ticked={};orderset('best', '');}
		navi.kwd = kwd;
		navi.dir = 0;
		srchOpts();
		_id('srch').blur();

		if(kwd.length){
			let mt = 0;
			if(!navi.reload){
				const f = await idbload('s',kwd).catch(e=>{});
				mt = f? f.mt : 0;
				if(mt === Dir.m){return media(f);}
			}

			getFresh(kwd, el, mt);
		}
	}
	return false;
};


async function dirList(i,el,kwd){
	kwd = i.d;
	if(!kwd || !(/^\d+$/.test(kwd))){
		kwd = Dir.home;
	}
	kwd = kwd + '';
	if(navi.mode!='d'){
		navi.mode = 'd';
		navi.mval = -1;
		_qs('body').classList.remove('search');
	}
	if(!Dir.d[kwd]){toast('Invalid dir '+kwd);return false;}
	if(kwd != navi.mval || navi.issort){
		if(!navi.issort){navi.ticked={};}
		if(kwd.length){
			let mt = Dir.d[kwd][2];
			if(!navi.reload){
				const f = await idbload('d',kwd).catch(e=>{});
				mt = f? f.mt : 0;
				if(mt && mt === Dir.d[kwd][2]){return media(f);}
			}
			getFresh(kwd, el, mt, Dir.d[kwd][0]);
		}
	}
	return false;
}

async function genPage(i,el){
	console.log('genPage:',i,el,event);

	if(event){event.preventDefault();}
	if(typeof i !== 'object'){
		i = {d: i};
	}
	if(i.hasOwnProperty('d')){
		if(i.d < 0){
			navi.issort = 1; i = getUrlID();
		}else if(!i.d){
			navi.issort = -1; i = getUrlID();
		}
	}
	if(i.hasOwnProperty('s')){
		return searchList(i);
	}else if(i.hasOwnProperty('t')){
		return timeList(i);
	}else if(i.hasOwnProperty('k')){
		return keyList(i);
	}else if(i.hasOwnProperty('a')){
		return albList(i);
	}else{
		return dirList(i,el);
	}

}


function imld(){
	const a = this.parentElement;
	a.classList.add("loaded");
}

function rightMenu(ah,i){
	_on(ah, 'contextmenu', (e) => cMenu(e, ah, i));
	_on(ah, 'touchstart', (e) => {
		if(e.touches.length === 1){
			navi.longpress = setTimeout(function(){
				cMenu(e, ah, i);
			}, 500);
		}
	}, false);
	_on(ah, 'touchmove', (e) => clearTimeout(navi.longpress));
	_on(ah, 'touchend', (e) => clearTimeout(navi.longpress));
}

async function media(j, n, f, m ){
	if(Array.isArray(j.fds)){
		j.fds = j.ids.reduce((a,k,i) => {a[k] = j.fds[i];return a;},{});
	}

	navi.mval = j.key;
	navi.issort = 0;
	navi.dir = (navi.mode=='d')? parseInt(navi.mval,10) : 0;

	f = j.fds;
	for(const k in f){
		f[k].w = (f[k].w || 0);
		f[k].h = (f[k].h || 0);
		f[k].r = (f[k].w || 0) * (f[k].h || 0);
		f[k].i = k;
	}


	const gs = _qs(".gallery");
	gs.classList.remove('fade');
	gs.classList.add('anim');

	const g = _ce('div',{'class':'gallery fade'});
	const rule = orderget();


	let tQt = 0, tSz = 0, tTl, folder = [], foldersort;


	tTl = (n||navi.mval);

	if(navi.mode=='s'){
		folder = dirnameSrch(navi.mval, false);
		_id('header').classList.remove('inside');
	}else if(navi.mode=='a'){
		if(navi.mval === 'Albums'){
			for(const a in Dir.a){
				const [id, name, qt, mt, own, family, share] = Dir.a[a];
				const u = getUrlID({a: id, n: name});
				const ah = _ce('a',{'class': 'stack', href: u.url, 'data-aid': id});
				ah.onclick = genPage.bind(this, u, ah);
				const im = _ce('img',{loading:'lazy'});
				im.onload = ()=>{im.className='sh';ah.classList.add("loaded");};
				if(qt){
					im.src = window.location.pathname + '?athmb='+id+'&mt='+mt;
				}
				ah.appendChild(im);
				ah.appendChild(_ce('i-con'));
				const fo = _ce('div',{'class':'info'});
				const fnm = _ce('f-nm');
				fnm.textContent = name + ' ('+qt+')' + (own? '':' ⇋') ;
				fo.appendChild(fnm);
				ah.appendChild(fo);
				rightMenu(ah,'alb');
				g.appendChild(ah);
				tQt+=qt;
			}

		}
	}else if(navi.mode=='t'){
		if(navi.mval === 'Timeline'){
			for(const t in j.i){
				const u = getUrlID({t: t});
				const ah = _ce('a', {href: u.url});
				ah.onclick = genPage.bind(this,u, ah);
				const im = _ce('img', {loading: 'lazy'});
				im.onload = ()=>{im.className='sh';ah.classList.add("loaded");};
				im.src = window.location.pathname + '?tthmb='+t+'&mt='+Dir.m;
				ah.appendChild(im);
				ah.appendChild(_ce('i-con'));
				const fo = _ce('div',{'class':'info'});
				const fnm = _ce('f-nm');
				let w = t.split('-');
				fnm.textContent = w[0] + ' '+ getMonth(w[1]) +' ('+j.i[t]+')';
				fo.appendChild(fnm);
				ah.appendChild(fo);
				ah.className = 'stack';
				g.appendChild(ah);
				tQt+=j.i[t];
			}
		}else{
			let w = tTl.split('-');
			if(w.length>1){
				w[1] = getMonth(w[1]);
				if(w.length>2){w[2]=parseInt(w[2],10);}
				tTl = w.join(' ');
			}
		}
	}else if(navi.mode=='k'){
		if(navi.mval === 'Tags'){

			for(const k in j.i[1]){
				const u = getUrlID({k: k});
				const ah = _ce('a', {href: u.url});
				ah.onclick = genPage.bind(this,u, ah);
				const im = _ce('img', {loading: 'lazy'});
				im.onload = ()=>{im.className='sh';ah.classList.add("loaded");};
				im.src = window.location.pathname + '?kthmb='+encodeURIComponent(k)+'&mt='+Dir.m;
				ah.appendChild(im);
				ah.appendChild(_ce('i-con'));
				const fo = _ce('div',{'class': 'info'});
				fo.appendChild(_ce('f-nm',0, {textContent: k + ' ('+j.i[1][k]+')'}));
				ah.appendChild(fo);
				ah.className = 'stack';
				g.appendChild(ah);
				tQt++;
			}
			g.appendChild(_ce('br'));
			for(const k in j.i[0]){
				const u = getUrlID({k: k});
				const ah = _ce('a',{'class': "loaded", href: u.url}, {textContent: k + ' ('+j.i[0][k]+')'});
				ah.onclick = genPage.bind(this,u, ah);
				g.appendChild(ah);
				tQt++;
			}
		}
	}else if(navi.mode=='d'){
		const [dirpath, pid, mt, sz, qt] = Dir.d[navi.dir];
		tSz = sz; tQt = qt;
		const tl = (dirpath.length)? dirpath : _p.url_pictures + '/';
		tTl = tl.split('/').join(' / ');

		_id('srch').value = ''; navi.kwd = '';
		_qs('body').classList.remove('search');

		folder = Object.keys(Dir.d).filter(key => Dir.d[key][1] === navi.dir);
	}

	expandMenu();

	if(folder.length){
		if(['size','date'].includes(rule.by)){
			m = rule.by === 'size' ? 3 : 2;
			if(rule.az === 'a'){//asc
				foldersort = folder.sort((a, b) => {return Dir.d[a][m] - Dir.d[b][m];});
			}else{//desc
				foldersort = folder.sort((a, b) => {return Dir.d[b][m] - Dir.d[a][m];});
			}
		}else{
			if(rule.az === 'a'){//asc
				foldersort = folder.sort((a, b) => {return Dir.d[a][0].toLowerCase().localeCompare(Dir.d[b][0].toLowerCase(), undefined, { sensitivity: 'base' });});

			}else{//desc
				foldersort = folder.sort((a, b) => {return Dir.d[b][0].toLowerCase().localeCompare(Dir.d[a][0].toLowerCase(), undefined, { sensitivity: 'base' });});
			}
		}

		for(const d of foldersort){
			const id = parseInt(d,10);
			const [dirpath, pid, mt, sz, qt] = Dir.d[id];

			const ah = _ce('a',{'class': 'dir', 'data-d': id, href: getUrlID(id).url});
			ah.onclick = genPage.bind(this,id, ah);
			const fldr = _ce('div',{'class': 'fldr'});
			const fldd = _ce('div');
			if(qt){
				const im = _ce('img', {loading: 'lazy'});
				im.onload = ()=>{im.className='sh';ah.classList.add("loaded");};
				im.onerror = ()=>{im.remove();};
				im.src = window.location.pathname + '?fthmb='+id+'&mt='+mt;
				fldd.appendChild(im);
			}
			fldr.appendChild(fldd);
			ah.appendChild(fldr);
			const fo = _ce('div',{'class': 'info'});

			fo.appendChild(_ce('f-nm',0,{textContent: getFilename(dirpath)}));

			fo.appendChild(_ce('f-mt',0,{textContent: relativeDate(mt)}));

			fo.appendChild(_ce('f-sz',0,{textContent: filesize(sz)}));

			fo.appendChild(_ce('f-qt',0,{textContent: qt}));

			ah.appendChild(fo);
			rightMenu(ah,'dir');
			g.appendChild(ah);
		}
	}

	let filesort;
	if(['size','date','dur','best','dim'].includes(rule.by)){
		m = {size: 's', date: 'm', dur: 'dur', best: 'z', dim: 'r'};
		m = m[ rule.by ];
		if(rule.az == 'a'){
			filesort = Object.keys(f).sort((a, b) => (f[a][m] || 0) - (f[b][m] || 0) );
		}else{
			filesort = Object.keys(f).sort((a, b) => (f[b][m] || 0) - (f[a][m] || 0) );
		}
	}else{//name
		m='n';
		if(rule.az === 'a'){
			filesort = Object.keys(f).sort((a, b) => f[a][m].localeCompare(f[b][m], undefined, { sensitivity: 'base' }));
		}else{
			filesort = Object.keys(f).sort((a, b) => f[b][m].localeCompare(f[a][m], undefined, { sensitivity: 'base' }));
		}
		if(rule.by === 'type'){
			if(rule.az == 'a'){
				filesort = Object.keys(f).sort((a, b) => getExt(f[a][m]).localeCompare(getExt(f[b][m]), undefined, { sensitivity: 'base' }));
			}else{
				filesort = Object.keys(f).sort((a, b) => getExt(f[b][m]).localeCompare(getExt(f[a][m]), undefined, { sensitivity: 'base' }));
			}
		}
	}


	let ii = 0;

	let updrefresh = 0;
	navi.thumbs = [];
	for(const k of filesort){
		const v = f[k];
		if(!v.d){v.d = navi.dir;}
		if(navi.mode!=='d'){tQt++; tSz += v.s;}
		ii++;
		const ah = _ce('a', {id: 'imt'+ii});

		let p = Dir.d[v.d][0];
		if(p !== ''){p+='/';}
		p =(p + v.n).split('/').map(y => encodeURIComponent(y)).join('/');
		let url = _p.url_pictures + '/' + p + '?mt='+v.m;

		const ext = getExt(v.n).toLowerCase();

		_att(ah,'data-s', v.s);
		_att(ah,'data-i', v.i);
		_att(ah,'data-d', v.d);
		_att(ah,'data-tick', '0');
		_att(ah,'data-ori', v.ori ? v.ori : 0 );

		ah.style.setProperty('--ratio', (v.h) ? v.w + '/' + v.h : '4/3');
		v.ft = _p.ext_images.includes(ext)? 1 : (_p.ext_videos.includes(ext)? 2 : 0);

		if(v.ft){
			_att(ah,'data-pswp-width', v.w);
			_att(ah,'data-pswp-height', v.h);
			const im = _ce('img', {loading: 'lazy'});
			im.onload = imld;
			if(v.th == 2){updrefresh = 1; navi.reload = 1;}
			let thm = thumbUrl(v.d, v.n, 'mt='+v.m);
			if(v.th){
				navi.thumbs.push([im, thm, v.i, '?ithumb='+v.i+'&n='+encodeURIComponent(v.n)+'&'+v.m, v.n]);
				thm='';
			}
			_att(im,'src', thm);
			ah.appendChild(im);
		}else{
			_att(ah,'data-ext', ext);
			ah.innerHTML = svgExt(ext);
			ah.classList.add('loaded');
		}

		_att(ah,'href', url);


		const tk = _ce('i',{'class': 'ticker'});
		tk.onclick = tickShow;
		ah.appendChild(tk);
		if(v.w){ah.appendChild(_ce('i-con'));}

		const fo = _ce('div',{'class': 'info'});

		fo.appendChild(_ce('f-nm',0,{textContent: v.n}));

		fo.appendChild(_ce('f-mt',0,{textContent: relativeDate(v.t)}));

		fo.appendChild(_ce('f-sz',0,{textContent: filesize(v.s)}));


		if(v.w){
			fo.appendChild(_ce('f-rs',0,{textContent: v.w+'x'+v.h}));
		}


		if(v.dev){
			fo.appendChild(_ce('f-dev',0,{innerHTML: '<i class="ico-camera"></i> '+v.dev}));
		}

		if(v.k){
			fo.appendChild(_ce('f-kw',0,{innerHTML: '<i class="ico-camera"></i> '+v.k}));
		}

		if(v.city){
			const gp = _ce('f-gps',{'data-loc': (v.lat/10000)+','+(v.lon/10000)},{innerHTML: '<i class="ico-map"></i> '+v.city});
			gp.onclick = (e) => {
				e.stopPropagation();
				e.preventDefault();
				window.open(maplink(gp.dataset.loc), '_blank').focus();
			};
			fo.appendChild(gp);

			ah.classList.add('gps');
		}

		if(v.ft>1){
			_att(ah,'data-pswp-type', 'video');
			fo.appendChild(_ce('v-dur',0,{textContent: vduration(v.dur)}));
		}

		ah.appendChild(fo);
		ah.classList.add('file');
		rightMenu(ah,'img');
		g.appendChild(ah);

	}

	thumbabrt.abort();

	wait(100, () => {
		const gc = _id('galleryc');
		gc.innerHTML = '';
		gc.appendChild(g);
		lightbox.init();
		tickShow();
		restoreScroll();
		thumbLoader(updrefresh);
		wait(5,()=>{startUp();startUp = ()=>{};});
		if(navi.hash){
			const h = navi.hash.split('/');
			if(h.length===3){
				const el = _qs('[data-i="'+h[1]+'"]');
				if(el){
					if(h[0]==='edit'){
						act_edit(el);
					}else if(h[0]==='view'){
						el.click();
					}
				}
			}
			navi.hash = '';
		}
	});

	navi.lsel = 0;

	sidebar(0);
	lsscroll();
	const uu = {[navi.mode]: navi.mval};if(n){uu.n = n;}
	m = getUrlID(uu);
	if(history.state && history.state.url === m.url){
		updHist(0,m);
	}else{
		updHist(1,m);
	}
	navi.lasturl = m.url;
	_id('sizes').innerHTML = tQt + ' Files'+ ((['Albums','Timeline','Tags'].includes(navi.mval))? '': ' · '+filesize(tSz));

	document.title = tTl + ' ('+ tQt + ' Files)';
	_id('sorter').className = 'ico-' + rule.by + rule.az;

}


function dbSetup(e){
	let x = _id('db_type'); let d=x.value;
	let f = {host:0,port:0,user:0,pass:0,schema:0,name:0,file:1};
	if(d != 'sqlite'){
		f.host=1; f.port=1; f.user=1; f.pass=1; f.name=1; f.file=0;
	}
	if(d == 'pgsql'){f.schema=1;}
	Object.keys(f).forEach(w=>{
		let m=_id('db_'+w).closest('p');
		m.classList.add('hide');
		if(f[w]){m.classList.remove('hide');}
	});
	if(e===0){x.onchange = dbSetup;console.log('x');}
}


const navi = {
	dir: 0,
	top: 1,
	side: 0,
	backbtn: 0,
	cm: {el: 0, time: 0, open: 0, pos: 0},
	longpress: 0,
	reload: 1,
	hist: 0,
	edit: 0,
	mode: 'd',
	mval: 0,
	view: {},
	sort: {},
	scroll: {},
	noroll: 0,
	ticked: {},
	ticksize: 0,
	issort: 0,
	kwd: '',
	hash: '',
	pscroll: 0,
	lasturl: '',
	thumbs: [],
	tasks: []
};


function maplink(c){
	return 'https://www.google.com/maps/search/?api=1&query='+ c;
}

let lightbox;

async function picT() {
	if(typeof startUp === 'undefined'){return;}

	lightbox = new PhotoSwipeLightbox({
		bgOpacity: 1,
		zoom: false,
		gallery: '.gallery',
		children: 'a[data-pswp-width]',
		pswpModule: PhotoSwipe
	});


	lightbox.on('contentInit', ({ content }) => {
		if(lightbox.pswp && lightbox.pswp.currSlide){
			lightbox.pswp.currSlide.data.element.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}
	});


	lightbox.on('beforeOpen', () => {
		let m = getUrlID(); m.url +='#fullscr';
		updHist(1,m);
		navi.backbtn = 0;
	});

	lightbox.on('change', () => {
		const ah = lightbox.pswp.currSlide.data.element;
		const id = _att(ah,'data-i');
		const n = _qs('f-nm',ah).textContent;
		let m = getUrlID(); m.url +='#view/'+id+'/'+encodeURIComponent(n);
		updHist(0,m);
	});

	lightbox.on('close', () => {
		if(!navi.backbtn){
			history.back();
		}
	});


	const slideshowPlugin = new PhotoSwipeSlideshow(lightbox,{defaultDelayMs:4000,playPauseButtonOrder:4,restartOnSlideChange:true});

	const videoPlugin = new PhotoSwipeVideoPlugin(lightbox, {});

	if(_p.auto_hide_slideshow_ui){
		const autoHideUI = new PhotoSwipeAutoHideUI(lightbox, {idleTime: _p.auto_hide_slideshow_ui * 1000});
	}

	lightbox.on('uiRegister', function(){

		//transition
		const trans = (i) => {
			const p = lightbox.pswp;
			const j = p.potentialIndex;
			i = p.getLoopedIndex(j + i);
			if(p.mainScroll.moveIndexBy(i - j, true)){
				p.dispatch('afterGoto');
			}
		};
		lightbox.pswp.next = () => trans(1);
		lightbox.pswp.prev = () => trans(-1);


		lightbox.pswp.ui.registerElement({
			name: 'ticker',
			ariaLabel: 'Select',
			order: 1,
			isButton: true,
			tagName: 'a',
			html: '<i class="ticker" style="color:#fff;font-size:170%"></i>',
			onInit: (el, pswp) => {
				pswp.on('change', () => {
					let x=pswp.currSlide.data.element;
					for(const i of ['data-tick','data-d','data-i','data-s','href']){
						_att(el, i, _att(x,i));
					}
					const exn = _qs('f-nm',el);
					if(exn){exn.remove();}
					const fnm = _qs('f-nm',x).cloneNode(true);
					el.appendChild(fnm);
					el.classList.add('loaded');
					_qs('i',el).onclick = tickShow;
				});
			}
		});

		lightbox.pswp.ui.registerElement({
			name: 'dots',
			ariaLabel: 'More',
			order: 9,
			isButton: true,
			html: '<i class="ico-dots" style="color:#fff;font-size:240%"></i>',
			onClick: (event, el) => {
				return cMenu(event, pswp.currSlide.data.element, 'img');
			}
		});

		lightbox.pswp.ui.registerElement({
			name:"info",
			order:9,
			isButton:false,
			appendTo:"wrapper",
			html:"",
			onInit:(el,pswp)=>{
				lightbox.pswp.on("change",()=>{
					const data = lightbox.pswp.currSlide.data.element;
					if(data){
						let tt = '';
						const d = _qs('f-dev',data);
						if(d){
							tt += '<br />'+d.innerHTML;
						}

						const kw = _qs('f-kw',data);
						if(kw){
							tt += '<br />'+kw.innerHTML;
						}
						const g = _qs('f-gps',data);
						if(g){
							tt += '<br /><a target="_blank" href="'+maplink(g.dataset.loc) + '">'+g.innerHTML +'</a>';
						}

						el.innerHTML = '<p>' + _qs('f-mt',data).innerHTML+', ' + (_qs('f-nm',data).textContent) + tt + '</p>';
					}
					return;
				});
			}
		});
	});

	history.scrollRestoration = "manual";

	for(const tb in db){
		if(!('indexedDB' in window)){break;}
		if(tb=='zb'){continue;}
		try{db[tb] = await mydb(tb);}catch(e){console.error(e);break;}
	}

	updateDirSize();

	await db.conf.get('sort',{}).then(r=>{if(r){navi.sort = r;}}).catch(e=>{});
	await db.conf.get('view',{}).then(r=>{if(r){navi.view = r;}}).catch(e=>{});

	await db.conf.get('scroll',{}).then(r=>{if(r){navi.scroll = r;}}).catch(e=>{});

	if(_p.can.delete){
		_on(document, 'keyup', function(e){
			const key = e.key || e.keyCode;
			if((key === 'Delete' || key === 46) && lightbox.pswp){
				const q = _qs('.pswp .ticker');
				if(q){q.click();}
			}
		});
	}


	_on(window, 'beforeunload', lsscroll);
	_on(document, 'visibilitychange', lsscroll);

	window.onblur = () => {
		if(navi.cm.open){hideContextMenu();}
	};

	_on(document,'scroll', function(e){
		if(navi.cm.open && Math.abs(window.scrollY - navi.cm.pos) > 50){
			hideContextMenu(e);
		}
	});

	_on(window, 'scroll', function(){
		const curr = window.pageYOffset;
		backupScroll();
		if(curr > 48 && curr > navi.pscroll){
			if(!navi.side){
				topbar(0);
			}
		}else{
				topbar(1);
		}
		navi.pscroll = curr <= 0 ? 0 : curr;
	});


	_qsa('main,#breadcrumbs,#selectbar,#title,#srchf,#topbar .rbtn:not(:first-child)').forEach(function(el){
		 _on(el, 'click', function(e){
			if(navi.side||navi.cm.open){
				e.stopPropagation();
				e.preventDefault();
				sidebar(0);
				hideContextMenu();
			}
			if(!e.target.closest("#header")){
				_id('header').classList.remove('inside');
			}else{
				_id('header').classList.add('inside');
			}
		},true);
	 });


	if(_p.can.search){
		_id('search').onclick = (e) => {
			const b = _qs('body');
			if(b.classList.contains('search')){
				const v = _id('srch').value;
				if(v.length && navi.kwd !== v){
					searchList({s: v});
				}else{
					b.classList.remove('search');
				}
			}else{
				srchOpts();
				_id('srch').focus();
			}
		};

		_on(_id('srchf'),'submit',(e) => {
			e.preventDefault();
			searchList({s: _id('srch').value});
			return false;
		});
	}else{
		_id('search').remove();
	}


	if(_p.can.login){
		_on(document,"touchstart", ()=>{}, true);
		navi.hash=window.location.hash.substring(1);
		_on(window,'resize',(e)=>{
			hideContextMenu();
		});

		if(document.fullscreenEnabled || document.webkitFullscreenEnabled){
			const b = _qs('#fscreen i');

			const handleFullscreen = () => {
				let i = 'max';
				if(document.fullscreen || document.webkitFullscreenElement){
					i = 'min';
				}
				b.className = 'ico-' + i;
			};

			_on(b,'click', ()=>{
				if(document.fullscreen){
					document.exitFullscreen();
				}else if(document.webkitFullscreenElement){
					document.webkitCancelFullScreen();
				}else if(document.documentElement.requestFullscreen){
					document.documentElement.requestFullscreen();
				}else{
					document.documentElement.webkitRequestFullScreen();
				}
				b.blur();
			});

			_on(document,'fullscreenchange', handleFullscreen);
			_on(document,'webkitfullscreenchange', handleFullscreen);
		}

		_on(window,'popstate',(e)=>{
				if(!e.state || !e.state.hasOwnProperty('url')){
					return;
				}
				const m = getUrlID();
				genPage(m);
			}
		);

		window.onhashchange = (e)=>{
			if(navi.edit){navi.backbtn = 1; _id("tclose").click(); return;}
			const {pswp} = lightbox;
			if(typeof pswp==="object" && pswp.isOpen){
				navi.backbtn = 1;
				pswp.close();

			}
		};
		for(const [j, a] of Object.entries(viewitms)){
			changeRow(navi.view[j] || a[2], j);
		}
		changeView();

		let m = getUrlID();
		buildMenu();
		if(m){
			if(!history.state){updHist(0,m);}
			genPage(m);
		}
		window.AppMode=window.matchMedia('(display-mode: standalone)').matches;
		if(window.AppMode){_qs('#fscreen').style.display='none';}

	}

}
