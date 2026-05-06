// Obtiene el formulario con id 'perfilForm'
const form = document.getElementById('perfilForm');
// Obtiene el div donde se mostrarán mensajes al usuario
const messageDiv = document.getElementById('message');

// Añade un evento al formulario para cuando se envíe
form.addEventListener('submit', async function(e) {
  e.preventDefault(); // Previene el envío estándar del formulario
  messageDiv.innerHTML = ''; // Limpia el contenido previo del mensaje
  messageDiv.className = ''; // Limpia las clases CSS del mensaje

  // Crea un objeto FormData con los datos del formulario
  const formData = new FormData(form);

  try {
    // Envía los datos al servidor usando fetch con método POST
    const response = await fetch('../../controllers/PerfilController.php', {
      method: 'POST',
      body: formData,
      headers: {
        'Accept': 'application/json' // Espera respuesta JSON
      }
    });

    // Si la respuesta no es OK (status fuera de 200-299), lanza error
    if (!response.ok) {
      throw new Error('Error en la respuesta del servidor');
    }

    // Convierte la respuesta JSON a objeto JavaScript
    const data = await response.json();

    // Si la respuesta indica éxito
    if (data.success) {
      messageDiv.textContent = data.message; // Muestra mensaje de éxito
      messageDiv.className = 'message success'; // Añade clase CSS de éxito

      // Redirige al usuario al home de vendedor después de 2 segundos
      setTimeout(() => {
        window.location.href = 'home_vendedor.php';
      }, 2000);
    } else {
      // Si hubo error, muestra mensaje de error recibido o uno genérico
      messageDiv.textContent = data.message || 'Error desconocido';
      messageDiv.className = 'message error'; // Añade clase CSS de error
    }
  } catch (error) {
    // En caso de error en la petición o procesamiento, muestra mensaje con el error
    messageDiv.textContent = 'Error al enviar el formulario: ' + error.message;
    messageDiv.className = 'message error'; // Añade clase CSS de error
  }
});
