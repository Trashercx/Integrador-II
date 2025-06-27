# Integrador II - Sistema de Ventas en linea y stock

## 📋 Descripción
Sistema web completo con integración de pagos mediante Stripe, desarrollado en PHP con funcionalidades de checkout y procesamiento de pagos seguros.

## 🛠️ Tecnologías Utilizadas
- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 8.x
- **Base de Datos**: MySQL
- **Pagos**: Stripe API
- **Servidor Local**: XAMPP
- **Librerías**: 
  - Stripe PHP SDK
  - SweetAlert2
  - FPDF (para reportes)

## 📦 Instalación

### Requisitos Previos
- XAMPP (PHP 8.x, MySQL, Apache)
- Composer (para dependencias PHP)
- Cuenta de Stripe (para API keys)

### Paso 1: Clonar el Repositorio
```bash
git clone https://github.com/Trashercx/Integrador-II.git
cd Integrador-II
```

### Paso 2: Configurar el Entorno
1. Coloca el proyecto en tu carpeta `htdocs` de XAMPP:
   ```
   C:\xampp\htdocs\integra\
   ```

2. Inicia Apache y MySQL desde el panel de XAMPP

### Paso 3: Instalar Dependencias
```bash
# Instalar Stripe PHP SDK y otras dependencias
composer install
```

### Paso 4: Configurar Base de Datos
1. Accede a phpMyAdmin: `http://localhost/phpmyadmin`
2. Crea una nueva base de datos (ej: `integrador_db`)
3. Importa el archivo SQL (si existe) o crea las tablas necesarias

### Paso 5: Configuración de Variables de Entorno

#### 🔧 Crear config.php
Crea un archivo `config.php` en la raíz del proyecto basándote en `config.example.php`:

```php
<?php

// Configuración de Rutas
define('BASE_URL', 'http://localhost/integra');

// API Keys de Stripe
$stripe_secret_key = 'sk_test_TU_SECRET_KEY_AQUI';
$stripe_publishable_key = 'pk_test_TU_PUBLISHABLE_KEY_AQUI';
?>
```

#### 🔑 Obtener API Keys de Stripe
1. Regístrate en [Stripe](https://stripe.com)
2. Ve a **Developers > API keys**
3. Copia tus keys de **Test mode**:
   - **Publishable key**: `pk_test_...`
   - **Secret key**: `sk_test_...`
4. Pégalas en tu `config.php` 

### Paso 6: Configurar Permisos (Opcional)
```bash
# Si estás en Linux/Mac, dar permisos de escritura para logs
chmod 755 log_stripe.txt
```

## 🚀 Uso del Sistema

### Acceso a la Aplicación
1. Abre tu navegador
2. Ve a: `http://localhost/integra`
3. Regístrate o inicia sesión
4. Navega por el sistema de checkout

### Funcionalidades Principales
- ✅ Sistema de usuarios y autenticación
- ✅ Carrito de compras
- ✅ Checkout con Stripe
- ✅ Procesamiento de pagos seguros
- ✅ Generación de reportes PDF
- ✅ Panel de administración

## 🔒 Seguridad

### Archivos Sensibles
El archivo `config.php` contiene información sensible y está excluido del repositorio por seguridad. Debes crearlo manualmente.

### Variables de Entorno Protegidas
- API Keys de Stripe
- Credenciales de base de datos
- Configuraciones del servidor

## 📁 Estructura del Proyecto
```
Integrador_II/
├── config.example.php      # Template de configuración
├── config.php             # Tu configuración (crear manualmente)
├── index.php              # Página principal
├── controller/             # Controladores PHP
│   ├── crear_sesion_stripe.php
│   └── finalizar_pago_stripe.php
├── css/                   # Estilos CSS
├── js/                    # JavaScript
├── bd/                    # Conexión a base de datos
├── vendor/                # Dependencias de Composer
└── fpdf/                  # Librería para PDFs
```

## 🐛 Solución de Problemas

### Error: "Class 'Stripe\Stripe' not found"
```bash
# Instalar dependencias de Stripe
composer require stripe/stripe-php
```

### Error: "config.php not found"
- Asegúrate de crear el archivo `config.php` basándote en `config.example.php`
- Verifica que esté en la raíz del proyecto

### Error: "Connection refused" (Base de datos)
- Verifica que MySQL esté corriendo en XAMPP
- Confirma las credenciales en `config.php`

### Problemas con Stripe
- Verifica que uses las keys correctas (test/live)
- Confirma que las keys estén bien configuradas en `config.php`

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Notas de Desarrollo

### Para Desarrolladores
- El sistema usa **keys de prueba** de Stripe por defecto
- Los logs se guardan en `log_stripe.txt`
- Para producción, cambiar a **live keys** de Stripe

### Testing
- Usa tarjetas de prueba de Stripe: `4242 4242 4242 4242`
- CVV: cualquier número de 3 dígitos
- Fecha: cualquier fecha futura

## 📄 Licencia
Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

## 👨‍💻 Autores
Curso INTEGRADOR


## 🆘 Soporte
Si tienes problemas o preguntas:
1. Revisa la sección [Solución de Problemas](#-solución-de-problemas)
2. Abre un [Issue](https://github.com/Trashercx/Integrador-II/issues)
3. Contacta al desarrollador

---
