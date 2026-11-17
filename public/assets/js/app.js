(() => {
 const menu=document.getElementById('menuToggle');
 const closeMenu=()=>{document.body.classList.remove('sidebar-open');menu?.setAttribute('aria-expanded','false');};
 menu?.addEventListener('click',()=>{const open=document.body.classList.toggle('sidebar-open');menu.setAttribute('aria-expanded',String(open));});
 document.getElementById('sidebarBackdrop')?.addEventListener('click',closeMenu);
 document.addEventListener('keydown',e=>{if(e.key==='Escape')closeMenu();});
 document.querySelectorAll('.sidebar .nav-link').forEach(a=>a.addEventListener('click',closeMenu));
 const filter=document.getElementById('offerFilter');filter?.addEventListener('input',()=>{const q=filter.value.toLocaleLowerCase('fr');document.querySelectorAll('.offer-row').forEach(r=>r.hidden=!r.textContent.toLocaleLowerCase('fr').includes(q));});
 const selects=[...document.querySelectorAll('.geo-list')];if(selects.length){const original=new Map(selects.map(s=>[s,[...s.options].map(o=>({value:o.value,parent:o.dataset.parent,selected:o.selected,text:o.textContent}))]));const order=['country','region','department','city'];function update(){for(let i=1;i<order.length;i++){const parent=selects.find(s=>s.dataset.kind===order[i-1]);const current=selects.find(s=>s.dataset.kind===order[i]);const allowed=new Set([...parent.selectedOptions].map(o=>o.value));const keep=new Set([...current.selectedOptions].map(o=>o.value));const opts=original.get(current);current.replaceChildren();opts.filter(o=>allowed.has(o.parent)||keep.has(o.value)).forEach(o=>{const el=new Option(o.text,o.value,false,keep.has(o.value));el.dataset.parent=o.parent;current.add(el);});}}selects.forEach(s=>s.addEventListener('change',update));update();}
})();
