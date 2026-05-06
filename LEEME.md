# Reverse Market — Guía de Instalación XAMPP

## Pasos

### 1. Copiar el proyecto
Copia la carpeta **`reverse_market`** a:
```
C:\xampp\htdocs\reverse_market
```

### 2. Importar la base de datos
1. Abre `http://localhost/phpmyadmin`
2. Crea una BD llamada `reverse_market`
3. Importa el archivo `reverse_market.sql` que está en la raíz del proyecto

### 3. Verificar configuración
Edita `config/db.php` y confirma:
```php
$host = 'localhost';
$db   = 'reverse_market';
$user = 'root';
$pass = '';  // normalmente vacío en XAMPP local
```

### 4. Abrir en el navegador
```
http://localhost/reverse_market/public/index.php
```

### Credenciales admin
| Correo | Contraseña |
|--------|-----------|
| admin@reversemarket.com | admin1234 |

## Estructura
```
reverse_market/
├── config/          ← Configuración BD
├── models/          ← Modelos de datos
├── lib/PHPMailer/   ← Librería email
├── reverse_market.sql ← Base de datos
└── public/
    ├── index.php    ← PUNTO DE ENTRADA ← Abrir esto
    ├── css/         ← Estilos
    ├── js/          ← Scripts
    ├── uploads/     ← Imágenes de productos
    ├── views/       ← Todas las páginas
    ├── controllers/ ← Lógica PHP
    └── partials/    ← Componentes reutilizables
```
