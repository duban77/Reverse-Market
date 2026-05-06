// Selecciona el formulario con id 'formAgregarProducto' y agrega un listener para el evento 'submit'
document.getElementById('formAgregarProducto').addEventListener('submit', async function (e) {
    e.preventDefault(); // Evita el envío normal del formulario para manejarlo con JavaScript

    const form = this; // Referencia al formulario actual
    const formData = new FormData(form); // Crea un objeto FormData con los datos del formulario

    // Validación básica manual (el HTML ya valida, pero esto es una protección extra)
    const nombre = formData.get('nombre')?.trim(); // Obtiene y limpia el valor del campo 'nombre'
    const descripcion = formData.get('descripcion')?.trim(); // Obtiene y limpia el valor del campo 'descripcion'
    const precio = parseFloat(formData.get('precio')); // Convierte el valor del campo 'precio' a número decimal
    const categoria = formData.get('categoria')?.trim(); // Obtiene y limpia el valor del campo 'categoria'

    // Si alguno de los campos está vacío o precio no es un número válido
    if (!nombre || !descripcion || isNaN(precio) || !categoria) {
        alert('Por favor completa todos los campos correctamente.'); // Muestra alerta al usuario
        return; // Sale de la función sin enviar datos
    }

    try {
        // Realiza la petición POST al controlador PHP para agregar el producto
        const response = await fetch('../../controllers/ProductoController.php', {
            method: 'POST', // Método HTTP
            body: formData, // Envío del formulario como cuerpo de la petición
        });

        // Si la respuesta del servidor no es OK (código distinto de 2xx)
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor'); // Lanza error para el catch
        }

        const data = await response.json(); // Convierte la respuesta en formato JSON

        // Si el servidor indica éxito en la operación
        if (data.success) {
            alert('✅ Producto agregado exitosamente'); // Muestra alerta de éxito
            form.reset(); // Limpia todos los campos del formulario
            window.location.href = '../users/home_vendedor.php'; // Redirige al vendedor a su página principal
        } else {
            alert('❌ Error al agregar el producto: ' + data.message); // Muestra alerta con el mensaje de error recibido
        }
    } catch (error) {
        console.error('Fetch error:', error); // Imprime el error en consola para depuración
        alert('⚠️ Hubo un problema al agregar el producto. Intenta nuevamente.'); // Alerta al usuario de un problema general
    }
});
