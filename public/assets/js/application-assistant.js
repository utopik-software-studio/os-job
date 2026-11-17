(()=>{
 const detail=document.querySelector('.app-detail');if(!detail)return;
 const nav=detail.querySelector('.dossier-tabs');if(!nav)return;
 const currentStatus=detail.dataset.currentStatus||'';
 const csrf=detail.querySelector('input[name="csrf"]')?.value;
 const id=detail.querySelector('input[name="id"]')?.value;
 if(!csrf||!id)return;
 const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
 const section=(parent,title,description,mode,placeholder)=>{
  const wrap=document.createElement('section');wrap.className='assistant-tool';
  wrap.innerHTML=`<h3>${title}</h3><p>${description}</p><textarea rows="5" maxlength="12000" placeholder="${placeholder}"></textarea><button type="button" class="button assistant-run">${mode==='interview'?'Préparation IA':mode==='reply'?'Rédiger une réponse':'Analyse IA'}</button><div class="assistant-result" aria-live="polite"></div>`;
  parent.prepend(wrap);const area=wrap.querySelector('textarea'),btn=wrap.querySelector('button'),result=wrap.querySelector('.assistant-result');
  btn.addEventListener('click',async()=>{
   btn.disabled=true;result.textContent='Traitement IA en cours…';
   try{
    const body=new URLSearchParams({csrf,id,mode,content:area.value});
    const response=await fetch('api/application-assistant.php',{method:'POST',body,credentials:'same-origin'});
    const raw=await response.text();let json;try{json=JSON.parse(raw)}catch(_){throw Error('Réponse serveur vide ou invalide (possible délai dépassé ou erreur PHP). Vérifiez les logs Apache/PHP et réessayez.')}if(!response.ok||!json.ok)throw Error(json.error||'Erreur IA');const d=json.data;const warning=d.context_warning?'<p class="assistant-context-warning">⚠ '+esc(d.context_warning)+'</p>':'';
    if(mode==='analyze'){
     const statuses={to_review:'À étudier',to_apply:'À postuler',applied:'Envoyée',waiting:'En attente',interview:'Entretien',proposal:'Proposition',rejected:'Refus',accepted:'Acceptée',unchanged:'Statut inchangé'};
     const status=d.proposed_status||'unchanged';
     result.innerHTML=`${warning}<h4>Analyse proposée</h4><p><strong>Statut actuel : ${esc(statuses[currentStatus]||currentStatus)}</strong> — conservé jusqu’à confirmation.</p><p><strong>${esc(d.summary)}</strong></p><p>${esc(d.reason)}</p><p>Statut suggéré : <strong>${esc(statuses[status]||'Inchangé')}</strong> · Confiance : ${Number(d.confidence)||0}%</p><p>Prochaine action : ${esc(d.next_action)}</p><button type="button" class="button assistant-save">Enregistrer dans la chronologie</button>${status!=='unchanged'?'<button type="button" class="button assistant-status">Confirmer le nouveau statut</button>':''}`;
     result.querySelector('.assistant-save').onclick=()=>submit('event',{event_type:'email',details:'Email reçu : '+area.value+'\n\nAnalyse IA (à confirmer) : '+String(d.summary||'')+'\nAction : '+String(d.next_action||'')});
     const confirm=result.querySelector('.assistant-status');if(confirm)confirm.onclick=()=>{if(window.confirm('Confirmer le changement de statut en « '+statuses[status]+' » ?'))submit('status',{status})};
    }else if(mode==='reply'){
     const text='Objet : '+String(d.subject||'')+'\n\n'+String(d.body||'');result.innerHTML=warning+'<h4>Proposition à relire</h4><textarea class="assistant-draft" rows="12" aria-label="Brouillon modifiable"></textarea><button type="button" class="button assistant-copy">Copier la réponse</button><button type="button" class="button assistant-store">Enregistrer le brouillon</button>';
     result.querySelector('textarea').value=text;if(d.quality_notice){const notice=document.createElement('p');notice.textContent=d.quality_notice;result.prepend(notice);}result.querySelector('.assistant-copy').onclick=()=>navigator.clipboard.writeText(result.querySelector('textarea').value);result.querySelector('.assistant-store').onclick=()=>saveDraft('reply',String(d.subject||'Réponse au recruteur'),result.querySelector('textarea').value,result);
    }else{
     const sections=[['summary','Synthèse'],['pitch','Présentation personnelle (60 secondes)'],['questions','Questions probables du recruteur'],['suggested_answers','Pistes de réponses du candidat'],['talking_points','Réalisations / points à documenter'],['questions_to_ask','Questions à poser au recruteur'],['watchouts','Points de vigilance']];
     const text=sections.map(([key,label])=>{const v=d[key];return label+'\n'+(Array.isArray(v)?v.map((x,i)=>(i+1)+'. '+String(x)).join('\n'):String(v||'À compléter'))}).join('\n\n');
     result.innerHTML='<h4>Préparation à relire et compléter</h4><p>Ce contenu est modifiable avant son enregistrement.</p><textarea class="assistant-draft" rows="22" aria-label="Préparation entretien modifiable"></textarea><button type="button" class="button assistant-copy">Copier la préparation</button><button type="button" class="button assistant-store">Enregistrer la préparation</button>';
     const editor=result.querySelector('textarea');editor.value=text;
     result.querySelector('.assistant-copy').onclick=()=>navigator.clipboard.writeText(editor.value);
     result.querySelector('.assistant-store').onclick=()=>saveDraft('interview','Préparation entretien',editor.value,result);
    }
   }catch(e){result.textContent='Erreur : '+e.message}finally{btn.disabled=false}
  });
  return wrap;
 };
 async function saveDraft(kind,title,draft,result){try{const body=new URLSearchParams({csrf,id,mode:'save_draft',kind,title,draft});const response=await fetch('api/application-assistant.php',{method:'POST',body,credentials:'same-origin'});const raw=await response.text();let json;try{json=JSON.parse(raw)}catch(_){throw Error('Réponse serveur vide ou invalide (possible délai dépassé ou erreur PHP). Vérifiez les logs Apache/PHP et réessayez.')}if(!response.ok||!json.ok)throw Error(json.error||'Erreur');const note=document.createElement('p');note.textContent='Brouillon enregistré. Rechargez le dossier pour le retrouver.';result.append(note)}catch(e){const note=document.createElement('p');note.textContent='Enregistrement impossible : '+e.message;result.append(note)}}
 function submit(action,fields){const form=document.createElement('form');form.method='post';form.style.display='none';for(const [k,v] of Object.entries({csrf,id,action,...fields})){const input=document.createElement('input');input.name=k;input.value=v;form.append(input)}document.body.append(form);form.submit()}
 const followup=document.querySelector('#dossier-followup'),calendar=document.querySelector('#dossier-calendar');
 if(followup){section(followup,'Analyser un email reçu','Collez le message complet. L’IA propose un statut, mais ne le modifie jamais sans votre validation.','analyze','Objet, expéditeur et texte intégral de l’email…');section(followup,'Assistant de réponse','Préparez une réponse personnalisée à relire et copier. Aucun email n’est envoyé automatiquement.','reply','Collez le message auquel vous souhaitez répondre…')}
 if(calendar)section(calendar,'Préparation intelligente aux entretiens','Questions, arguments et points à clarifier adaptés à cette offre.','interview','Précisions facultatives : type d’entretien, interlocuteur, difficultés…');
})();
