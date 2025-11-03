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

function apiGet(params) {
  const url = '/api?' + new URLSearchParams(params);
  const headers = {};
  if (window.CSRF) headers['X-CSRF'] = window.CSRF;
  return fetch(url, { headers }).then(r => r.json());
}

function apiPost(params) {
  const body = new URLSearchParams(params);
  if (window.CSRF) body.set('csrf', window.CSRF);
  return fetch('/api', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body
  }).then(r => r.json());
}

function post(accion, datos) {
  return apiPost({ accion, ...datos });
}
function get(accion, params) {
  return apiGet({ accion, ...params });
}

function toast(msg) {
  alert(msg);
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
  apiGet({accion:'colocaciones', partida_id: idPartida}).then(r => {
    if (!r || !r.ok) {
      console.warn('Error cargando colocaciones:', r?.msg);
      return;
    }
    limpiar();
    (r.colocaciones || []).forEach(c => {
      const clase = Object.keys(mapa).find(k => mapa[k] === c.recinto);
      const rec   = clase && document.querySelector(`.recinto.${clase}`);
      const slot  = rec && rec.querySelectorAll('.slot')[c.slot];
      if (slot) pintar(slot, String(c.especie || '').trim().toLowerCase());
    });
  }).catch(error => {
    console.error('Error cargando colocaciones:', error);
    toast('No se pudo cargar las colocaciones');
  });
}

let tocando = false;
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
      apiPost({accion:'colocar', partida_id: idPartida, recinto: clave, slot: idx, especie: especieActual})
        .then(r => { 
          if (r && r.ok) {
            cargar();
            if (typeof cargarEstado === 'function') {
              cargarEstado();
            }
          } else {
            toast(r?.msg || 'No se pudo colocar');
          }
        })
        .catch(error => {
          console.error('Error colocando:', error);
          toast('Error de conexión');
        });
    } else {
      pintar(slot, especieActual);
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  if (idPartida) cargar();
  else if (AUTOCREAR) asegurarPartida().then(cargar);
});
