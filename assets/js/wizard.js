const $ = (s, c = document) => c.querySelector(s), $$ = (s, c = document) => [...c.querySelectorAll(s)];
let step = +$('#wizard').dataset.step || 1, timer = null;
const flash = () => { const s = $('#saved'); s.classList.add('on'); setTimeout(() => s.classList.remove('on'), 1200); };
const post = (body) => fetch('api/save_step.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) }).then(r => r.json()).catch(e => ({ ok: false, error: e.message }));

function reps(kind, fields) {
  return $$(`.rep[data-kind=${kind}]`).map(r => {
    const o = {}; fields.forEach(f => { const el = $(`[data-f=${f}]`, r); o[f] = el ? el.value : ''; }); return o;
  });
}
function payload(n) {
  if (n === 1) { const o = {}; $$('[data-p]').forEach(i => o[i.dataset.p] = i.value); return { profile: o }; }
  if (n === 2) return { experience: reps('experience', ['title', 'company', 'date', 'location', 'bullets']), education: reps('education', ['degree', 'school', 'date', 'note']) };
  if (n === 3) return {
    skills: reps('skills', ['name', 'level']).map(s => ({ name: s.name, level: +s.level })),
    languages: reps('languages', ['name', 'level']),
    custom: reps('custom', ['heading', 'place', 'lines']) };
  if (n === 4) { const t = {}; $$('[data-t]').forEach(i => t[i.dataset.t] = i.type === 'range' ? +i.value : (i.dataset.t === 'upper' ? i.value === '1' : i.value));
    t.template = $('input[name=template]:checked')?.value || t.template || 'modern'; return { theme: t }; }
  return {};
}
function save(extra) { post(Object.assign(payload(step), extra || {})).then(() => flash()); }
function showStep(n) { step = n; $$('.stepbox').forEach(b => b.hidden = +b.dataset.step !== n);
  $$('.pill').forEach(p => p.classList.toggle('on', +p.dataset.go === n)); save({ step: n }); window.scrollTo(0, 0); }
$$('.pill').forEach(p => p.onclick = () => showStep(+p.dataset.go));

let deb; document.querySelector('.wrap').addEventListener('input', () => { clearTimeout(deb); deb = setTimeout(() => save(), 500); });
document.querySelector('.wrap').addEventListener('change', e => { if (e.target.name === 'template') { $$('.tpl-card').forEach(c => c.classList.toggle('sel', c.querySelector('input').checked)); } save(); });

const addRep = (btn, tpl, list) => $(btn).onclick = () => { $(list).appendChild($(tpl).content.cloneNode(true)); };
addRep('#addExp', '#tpl-exp', '#expList'); addRep('#addEdu', '#tpl-edu', '#eduList');
addRep('#addSkill', '#tpl-skill', '#skillList'); addRep('#addLang', '#tpl-lang', '#langList'); addRep('#addCustom', '#tpl-custom', '#customList');
document.addEventListener('click', e => { const rm = e.target.closest('.rm'); if (rm) { rm.closest('.rep').remove(); save(); } });

/* photo upload */
$('#photoFile').onchange = async e => { const f = e.target.files[0]; if (!f) return;
  const fd = new FormData(); fd.append('photo', f);
  const r = await fetch('api/upload_photo.php', { method: 'POST', body: fd }).then(r => r.json()).catch(e => ({ ok: false, error: e.message }));
  if (r.ok) { $('#photoPrev').src = r.url; flash(); } else alert(r.error); };
