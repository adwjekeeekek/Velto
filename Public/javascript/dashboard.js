document.addEventListener('DOMContentLoaded', () => {
  const btnIniciarDigitalizado = document.getElementById('botonIniciarDigitalizado');
  const btnIniciarSeguimiento = document.getElementById('botonIniciarSeguimiento');
  const modalIniciarPartida = document.getElementById('modalIniciarPartida');
  const overlayModal = document.getElementById('fondoModal');
  const selectModo = document.getElementById('selectorModo');

  // Configuración del menú de navegación
  const btnConfig = document.getElementById('botonConfiguracion');
  const menuNav = document.getElementById('menuNavegacion');
  const btnCerrarNav = document.getElementById('botonCerrar');

  // Función para alternar el menú de navegación
  function toggleNav() {
    menuNav.classList.toggle('activo');
  }

  // Función para cerrar el menú de navegación
  function cerrarNav() {
    menuNav.classList.remove('activo');
  }

  // Eventos para el menú de navegación
  if (btnConfig) btnConfig.addEventListener('click', toggleNav);
  if (btnCerrarNav) btnCerrarNav.addEventListener('click', cerrarNav);

  // Cerrar el menú cuando se hace clic fuera de él
  document.addEventListener('click', (e) => {
    if (menuNav && btnConfig && !menuNav.contains(e.target) && !btnConfig.contains(e.target)) {
      cerrarNav();
    }
  });

  // Código existente para el modal
  function abrirModal(modo) {
    if (selectModo) {
      // Mapear modos correctamente
      const modoMap = {
        'digitalizado': 'digitalizado',
        'seguimiento': 'seguimiento'
      };
      selectModo.value = modoMap[modo] || 'digitalizado';
    }
    if (modalIniciarPartida) {
      modalIniciarPartida.classList.add('activo');
      modalIniciarPartida.setAttribute('aria-hidden', 'false');
    }
  }

  function cerrarModal() {
    if (modalIniciarPartida) {
      modalIniciarPartida.classList.remove('activo');
      modalIniciarPartida.setAttribute('aria-hidden', 'true');
    }
  }

  // Función para crear partida
  async function crearPartida(modo, jugadores = null) {
    try {
      // Mostrar indicador de carga
      const botonSubmit = document.querySelector('#formularioIniciarPartida button[type="submit"]');
      if (botonSubmit) {
        botonSubmit.disabled = true;
        botonSubmit.textContent = 'Creando...';
        botonSubmit.classList.add('cargando');
      }

      // Preparar datos
      const datos = {
        accion: 'crear_partida',
        modo: modo
      };

      // Si se proporcionan jugadores, incluirlos
      if (jugadores && Array.isArray(jugadores)) {
        datos.jugadores = JSON.stringify(jugadores);
      }

      // Crear FormData
      const formData = new URLSearchParams();
      Object.keys(datos).forEach(key => {
        formData.append(key, datos[key]);
      });

      const response = await fetch('/api', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
      });
      
      const data = await response.json();

      if (data.ok) {
        // Cerrar modal
        cerrarModal();
        // Redirigir al tablero
        window.location.href = `/tablero?partida_id=${data.partida_id}&modo=${modo}`;
      } else {
        alert('Error creando partida: ' + (data.msg || 'Error desconocido'));
        // Restaurar botón
        if (botonSubmit) {
          botonSubmit.disabled = false;
          botonSubmit.textContent = 'Crear Partida';
          botonSubmit.classList.remove('cargando');
        }
      }
    } catch (error) {
      console.error('Error creando partida:', error);
      alert('Error de conexión');
      // Restaurar botón
      const botonSubmit = document.querySelector('#formularioIniciarPartida button[type="submit"]');
      if (botonSubmit) {
        botonSubmit.disabled = false;
        botonSubmit.textContent = 'Crear Partida';
        botonSubmit.classList.remove('cargando');
      }
    }
  }

  // Manejar envío del formulario del modal
  const formularioIniciarPartida = document.getElementById('formularioIniciarPartida');
  if (formularioIniciarPartida) {
    formularioIniciarPartida.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const modo = selectModo.value;
      const cantidadJugadores = document.getElementById('cantidadJugadores').value;
      
      // Recopilar nombres de jugadores
      const jugadores = [];
      for (let i = 1; i <= cantidadJugadores; i++) {
        const inputJugador = document.getElementById(`jugador${i}`);
        if (inputJugador && inputJugador.value.trim()) {
          jugadores.push({
            nombre: inputJugador.value.trim(),
            id: i
          });
        }
      }
      
      // Validar que haya al menos 2 jugadores
      if (jugadores.length < 2) {
        alert('Se necesitan al menos 2 jugadores para comenzar el juego');
        return;
      }
      
      await crearPartida(modo, jugadores);
    });
  }

  // Abrir modal para configurar partida
  if (btnIniciarDigitalizado) {
    btnIniciarDigitalizado.addEventListener('click', (e) => { 
      e.preventDefault();
      abrirModal('digitalizado');
    });
  }
  
  if (btnIniciarSeguimiento) {
    btnIniciarSeguimiento.addEventListener('click', (e) => { 
      e.preventDefault(); 
      abrirModal('seguimiento');
    });
  }
  
  if (overlayModal) overlayModal.addEventListener('click', cerrarModal);

  const btnCancelarInicio = document.getElementById('botonCancelar');
  if (btnCancelarInicio) btnCancelarInicio.addEventListener('click', cerrarModal);

  // Manejar cambio en cantidad de jugadores
  const cantidadJugadoresSelect = document.getElementById('cantidadJugadores');
  if (cantidadJugadoresSelect) {
    cantidadJugadoresSelect.addEventListener('change', (e) => {
      const cantidad = parseInt(e.target.value);
      actualizarCamposJugadores(cantidad);
    });
  }

  // Función para actualizar campos de jugadores
  function actualizarCamposJugadores(cantidad) {
    const contenedor = document.getElementById('contenedorNombresJugadores');
    if (!contenedor) return;

    // Limpiar contenedor
    contenedor.innerHTML = '';

    // Crear campos para cada jugador
    for (let i = 1; i <= cantidad; i++) {
      const div = document.createElement('div');
      div.className = 'mb-3';
      
      const label = document.createElement('label');
      label.className = 'form-label';
      label.textContent = `Jugador ${i}`;
      label.setAttribute('for', `jugador${i}`);
      
      const input = document.createElement('input');
      input.type = 'text';
      input.className = 'form-control';
      input.id = `jugador${i}`;
      input.name = `jugador${i}`;
      input.required = true;
      
      // Valor por defecto para el primer jugador
      if (i === 1) {
        // Obtener nombre del usuario actual desde el HTML
        const nombreUsuario = document.querySelector('.mensaje-bienvenida strong')?.textContent || 'Jugador 1';
        input.value = nombreUsuario;
      } else {
        input.value = `Jugador ${i}`; 
      }
      
      div.appendChild(label);
      div.appendChild(input);
      contenedor.appendChild(div);
    }
  }

  // Inicializar con 2 jugadores por defecto
  actualizarCamposJugadores(2);
});
