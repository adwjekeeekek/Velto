const API = '/api';
let partidaId = Number(window.PARTIDA_ID || 0);
let especieElegida = null;
let estadoPartida = null;
let ultimoTurnoId = null;

const mapaRecintos = {
  'bosque_semejanza': 'bosque_semejanza',
  'prado_diferencia': 'prado_diferencia',
  'pradera_amor': 'pradera_amor',
  'trio_frondoso': 'trio_frondoso',
  'rey_selva': 'rey_selva',
  'isla_solitaria': 'isla_solitaria',
  'rio': 'rio'
};

const imagenesDinos = {
  triceratops: '/Public/images/dinos/t.png',
  trex: '/Public/images/dinos/t2.png',
  velociraptor: '/Public/images/dinos/t3.png',
  stegosaurus: '/Public/images/dinos/t4.png',
  brachiosaurus: '/Public/images/dinos/t5.png',
  pterodactilo: '/Public/images/dinos/t6.png'
};

async function apiGet(params) {
  const url = '/api?' + new URLSearchParams(params);
  const headers = {};
  if (window.CSRF) headers['X-CSRF'] = window.CSRF;
  const r = await fetch(url, { headers });
  return r.json();
}

async function apiPost(params) {
  const body = new URLSearchParams(params);
  if (window.CSRF) body.set('csrf', window.CSRF);
  const r = await fetch('/api', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body
  });
  return r.json();
}

async function post(accion, datos) {
  return apiPost({ accion, ...datos });
}

async function get(accion, params) {
  return apiGet({ accion, ...params });
}

function toast(msg, tipo = 'error') {
  let toastContainer = document.getElementById('toast-container');
  if (!toastContainer) {
    toastContainer = document.createElement('div');
    toastContainer.id = 'toast-container';
    toastContainer.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 20000;
      display: flex;
      flex-direction: column;
      gap: 10px;
    `;
    document.body.appendChild(toastContainer);
  }
  
  const toast = document.createElement('div');
  toast.className = `toast toast-${tipo}`;
  toast.style.cssText = `
    background: ${tipo === 'error' ? 'linear-gradient(135deg, #d32f2f, #c62828)' : 'linear-gradient(135deg, #388e3c, #2e7d32)'};
    color: white;
    padding: 16px 24px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
    font-weight: 600;
    font-size: 0.95rem;
    max-width: 350px;
    word-wrap: break-word;
    animation: slideInRight 0.3s ease;
    border: 2px solid ${tipo === 'error' ? '#b71c1c' : '#1b5e20'};
  `;
  toast.textContent = msg;
  
  const style = document.createElement('style');
  style.textContent = `
    @keyframes slideInRight {
      from {
        transform: translateX(400px);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    @keyframes slideOutRight {
      from {
        transform: translateX(0);
        opacity: 1;
      }
      to {
        transform: translateX(400px);
        opacity: 0;
      }
    }
  `;
  if (!document.getElementById('toast-styles')) {
    style.id = 'toast-styles';
    document.head.appendChild(style);
  }
  
  toastContainer.appendChild(toast);
  
  setTimeout(() => {
    toast.style.animation = 'slideOutRight 0.3s ease';
    setTimeout(() => {
      toast.remove();
      if (toastContainer.children.length === 0) {
        toastContainer.remove();
      }
    }, 300);
  }, 4000);
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
    if (!slots[i].dataset.especie && slots[i].children.length === 0) {
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
    
    if (recinto.classList.contains('bloqueado')) {
      toast('🚫 Este recinto está bloqueado por la restricción del dado', 'error');
      return;
    }
    
    if (!especieElegida) {
      toast('🦖 Selecciona un dinosaurio del inventario primero', 'error');
      return;
    }
    
    const clave = claveRecinto(recinto);
    if (!clave) {
      console.warn('⚠️ No se pudo obtener clave del recinto');
      return;
    }
    
    const slots = [...recinto.querySelectorAll('.slot')];
    if (!slots.length) {
      console.warn('⚠️ No hay slots en el recinto');
      return;
    }
    
    let slot = e.target.closest('.slot');
    let indice = (slot && recinto.contains(slot)) ? indiceSlot(slot) : -1;
    
    if (indice < 0) {
      indice = primerSlotLibre(recinto);
      if (indice < 0) {
        toast('❌ Este recinto está lleno', 'error');
        return;
      }
      slot = slots[indice];
    }
    
    if (slot.dataset.especie) {
      toast('⚠️ Este slot ya está ocupado', 'error');
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
      toast(data.msg || 'No se pudo colocar', 'error'); 
      return; 
    }
    
    toast(`✅ Dinosaurio colocado correctamente`, 'success');
    
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
    console.error('❌ Error colocando:', error);
    toast('Error de conexión con el servidor', 'error');
  }
}

async function cargarEstado() {
  if (!partidaId) return;
  
  try {
    const respuesta = await apiGet({accion:'estado_partida', partida_id: partidaId});
    
    if (!respuesta || !respuesta.ok) {
      alert('Error cargando estado: ' + (respuesta?.msg || 'Sin respuesta'));
      return;
    }
    
    estadoPartida = respuesta;
    
    if (estadoPartida.estado === 'fin' || estadoPartida.finalizada) {
      actualizarUI();
      mostrarPantallaFinal();
      return;
    }
    
    const cambioTurno = ultimoTurnoId !== null && ultimoTurnoId !== estadoPartida.turno_id;
    if (cambioTurno) {
      mostrarModalTurno();
    }
    
    ultimoTurnoId = estadoPartida.turno_id;
    actualizarUI();
    
    if (estadoPartida.modo === 'digitalizado') {
      // En modo digitalizado, cargar colocaciones también
      await cargarColocaciones();
    } else {
      await cargarColocaciones();
    }
  } catch (error) {
    console.error('Error cargando estado:', error);
    alert('Error de conexión: ' + error.message);
  }
}

function actualizarUI() {
  if (!estadoPartida) return;
  
  const jugadaActual = estadoPartida.jugada || 1;
  const rondaActual = estadoPartida.ronda || 1;
  const numJugadores = estadoPartida.jugadores?.length || 2;
  
  const turnosPorRonda = (numJugadores === 2) ? 3 : 6;
  const rondasTotales = (numJugadores === 2) ? 4 : 2;
  const totalTurnos = 12;
  const turnoGlobal = ((rondaActual - 1) * turnosPorRonda) + jugadaActual;
  
  document.querySelectorAll('#jugada-actual-ui, #jugada-actual-ui-mobile').forEach(el => {
    el.textContent = `${turnoGlobal}/12`;
  });
  
  document.querySelectorAll('#ronda-actual-ui, #ronda-actual-ui-mobile').forEach(el => {
    el.textContent = `${rondaActual}/${rondasTotales}`;
  });
  
  const jugadorEnTurno = estadoPartida.jugador_en_turno;
  let nombreJugador = '';
  
  if (jugadorEnTurno) {
    nombreJugador = jugadorEnTurno.nombre;
  } else if (estadoPartida.turno_id) {
    const jugador = estadoPartida.jugadores?.find(j => j.id === estadoPartida.turno_id);
    nombreJugador = jugador ? jugador.nombre : 'Esperando...';
  } else if (estadoPartida.jugadores && estadoPartida.jugadores.length > 0) {
    nombreJugador = estadoPartida.jugadores[0].nombre;
  } else {
    nombreJugador = 'Esperando...';
  }
  
  document.querySelectorAll('#turno-actual-ui, #turno-actual-ui-mobile').forEach(el => {
    el.textContent = nombreJugador;
  });
  
  actualizarBotonDado(estadoPartida);
  
  if (estadoPartida.modo === 'seguimiento') {
    bloquearRecintosSegunPaso(estadoPartida);
  } else if (estadoPartida.modo === 'digitalizado' && estadoPartida.dado) {
    bloquearRecintosSegunDado(estadoPartida.dado);
  } else {
    document.querySelectorAll('.recinto').forEach(r => {
      r.classList.remove('bloqueado');
      r.style.pointerEvents = 'auto';
      r.style.opacity = '1';
    });
  }
  
  actualizarInventario();
}

function actualizarInventario() {
  const usandoBolsa = estadoPartida && estadoPartida.mi_mano && estadoPartida.mi_mano.length > 0;
  const especies = usandoBolsa
    ? estadoPartida.mi_mano
    : ['triceratops', 'trex', 'velociraptor', 'stegosaurus', 'brachiosaurus', 'pterodactilo'];
  
  if (!usandoBolsa && estadoPartida && !window._bolsaWarningShown) {
    console.warn('⚠️ Sistema de bolsa no activo. Ejecuta http://localhost:8080/actualizar-bd para activarlo');
    window._bolsaWarningShown = true;
  }
  
  const inventarioDesktop = document.querySelector('.grid');
  if (inventarioDesktop) {
    inventarioDesktop.innerHTML = '';
    especies.forEach(especie => {
      const especieNormalizada = especie.toLowerCase();
      const item = document.createElement('button');
      item.className = 'dino';
      item.dataset.especie = especieNormalizada;
      item.innerHTML = `
        <img src="${imagenesDinos[especieNormalizada]}" alt="${especieNormalizada}">
        <span>${especieNormalizada}</span>
      `;
      inventarioDesktop.appendChild(item);
    });
  }
  
  const inventarioMobile = document.querySelector('.lista');
  if (inventarioMobile) {
    inventarioMobile.innerHTML = '';
    especies.forEach(especie => {
      const especieNormalizada = especie.toLowerCase();
      const item = document.createElement('li');
      item.className = 'dino';
      item.dataset.especie = especieNormalizada;
      item.innerHTML = `
        <img src="${imagenesDinos[especieNormalizada]}" alt="${especieNormalizada}">
        <span>${especieNormalizada}</span>
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
    const esModoSeguimiento = estado.modo === 'seguimiento';
    const esEstadoDado = estado.estado === 'dado';
    
    if (esModoSeguimiento) {
      boton.disabled = true;
      boton.classList.add('deshabilitado');
      
      const resultadoElements = document.querySelectorAll('.dice-result, #diceResult');
      resultadoElements.forEach(resultado => {
        resultado.textContent = '📋 Modo Seguimiento - El dado no se utiliza';
      });
      return;
    }
    
    const habilitado = esModoDigitalizado && esEstadoDado;
    
    boton.disabled = !habilitado;
    if (habilitado) {
      boton.classList.remove('deshabilitado');
    } else {
      boton.classList.add('deshabilitado');
    }
    
    const resultadoElements = document.querySelectorAll('.dice-result, #diceResult');
    resultadoElements.forEach(resultado => {
      if (estado.dado) {
        const carasMap = {
          'bosque': '🌳 Bosque - Recintos del área del Bosque',
          'llanura': '🌾 Llanura - Recintos del área de Llanura',
          'baños': '🚻 Baños - Recintos a la derecha del Río',
          'banos': '🚻 Baños - Recintos a la derecha del Río',
          'cafeteria': '☕ Cafetería - Recintos a la izquierda del Río',
          'cafetería': '☕ Cafetería - Recintos a la izquierda del Río',
          'vacio': '🈳 Recinto Vacío - Solo recintos vacíos',
          'vacío': '🈳 Recinto Vacío - Solo recintos vacíos',
          'sin_trex': '🦖 Sin T-Rex - Recintos sin T-Rex',
          'trex': '🦖 Sin T-Rex - Recintos sin T-Rex'
        };
        resultado.textContent = carasMap[estado.dado.toLowerCase()] || `Resultado: ${estado.dado}`;
      } else if (esEstadoDado) {
        resultado.textContent = '🎲 Lanza el dado para determinar las restricciones';
      } else {
        resultado.textContent = '⏳ Coloca un dinosaurio primero';
      }
    });
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

function bloquearRecintosSegunDado(caraDado) {
  if (!caraDado) {
    // Sin dado lanzado, desbloquear todo
    document.querySelectorAll('.recinto').forEach(r => {
      r.classList.remove('bloqueado');
      r.style.pointerEvents = 'auto';
      r.style.opacity = '1';
    });
    return;
  }
  
  const recintosBosque = ['bosque_semejanza', 'trio_frondoso', 'rey_selva'];
  const recintosLlanura = ['prado_diferencia', 'pradera_amor', 'isla_solitaria'];
  const recintosIzquierda = ['bosque_semejanza', 'trio_frondoso', 'rey_selva'];
  const recintosDerecha = ['prado_diferencia', 'pradera_amor', 'isla_solitaria'];
  const rio = ['rio'];
  
  let recintosPermitidos = [];
  
  switch(caraDado.toLowerCase()) {
    case 'bosque':
      recintosPermitidos = [...recintosBosque, ...rio];
      break;
    case 'llanura':
      recintosPermitidos = [...recintosLlanura, ...rio];
      break;
    case 'cafeteria':
    case 'cafetería':
      recintosPermitidos = [...recintosIzquierda, ...rio];
      break;
    case 'baños':
    case 'banos':
      recintosPermitidos = [...recintosDerecha, ...rio];
      break;
    case 'vacio':
    case 'vacío':
    case 'recinto_vacio':
      recintosPermitidos = [...recintosBosque, ...recintosLlanura, ...rio];
      break;
    case 'trex':
    case 't-rex':
    case 'sin_trex':
      recintosPermitidos = [...recintosBosque, ...recintosLlanura, ...rio];
      break;
    default:
      recintosPermitidos = [...recintosBosque, ...recintosLlanura, ...rio];
  }
  
  // Aplicar bloqueo visual
  document.querySelectorAll('.recinto').forEach(r => {
    // Extraer el nombre del recinto de las clases
    let nombreRecinto = null;
    r.classList.forEach(clase => {
      if (clase !== 'recinto' && clase !== 'bloqueado') {
        nombreRecinto = clase;
      }
    });
    
    let permitido = recintosPermitidos.includes(nombreRecinto);
    
    if (permitido && (caraDado.toLowerCase() === 'vacio' || caraDado.toLowerCase() === 'vacío' || caraDado.toLowerCase() === 'recinto_vacio')) {
      const slots = r.querySelectorAll('.slot.occupied');
      permitido = slots.length === 0;
    }
    
    if (permitido && (caraDado.toLowerCase() === 'trex' || caraDado.toLowerCase() === 't-rex' || caraDado.toLowerCase() === 'sin_trex')) {
      const slots = r.querySelectorAll('.slot');
      let tieneTrex = false;
      slots.forEach(slot => {
        if (slot.dataset.especie === 'trex') {
          tieneTrex = true;
        }
      });
      permitido = !tieneTrex;
    }
    
    r.classList.toggle('bloqueado', !permitido);
    r.style.pointerEvents = permitido ? 'auto' : 'none';
    r.style.opacity = permitido ? '1' : '0.4';
  });
}

async function cargarColocaciones() {
  if (!partidaId) return;
  
  try {
    const respuesta = await apiGet({accion:'colocaciones', partida_id: partidaId});
    if (!respuesta || !respuesta.ok) {
      console.warn('Error cargando colocaciones:', respuesta?.msg);
      return;
    }
    
    limpiarVistaTotal();
    
    (respuesta.colocaciones || []).forEach(colocacion => {
      const claseRecinto = Object.keys(mapaRecintos).find(k => mapaRecintos[k] === colocacion.recinto);
      const recinto = claseRecinto && document.querySelector(`.recinto.${claseRecinto}`);
      const slot = recinto && recinto.querySelectorAll('.slot')[colocacion.slot];
      
      if (slot) {
        pintarSlot(slot, colocacion.especie.toLowerCase());
      }
    });
  } catch (error) {
    console.error('Error cargando colocaciones:', error);
    toast('No se pudo cargar las colocaciones');
  }
}

// --- Acciones del juego ---
async function lanzarDado() {
  if (!partidaId) {
    toast('No hay partida activa', 'error');
    return;
  }
  
  try {
    const respuesta = await apiPost({accion:'lanzar_dado', partida_id: partidaId});
    
    if (respuesta && respuesta.ok) {
      toast(`🎲 Dado lanzado: ${respuesta.dado}`, 'success');
      await cargarEstado();
    } else {
      toast(respuesta?.msg || 'Error al lanzar dado', 'error');
    }
  } catch (error) {
    toast('No se pudo conectar con el servidor', 'error');
  }
}

document.addEventListener('DOMContentLoaded', () => {
  prepararInventario();
  prepararClicks();
  
  const botonesDado = document.querySelectorAll('.boton-dado');
  botonesDado.forEach((boton) => {
    boton.addEventListener('click', () => {
      lanzarDado();
    });
  });
  
  const botonScoreboard = document.getElementById('botonScoreboard');
  if (botonScoreboard) {
    botonScoreboard.addEventListener('click', mostrarScoreboard);
  }
  
  const botonScoreboardMobile = document.getElementById('botonScoreboardMobile');
  if (botonScoreboardMobile) {
    botonScoreboardMobile.addEventListener('click', () => {
      document.getElementById('mobileMenu').classList.remove('active');
      mostrarScoreboard();
    });
  }
  
  const botonReglas = document.getElementById('botonReglas');
  if (botonReglas) {
    botonReglas.addEventListener('click', () => {
      document.getElementById('modal-reglas').style.display = 'flex';
    });
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
    const respuesta = await apiGet({accion:'puntaje', partida_id: partidaId});
    if (!respuesta || !respuesta.ok) {
      alert('Error cargando puntuaciones');
      return;
    }
    
    const jugadores = respuesta.jugadores || [];
    
    jugadores.sort((a, b) => (b.puntos || 0) - (a.puntos || 0));
    
    const scoreboardContainer = document.getElementById('scoreboard-container');
    if (!scoreboardContainer) return;
    
    if (jugadores.length === 0) {
      scoreboardContainer.innerHTML = `
        <div class="scoreboard-empty">
          <div class="scoreboard-empty-icon">🏆</div>
          <p>No hay puntuaciones aún</p>
        </div>
      `;
    } else {
      scoreboardContainer.innerHTML = '';
      
      jugadores.forEach((jugador, index) => {
        const posicion = index + 1;
        let posicionClass = '';
        let emoji = '👤';
        
        if (posicion === 1) {
          posicionClass = 'first';
          emoji = '🥇';
        } else if (posicion === 2) {
          posicionClass = 'second';
          emoji = '🥈';
        } else if (posicion === 3) {
          posicionClass = 'third';
          emoji = '🥉';
        }
        
        const scoreItem = document.createElement('div');
        scoreItem.className = 'scoreboard-item';
        scoreItem.innerHTML = `
          <div class="scoreboard-position ${posicionClass}">${posicion}</div>
          <div class="scoreboard-avatar">${emoji}</div>
          <div class="scoreboard-info">
            <p class="scoreboard-name">${jugador.nombre}</p>
            <p class="scoreboard-label">Jugador ${posicion}</p>
          </div>
          <div class="scoreboard-points">${jugador.puntos || 0}</div>
        `;
        
        scoreboardContainer.appendChild(scoreItem);
      });
    }
    
    document.getElementById('modal-scoreboard').style.display = 'flex';
    
  } catch (error) {
    console.error('Error mostrando scoreboard:', error);
    alert('No se pudo cargar las puntuaciones');
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

function mostrarModalTurno() {
  const modal = document.getElementById('modal-turno');
  const nombreElement = document.getElementById('modal-turno-nombre');
  const countdownElement = document.getElementById('modal-turno-countdown');
  
  if (!modal || !estadoPartida || !estadoPartida.jugador_en_turno) return;
  
  limpiarVistaTotal();
  
  const nombreJugador = estadoPartida.jugador_en_turno.nombre || 'Jugador';
  nombreElement.textContent = nombreJugador;
  
  modal.style.display = 'flex';
  
  let contador = 3;
  countdownElement.textContent = contador;
  
  const interval = setInterval(() => {
    contador--;
    if (contador > 0) {
      countdownElement.textContent = contador;
    } else {
      clearInterval(interval);
      modal.style.display = 'none';
      cargarColocaciones();
    }
  }, 1000);
}

async function mostrarPantallaFinal() {
  const pantallaFinal = document.getElementById('pantalla-final');
  if (!pantallaFinal) return;
  
  try {
    const respuesta = await apiGet({accion:'puntaje', partida_id: partidaId});
    if (!respuesta || !respuesta.ok) {
      console.warn('Error cargando puntuaciones finales');
      return;
    }
    
    const jugadores = respuesta.jugadores || [];
    
    jugadores.sort((a, b) => (b.puntos || 0) - (a.puntos || 0));
    
    const podiumContainer = document.getElementById('podium-container');
    podiumContainer.innerHTML = '';
    
    const medallas = ['🥇', '🥈', '🥉'];
    jugadores.slice(0, 3).forEach((jugador, index) => {
      const podiumDiv = document.createElement('div');
      podiumDiv.className = 'podium-place';
      podiumDiv.innerHTML = `
        <div class="podium-medal">${medallas[index]}</div>
        <div class="podium-name">${jugador.nombre}</div>
        <div class="podium-points">${jugador.puntos || 0} pts</div>
        <div class="podium-base"></div>
      `;
      podiumContainer.appendChild(podiumDiv);
    });
    
    const scoresTable = document.getElementById('scores-table');
    scoresTable.innerHTML = '';
    
    jugadores.forEach((jugador, index) => {
      const scoreRow = document.createElement('div');
      scoreRow.className = 'score-row';
      scoreRow.innerHTML = `
        <div class="score-position">${index + 1}</div>
        <div class="score-avatar">👤</div>
        <div class="score-info">
          <div class="score-name">${jugador.nombre}</div>
          <div class="score-label">Jugador ${index + 1}</div>
        </div>
        <div class="score-points">${jugador.puntos || 0}</div>
      `;
      scoresTable.appendChild(scoreRow);
    });
    
    pantallaFinal.style.display = 'flex';
  } catch (error) {
    console.error('Error mostrando pantalla final:', error);
    alert('Error cargando resultados finales');
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

