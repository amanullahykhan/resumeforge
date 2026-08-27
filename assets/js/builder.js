const $ = (s, c = document) => c.querySelector(s), $$ = (s, c = document) => [...c.querySelectorAll(s)];
const flash = () => { const s = $('#saved'); s.classList.add('on'); setTimeout(() => s.classList.remove('on'), 1200); };
let deb, rel;
function theme() {
  const t = {};
  $$('[data-t]').forEach(i => t[i.dataset.t] = i.type === 'range' ? +i.value : i.value);
  t.template = $('input[name=template]:checked')?.value || 'modern';
  return t;
}
function saveTheme() {
  fetch('api/save_step.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ theme: theme() }) })
    .then(r => r.json()).then(() => { flash(); clearTimeout(rel); rel = setTimeout(() => { const f = $('#preview'); f.src = 'api/preview.php?v=' + Date.now(); }, 350); }).catch(e => console.error(e));
}
document.querySelector('.left').addEventListener('input', () => { clearTimeout(deb); deb = setTimeout(saveTheme, 400); });
document.querySelector('.left').addEventListener('change', e => {
  if (e.target.name === 'template') $$('.tpl-card').forEach(c => c.classList.toggle('sel', c.querySelector('input').checked));
  saveTheme();
});

/* ---------- CLIENT-SIDE PNG CAPTURE (html2canvas fallback) ---------- */
let h2cLoaded = false;
function loadHtml2Canvas() {
  return new Promise((resolve, reject) => {
    if (h2cLoaded && window.html2canvas) { resolve(); return; }
    const s = document.createElement('script');
    s.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js';
    s.onload = () => { h2cLoaded = true; resolve(); };
    s.onerror = () => reject(new Error('Failed to load html2canvas'));
    document.head.appendChild(s);
  });
}

async function clientPngCapture() {
  const st = $('#xStatus');
  st.textContent = 'Capturing preview as PNG…';
  try {
    await loadHtml2Canvas();
    const iframe = $('#preview');
    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
    const sheet = iframeDoc.querySelector('.sheet');
    if (!sheet) throw new Error('No resume preview found');
    const canvas = await html2canvas(sheet, {
      scale: 2,
      useCORS: true,
      allowTaint: true,
      backgroundColor: '#ffffff',
      logging: false,
      windowWidth: 794,
      windowHeight: 1122,
    });
    canvas.toBlob(blob => {
      if (!blob) { st.innerHTML = '⚠ Failed to generate PNG'; return; }
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url; a.download = 'resume.png'; a.click();
      setTimeout(() => URL.revokeObjectURL(url), 5000);
      st.innerHTML = '✓ PNG downloaded (client-side capture)';
    }, 'image/png');
  } catch (e) {
    st.innerHTML = '⚠ ' + e.message;
  }
}

/* ---------- EXPORT BUTTONS ---------- */
$$('[data-x]').forEach(b => b.onclick = async () => {
  const st = $('#xStatus'); st.textContent = 'Generating ' + b.dataset.x.toUpperCase() + '…';
  const r = await fetch('api/export.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ format: b.dataset.x }) }).then(r => r.json()).catch(e => ({ ok: false, error: e.message }));
  if (!r.ok) { st.innerHTML = '⚠ ' + r.error; return; }
  // Client-side PNG fallback when server can't render PNG
  if (r.clientFallback === 'png') { await clientPngCapture(); return; }
  st.innerHTML = '✓ <a href="' + r.url + '" target="_blank" download>Download file</a>' + (r.note ? '<br>' + r.note : '');
  const a = document.createElement('a'); a.href = r.url; a.download = ''; a.click();
});
