//app.js - Modificado con integración de promociones
// Seleccionamos todos los botones de añadir al carrito y el icono del header
const addToCart = document.querySelectorAll('[data-btn-action="add-btn-cart"]');
const cartIcon = document.querySelector('.cart-icon'); // Ahora selecciona por clase
const closeModal = document.querySelector('.jsModalClose');

// Variables para promociones
let promocionManager = null;
let recomendacionManager = null;

// Inicialización cuando se carga el DOM
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar managers si existen las clases
    if (typeof PromocionManager !== 'undefined') {
        promocionManager = new PromocionManager();
    }
    if (typeof RecomendacionManager !== 'undefined') {
        recomendacionManager = new RecomendacionManager();
    }
    
    // Inicializar funcionalidades existentes
    inicializarEventosCarrito();
    inicializarVaciarCarrito();
});

// Función para inicializar eventos del carrito
function inicializarEventosCarrito() {
    // Event listeners para los botones de añadir al carrito
    addToCart.forEach(btn => {
        btn.addEventListener('click', (event) => {
            const nameModal = event.target.getAttribute('data-modal');
            const modal = document.querySelector(nameModal);
            actualizarCarritoModal();
            modal.classList.add('active');
        });
    });

    // Event listener para el icono del carrito
    if (cartIcon) {
        cartIcon.addEventListener('click', () => {
            const modal = document.querySelector('.modal');
            actualizarCarritoModal();
            modal.classList.add('active');
        });
    }

    // Cerrar el modal
    if (closeModal) {
        closeModal.addEventListener('click', (event) => {
            event.target.parentNode.parentNode.classList.remove('active');
        });
    }

    // Cerrar modal al hacer click fuera
    window.onclick = (event) => {
        const modal = document.querySelector('.modal.active');
        if (event.target == modal) {
            modal.classList.remove('active');
        }
    };
}

// Función modificada para agregar al carrito con promociones
function agregarAlCarrito(id, nombre, precio, imagen, promocion = null) {
    // Obtener el carrito desde localStorage
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    
    // Calcular precio con promoción si existe
    let precioFinal = precio;
    let descuentoAplicado = null;
    
    if (promocion) {
        const resultado = aplicarPromocionAProducto(precio, promocion);
        precioFinal = resultado.precioFinal;
        descuentoAplicado = resultado.descuento;
    }

    // Buscar si el producto ya existe en el carrito
    const productoExistente = carrito.find(producto => producto.id === id);

    if (productoExistente) {
        // Incrementar la cantidad si ya existe
        productoExistente.cantidad++;
    } else {
        // Agregar un nuevo producto al carrito
        const nuevoProducto = {
            id,
            nombre,
            precio: precioFinal,
            precioOriginal: precio,
            imagen,
            cantidad: 1,
            promocion: promocion,
            descuentoAplicado: descuentoAplicado
        };
        carrito.push(nuevoProducto);
    }

    // Guardar el carrito actualizado en localStorage
    localStorage.setItem('carrito', JSON.stringify(carrito));

    // Mostrar notificación si hay promoción aplicada
    if (descuentoAplicado) {
        mostrarNotificacionDescuento(descuentoAplicado);
    }

    // Actualizar el modal del carrito
    actualizarCarritoModal();
}

// Función mejorada para agregar al carrito con promociones desde recomendaciones
function agregarAlCarritoConPromocion(id, nombre, precio, imagen, promocion = null) {
    return agregarAlCarrito(id, nombre, precio, imagen, promocion);
}

// Función para aplicar promoción a un producto
function aplicarPromocionAProducto(precio, promocion) {
    let precioFinal = precio;
    let descuento = 0;
    
    if (promocion && promocion.activa) {
        if (promocion.tipo === 'porcentaje') {
            descuento = (precio * promocion.valor) / 100;
        } else if (promocion.tipo === 'fijo') {
            descuento = promocion.valor;
        }
        
        precioFinal = Math.max(0, precio - descuento);
    }
    
    return {
        precioFinal: precioFinal,
        descuento: descuento,
        porcentajeDescuento: promocion ? promocion.valor : 0
    };
}

// Función para mostrar notificación de descuento
function mostrarNotificacionDescuento(descuento) {
    // Crear elemento de notificación
    const notificacion = document.createElement('div');
    notificacion.className = 'notificacion-descuento';
    notificacion.innerHTML = `
        <div class="notificacion-contenido">
            <i class="fas fa-check-circle"></i>
            <span>¡Descuento aplicado! Ahorras S/. ${descuento.toFixed(2)}</span>
        </div>
    `;
    
    // Agregar estilos inline para la notificación
    notificacion.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #4CAF50, #45a049);
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10000;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    `;
    
    document.body.appendChild(notificacion);
    
    // Animar entrada
    setTimeout(() => {
        notificacion.style.opacity = '1';
        notificacion.style.transform = 'translateX(0)';
    }, 100);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notificacion.style.opacity = '0';
        notificacion.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notificacion.parentNode) {
                document.body.removeChild(notificacion);
            }
        }, 300);
    }, 3000);
}

// Función mejorada para actualizar el modal del carrito
function actualizarCarritoModal() {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    const modalLista = document.querySelector('.modal__list');
    
    if (!modalLista) return;
    
    modalLista.innerHTML = '';

    carrito.forEach(producto => {
        // Determinar si mostrar precio original tachado
        const precioHTML = producto.descuentoAplicado ? 
            `<p class="precio-original">S/. ${producto.precioOriginal}</p>
             <p><strong>S/. ${producto.precio}</strong> 
             <span class="badge-descuento">-${producto.promocion.valor}%</span></p>` :
            `<p><strong>S/. ${producto.precio}</strong></p>`;
            
        modalLista.innerHTML += `
            <div class="modal__item">
                <div class="modal__thumb">
                    <img src="${producto.imagen}" alt="${producto.nombre}">
                </div>
                <div class="modal__text-product">
                    <p style="margin-bottom:10px">${producto.nombre}</p>
                    ${precioHTML}
                    <div class="quantity-control">
                        <button class="btn-count" onclick="modificarCantidad('${producto.id}', -1)">-</button>
                        <input class="card-count" type="number" value="${producto.cantidad}" min="1" readonly>
                        <button class="btn-count" onclick="modificarCantidad('${producto.id}', 1)">+</button>
                        <button class="btn-eliminar" onclick="eliminarDelCarrito('${producto.id}')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    calcularTotales();
    
    // Actualizar badge del carrito con cantidad de productos
    actualizarBadgeCarrito();
}

// Función para actualizar precio con descuento (con animación)
function actualizarPrecioConDescuento(elementoPrecio, precioOriginal, descuento) {
    if (!elementoPrecio) return;
    
    const precioFinal = precioOriginal - descuento;
    
    // Agregar clase de animación
    elementoPrecio.classList.add('precio-actualizando');
    
    setTimeout(() => {
        elementoPrecio.innerHTML = `
            <span class="precio-original">S/. ${precioOriginal.toFixed(2)}</span>
            <span class="precio-descuento">S/. ${precioFinal.toFixed(2)}</span>
        `;
        elementoPrecio.classList.remove('precio-actualizando');
        elementoPrecio.classList.add('precio-actualizado');
    }, 200);
}

// Función para actualizar badge del carrito
function actualizarBadgeCarrito() {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    const cantidadTotal = carrito.reduce((total, producto) => total + producto.cantidad, 0);
    
    // Buscar o crear badge del carrito
    let badge = document.querySelector('.cart-badge');
    if (!badge && cantidadTotal > 0) {
        badge = document.createElement('span');
        badge.className = 'cart-badge';
        cartIcon.appendChild(badge);
    }
    
    if (badge) {
        if (cantidadTotal > 0) {
            badge.textContent = cantidadTotal;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }
}

// Función para validar código promocional en tiempo real
function validarCodigoEnTiempoReal(codigo, callback) {
    // Simular validación AJAX
    setTimeout(() => {
        const promocionValida = promocionManager && promocionManager.validarCodigoPromocional(codigo);
        callback(promocionValida);
    }, 500);
}

// Funciones existentes mantenidas
function modificarCantidad(id, cambio) {
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

    carrito = carrito.map(producto => {
        if (producto.id === id) {
            producto.cantidad = Math.max(1, producto.cantidad + cambio);
        }
        return producto;
    });

    localStorage.setItem('carrito', JSON.stringify(carrito));
    actualizarCarritoModal();
}

function eliminarDelCarrito(id) {
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

    // Filtrar los productos eliminando el seleccionado
    carrito = carrito.filter(producto => producto.id !== id);

    // Guardar el carrito actualizado en localStorage
    localStorage.setItem('carrito', JSON.stringify(carrito));

    // Actualizar el modal del carrito
    actualizarCarritoModal();
}

function calcularTotales() {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    let subtotal = 0;
    let totalDescuentos = 0;

    // Calcular el subtotal y descuentos
    carrito.forEach(producto => {
        const subtotalProducto = parseFloat(producto.precio) * producto.cantidad;
        subtotal += subtotalProducto;
        
        if (producto.descuentoAplicado) {
            totalDescuentos += producto.descuentoAplicado * producto.cantidad;
        }
    });

    // Actualizar valores en el modal
    document.getElementById('total').innerText = `S/. ${subtotal.toFixed(2)}`;
    
    // Mostrar descuentos si existen
    const descuentoElement = document.getElementById('descuento');
    if (descuentoElement && totalDescuentos > 0) {
        descuentoElement.innerText = `S/. ${totalDescuentos.toFixed(2)}`;
        descuentoElement.parentElement.style.display = 'block';
    }
}

// Funciones existentes mantenidas sin cambios
function enviarPedidoWhatsApp() {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    const numeroWhatsApp = '+51937050119';
    
    if (carrito.length === 0) {
        alert('El carrito está vacío');
        return;
    }

    let mensaje = '🛒 *NUEVO PEDIDO*\n\n';
    
    carrito.forEach(producto => {
        mensaje += `*${producto.nombre}*\n`;
        mensaje += `📦 Cantidad: ${producto.cantidad}\n`;
        mensaje += `💵 Precio unitario: S/. ${producto.precio}\n`;
        
        if (producto.descuentoAplicado) {
            mensaje += `🎉 Descuento aplicado: S/. ${producto.descuentoAplicado.toFixed(2)}\n`;
        }
        
        mensaje += `📝 Subtotal: S/. ${(producto.precio * producto.cantidad).toFixed(2)}\n`;
        mensaje += `-------------------------\n`;
    });

    const total = carrito.reduce((sum, producto) => sum + (producto.precio * producto.cantidad), 0);
    mensaje += `\n*RESUMEN DEL PEDIDO*\n`;
    mensaje += `💰 *Total a pagar: S/. ${total.toFixed(2)}*\n\n`;
    mensaje += `¡Gracias por tu pedido! 😊`;

    const mensajeCodificado = encodeURIComponent(mensaje);
    const urlWhatsApp = `https://api.whatsapp.com/send?phone=${numeroWhatsApp}&text=${mensajeCodificado}`;
    window.open(urlWhatsApp, '_blank');
}

function procederAlCheckout() {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    
    if (carrito.length === 0) {
        alert('El carrito está vacío');
        return;
    }
    document.cookie = "carrito=" + encodeURIComponent(JSON.stringify(carrito)) + "; path=/";
    // Redirigir a la página de checkout
    window.location.href = '/view/checkout.php';
}

// Función para inicializar vaciar carrito
function inicializarVaciarCarrito() {
    const vaciarBtn = document.getElementById('vaciarCarritoBtn');
    if (vaciarBtn) {
        vaciarBtn.addEventListener('click', () => {
            Swal.fire({
                title: '¿Vaciar el carrito?',
                text: 'Se eliminarán todos los productos del carrito.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, vaciar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    container: 'z-top-alert'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    localStorage.removeItem('carrito');
                    document.cookie = "carrito=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                    actualizarCarritoModal();
                    Swal.fire({
                        icon: 'success',
                        title: 'Listo',
                        text: 'El carrito fue vaciado',
                        timer: 1500,
                        showConfirmButton: false,
                        customClass: {
                            container: 'z-top-alert'
                        }
                    });
                }
            });
        });
    }
}