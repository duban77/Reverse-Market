// Función para mostrar u ocultar la contraseña
function togglePassword() {
  // Obtiene el campo de contraseña por su id "password"
  const passwordField = document.getElementById("password");
  // Cambia el tipo de input entre "password" y "text" para mostrar u ocultar la contraseña
  const type = passwordField.type === "password" ? "text" : "password";
  // Asigna el nuevo tipo al campo de contraseña
  passwordField.type = type;
}

// Función para validar que las contraseñas coincidan
function validarFormulario() {
  // Obtiene el valor del campo de contraseña
  const password = document.getElementById("password").value;
  // Obtiene el valor del campo de confirmación de contraseña
  const confirmPassword = document.getElementById("confirmPassword").value;

  // Verifica si las contraseñas no son iguales
  if (password !== confirmPassword) {
    // Muestra alerta de error si no coinciden
    alert("Las contraseñas no coinciden.");
    // Devuelve falso para evitar el envío del formulario
    return false;
  }

  // Si las contraseñas coinciden, devuelve verdadero para permitir envío
  return true;
}
