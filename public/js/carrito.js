// Obtiene el carrito guardado en localStorage y lo convierte de JSON a objeto JavaScript.
// Si no existe nada guardado, inicializa carrito como un arreglo vacío.
const carrito = JSON.parse(localStorage.getItem('carrito')) || [];

// Función para mostrar los productos del carrito en el HTML
function mostrarCarrito() {
  // Obtiene el contenedor donde se mostrarán los productos del carrito
  const productosCarritoDiv = document.getElementById('productos-carrito');
  productosCarritoDiv.innerHTML = ''; // Limpia el contenido previo

  // Si el carrito está vacío, muestra un mensaje y termina la función
  if (carrito.length === 0) {
    productosCarritoDiv.innerHTML = '<p>No hay productos en el carrito.</p>';
    return;
  }

  // Recorre cada id de producto en el carrito
  carrito.forEach(productoId => {
    // Aquí deberías hacer una llamada a la base de datos para obtener los detalles del producto
    // Simularemos esto con un objeto de producto creado manualmente
    const producto = { 
      id: productoId, 
      nombre: 'Producto ' + productoId, // Nombre simulado concatenando el id
      precio: (productoId * 10).toFixed(2) // Precio simulado, ejemplo: id*10 con 2 decimales
    };

    // Crea un nuevo div para mostrar el producto
    const productoDiv = document.createElement('div');
    // Inserta el nombre y precio dentro del div usando template literals
    productoDiv.innerHTML = `<h3>${producto.nombre}</h3><p>Precio: $${producto.precio}</p>`;
    // Agrega el div al contenedor de productos en el carrito
    productosCarritoDiv.appendChild(productoDiv);
  });
}

// Función para simular la acción de realizar una compra
function realizarCompra() {
  alert('Compra realizada!'); // Muestra alerta de confirmación
  localStorage.removeItem('carrito'); // Limpia el carrito en localStorage
  mostrarCarrito(); // Actualiza la vista del carrito vacío
}

// Llama a la función para mostrar el carrito al cargar el script
mostrarCarrito();
