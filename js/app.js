// Seleccionamos todos los botones de añadir al carrito y el icono del header
const addToCart = document.querySelectorAll('[data-btn-action="add-btn-cart"]');
const cartIcon = document.querySelector('.cart-icon'); // Ahora selecciona por clase
const closeModal = document.querySelector('.jsModalClose');

// Event listeners para los botones de añadir al carrito
addToCart.forEach(btn => {
    btn.addEventListener('click', (event) => {
        const nameModal = event.target.getAttribute('data-modal');
        const modal = document.querySelector(nameModal);
        actualizarCarritoModal()
        modal.classList.add('active');
    });
});

// Event listener para el icono del carrito
cartIcon.addEventListener('click', () => {
    // Reemplaza '.modal' con el selector correcto de tu modal
    const modal = document.querySelector('.modal');
    actualizarCarritoModal()
    modal.classList.add('active');
    
});

// Cerrar el modalque mejores los iconos de mi carrito en la parte de co
closeModal.addEventListener('click', (event) => {
    event.target.parentNode.parentNode.classList.remove('active');
});

// Cerrar modal al hacer click fuera
window.onclick = (event) => {
    const modal = document.querySelector('.modal.active');
    if (event.target == modal) {
        modal.classList.remove('active');
    }
};

function agregarAlCarrito(id, nombre, precio, imagen) {
    // Obtener el carrito desde localStorage
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

    // Buscar si el producto ya existe en el carrito
    const productoExistente = carrito.find(producto => producto.id === id);

    if (productoExistente) {
        // Incrementar la cantidad si ya existe
        productoExistente.cantidad++;
    } else {
        // Agregar un nuevo producto al carrito
        carrito.push({ id, nombre, precio, imagen, cantidad: 1 });
    }

    // Guardar el carrito actualizado en localStorage
    localStorage.setItem('carrito', JSON.stringify(carrito));

    // Actualizar el modal del carrito
    actualizarCarritoModal();

    
}

// Función para actualizar el modal del carrito



function actualizarCarritoModal() {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    const modalLista = document.querySelector('.modal__list');
    modalLista.innerHTML = '';

    carrito.forEach(producto => {
        modalLista.innerHTML += `
            <div class="modal__item">
                <div class="modal__thumb">
                    <img src="${producto.imagen}" alt="${producto.nombre}">
                </div>
                <div class="modal__text-product">
                    <p style= margin-bottom:10px >${producto.nombre}</p>
                    <p><strong>S/. ${producto.precio}</strong></p>

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
// en la linea 79 va esto  <p><strong>S/. ${producto.precio}</strong></p>   es para el precio
    calcularTotales();
}



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




// Función para actualizar el contenido del modal del carrito
function actualizarModalCarrito() {
    const modalList = document.querySelector('.modal__list');
    const totalPriceElem = document.querySelector('.modal__total-cart');

    // Limpiar el contenido actual
    modalList.innerHTML = '';

    let total = 0;

    carrito.forEach(producto => {
        total += producto.precio * producto.cantidad;

        // Crear los elementos del producto
        const itemHTML = `
            <div class="modal__item">
                <div class="modal__thumb">
                    <img src="${producto.imagen}" alt="${producto.nombre}">
                </div>
                <div class="modal__text-product">
                    <p>${producto.nombre}</p>
                    <p><strong>S/. ${producto.precio} x ${producto.cantidad}</strong></p>
                    <button class="btn-eliminar" onclick="eliminarDelCarrito('${producto.id}')">Eliminar</button>
                </div>
            </div>
        `;
        modalList.innerHTML += itemHTML;
    });

    // Actualizar el precio total
    totalPriceElem.textContent = `Total: S/. ${total.toFixed(2)}`;
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

    // Calcular el subtotal
    carrito.forEach(producto => {
        subtotal += parseFloat(producto.precio) * producto.cantidad;
    });

    // Actualizar valores en el modal
    // document.getElementById('subtotal').innerText = `S/. ${subtotal.toFixed(2)}`;
    // document.getElementById('descuento').innerText = `S/. 0.00`; // Sin descuento por ahora
    document.getElementById('total').innerText = `S/. ${subtotal.toFixed(2)}`;
}
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
document.addEventListener('DOMContentLoaded', () => {
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
                            container: 'z-top-alert'  // 👈 Añadimos esto aquí también
                        }
                    });
                }
            });
        });
    }
});
