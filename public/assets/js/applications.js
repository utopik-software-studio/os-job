(()=>{
const table=document.getElementById('app-table'),kanban=document.getElementById('app-kanban');if(!table||!kanban)return;
function set(v){table.hidden=v!=='table';kanban.hidden=v!=='kanban';document.querySelectorAll('.app-toggle').forEach(b=>b.classList.toggle('app-active',b.dataset.view===v));try{localStorage.setItem('osjob_application_view',v)}catch(e){}}
document.querySelectorAll('.app-toggle').forEach(b=>b.addEventListener('click',()=>set(b.dataset.view)));let v='table';try{v=localStorage.getItem('osjob_application_view')||v}catch(e){}set(v);
let dragging=null;kanban.querySelectorAll('.app-card').forEach(card=>{
 card.addEventListener('dragstart',e=>{if(e.target.closest('a,button,select,input')){e.preventDefault();return;}dragging=card;card.classList.add('dragging');e.dataTransfer.effectAllowed='move';e.dataTransfer.setData('text/plain',card.dataset.id)});
 card.addEventListener('dragend',()=>{card.classList.remove('dragging');dragging=null;kanban.querySelectorAll('.app-lane').forEach(l=>l.classList.remove('drag-over'))});
});
kanban.querySelectorAll('.app-lane').forEach(lane=>{
 lane.addEventListener('dragover',e=>{if(!dragging)return;e.preventDefault();e.dataTransfer.dropEffect='move';lane.classList.add('drag-over')});
 lane.addEventListener('dragleave',e=>{if(!lane.contains(e.relatedTarget))lane.classList.remove('drag-over')});
 lane.addEventListener('drop',async e=>{e.preventDefault();lane.classList.remove('drag-over');if(!dragging)return;const card=dragging,old=card.parentElement,status=lane.dataset.status;if(old===lane)return;
  lane.appendChild(card);card.setAttribute('aria-busy','true');const data=new URLSearchParams({csrf:kanban.dataset.csrf,id:card.dataset.id,status});
  try{const res=await fetch('api/kanban.php',{method:'POST',body:data,credentials:'same-origin',headers:{'Accept':'application/json'}});const json=await res.json();if(!res.ok||!json.ok)throw Error(json.error||'Échec de la sauvegarde');location.reload();}
  catch(err){old.appendChild(card);card.removeAttribute('aria-busy');alert('Déplacement non enregistré : '+err.message)}
 });
});
const dialog=document.getElementById('preview-dialog'),frame=document.getElementById('preview-frame');
document.querySelectorAll('.preview-open').forEach(b=>b.addEventListener('click',()=>{if(!dialog||!frame)return;document.getElementById('preview-name').textContent=b.dataset.name||'Aperçu';frame.src=b.dataset.preview;dialog.showModal()}));
const close=()=>{if(dialog){dialog.close();frame.src='about:blank'}};document.getElementById('preview-close')?.addEventListener('click',close);dialog?.addEventListener('close',()=>{frame.src='about:blank'});
})();
