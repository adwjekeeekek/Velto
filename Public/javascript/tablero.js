const API = '/api';
let partidaId = Number(window.PARTIDA_ID || 0);
let especieElegida = null;
let estadoPartida = null;

const mapaRecintos = {
  'bosque-semejanza': 'bosque_semejanza',
  'prado-diferencia': 'prado_diferencia',
  'pradera-amor': 'pradera_amor',
  'trio-frondoso': 'trio_frondoso',
  'rey-selva': 'rey_selva',
  'isla-solitaria': 'isla_solitaria',
  'recinto-rio': 'rio'
};

const imagenesDinos = {
  triceratops: '/Public/images/dinos/t.png',
  trex: '/Public/images/dinos/t2.png',
  velociraptor: '/Public/images/dinos/t3.png',
  stegosaurus: '/Public/images/dinos/t4.png',
  brachiosaurus: '/Public/images/dinos/t5.png',
  pterodactilo: '/Public/images/dinos/t6.png'
};

async function post(accion, datos) {
  const body = new URLSearchParams({ accion, ...datos });
  const response = await fetch(API, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
    body
  });
  return await response.json();
}

async function get(accion, params) {
  const qs = new URLSearchParams({ accion, ...params });
  const response = await fetch(`${API}?${qs}`);
  return await response.json();
}

function claveRecinto(elemento) {
  for (const clase of elemento.classList) {
    if (mapaRecintos[clase]) return mapaRecintos[clase];
  }
  return null;
}

function indiceSlot(slot) {
  const slots = [...slot.parentElement.querySelectorAll('.slot')];
  return slots.indexOf(slot);
}

function primerSlotLibre(recinto) {
  const slots = recinto.querySelectorAll('.slot');
  for (let i = 0; i < slots.length; i++) {
    if (!slots[i].dataset.especie && !slots[i].firstElementChild) {
      return i;
    }
  }
  return -1;
}

function pintarSlot(slotEl, especie) {
  if (!slotEl || !slotEl.classList.contains('slot')) return;
  
  slotEl.innerHTML = '';
  const img = document.createElement('img');
  img.src = imagenesDinos[especie] || imagenesDinos.triceratops;
  img.alt = especie;
  img.style.width = '100%';
  img.style.height = '100%';
  img.style.objectFit = 'contain';
  img.style.display = 'block';
  slotEl.appendChild(img);
  slotEl.dataset.especie = especie;
}

function limpiarVista() {
  document.querySelectorAll('.recinto .slot').forEach(slot => {
    slot.innerHTML = '';
    slot.removeAttribute('data-especie');
  });
}

function limpiarVistaTotal() {
  document.querySelectorAll('.recinto .slot').forEach(s => {
    s.innerHTML = '';
    s.removeAttribute('data-especie');
  });
}

function prepararInventario() {
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.dino');
    if (!btn) return;
    
    document.querySelectorAll('.dino').forEach(el => {
      el.classList.remove('seleccionado');
      el.setAttribute('aria-pressed', 'false');
    });
    
    btn.classList.add('seleccionado');
    btn.setAttribute('aria-pressed', 'true');
    
    especieElegida = btn.dataset.especie || btn.getAttribute('aria-label') || btn.textContent;
    especieElegida = String(especieElegida || '').trim().toLowerCase();
  });
}

function prepararClicks() {
  document.addEventListener('click', (e) => {
    const recinto = e.target.closest('.recinto');
    if (!recinto) return;
    
    if (!especieElegida) {
      alert('Elegí un dinosaurio primero.');
      return;
    }
    
    const clave = claveRecinto(recinto);
    if (!clave) return;
    
    const slots = [...recinto.querySelectorAll('.slot')];
    if (!slots.length) return;
    
    let slot = e.target.closest('.slot');
    let indice = (slot && recinto.contains(slot)) ? indiceSlot(slot) : -1;
    
    if (indice < 0) {
      indice = primerSlotLibre(recinto);
      if (indice < 0) {
        alert('Este recinto está lleno.');
        return;
      }
      slot = slots[indice];
    }
    
    if (slot.dataset.especie) {
      alert('Ese slot ya está ocupado.');
      return;
    }
    
    colocar(clave, indice, especieElegida);
  });
}

async function colocar(recinto, slot, especie) {
  if (!partidaId) return;
  
  try {
    const body = new URLSearchParams({
      accion: 'colocar',
      partida_id: partidaId,
      recinto, slot, especie
    });
    
    const r = await fetch('/api', { 
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body 
    });
    
    const data = await r.json();
    if (!data.ok) { 
      alert(data.msg || 'No se pudo colocar'); 
      return; 
    }
    
    // En modo digitalizado, mostrar la pieza que acaba de colocar
    if (estadoPartida && estadoPartida.modo === 'digitalizado') {
      const claseRecinto = Object.keys(mapaRecintos).find(k => mapaRecintos[k] === recinto);
      const recintoEl = claseRecinto && document.querySelector(`.recinto.${claseRecinto}`);
      const slotEl = recintoEl && recintoEl.querySelectorAll('.slot')[slot];
      
      if (slotEl) {
        pintarSlot(slotEl, especie);
      }
      
      await cargarEstado();
    } else {
      limpiarVistaTotal();
      await cargarEstado();
    }
    
    const modal = document.getElementById('modalScoreboard');
    if (modal && modal.classList.contains('show')) {
      await mostrarScoreboard();
    }
  } catch (error) {
    console.error('Error colocando:', error);
    alert('Error de conexión');
  }
}

async function cargarEstado() {
  if (!partidaId) return;
  
  try {
    const respuesta = await get('estado_partida', { partida_id: partidaId });
    if (!respuesta || !respuesta.ok) return;
    
    estadoPartida = respuesta;
    actualizarUI();
    
    if (estadoPartida.modo === 'digitalizado') {
    } else {
    cargarColocaciones();
    }
  } catch (error) {
    console.error('Error cargando estado:', error);
  }
}

function actualizarUI() {
  if (!estadoPartida) return;
  
  const jugadaActual = estadoPartida.jugada || 1;
  const rondaActual = estadoPartida.ronda || 1;
  
  document.querySelectorAll('#ronda-actual-ui, #ronda-actual-ui-mobile').forEach(el => {
    el.textContent = `${jugadaActual}/12 — ${rondaActual}`;
  });
  
  const jugadorEnTurno = estadoPartida.jugador_en_turno;
  const nombreTurno = jugadorEnTurno ? jugadorEnTurno.nombre : `Jugador ${estadoPartida.turno_id || '-'}`;
  
  document.querySelectorAll('#turno-actual-ui, #turno-actual-ui-mobile').forEach(el => {
    el.textContent = nombreTurno;
  });
  
  const jugadorActual = estadoPartida.jugadores?.find(j => j.usuario_id === (window.USUARIO_ID || 1));
  const puntajeActual = jugadorActual?.puntos || 0;
  document.querySelectorAll('#puntaje-actual-ui, #puntaje-actual-ui-mobile').forEach(el => {
    el.textContent = `Puntaje: ${puntajeActual}`;
  });
  
  actualizarBotonDado(estadoPartida);
  
  if (estadoPartida.modo === 'seguimiento') {
    bloquearRecintosSegunPaso(estadoPartida);
  } else {
    document.querySelectorAll('.recinto').forEach(r => {
      r.classList.remove('bloqueado');
      r.style.pointerEvents = 'auto';
      r.style.opacity = '1';
    });
  }
  
  // Actualizar footer
  const turnoActual = document.getElementById('turno-actual');
  const rondaActualElement = document.getElementById('ronda-actual');
  
  if (turnoActual) {
    turnoActual.textContent = nombreTurno;
  }
  if (rondaActualElement) {
    rondaActualElement.textContent = estadoPartida.ronda || '-';
  }
  
  const textoTurnoMobile = document.getElementById('texto-turno-mobile');
  if (textoTurnoMobile) {
    textoTurnoMobile.textContent = `Ronda ${estadoPartida.ronda} — Turno de ${nombreTurno}`;
    textoTurnoMobile.style.display = 'block';
  }
  
  actualizarInventario();
  
  if (estadoPartida.estado === 'fin') {
    mostrarPantallaFinal();
  }
}

function actualizarInventario() {
  const especies = ['triceratops', 'trex', 'velociraptor', 'stegosaurus', 'brachiosaurus', 'pterodactilo'];
  
  const inventarioDesktop = document.querySelector('.grid');
  if (inventarioDesktop) {
    inventarioDesktop.innerHTML = '';
    especies.forEach(especie => {
      const item = document.createElement('button');
      item.className = 'dino';
      item.dataset.especie = especie;
      item.innerHTML = `
        <img src="${imagenesDinos[especie]}" alt="${especie}">
        <span>${especie}</span>
      `;
      inventarioDesktop.appendChild(item);
    });
  }
  
  const inventarioMobile = document.querySelector('.lista');
  if (inventarioMobile) {
    inventarioMobile.innerHTML = '';
    especies.forEach(especie => {
      const item = document.createElement('li');
      item.className = 'dino';
      item.dataset.especie = especie;
      item.innerHTML = `
        <img src="${imagenesDinos[especie]}" alt="${especie}">
        <span>${especie}</span>
      `;
      inventarioMobile.appendChild(item);
    });
  }
}

function actualizarBotonDado(estado) {
  const botonesDado = document.querySelectorAll('.boton-dado');
  
  botonesDado.forEach(boton => {
    if (!estado) {
      boton.disabled = true;
      boton.classList.add('deshabilitado');
      return;
    }
    
    const esModoDigitalizado = estado.modo === 'digitalizado';
    const esEstadoDado = estado.estado === 'dado';
    const esMiTurno = estado.turno_id === (window.USUARIO_ID || 1);
    
    const habilitado = esModoDigitalizado && esEstadoDado && esMiTurno;
    
    boton.disabled = !habilitado;
    if (habilitado) {
      boton.classList.remove('deshabilitado');
    } else {
      boton.classList.add('deshabilitado');
    }
    
    const resultado = boton.parentElement.querySelector('.resultado');
    if (resultado && estado.dado) {
      resultado.textContent = `Resultado: ${estado.dado}`;
    }
  });
}

function bloquearRecintosSegunPaso(est) {
  const permitido = est.recinto_permitido;
  if (!permitido) return;
  
  document.querySelectorAll('.recinto').forEach(r => {
    const claseRecinto = Object.keys(mapaRecintos).find(k => mapaRecintos[k] === permitido);
    const ok = claseRecinto ? r.classList.contains(claseRecinto) : false;
    
    r.classList.toggle('bloqueado', !ok);
    r.style.pointerEvents = ok ? 'auto' : 'none';
    r.style.opacity = ok ? '1' : '0.4';
  });
  
  const aviso = document.getElementById('aviso-seguimiento');
  if (aviso) {
    aviso.textContent = permitido ? `Colocá en: ${permitido.replace('_', ' ')}` : '';
  }
}

async function cargarColocaciones() {
  if (!partidaId) return;
  
  try {
    const respuesta = await get('colocaciones', { partida_id: partidaId });
    if (!respuesta || !respuesta.ok) return;
    
    limpiarVistaTotal();
    
    respuesta.colocaciones.forEach(colocacion => {
      const claseRecinto = Object.keys(mapaRecintos).find(k => mapaRecintos[k] === colocacion.recinto);
      const recinto = claseRecinto && document.querySelector(`.recinto.${claseRecinto}`);
      const slot = recinto && recinto.querySelectorAll('.slot')[colocacion.slot];
      
      if (slot) {
        pintarSlot(slot, colocacion.especie.toLowerCase());
      }
    });
  } catch (error) {
    console.error('Error cargando colocaciones:', error);
  }
}

// --- Acciones del juego ---
async function lanzarDado() {
  if (!partidaId) return;
  
  try {
    const respuesta = await post('lanzar_dado', {
      partida_id: partidaId
    });
    
    if (respuesta && respuesta.ok) {
      await cargarEstado();
    } else {
      alert(respuesta?.msg || 'Error al lanzar dado');
    }
  } catch (error) {
    console.error('Error lanzando dado:', error);
    alert('Error de conexión');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  prepararInventario();
  prepararClicks();
  
  document.querySelectorAll('.boton-dado').forEach(boton => {
    boton.addEventListener('click', lanzarDado);
  });
  
  const botonScoreboard = document.getElementById('botonScoreboard');
  if (botonScoreboard) {
    botonScoreboard.addEventListener('click', mostrarScoreboard);
  }
  
  if (partidaId) {
    cargarEstado();
  } else {
    crearNuevaPartida();
  }
  
  setInterval(cargarEstado, 5000);
});

async function crearNuevaPartida() {
  try {
    const modo = window.MODO_JUEGO || 'digitalizado';
    const jugadores = [
      { id: 1, nombre: 'Jugador 1', usuario_id: window.USUARIO_ID || 1 },
      { id: 2, nombre: 'Jugador 2', usuario_id: 2 }
    ];
    
    const respuesta = await post('crear_partida', { 
      modo: modo, 
      jugadores: jugadores 
    });
    
    if (respuesta && respuesta.ok && respuesta.partida_id) {
      partidaId = Number(respuesta.partida_id);
      window.PARTIDA_ID = partidaId;
      cargarEstado();
    } else {
      alert('Error creando partida: ' + (respuesta?.msg || 'Error desconocido'));
    }
  } catch (error) {
    console.error('Error creando partida:', error);
  }
}

// --- Scoreboard ---
async function mostrarScoreboard() {
  if (!partidaId) return;
  
  try {
    const respuesta = await get('puntaje', { partida_id: partidaId });
    if (!respuesta || !respuesta.ok) return;
    
    actualizarScoreboard(respuesta.jugadores || []);
    
    const modal = new bootstrap.Modal(document.getElementById('modalScoreboard'));
    modal.show();
  } catch (error) {
    console.error('Error cargando scoreboard:', error);
  }
}

function actualizarScoreboard(jugadores) {
  const scoreboard = document.querySelector('#scoreboard ul');
  if (!scoreboard) return;
  
  scoreboard.innerHTML = '';
  
  jugadores.forEach((jugador, index) => {
    const li = document.createElement('li');
    li.className = 'd-flex justify-content-between align-items-center mb-2';
    
    const posicion = index + 1;
    const emoji = posicion === 1 ? '🥇' : posicion === 2 ? '🥈' : posicion === 3 ? '🥉' : '🏅';
    
    li.innerHTML = `
      <div class="d-flex align-items-center">
        <span class="me-2">${emoji}</span>
        <span class="fw-bold">${jugador.nombre}</span>
      </div>
      <span class="badge bg-primary">${jugador.puntos} pts</span>
    `;
    
    scoreboard.appendChild(li);
  });
}

async function mostrarPantallaFinal() {
  const pantallaFinal = document.getElementById('pantalla-final');
  if (!pantallaFinal) return;
  
  try {
    const respuesta = await get('puntaje', { partida_id: partidaId });
    if (!respuesta || !respuesta.ok) return;
    
    const jugadorActual = respuesta.jugadores?.find(j => j.usuario_id === (window.USUARIO_ID || 1));
    const puntajeTotal = jugadorActual?.puntos || 0;
    
    document.getElementById('puntos-totales').textContent = puntajeTotal;
    
    if (jugadorActual?.desglose) {
      actualizarDesglosePuntos(jugadorActual.desglose);
    }
    
    pantallaFinal.style.display = 'flex';
  } catch (error) {
    console.error('Error mostrando pantalla final:', error);
  }
}

function nuevaPartida() {
  if (confirm('¿Crear una nueva partida?')) {
    window.location.href = 'tablero.php?modo=digitalizado';
  }
}

function volverInicio() {
  if (confirm('¿Volver al menú principal?')) {
    window.location.href = 'index.php';
  }
}

function actualizarDesglosePuntos(desglose) {
  const contenedor = document.getElementById('desglose-puntos');
  if (!contenedor) return;
  
  contenedor.innerHTML = '';
  
  Object.entries(desglose).forEach(([categoria, puntos]) => {
    if (puntos > 0) {
      const item = document.createElement('li');
                item.className = 'item-punto';
                item.innerHTML = `
                  <span class="etiqueta-punto">${categoria}</span>
                  <span class="valor-punto">+${puntos}</span>
                `;
      contenedor.appendChild(item);
    }
  });
}

document.addEventListener('DOMContentLoaded', function() {
  const botonReglas = document.getElementById('botonReglas');
  if (botonReglas) {
    botonReglas.addEventListener('click', function() {
      const modal = new bootstrap.Modal(document.getElementById('modalReglas'));
      modal.show();
    });
  }

  const botonReglasMobile = document.getElementById('botonReglasMobile');
  if (botonReglasMobile) {
    botonReglasMobile.addEventListener('click', function() {
      const modal = new bootstrap.Modal(document.getElementById('modalReglas'));
      modal.show();
    });
  }
});
