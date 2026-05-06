<?php if(session_status()===PHP_SESSION_NONE)session_start(); ?>
<!DOCTYPE html>
<!-- Define el documento como HTML5 -->
<html lang="es">
<!-- Idioma español -->
<head>
<?php include __DIR__ . '/../partials/head.php'; ?>
  <meta charset="UTF-8">
  <!-- Meta etiqueta para asegurar que el diseño responda a diferentes dispositivos -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Título de la página -->
  <title>Cómo Funciona</title>
  <!-- Enlace a la hoja de estilos externa -->
  
</head>
<body>
  <!-- Encabezado del sitio -->
  <header>
    <!-- Barra de navegación -->
    <nav class="navbar">
      <!-- Sección izquierda con botones de inicio de sesión y registro -->
      <div class="nav-left">
        <a href="login.php" class="btn">Iniciar Sesión</a>
        <a href="register.php" class="btn">Registrarse</a>
      </div>
      <!-- Sección central con menú de navegación -->
      <div class="nav-center">
        <ul class="nav-menu">
          <li><a href="../../public/index.html">Inicio</a></li>
          <li><a href="como_funciona.php">Cómo Funciona</a></li>
          <li><a href="servicios.php">Servicios</a></li>
          <li><a href="contacto.php">Contacto</a></li>
        </ul>
      </div>
    </nav>
  </header>

  <!-- Contenedor principal del contenido -->
  <div class="container">
    <!-- Título principal -->
    <h1>¿Cómo Funciona?</h1>
    <!-- Párrafo descriptivo -->
    <p>Publica tu necesidad y recibe ofertas personalizadas de proveedores interesados.</p>
    <!-- Lista ordenada con los pasos para usar la plataforma -->
    <ol>
      <li>Regístrate en la plataforma como comprador o vendedor.</li>
      <li>Si eres comprador, publica una necesidad.</li>
      <li>Los vendedores interesados envían sus propuestas.</li>
      <li>Compara ofertas y elige la que más se ajuste a ti.</li>
    </ol>
  </div>

  
</body>
</html>
