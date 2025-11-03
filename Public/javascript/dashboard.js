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
  const sidebarOverlay = document.getElementById('sidebarOverlay');

  function toggleNav() {
    menuNav.classList.toggle('active');
    sidebarOverlay.classList.toggle('active');
    document.body.style.overflow = menuNav.classList.contains('active') ? 'hidden' : '';
  }

  function cerrarNav() {
    menuNav.classList.remove('active');
    sidebarOverlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  if (btnConfig) btnConfig.addEventListener('click', toggleNav);
  if (btnCerrarNav) btnCerrarNav.addEventListener('click', cerrarNav);
  if (sidebarOverlay) sidebarOverlay.addEventListener('click', cerrarNav);

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && menuNav.classList.contains('active')) {
      cerrarNav();
    }
  });

  function abrirModal(modo) {
    if (selectModo) {
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

  async function crearPartida(modo, jugadores = null) {
    try {
      const botonSubmit = document.querySelector('#formularioIniciarPartida button[type="submit"]');
      if (botonSubmit) {
        botonSubmit.disabled = true;
        botonSubmit.textContent = 'Creando...';
        botonSubmit.classList.add('cargando');
      }

      const datos = {
        accion: 'crear_partida',
        modo: modo
      };

      if (jugadores && Array.isArray(jugadores)) {
        datos.jugadores = JSON.stringify(jugadores);
      }

      const formData = new URLSearchParams();
      Object.keys(datos).forEach(key => {
        formData.append(key, datos[key]);
      });

      if (window.CSRF) {
        formData.append('csrf', window.CSRF);
      }

      const response = await fetch('/api', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData
      });
      
      const data = await response.json();

      if (data.ok) {
        cerrarModal();
        window.location.href = `/tablero-nuevo?partida_id=${data.partida_id}&modo=${modo}`;
      } else {
        const errorMsg = data.msg || 'Error desconocido';
        alert('Error creando partida: ' + errorMsg);
        if (botonSubmit) {
          botonSubmit.disabled = false;
          botonSubmit.textContent = 'Crear Partida';
        botonSubmit.classList.remove('cargando');
      }
      }
    } catch (error) {
      console.error('Error creando partida:', error);
      alert('Error de conexión');
      const botonSubmit = document.querySelector('#formularioIniciarPartida button[type="submit"]');
      if (botonSubmit) {
        botonSubmit.disabled = false;
        botonSubmit.textContent = 'Crear Partida';
        botonSubmit.classList.remove('cargando');
      }
    }
  }

  const formularioIniciarPartida = document.getElementById('formularioIniciarPartida');
  if (formularioIniciarPartida) {
    formularioIniciarPartida.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const modo = selectModo.value;
      const cantidadJugadores = document.getElementById('cantidadJugadores').value;
      
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
      
      if (jugadores.length < 2) {
        alert('Se necesitan al menos 2 jugadores para comenzar el juego');
        return;
      }
      
      await crearPartida(modo, jugadores);
    });
  }

  function setupGameModeListeners(element, modo) {
    if (!element) return;
    
    element.addEventListener('click', (e) => {
      if (e.target.closest('.btn-play')) return;
      e.preventDefault();
      abrirModal(modo);
    });
    
    const btnPlay = element.querySelector('.btn-play');
    if (btnPlay) {
      btnPlay.addEventListener('click', (e) => {
        e.stopPropagation();
        e.preventDefault();
        abrirModal(modo);
      });
    }
  }
  
  setupGameModeListeners(btnIniciarDigitalizado, 'digitalizado');
  setupGameModeListeners(btnIniciarSeguimiento, 'seguimiento');
  
  if (overlayModal) overlayModal.addEventListener('click', cerrarModal);

  const btnCancelarInicio = document.getElementById('botonCancelar');
  if (btnCancelarInicio) btnCancelarInicio.addEventListener('click', cerrarModal);

  const cantidadJugadoresSelect = document.getElementById('cantidadJugadores');
  if (cantidadJugadoresSelect) {
    cantidadJugadoresSelect.addEventListener('change', (e) => {
      const cantidad = parseInt(e.target.value);
      actualizarCamposJugadores(cantidad);
    });
  }

  function actualizarCamposJugadores(cantidad) {
    const contenedor = document.getElementById('contenedorNombresJugadores');
    if (!contenedor) return;

    contenedor.innerHTML = '';

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
      
      if (i === 1) {
        const nombreUsuario = document.querySelector('.welcome-text strong')?.textContent 
          || document.querySelector('.sidebar-username')?.textContent 
          || 'Jugador 1';
        input.value = nombreUsuario;
      } else {
        input.value = `Jugador ${i}`; 
      }
      
      div.appendChild(label);
      div.appendChild(input);
      contenedor.appendChild(div);
    }
  }

  actualizarCamposJugadores(2);

  const editButtons = document.querySelectorAll('.btn-edit');
  const modales = {
    nombre: document.getElementById('modalEditarNombre'),
    email: document.getElementById('modalEditarEmail'),
    password: document.getElementById('modalEditarPassword')
  };

  editButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const tipo = btn.dataset.edit;
      const modal = modales[tipo];
      if (modal) {
        modal.classList.add('activo');
        modal.setAttribute('aria-hidden', 'false');
        
        if (tipo === 'nombre') {
          const valorActual = document.querySelector('.sidebar-username').textContent;
          document.getElementById('inputNuevoNombre').value = valorActual;
        } else if (tipo === 'email') {
          const valorActual = document.querySelector('.sidebar-email').textContent;
          document.getElementById('inputNuevoEmail').value = valorActual;
        }
      }
    });
  });

  document.querySelectorAll('.btn-cancelar-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('.modal-editar');
      if (modal) {
        modal.classList.remove('activo');
        modal.setAttribute('aria-hidden', 'true');
        modal.querySelector('form').reset();
      }
    });
  });

  document.querySelectorAll('.modal-editar .fondo-modal').forEach(fondo => {
    fondo.addEventListener('click', () => {
      const modal = fondo.closest('.modal-editar');
      if (modal) {
        modal.classList.remove('activo');
        modal.setAttribute('aria-hidden', 'true');
        modal.querySelector('form').reset();
      }
    });
  });

  const formNombre = document.getElementById('formEditarNombre');
  if (formNombre) {
    formNombre.addEventListener('submit', async (e) => {
      e.preventDefault();
      const nombre = document.getElementById('inputNuevoNombre').value.trim();
      
      if (!nombre || nombre.length < 3) {
        mostrarMensaje('El nombre debe tener al menos 3 caracteres', 'error');
        return;
      }

      const btn = formNombre.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.textContent = 'Guardando...';

      try {
        const formData = new URLSearchParams();
        formData.append('accion', 'actualizar_nombre');
        formData.append('nombre', nombre);

        const res = await fetch('/api', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: formData
        });

        const data = await res.json();

        if (data.ok) {
          document.querySelector('.sidebar-username').textContent = nombre;
          document.querySelectorAll('.nav-label-value')[0].textContent = nombre;
          document.querySelector('.welcome-text strong').textContent = nombre;
          modales.nombre.classList.remove('activo');
          formNombre.reset();
          mostrarMensaje('Nombre actualizado correctamente', 'exito');
        } else {
          mostrarMensaje(data.msg || 'Error al actualizar', 'error');
        }
      } catch (error) {
        mostrarMensaje('Error de conexión', 'error');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Guardar';
      }
    });
  }

  const formEmail = document.getElementById('formEditarEmail');
  if (formEmail) {
    formEmail.addEventListener('submit', async (e) => {
      e.preventDefault();
      const email = document.getElementById('inputNuevoEmail').value.trim();
      
      if (!email || !email.includes('@')) {
        mostrarMensaje('Email inválido', 'error');
        return;
      }

      const btn = formEmail.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.textContent = 'Guardando...';

      try {
        const formData = new URLSearchParams();
        formData.append('accion', 'actualizar_email');
        formData.append('email', email);

        const res = await fetch('/api', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: formData
        });

        const data = await res.json();

        if (data.ok) {
          document.querySelector('.sidebar-email').textContent = email;
          document.querySelectorAll('.nav-label-value')[1].textContent = email;
          modales.email.classList.remove('activo');
          formEmail.reset();
          mostrarMensaje('Email actualizado correctamente', 'exito');
        } else {
          mostrarMensaje(data.msg || 'Error al actualizar', 'error');
        }
      } catch (error) {
        mostrarMensaje('Error de conexión', 'error');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Guardar';
      }
    });
  }

  const formPassword = document.getElementById('formEditarPassword');
  if (formPassword) {
    formPassword.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const actual = document.getElementById('inputPasswordActual').value;
      const nuevo = document.getElementById('inputPasswordNuevo').value;
      const confirmar = document.getElementById('inputPasswordConfirmar').value;

      if (!actual || !nuevo || !confirmar) {
        mostrarMensaje('Todos los campos son obligatorios', 'error');
        return;
      }

      if (nuevo.length < 6) {
        mostrarMensaje('La nueva contraseña debe tener al menos 6 caracteres', 'error');
        return;
      }

      if (nuevo !== confirmar) {
        mostrarMensaje('Las contraseñas no coinciden', 'error');
        return;
      }

      const btn = formPassword.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.textContent = 'Cambiando...';

      try {
        const formData = new URLSearchParams();
        formData.append('accion', 'actualizar_password');
        formData.append('password_actual', actual);
        formData.append('password_nuevo', nuevo);

        const res = await fetch('/api', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: formData
        });

        const data = await res.json();

        if (data.ok) {
          modales.password.classList.remove('activo');
          formPassword.reset();
          mostrarMensaje('Contraseña actualizada correctamente', 'exito');
        } else {
          mostrarMensaje(data.msg || 'Error al actualizar', 'error');
        }
      } catch (error) {
        mostrarMensaje('Error de conexión', 'error');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Cambiar';
      }
    });
  }

  function mostrarMensaje(texto, tipo) {
    const div = document.createElement('div');
    div.className = `mensaje-${tipo}`;
    div.textContent = texto;
    div.style.cssText = `
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 1rem 1.5rem;
      border-radius: 10px;
      font-weight: 600;
      z-index: 9999;
      animation: slideIn 0.3s ease;
      box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    `;
    
    if (tipo === 'error') {
      div.style.background = '#f44336';
      div.style.color = 'white';
    } else {
      div.style.background = '#4caf50';
      div.style.color = 'white';
    }
    
    document.body.appendChild(div);
    
    setTimeout(() => {
      div.style.animation = 'slideOut 0.3s ease';
      setTimeout(() => div.remove(), 300);
    }, 3000);
  }
});
