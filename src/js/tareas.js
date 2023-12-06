(function () {
  obtenerTareas();
  let tareas = [];
  let filtradas = [];

  // Boton para mostrar el modal de agregar tarea
  const nuevaTareaBtn = document.querySelector('#agregar-tarea');
  nuevaTareaBtn.addEventListener('click', function () {
    mostrarFormulario();
  });

  // Filtros de busqueda
  const filtros = document.querySelectorAll('#filtros input[type="radio"]');
  filtros.forEach(radio => {
    radio.addEventListener('input', filtrarTareas);
  });
  
  function filtrarTareas(e) {
    const filto = e.target.value;

    if (filto !== '') {
      filtradas = tareas.filter(tarea => tarea.estado === filto);
    } else {
      filtradas = [];
    }

    mostrarTareas();
  }

  async function obtenerTareas() {
    try {
      const id = obtenerProyecto();
      const url = `http://localhost:3000/api/tareas?id=${id}`;
      const respuesta = await fetch(url);
      const resultado = await respuesta.json();

      tareas = resultado.tareas;
      mostrarTareas();

    } catch (error) {
      console.log(error);
    }
  }

  function mostrarTareas() {
    limpiarTareas();
    totalPendientes();
    totalCompletadas();

    const arrayTareas = filtradas.length ? filtradas : tareas;

    if (arrayTareas.length === 0) {
      const contenedorTareas = document.querySelector('#listado-tareas');

      const textoNoTareas = document.createElement('LI');
      textoNoTareas.textContent = 'No hay tareas en este proyecto';
      textoNoTareas.classList.add('no-tareas');

      contenedorTareas.appendChild(textoNoTareas);
      return;
    }

    const estados = {
      1: 'Completa',
      0: 'Pendiente'
    }

    arrayTareas.forEach(tarea => {
      const contenedorTarea = document.createElement('LI');
      contenedorTarea.dataset.tareaId = tarea.id;
      contenedorTarea.classList.add('tarea');

      const nombreTarea = document.createElement('P');
      nombreTarea.textContent = tarea.nombre;
      nombreTarea.ondblclick = function () {
        mostrarFormulario(true, { ...tarea });
      }

      const opcionesDiv = document.createElement('DIV');
      opcionesDiv.classList.add('opciones');

      // Botones
      const btnEstadoTarea = document.createElement('BUTTON');
      btnEstadoTarea.classList.add('estado-tarea');
      btnEstadoTarea.classList.add(`${estados[tarea.estado].toLowerCase()}`);
      btnEstadoTarea.textContent = estados[tarea.estado];
      btnEstadoTarea.dataset.estadoTarea = tarea.estado;
      btnEstadoTarea.ondblclick = function () {
        cambiarEstadoTarea({...tarea});
      }

      const btnEliminarTarea = document.createElement('BUTTON');
      btnEliminarTarea.classList.add('eliminar-tarea');
      btnEliminarTarea.dataset.idTarea = tarea.id;
      btnEliminarTarea.textContent = 'Eliminar';
      btnEliminarTarea.ondblclick = function () {
        confirmarEliminarTarea({...tarea});
      }

      opcionesDiv.appendChild(btnEstadoTarea);
      opcionesDiv.appendChild(btnEliminarTarea);

      contenedorTarea.appendChild(nombreTarea);
      contenedorTarea.appendChild(opcionesDiv);

      const listadoTareas = document.querySelector('#listado-tareas');
      listadoTareas.appendChild(contenedorTarea);
    });
  }

  function totalPendientes() {
    const totalPendientes = tareas.filter(tarea => tarea.estado === '0');
    const pendienteRadio = document.querySelector('#pendientes');

    if (totalPendientes.length === 0) {
      pendienteRadio.disabled = true;
    } else {
      pendienteRadio.disabled = false;
    }
  }

  function totalCompletadas() {
    const totalCompletadas = tareas.filter(tarea => tarea.estado === '1');
    const completadasRadio = document.querySelector('#completadas');

    if (totalCompletadas.length === 0) {
      completadasRadio.disabled = true;
    } else {
      completadasRadio.disabled = false;
    }
  }

  function mostrarFormulario(editar = false, tarea = {}) {
    const modal = document.createElement('DIV');
    modal.classList.add('modal');
    modal.innerHTML = `
      <form class="formulario nueva-tarea">
        <legend>${editar ? 'Editar Tarea' : 'Añade una nueva tarea'}</legend>
        <div class="campo">
          <label>Tarea</label>
          <input
            type="text"
            name="tarea"
            id="tarea"
            placeholder="${tarea.nombre ? 'Edita la tarea' : 'Añadir tarea al proyecto actual'}"
            value="${tarea.nombre ? tarea.nombre : ''}"/>
        </div>
        
        <div class="opciones">
          <input type="submit" class="submit-nueva-tarea" value="${editar ? 'Guardar cambios' : 'Añadir Tarea'}"/>
          <button type="button" class="cerrar-modal">Cancelar</button>
        </div>
      </form>
    `;

    setTimeout(() => {
      const formulario = document.querySelector('.formulario');
      formulario.classList.add('animar');
    }, 0);

    modal.addEventListener('click', function (e) {
      e.preventDefault();

      if (e.target.classList.contains('cerrar-modal') || e.target.classList.contains('modal')) {
        const formulario = document.querySelector('.formulario');
        formulario.classList.add('cerrar');

        setTimeout(() => {
          modal.remove();
        }, 500);
      }

      if (e.target.classList.contains('submit-nueva-tarea')) {
        const nombreTarea = document.querySelector('#tarea').value.trim();

        if (nombreTarea === '') {
          // Mostrar una alerta de error
          mostrarAlerta('El nombre de la tarea es obligatorio', 'error', document.querySelector('.formulario legend'));
    
          return;
        }

        if (editar) {
          // Editar tarea
          tarea.nombre = nombreTarea;
          actualizarTarea(tarea);
        } else {
          // Agregar tarea
          agregarTarea(nombreTarea);
        }
      }
    });

    document.querySelector('.dashboard').appendChild(modal);
  }

  function mostrarAlerta(mensaje, tipo, referencia) {
    // Si hay una alerta previa, entonces no crear otra
    const alertaPrevia = document.querySelector('.alerta');

    if (alertaPrevia) {
      alertaPrevia.remove();
    }

    const alerta = document.createElement('DIV');
    alerta.textContent = mensaje;
    alerta.classList.add('alerta', tipo);

    // Insertar en el DOM
    referencia.parentElement.insertBefore(alerta, referencia.nextElementSibling);

    // Eliminar la alerta después de 3 segundos
    setTimeout(() => {
      alerta.remove();
    }, 3000);
  }

  async function agregarTarea(tarea) {
    // Construir la peticion
    const datos = new FormData();
    datos.append('nombre', tarea);
    datos.append('proyectoId', obtenerProyecto());

    try {
      const url = 'http://localhost:3000/api/tarea';
      const respuesta = await fetch(url, {
        method: 'POST',
        body: datos
      });
      
      const resultado = await respuesta.json();

      if (resultado.tipo === 'exito') {

        // Agregar el objeto de tarea al global de tareas
        const tareaObj = {
          id: String(resultado.id),
          nombre: tarea,
          estado: "0",
          proyectoId: resultado.proyectoId
        }

        tareas = [...tareas, tareaObj];
        mostrarTareas();
      }

      Swal.fire(resultado.mensaje, "", resultado.tipo === 'exito' ? "success" : 'error');

      // Cerrar Modal
      const modal = document.querySelector('.modal');
      setTimeout(() => {
        modal.remove();
      }, 0);

    } catch (error) {
      console.log(error);
    }
  }

  function cambiarEstadoTarea(tarea) {
    const nuevoEstado = (tarea.estado === '0') ? '1' : '0';
    tarea.estado = nuevoEstado;

    actualizarTarea(tarea);
  }

  function confirmarEliminarTarea(tarea) {
    Swal.fire({
      title: "¿Eliminar Tarea?",
      showCancelButton: true,
      confirmButtonText: "Si",
      cancelButtonText: "No"
    }).then((result) => {
      if (result.isConfirmed) {
        // Swal.fire("Tarea eliminada correctamente!", "", "success");
        eliminarTarea(tarea);
      }
    });
  }

  async function eliminarTarea(tarea) {
    const {id, nombre, estado, proyectoId} = tarea;

    const datos = new FormData();
    datos.append('id', id);
    datos.append('nombre', nombre);
    datos.append('estado', estado);
    datos.append('proyectoId', obtenerProyecto());

    try {
      const url = `http://localhost:3000/api/tarea/eliminar`;
      const respuesta = await fetch(url, {
        method: 'POST',
        body: datos
      });

      const resultado = await respuesta.json();

      if (resultado.tipo === 'exito') {
        tareas = tareas.filter(tareaMemoria => tareaMemoria.id !== id);
        mostrarTareas();
      }

      // mostrarAlerta(
      //   resultado.mensaje,
      //   resultado.tipo,
      //   document.querySelector('.contenedor-nueva-tarea'));

      Swal.fire("Eliminado!", resultado.mensaje, resultado.tipo === 'exito' ? "success" : 'error');      
      
    } catch (error) {
      
    }
  }

  async function actualizarTarea(tarea) {
    const {estado, id, nombre, proyectoId} = tarea;

    const datos = new FormData();
    datos.append('id', id);
    datos.append('nombre', nombre);
    datos.append('estado', estado);
    datos.append('proyectoId', obtenerProyecto());

    try {
      const url = `http://localhost:3000/api/tarea/actualizar`;
      const respuesta = await fetch(url, {
        method: 'POST',
        body: datos
      });

      const resultado = await respuesta.json();

      if (resultado.tipo === 'exito') {
        tareas = tareas.map(tareaMemoria => {
          if (tareaMemoria.id === id) {
            tareaMemoria.estado = estado;
            tareaMemoria.nombre = nombre;
          }
          return tareaMemoria;
        });

        mostrarTareas();

      }

      Swal.fire(resultado.mensaje, "", resultado.tipo === 'exito' ? "success" : 'error');

      setTimeout(() => {
        const modal = document.querySelector('.modal');
        if (modal) {
          modal.remove();
        }
      }, 0);

    } catch (error) {
      console.log(error);
    }
  }

  function obtenerProyecto() {
    const proyectoParams = new URLSearchParams(window.location.search);
    const proyecto = Object.fromEntries(proyectoParams.entries());
    
    // console.log(proyectoParams.get('id'));
    
    return proyecto.id;
  }

  function limpiarTareas() {
    const listadoTareas = document.querySelector('#listado-tareas');
    while (listadoTareas.firstChild) {
      listadoTareas.removeChild(listadoTareas.firstChild);
    }
  }

})();