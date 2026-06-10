<?php
// admin/scanner.php
require_once 'auth_guard.php';
require_once 'admin_nav.php';
?>
<div class="container">
  <h1>Scanner de billets</h1>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;flex-wrap:wrap">

    <!-- Camera scanner -->
    <div class="card">
      <h2 style="font-size:15px;margin-bottom:14px">📷 Scanner via caméra</h2>
      <div id="reader" style="border:2px solid #333;border-radius:8px;overflow:hidden;max-width:400px"></div>
      <div id="scan-status" style="margin-top:14px"></div>
    </div>

    <!-- Manual code entry -->
    <div class="card">
      <h2 style="font-size:15px;margin-bottom:14px">⌨️ Saisie manuelle</h2>
      <label>Code billet</label>
      <input type="text" id="manual-code" placeholder="TKT-XXXXXXXXXXXXXXXX"
             style="font-family:monospace;letter-spacing:2px">
      <button class="btn" onclick="verifyCode(document.getElementById('manual-code').value)">
        Vérifier
      </button>

      <div id="manual-result" style="margin-top:16px"></div>
    </div>
  </div>

  <!-- Scan history -->
  <div class="card" style="margin-top:20px">
    <h2 style="font-size:15px;margin-bottom:10px">Historique de scan (session)</h2>
    <div id="scan-log" style="font-size:12px;font-family:monospace;max-height:200px;overflow-y:auto">
      <p style="color:#555">Aucun scan pour l'instant.</p>
    </div>
  </div>
</div>

<!-- html5-qrcode CDN -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
const VERIFY_URL = '../portal/verify.php';
const scanLog = [];
let lastScanned = '';
let lastScannedAt = 0;

// ----------- Camera scanner -----------
const html5QrCode = new Html5Qrcode("reader");
html5QrCode.start(
  { facingMode: "environment" },
  { fps: 10, qrbox: { width: 250, height: 250 } },
  (decodedText) => {
    const now = Date.now();
    // Debounce: same code within 3 seconds → ignore
    if (decodedText === lastScanned && (now - lastScannedAt) < 3000) return;
    lastScanned = decodedText;
    lastScannedAt = now;

    // Extract code from URL if needed
    let code = decodedText;
    const urlMatch = decodedText.match(/[?&]code=([^&]+)/);
    if (urlMatch) code = decodeURIComponent(urlMatch[1]);

    verifyCode(code, 'scan-status');
  }
).catch(err => {
  document.getElementById('reader').innerHTML =
    '<p style="color:#e74c3c;padding:16px">Caméra non accessible : ' + err + '</p>';
});

// ----------- Verify function -----------
async function verifyCode(code, resultDivId = 'manual-result') {
  code = code.trim();
  if (!code) return;

  const div = document.getElementById(resultDivId);
  div.innerHTML = '<p style="color:#888">Vérification…</p>';

  try {
    const formData = new FormData();
    formData.append('code', code);
    const res = await fetch(VERIFY_URL, { method: 'POST', body: formData });
    const data = await res.json();

    const colors = { valid: '#2ecc71', used: '#e74c3c', invalid: '#e74c3c', error: '#e5a800' };
    const color  = colors[data.status] || '#aaa';

    div.innerHTML = `
      <div style="border:2px solid ${color};border-radius:8px;padding:14px;background:${color}18">
        <p style="font-size:18px;font-weight:bold;color:${color}">${data.message}</p>
        ${data.nom   ? `<p style="font-size:13px;margin-top:6px">👤 ${data.nom}</p>` : ''}
        ${data.event ? `<p style="font-size:13px">🎵 ${data.event}</p>` : ''}
        ${data.date  ? `<p style="font-size:13px">📅 ${data.date} — ${data.lieu}</p>` : ''}
        <p style="font-size:11px;color:#666;margin-top:8px">Code : ${code}</p>
      </div>
    `;

    // Log
    const time = new Date().toLocaleTimeString('fr-FR');
    const icon = data.status === 'valid' ? '✅' : (data.status === 'used' ? '⚠️' : '❌');
    scanLog.unshift(`[${time}] ${icon} ${code} — ${data.status}`);
    const logDiv = document.getElementById('scan-log');
    logDiv.innerHTML = scanLog.map(l => `<div>${l}</div>`).join('');

  } catch (e) {
    div.innerHTML = '<p style="color:#e74c3c">Erreur réseau.</p>';
  }
}
</script>
</body></html>
