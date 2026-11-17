/* OS::JOB - progressive enhancement: original forms remain intact. */
(()=>{
 const detail=document.querySelector('.app-detail');if(!detail)return;
 const toolbar=detail.querySelector(':scope > .app-toolbar');
 const score=detail.querySelector(':scope > .app-score-detail');
 const ai=detail.querySelector(':scope > .app-ai-section');
 const grid=detail.querySelector(':scope > .app-detail-grid');
 if(!toolbar||!score||!ai||!grid)return;
 const tabs=[['overview','Vue d’ensemble'],['analysis','Analyse & IA'],['documents','CV & documents'],['followup','Échanges & emails'],['calendar','Entretiens & rappels'],['history','Historique']];
 const nav=document.createElement('nav');nav.className='dossier-tabs';nav.setAttribute('aria-label','Sections du dossier');
 const panels={};
 tabs.forEach(([id,label])=>{
  const button=document.createElement('button');button.type='button';button.className='dossier-tab';button.textContent=label;button.dataset.tab=id;button.setAttribute('aria-controls','dossier-'+id);nav.appendChild(button);
  const panel=document.createElement('div');panel.id='dossier-'+id;panel.className='dossier-panel';panel.setAttribute('role','region');panel.setAttribute('aria-label',label);panels[id]=panel;
 });
 toolbar.after(nav);let anchor=nav;tabs.forEach(([id])=>{anchor.after(panels[id]);anchor=panels[id]});
 const summary=document.createElement('div');summary.className='dossier-intro';summary.innerHTML='<h3>Vue d’ensemble</h3><p>Décision, statut et informations essentielles. Chaque action détaillée se trouve dans son onglet.</p>';
 panels.overview.append(summary,score);
 const decision=detail.querySelector(':scope > .app-v010-panel');
 if(decision){
   const headers=[...decision.querySelectorAll('h3')];
   const achievements=headers.find(h=>h.textContent.includes('Réalisations'));
   if(achievements){let n=achievements;const removed=[];while(n){removed.push(n);n=n.nextElementSibling}removed.forEach(el=>el.remove())}
   panels.overview.append(decision);
 }
 panels.analysis.append(ai);
 const history=detail.querySelector('.app-history-source');if(history)panels.history.append(history);
 const historyFilter=detail.querySelector('#history-filter');if(historyFilter)historyFilter.addEventListener('change',()=>{let visible=0;detail.querySelectorAll('.history-item').forEach(item=>{item.hidden=historyFilter.value!=='all'&&item.dataset.historyKind!==historyFilter.value;if(!item.hidden)visible++});const empty=detail.querySelector('.history-empty');if(empty)empty.hidden=visible>0;});
 const nodes=[...grid.children];grid.remove();
 nodes.forEach(node=>{const h=node.querySelector('h3');const title=(h?.textContent||'').trim().toLowerCase();let target='followup';if(title.includes('documents')||title.includes('cv et lettres'))target='documents';else if(title.includes('entretien')||title.includes('relances'))target='calendar';panels[target].append(node)});
 const overlay=document.createElement('div');overlay.className='dossier-overlay';document.body.append(overlay);
 detail.classList.add('dossier-drawer');document.body.classList.add('dossier-open');
 const close=()=>{window.location.href=new URL(window.location.href).pathname+'?page=applications'};
 overlay.addEventListener('click',close);
 document.addEventListener('keydown',e=>{if(e.key==='Escape'&&!document.querySelector('.preview-dialog[open]'))close()});
 const closeLink=toolbar.querySelector('a[href*="applications"]');if(closeLink)closeLink.setAttribute('aria-label','Fermer le dossier');
 tabs.forEach(([id,label])=>{if(!panels[id].children.length){const empty=document.createElement('p');empty.textContent='Aucune information enregistrée.';panels[id].append(empty)}});
 function activate(id){tabs.forEach(([key])=>{const active=key===id;panels[key].hidden=!active;const b=nav.querySelector('[data-tab="'+key+'"]');b.classList.toggle('active',active);b.setAttribute('aria-current',active?'page':'false');b.setAttribute('aria-selected',String(active))});try{sessionStorage.setItem('osjob_dossier_tab',id)}catch(e){}}
 nav.querySelectorAll('button').forEach(b=>b.addEventListener('click',()=>activate(b.dataset.tab)));
 let initial='overview';try{initial=sessionStorage.getItem('osjob_dossier_tab')||initial}catch(e){}if(!panels[initial])initial='overview';activate(initial);
})();
