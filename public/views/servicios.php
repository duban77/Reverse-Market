<?php if(session_status()===PHP_SESSION_NONE)session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
<?php include __DIR__ . '/../partials/head.php'; ?>
  <!-- Codificación de caracteres -->
  <meta charset="UTF-8">
  <!-- Título que aparece en la pestaña del navegador -->
  <title>Servicios</title>
  <!-- Enlace a la hoja de estilos principal -->
  
</head>
<body>
  <header>
    <!-- Barra de navegación principal -->
    <nav class="navbar">
      <!-- Sección izquierda de la barra con botones de login y registro -->
      <div class="nav-left">
        <a href="login.php" class="btn">Iniciar Sesión</a>
        <a href="register.php" class="btn register">Registrarse</a>
      </div>
      <!-- Sección central de la barra con menú de navegación -->
      <div class="nav-center">
        <ul class="nav-menu">
          <li><a href="index.php">Inicio</a></li>
          <li><a href="como_funciona.php">Cómo Funciona</a></li>
          <li><a href="servicios.php">Servicios</a></li>
          <li><a href="contacto.php">Contacto</a></li>
        </ul>
      </div>
    </nav>
  </header>

  <!-- Contenido principal de la página -->
  <main class="container">
    <h1>Nuestros Servicios</h1>
    <ul>
      <!-- Listado de servicios ofrecidos -->
      <li>Publicación de necesidades personalizadas.</li>
      <li>Ofertas competitivas de vendedores.</li>
      <li>Chat directo y seguro.</li>
      <li>Calificaciones y seguimiento.</li>
    </ul>
  </main>

</body>
</html>
