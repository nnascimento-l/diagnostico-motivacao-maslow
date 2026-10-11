/* Gráfico de colunas verticais (padrão Excel), usado no questionário e na área admin.
   graficoSVG(nome, linhas) devolve um SVG; cada linha tem: n (nome), s (rótulo) e p (percentual 0-100). */
function gEsc(s){return String(s).replace(/[&<>"']/g,function(c){return{"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]})}
function gFmt(x){return (Math.round(x*10)/10).toLocaleString("pt-BR",{minimumFractionDigits:0,maximumFractionDigits:1})}
var PAL=['#d38340','#37789a','#c96560','#a36aae','#1f7050','#c9a227'];
function graficoSVG(nome,rows){
var NA=Math.min(760,Math.max(300,(document.documentElement.clientWidth||360)-60)),SM=NA<520,W=NA,H=SM?400:440,L=SM?42:56,R=SM?10:20,T=SM?74:64,B=SM?90:70,pw=W-L-R,ph=H-T-B,n=rows.length,slot=pw/n,bw=Math.min(72,slot*.55);
var s='<svg viewBox="0 0 '+W+' '+H+'" role="img" aria-label="Gráfico de colunas com o percentual por elemento" font-family="Calibri,Arial,IBM Plex Sans,sans-serif">';
s+='<rect width="'+W+'" height="'+H+'" fill="#fff"/>';
if(SM){s+='<text x="'+W/2+'" y="22" text-anchor="middle" font-size="14" font-weight="600" fill="#404040">Diagnóstico Corporativo</text><text x="'+W/2+'" y="42" text-anchor="middle" font-size="14" font-weight="600" fill="#404040">'+gEsc(nome)+'</text>'}else{s+='<text x="'+W/2+'" y="26" text-anchor="middle" font-size="18" font-weight="600" fill="#404040">Diagnóstico Corporativo — '+gEsc(nome)+'</text>'}
s+='<text x="'+W/2+'" y="'+(SM?60:46)+'" text-anchor="middle" font-size="12" fill="#595959">Percentual alcançado por elemento (%)</text>';
for(var g=0;g<=100;g+=20){var y=T+ph-ph*g/100;
s+='<line x1="'+L+'" x2="'+(W-R)+'" y1="'+y+'" y2="'+y+'" stroke="'+(g?'#d9d9d9':'#8c8c8c')+'"/>';
s+='<text x="'+(L-8)+'" y="'+(y+4)+'" text-anchor="end" font-size="12" fill="#595959">'+g+'%</text>';}
s+='<line x1="'+L+'" x2="'+L+'" y1="'+T+'" y2="'+(T+ph)+'" stroke="#8c8c8c"/>';
rows.forEach(function(r,i){
var cx=L+slot*i+slot/2,h=ph*r.p/100,y=T+ph-h;
s+='<rect x="'+(cx-bw/2)+'" y="'+y+'" width="'+bw+'" height="'+h+'" fill="'+PAL[i%PAL.length]+'"><title>'+gEsc(r.n)+': '+gFmt(r.p)+'%</title></rect>';
s+='<text x="'+cx+'" y="'+(y-6)+'" text-anchor="middle" font-size="'+(SM?11:13)+'" font-weight="600" fill="#262626">'+gFmt(r.p)+'%</text>';
s+=SM?'<text transform="translate('+(cx+4)+','+(T+ph+16)+') rotate(-35)" text-anchor="end" font-size="11" fill="#404040">'+gEsc(r.s)+'</text>':'<text x="'+cx+'" y="'+(T+ph+20)+'" text-anchor="middle" font-size="12" fill="#404040">'+gEsc(r.s)+'</text>';
});
return s+'</svg>';
}
