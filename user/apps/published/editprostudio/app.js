'use strict';
const $=(s,e=document)=>e.querySelector(s);
const h=(t,a={},...k)=>{const e=document.createElement(t);Object.assign(e,a);e.append(...k);return e};
const fmt=s=>Math.floor(s/60)+':'+String(Math.floor(s%60)).padStart(2,'0');
const clamp=(v,a,b)=>Math.max(a,Math.min(b,v));
let tt;function toast(m){const t=$('#toast');t.textContent=m;t.classList.add('on');clearTimeout(tt);tt=setTimeout(()=>t.classList.remove('on'),2600)}
function save(blob,name){const a=h('a',{href:URL.createObjectURL(blob),download:name});document.body.append(a);a.click();a.remove();toast('Saved '+name+' (check Downloads / Files)')}
function slider(label,min,max,val,step,fn){const o=h('output',{textContent:val}),i=h('input',{type:'range',min,max,value:val,step});i.oninput=()=>{o.textContent=i.value;fn(+i.value)};return h('label',{className:'sl'},label,i,o)}
function chips(list,cur,fn){const w=h('div',{className:'chips'});list.forEach(n=>{const b=h('button',{textContent:n,className:'chip'+(n===cur?' on':'')});b.onclick=()=>{w.querySelectorAll('.chip').forEach(c=>c.classList.remove('on'));b.classList.add('on');fn(n)};w.append(b)});return w}
function btn(t,fn,pri){return h('button',{textContent:t,className:'btn'+(pri?' pri':''),onclick:fn})}
function tabs(root,defs){const bar=h('nav',{className:'tabs'}),pan=h('div',{className:'panel'});Object.entries(defs).forEach(([n,f],i)=>{const b=h('button',{textContent:n});b.onclick=()=>{bar.querySelectorAll('button').forEach(x=>x.classList.remove('on'));b.classList.add('on');pan.replaceChildren(f())};bar.append(b);if(!i)b.click()});root.replaceChildren(pan,bar)}

/* ---------- navigation ---------- */
const NAMES={home:'EditPro Studio',photo:'Photo editor',video:'Video editor',music:'Music editor'};
function go(v){document.querySelectorAll('.view').forEach(e=>e.hidden=e.id!=='v-'+v);$('#ttl').textContent=NAMES[v];$('#back').hidden=v==='home';if(v!=='video')$('#vid').pause();mstop()}
document.querySelectorAll('[data-go]').forEach(b=>b.onclick=()=>go(b.dataset.go));
$('#back').onclick=()=>go('home');

/* ---------- PHOTO ---------- */
const PF={None:'',Mono:'grayscale(1)',Warm:'sepia(.4) saturate(1.3)',Cool:'hue-rotate(180deg) saturate(.8)',Fade:'contrast(.8) brightness(1.2)',Vivid:'saturate(1.8) contrast(1.1)'};
const P0=()=>({rot:0,flip:1,b:100,c:100,s:100,bl:0,f:'None',ar:0,fr:'None',max:1080,items:[],cur:null});
let P=P0(),PI=null;
const pcss=()=>`brightness(${P.b}%) contrast(${P.c}%) saturate(${P.s}%) blur(${P.bl}px) ${PF[P.f]}`;
function pdraw(){
  if(!PI)return;const cv=$('#pc'),iw=PI.width,ih=PI.height;let sw=iw,sh=ih;
  if(P.ar){if(iw/ih>P.ar){sw=ih*P.ar}else{sh=iw/P.ar}}
  const k=Math.min(1,P.max/Math.max(sw,sh)),w=sw*k,hh=sh*k,sw2=P.rot%180;
  cv.width=sw2?hh:w;cv.height=sw2?w:hh;const x=cv.getContext('2d');
  x.translate(cv.width/2,cv.height/2);x.rotate(P.rot*Math.PI/180);x.scale(P.flip,1);
  x.drawImage(PI,(iw-sw)/2,(ih-sh)/2,sw,sh,-w/2,-hh/2,w,hh);x.setTransform(1,0,0,1,0,0);
  if(P.fr!=='None'){x.strokeStyle=P.fr==='White'?'#fff':'#000';x.lineWidth=cv.width*.05;x.strokeRect(0,0,cv.width,cv.height)}
  P.items.forEach(i=>{x.font=`800 ${i.size*cv.width/300}px sans-serif`;x.textAlign='center';x.textBaseline='middle';x.lineWidth=cv.width/100;x.strokeStyle='#0009';x.fillStyle='#fff';if(!i.emoji)x.strokeText(i.t,i.x*cv.width,i.y*cv.height);x.fillText(i.t,i.x*cv.width,i.y*cv.height)});
  cv.style.filter=pcss();
}
function pneed(){if(!PI){toast('Open a photo first');return false}return true}
$('#pf').onchange=e=>{const f=e.target.files[0];if(!f)return;const im=new Image();im.onload=()=>{PI=im;P=P0();$('#pe').hidden=true;pdraw();ptabs()};im.onerror=()=>toast('Could not read that image');im.src=URL.createObjectURL(f)};
$('#pr').onclick=()=>{if(pneed()){P=P0();pdraw();ptabs()}};
$('#pc').onclick=e=>{if(!P.cur)return;const r=e.target.getBoundingClientRect();P.cur.x=(e.clientX-r.left)/r.width;P.cur.y=(e.clientY-r.top)/r.height;pdraw()};
function addItem(t,size,emoji){if(!pneed())return;P.cur={t,size,emoji,x:.5,y:.5};P.items.push(P.cur);pdraw();toast('Tap the photo to move it')}
function pexport(){
  if(!pneed())return;const cv=$('#pc'),o=h('canvas',{width:cv.width,height:cv.height}),x=o.getContext('2d');
  if('filter' in x){x.filter=pcss();x.drawImage(cv,0,0)}else{
    x.drawImage(cv,0,0);const d=x.getImageData(0,0,o.width,o.height),a=d.data,b=P.b/100,c=P.c/100,s=P.s/100;
    for(let i=0;i<a.length;i+=4){const g=.3*a[i]+.59*a[i+1]+.11*a[i+2];for(let j=0;j<3;j++){let v=g+(a[i+j]-g)*s;v=((v*b)-128)*c+128;a[i+j]=v}}
    x.putImageData(d,0,0);toast('This browser skips blur and presets on export')}
  o.toBlob(b=>save(b,'editpro-photo.png'),'image/png');
}
function ptabs(){tabs($('#ptools'),{
  Adjust:()=>h('div',{className:'panel',style:'padding:0;max-height:none'},slider('Brightness',50,150,P.b,1,v=>{P.b=v;pdraw()}),slider('Contrast',50,150,P.c,1,v=>{P.c=v;pdraw()}),slider('Saturation',0,200,P.s,1,v=>{P.s=v;pdraw()}),slider('Blur',0,8,P.bl,.5,v=>{P.bl=v;pdraw()})),
  Filters:()=>chips(Object.keys(PF),P.f,n=>{P.f=n;pdraw()}),
  Crop:()=>h('div',{className:'panel',style:'padding:0;max-height:none'},chips(['Free','1:1','4:5','16:9','9:16'],P.ar?{1:'1:1',.8:'4:5',1.7777777777777777:'16:9',.5625:'9:16'}[P.ar]:'Free',n=>{P.ar={Free:0,'1:1':1,'4:5':.8,'16:9':16/9,'9:16':9/16}[n];pdraw()}),h('div',{className:'row'},btn('Rotate ↻',()=>{P.rot=(P.rot+90)%360;pdraw()}),btn('Flip',()=>{P.flip*=-1;pdraw()})),chips(['None','White','Black'],P.fr,n=>{P.fr=n;pdraw()})),
  Text:()=>{const i=h('input',{type:'text',placeholder:'Type your text'});return h('div',{className:'row'},i,btn('Add',()=>i.value.trim()?addItem(i.value.trim(),28):toast('Type some text first'),1),btn('Undo',()=>{P.items.pop();P.cur=P.items.at(-1)||null;pdraw()}))},
  Stickers:()=>{const w=h('div',{className:'chips'});['😀','😍','🔥','⭐','❤️','🎉','🎂','💍','🎬','🎵','👑','🌈'].forEach(s=>w.append(h('button',{className:'chip',textContent:s,onclick:()=>addItem(s,60,1)})));return w},
  Save:()=>h('div',{className:'panel',style:'padding:0;max-height:none'},chips(['1080','720','480'],String(P.max),n=>{P.max=+n;pdraw()}),btn('Save photo (PNG)',pexport,1))
})}
ptabs();

/* ---------- VIDEO ---------- */
const vid=$('#vid');
const VF=PF;
const V={rot:0,b:100,c:100,s:100,f:'None',sp:1,vol:1,in:0,out:0,t:'',ts:22,ty:.85,busy:false};
const vcss=()=>`brightness(${V.b}%) contrast(${V.c}%) saturate(${V.s}%) ${VF[V.f]}`;
function vfx(){
  vid.style.filter=vcss();const st=$('#v-video .stage'),r=V.rot%180;
  vid.style.transform=`rotate(${V.rot}deg) scale(${r&&vid.clientWidth?Math.min(st.clientHeight/vid.clientWidth,st.clientWidth/vid.clientHeight,1):1})`;
  vid.playbackRate=V.sp;vid.volume=V.vol;const t=$('#vtx');t.textContent=V.t;t.style.fontSize=V.ts+'px';t.style.top=V.ty*100+'%';
}
function vtrack(){const d=vid.duration||1;const i=$('#trk i'),b=$('#trk b');i.style.left=V.in/d*100+'%';i.style.width=(V.out-V.in)/d*100+'%';b.style.left=vid.currentTime/d*100+'%';$('#vs').value=vid.currentTime;$('#vt2').textContent=fmt(vid.currentTime)+' / '+fmt(vid.duration||0)}
function vneed(){if(!vid.duration){toast('Open a video first');return false}return true}
$('#vf').onchange=e=>{const f=e.target.files[0];if(!f)return;vid.src=URL.createObjectURL(f)};
vid.onloadedmetadata=()=>{$('#ve').hidden=true;V.in=0;V.out=vid.duration;$('#vs').max=vid.duration;vfx();vtrack();vtabs()};
vid.onerror=()=>toast('This video format is not supported here');
vid.ontimeupdate=()=>{if(!V.busy&&vid.currentTime>=V.out){vid.pause();vid.currentTime=V.in}vtrack()};
vid.onplay=()=>$('#vp').textContent='❚❚';vid.onpause=()=>$('#vp').textContent='▶';
$('#vp').onclick=()=>{if(!vneed())return;if(vid.paused){if(vid.currentTime<V.in||vid.currentTime>=V.out)vid.currentTime=V.in;vid.play()}else vid.pause()};
$('#vs').oninput=e=>{if(vneed())vid.currentTime=+e.target.value};
$('#vx').onclick=async()=>{
  if(!vneed()||V.busy)return;if(!window.MediaRecorder||!HTMLCanvasElement.prototype.captureStream)return toast('Export is not supported by this browser');
  V.busy=true;toast('Exporting in real time… keep this screen open');
  const W=vid.videoWidth,H=vid.videoHeight,sc=Math.min(1,720/Math.max(W,H)),w=W*sc,hh=H*sc,r=V.rot%180,cv=h('canvas',{width:r?hh:w,height:r?w:hh}),x=cv.getContext('2d'),st=cv.captureStream(30);
  try{const a=(vid.captureStream||vid.mozCaptureStream).call(vid);a.getAudioTracks().forEach(t=>st.addTrack(t))}catch(e){toast('No audio capture in this browser; exporting silent video')}
  const mime=['video/mp4','video/webm;codecs=vp9,opus','video/webm'].find(m=>MediaRecorder.isTypeSupported(m))||'',ch=[],rec=new MediaRecorder(st,mime?{mimeType:mime}:{});
  rec.ondataavailable=e=>e.data.size&&ch.push(e.data);
  rec.onstop=()=>{V.busy=false;vid.pause();save(new Blob(ch,{type:rec.mimeType}),'editpro-video.'+(rec.mimeType.includes('mp4')?'mp4':'webm'))};
  const draw=()=>{x.setTransform(1,0,0,1,0,0);x.filter=vcss();x.clearRect(0,0,cv.width,cv.height);x.translate(cv.width/2,cv.height/2);x.rotate(V.rot*Math.PI/180);x.drawImage(vid,-w/2,-hh/2,w,hh);x.setTransform(1,0,0,1,0,0);x.filter='none';
    if(V.t){x.font=`800 ${V.ts*cv.width/$('#v-video .stage').clientWidth}px sans-serif`;x.textAlign='center';x.textBaseline='middle';x.fillStyle='#fff';x.shadowColor='#000';x.shadowBlur=6;x.fillText(V.t,cv.width/2,V.ty*cv.height);x.shadowBlur=0}};
  const loop=()=>{draw();if(vid.currentTime>=V.out||vid.ended)rec.stop();else requestAnimationFrame(loop)};
  vid.pause();vid.currentTime=V.in;await new Promise(r=>setTimeout(r,350));rec.start();vid.play().then(loop).catch(()=>{V.busy=false;rec.stop()});
};
function vtabs(){tabs($('#vtools'),{
  Trim:()=>{const A=h('div',{className:'panel',style:'padding:0;max-height:none'});const d=vid.duration||1;
    A.append(slider('Start',0,d,V.in,.1,v=>{V.in=Math.min(v,V.out-.1);vid.currentTime=V.in;vtrack()}),slider('End',0,d,V.out,.1,v=>{V.out=Math.max(v,V.in+.1);vtrack()}),
    h('div',{className:'row'},btn('Cut start here',()=>{if(vneed()){V.in=Math.min(vid.currentTime,V.out-.1);vtrack();vtabs()}}),btn('Cut end here',()=>{if(vneed()){V.out=Math.max(vid.currentTime,V.in+.1);vtrack();vtabs()}}),btn('Rotate ↻',()=>{V.rot=(V.rot+90)%360;vfx()})));return A},
  Sound:()=>h('div',{className:'panel',style:'padding:0;max-height:none'},slider('Speed',.25,3,V.sp,.25,v=>{V.sp=v;vfx()}),slider('Volume',0,1,V.vol,.05,v=>{V.vol=v;vfx()})),
  Look:()=>h('div',{className:'panel',style:'padding:0;max-height:none'},chips(Object.keys(VF),V.f,n=>{V.f=n;vfx()}),slider('Brightness',50,150,V.b,1,v=>{V.b=v;vfx()}),slider('Contrast',50,150,V.c,1,v=>{V.c=v;vfx()}),slider('Saturation',0,200,V.s,1,v=>{V.s=v;vfx()})),
  Text:()=>{const i=h('input',{type:'text',placeholder:'Caption',value:V.t,oninput:()=>{V.t=i.value;vfx()}});return h('div',{className:'panel',style:'padding:0;max-height:none'},i,slider('Size',12,40,V.ts,1,v=>{V.ts=v;vfx()}),chips(['Top','Middle','Bottom'],V.ty<.3?'Top':V.ty<.7?'Middle':'Bottom',n=>{V.ty={Top:.15,Middle:.5,Bottom:.85}[n];vfx()}))}
})}
vtabs();

/* ---------- MUSIC ---------- */
const M={buf:null,s:0,e:0,fi:0,fo:0,vol:1,src:null,rec:null,ac:null};
const AC=()=>M.ac||(M.ac=new(window.AudioContext||window.webkitAudioContext)());
function mneed(){if(!M.buf){toast('Open or record audio first');return false}return true}
function mstop(){if(M.src){try{M.src.stop()}catch(e){}M.src=null}const p=$('#mp');if(p)p.textContent='▶ Preview'}
async function mload(blob,append){
  try{const nb=await AC().decodeAudioData(await blob.arrayBuffer());mset(append&&M.buf?mcat(M.buf,nb):nb)}catch(e){toast('Could not read that audio file')}
}
function mset(b){M.buf=b;M.s=0;M.e=b.duration;$('#me').hidden=true;wave();mtabs()}
function mcat(a,b){const n=Math.max(a.numberOfChannels,b.numberOfChannels),o=AC().createBuffer(n,a.length+b.length,a.sampleRate);for(let c=0;c<n;c++){o.getChannelData(c).set(a.getChannelData(Math.min(c,a.numberOfChannels-1)));o.getChannelData(c).set(b.getChannelData(Math.min(c,b.numberOfChannels-1)),a.length)}return o}
function wave(){
  const cv=$('#wc');cv.width=cv.clientWidth*2;cv.height=cv.clientHeight*2;const x=cv.getContext('2d'),d=M.buf.getChannelData(0),step=Math.max(1,Math.floor(d.length/cv.width)),mid=cv.height/2;
  x.clearRect(0,0,cv.width,cv.height);x.fillStyle='#36d1b0';
  for(let i=0;i<cv.width;i++){let lo=0,hi=0;for(let j=0;j<step;j+=Math.max(1,step>>5)){const v=d[i*step+j]||0;if(v<lo)lo=v;if(v>hi)hi=v}x.fillRect(i,mid+lo*mid,1,Math.max(1,(hi-lo)*mid))}
  x.fillStyle='rgba(16,18,24,.75)';const a=M.s/M.buf.duration*cv.width,b=M.e/M.buf.duration*cv.width;x.fillRect(0,0,a,cv.height);x.fillRect(b,0,cv.width-b,cv.height);
}
$('#mf').onchange=e=>{const f=e.target.files[0];if(f)mload(f,false)};
$('#mp').onclick=()=>{
  if(!mneed())return;if(M.src){mstop();return}
  const c=AC();c.resume();const s=c.createBufferSource(),g=c.createGain(),t=c.currentTime,d=M.e-M.s;s.buffer=M.buf;
  g.gain.setValueAtTime(M.fi?0:M.vol,t);if(M.fi)g.gain.linearRampToValueAtTime(M.vol,t+M.fi);if(M.fo){g.gain.setValueAtTime(M.vol,t+Math.max(M.fi,d-M.fo));g.gain.linearRampToValueAtTime(0,t+d)}
  s.connect(g).connect(c.destination);s.start(0,M.s,d);s.onended=()=>{if(M.src===s){M.src=null;$('#mp').textContent='▶ Preview'}};M.src=s;$('#mp').textContent='■ Stop';
};
async function mrec(b){
  if(M.rec){M.rec.stop();return}
  try{const st=await navigator.mediaDevices.getUserMedia({audio:true}),r=new MediaRecorder(st),ch=[];
    r.ondataavailable=e=>ch.push(e.data);r.onstop=()=>{st.getTracks().forEach(t=>t.stop());M.rec=null;b.textContent='● Record';mload(new Blob(ch),true)};
    r.start();M.rec=r;b.textContent='■ Stop recording'}catch(e){toast('Microphone permission was denied or is unavailable')}
}
function msfx(kind){
  const c=AC(),sr=c.sampleRate,n=Math.floor(sr*(kind==='Whoosh'?.8:.5)),o=c.createBuffer(1,n,sr),d=o.getChannelData(0);
  for(let i=0;i<n;i++){const t=i/sr,e=1-i/n;d[i]=kind==='Beep'?Math.sin(6.28*880*t)*(i<n-400?.4:0):kind==='Chime'?Math.sin(6.28*1320*t)*e*e*.5:(Math.random()*2-1)*Math.sin(Math.PI*i/n)*.4}
  mset(M.buf?mcat(M.buf,o):o);
}
function mexport(){
  if(!mneed())return;const b=M.buf,sr=b.sampleRate,a=Math.floor(M.s*sr),n=Math.floor(M.e*sr)-a,ch=Math.min(2,b.numberOfChannels),v=new DataView(new ArrayBuffer(44+n*ch*2)),w=(o,s)=>[...s].forEach((c,i)=>v.setUint8(o+i,c.charCodeAt(0)));
  w(0,'RIFF');v.setUint32(4,36+n*ch*2,true);w(8,'WAVEfmt ');v.setUint32(16,16,true);v.setUint16(20,1,true);v.setUint16(22,ch,true);v.setUint32(24,sr,true);v.setUint32(28,sr*ch*2,true);v.setUint16(32,ch*2,true);v.setUint16(34,16,true);w(36,'data');v.setUint32(40,n*ch*2,true);
  const fi=M.fi*sr,fo=M.fo*sr,D=[...Array(ch).keys()].map(c=>b.getChannelData(c));
  for(let i=0;i<n;i++){const g=M.vol*(fi?Math.min(1,i/fi):1)*(fo?Math.min(1,(n-i)/fo):1);for(let c=0;c<ch;c++)v.setInt16(44+(i*ch+c)*2,clamp(D[c][a+i]*g,-1,1)*32767,true)}
  save(new Blob([v],{type:'audio/wav'}),'editpro-audio.wav');
}
function mtabs(){tabs($('#mtools'),{
  Trim:()=>{const d=M.buf?M.buf.duration:1;return h('div',{className:'panel',style:'padding:0;max-height:none'},slider('Start',0,d,M.s,.05,v=>{if(M.buf){M.s=Math.min(v,M.e-.05);wave()}}),slider('End',0,d,M.e,.05,v=>{if(M.buf){M.e=Math.max(v,M.s+.05);wave()}}))},
  Fades:()=>h('div',{className:'panel',style:'padding:0;max-height:none'},slider('Fade in',0,5,M.fi,.1,v=>M.fi=v),slider('Fade out',0,5,M.fo,.1,v=>M.fo=v),slider('Volume',0,1.5,M.vol,.05,v=>M.vol=v)),
  Add:()=>{const rb=btn(M.rec?'■ Stop recording':'● Record',()=>mrec(rb),1);const mi=h('label',{className:'btn'},'Append audio',h('input',{type:'file',accept:'audio/*',hidden:true,onchange:e=>e.target.files[0]&&mload(e.target.files[0],true)}));
    return h('div',{className:'panel',style:'padding:0;max-height:none'},h('div',{className:'row'},rb,mi),chips(['Beep','Chime','Whoosh'],'',msfx))},
  Save:()=>h('div',{className:'panel',style:'padding:0;max-height:none'},btn('Save audio (WAV)',mexport,1))
})}
mtabs();
addEventListener('resize',()=>{vfx();if(M.buf)wave()});
