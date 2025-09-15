// tap en inventario => selecciona especie
// tap en recinto/slot => coloca en ese slot (o en el primero libre)

const API = '/api';
const MODO = window.MODO_JUEGO || 'digitalizado';
const AUTOCREAR = true;

let idPartida = Number(window.PARTIDA_ID || 0);
let especieActual = null;

const mapa = {
  'bosque-semejanza': 'bosque_semejanza',
  'prado-diferencia': 'prado_diferencia',
  'pradera-amor':     'pradera_amor',
  'trio-frondoso':    'trio_frondoso',
  'rey-selva':        'rey_selva',
  'isla-solitaria':   'isla_solitaria',
  'recinto-rio':      'rio'
};

const imagen = {
  triceratops:   '/Public/images/dinos/t.png',
  trex:          '/Public/images/dinos/t2.png',
  velociraptor:  '/Public/images/dinos/t3.png',
  stegosaurus:   '/Public/images/dinos/t4.png',
  brachiosaurus: '/Public/images/dinos/t5.png',
  pterodactilo:  '/Public/images/dinos/t6.png'
};

// --- API pequeña ---
function post(accion, datos) {
  const body = new URLSearchParams({ accion, ...datos });
  return fetch(API, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body })
    .then(r => r.json());
}
function get(accion, params) {
  const qs = new URLSearchParams({ accion, ...params });
  return fetch(`${API}?${qs}`).then(r => r.json());
}
function asegurarPartida() {
  if (idPartida || !AUTOCREAR) return Promise.resolve(idPartida);
  return post('crear_partida', { modo: MODO }).then(r => {
    if (r && r.ok && r.partida_id) {
      idPartida = Number(r.partida_id);
      window.PARTIDA_ID = idPartida;
    }
    return idPartida;
  });
}

// --- utilidades cortas ---
function claveRecinto(el) {
  for (const c of el.classList) if (mapa[c]) return mapa[c];
  return null;
}
function idxSlot(slot) {
  const lista = [...slot.parentElement.querySelectorAll('.slot')];
  return lista.indexOf(slot);
}
function primerLibre(recinto) {
  const slots = recinto.querySelectorAll('.slot');
  for (let i = 0; i < slots.length; i++) {
    if (!slots[i].dataset.especie && !slots[i].firstElementChild) return i;
  }
  return -1;
}
function pintar(slot, especie) {
  if (!slot || !slot.classList.contains('slot')) return;
  slot.innerHTML = '';
  const img = new Image();
  img.src = imagen[especie] || imagen.triceratops;
  img.alt = especie;
  img.className = 'dino';
  slot.appendChild(img);
  slot.dataset.especie = especie;
}
function limpiar() {
  document.querySelectorAll('.recinto .slot').forEach(s => { s.innerHTML = ''; s.removeAttribute('data-especie'); });
}
function cargar() {
  if (!idPartida) return;
  get('colocaciones', { partida_id: idPartida }).then(r => {
    if (!r || !r.ok) return;
    limpiar();
    (r.colocaciones || []).forEach(c => {
      const clase = Object.keys(mapa).find(k => mapa[k] === c.recinto);
      const rec   = clase && document.querySelector(`.recinto.${clase}`);
      const slot  = rec && rec.querySelectorAll('.slot')[c.slot];
      if (slot) pintar(slot, String(c.especie || '').trim().toLowerCase());
    });
  });
}

// --- un solo handler para TODO (sirve aunque el drop-up cree elementos luego) ---
let tocando = false; // evita doble touchend+click en móvil
document.addEventListener('touchend', (e) => { tocando = true; setTimeout(() => tocando = false, 250); manejarTap(e); });
document.addEventListener('click', (e) => { if (tocando) return; manejarTap(e); });

function manejarTap(e) {
  const btn = e.target.closest('.item-inventario') || e.target.closest('.tarjeta-dinosaurio');
  if (btn) {
    document.querySelectorAll('.item-inventario, .tarjeta-dinosaurio').forEach(x => { 
      x.classList.remove('seleccionado'); 
      x.setAttribute('aria-pressed','false'); 
    });
    btn.classList.add('seleccionado');
    btn.setAttribute('aria-pressed','true');

    const val = btn.dataset.especie || btn.getAttribute('aria-label') || btn.textContent;
    especieActual = String(val || '').trim().toLowerCase();
    e.preventDefault();
    e.stopPropagation();
    return;
  }

  // 2) Colocación en tablero
  const recinto = e.target.closest('.recinto');
  if (!recinto) return;
  if (!especieActual) { alert('Elegí un dinosaurio primero.'); return; }

  const clave = claveRecinto(recinto);
  if (!clave) return;

  const slots = [...recinto.querySelectorAll('.slot')];
  if (!slots.length) return;

  let slot = e.target.closest('.slot');
  let idx  = (slot && recinto.contains(slot)) ? idxSlot(slot) : -1;

  if (idx < 0) {
    idx = primerLibre(recinto);
    if (idx < 0) { alert('Este recinto está lleno.'); return; }
    slot = slots[idx];
  }
  if (slot.dataset.especie) { alert('Ese slot ya está ocupado.'); return; }

  asegurarPartida().then(() => {
    if (idPartida) {
      post('colocar', { partida_id: idPartida, recinto: clave, slot: idx, especie: especieActual })
        .then(r => { if (r && r.ok) cargar(); else alert((r && r.msg) ? r.msg : 'No se pudo colocar.'); });
    } else {
      pintar(slot, especieActual); // solo UI
    }
  });
}

// inicio
document.addEventListener('DOMContentLoaded', () => {
  if (idPartida) cargar();
  else if (AUTOCREAR) asegurarPartida().then(cargar);
});
