// js/promociones.js
// Sistema de Promociones Interactivo para Zeus Importaciones
// Compatible con app.js existente

class PromocionManager {
    constructor() {
        this.codigosPromocionales = new Map();
        this.promocionesActivas = new Map();
        this.countdownTimers = new Map();
        this.init();
    }
    
    init() {
        this.cargarPromocionesActivas();
        this.inicializarEventListeners();
        this.inicializarCountdowns();
        this.aplicarEfectosVisuales();
        console.log('PromocionManager inicializado correctamente');
    }
    
    cargarPromocionesActivas() {
        // Cargar promociones desde el DOM
        const elementosPromocion = document.querySelectorAll('[data-promocion]');
        elementosPromocion.forEach(elemento => {
            const promocionData = JSON.parse(elemento.getAttribute('data-promocion'));
            this.promocionesActivas.set(promocionData.id, promocionData);
        });
    }
    
    inicializarEventListeners() {
        // Botón para abrir modal de código promocional
        const btnCodigoPromo = document.getElementById('btnCodigoPromocional');
        if (btnCodigoPromo) {
            btnCodigoPromo.addEventListener('click', () => this.abrirModalCodigoPromocional());
        }
        
        // Input de código promocional con validación en tiempo real
        const inputCodigo = document.getElementById('inputCodigoPromocional');
        if (inputCodigo) {
            let timeout;
            inputCodigo.addEventListener('input', (e) => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.validarCodigoEnTiempoReal(e.target.value);
                }, 500);
            });
        }
        
        // Botón aplicar código
        const btnAplicarCodigo = document.getElementById('btnAplicarCodigo');
        if (btnAplicarCodigo) {
            btnAplicarCodigo.addEventListener('click', () => this.aplicarCodigoPromocional());
        }
        
        // Cerrar modal de código promocional
        const cerrarModalCodigo = document.getElementById('cerrarModalCodigo');
        if (cerrarModalCodigo) {
            cerrarModalCodigo.addEventListener('click', () => this.cerrarModalCodigoPromocional());
        }
    }
    
    inicializarCountdowns() {
        const elementosCountdown = document.querySelectorAll('[data-countdown]');
        elementosCountdown.forEach(elemento => {
            const fechaFin = new Date(elemento.getAttribute('data-countdown'));
            this.mostrarCountdownPromocion(elemento, fechaFin);
        });
    }
    
    aplicarEfectosVisuales() {
        // Animaciones para badges promocionales
        const badges = document.querySelectorAll('.badge-promocion');
        badges.forEach((badge, index) => {
            setTimeout(() => {
                badge.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    badge.style.transform = 'scale(1)';
                }, 200);
            }, index * 100);
        });
        
        // Efecto pulsante para ofertas flash
        const ofertasFlash = document.querySelectorAll('.oferta-flash');
        ofertasFlash.forEach(oferta => {
            setInterval(() => {
                oferta.classList.add('pulse-animation');
                setTimeout(() => {
                    oferta.classList.remove('pulse-animation');
                }, 1000);
            }, 3000);
        });
    }
    
    // Modificar función existente de agregar al carrito para manejar promociones
    aplicarDescuentoCarrito(productos) {
        let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
        let descuentoTotal = 0;
        let ahorroTotal = 0;
        
        carrito = carrito.map(producto => {
            const promocion = this.promocionesActivas.get(producto.id.toString());
            if (promocion && this.validarPromocionActiva(promocion)) {
                const precioOriginal = parseFloat(producto.precio);
                const descuento = promocion.tipo === 'porcentaje' ? 
                    precioOriginal * (promocion.valor / 100) : 
                    promocion.valor;
                
                producto.precioOriginal = precioOriginal;
                producto.precio = (precioOriginal - descuento).toFixed(2);
                producto.descuento = descuento.toFixed(2);
                producto.promocion = promocion;
                
                ahorroTotal += descuento * producto.cantidad;
            }
            return producto;
        });
        
        // Aplicar códigos promocionales
        const codigoAplicado = localStorage.getItem('codigoPromocionalAplicado');
        if (codigoAplicado && this.codigosPromocionales.has(codigoAplicado)) {
            const codigoPromo = this.codigosPromocionales.get(codigoAplicado);
            const subtotal = carrito.reduce((sum, p) => sum + (parseFloat(p.precio) * p.cantidad), 0);
            
            if (codigoPromo.tipo === 'porcentaje') {
                descuentoTotal = subtotal * (codigoPromo.valor / 100);
            } else {
                descuentoTotal = codigoPromo.valor;
            }
            
            ahorroTotal += descuentoTotal;
        }
        
        localStorage.setItem('carrito', JSON.stringify(carrito));
        localStorage.setItem('descuentoTotal', descuentoTotal.toString());
        localStorage.setItem('ahorroTotal', ahorroTotal.toString());
        
        this.mostrarNotificacionDescuento(ahorroTotal);
        return { carrito, descuentoTotal, ahorroTotal };
    }
    
    validarCodigoPromocional(codigo) {
        return new Promise((resolve) => {
            // Simular validación AJAX
            setTimeout(() => {
                const codigosValidos = {
                    'ZEUS20': { tipo: 'porcentaje', valor: 20, descripcion: '20% de descuento' },
                    'ENVIOGRATIS': { tipo: 'fijo', valor: 15, descripcion: 'Envío gratis' },
                    'PRIMERACOMPRA': { tipo: 'porcentaje', valor: 15, descripcion: '15% primera compra' },
                    'FLASH50': { tipo: 'fijo', valor: 50, descripcion: 'S/. 50 de descuento' }
                };
                
                if (codigosValidos[codigo.toUpperCase()]) {
                    this.codigosPromocionales.set(codigo.toUpperCase(), codigosValidos[codigo.toUpperCase()]);
                    resolve({ valido: true, codigo: codigosValidos[codigo.toUpperCase()] });
                } else {
                    resolve({ valido: false, mensaje: 'Código promocional no válido' });
                }
            }, 800);
        });
    }
    
    async validarCodigoEnTiempoReal(codigo) {
        const inputCodigo = document.getElementById('inputCodigoPromocional');
        const feedbackElement = document.getElementById('feedbackCodigo');
        
        if (!codigo || codigo.length < 3) {
            this.mostrarFeedbackCodigo('', 'neutral');
            return;
        }
        
        // Mostrar loading
        this.mostrarFeedbackCodigo('Validando...', 'loading');
        
        try {
            const resultado = await this.validarCodigoPromocional(codigo);
            
            if (resultado.valido) {
                this.mostrarFeedbackCodigo(`✓ ${resultado.codigo.descripcion}`, 'success');
                inputCodigo.classList.add('codigo-valido');
                inputCodigo.classList.remove('codigo-invalido');
            } else {
                this.mostrarFeedbackCodigo(`✗ ${resultado.mensaje}`, 'error');
                inputCodigo.classList.add('codigo-invalido');
                inputCodigo.classList.remove('codigo-valido');
            }
        } catch (error) {
            this.mostrarFeedbackCodigo('Error al validar código', 'error');
        }
    }
    
    mostrarFeedbackCodigo(mensaje, tipo) {
        const feedbackElement = document.getElementById('feedbackCodigo');
        if (!feedbackElement) return;
        
        feedbackElement.textContent = mensaje;
        feedbackElement.className = `feedback-codigo ${tipo}`;
        
        if (tipo === 'loading') {
            feedbackElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validando...';
        }
    }
    
    async aplicarCodigoPromocional() {
        const inputCodigo = document.getElementById('inputCodigoPromocional');
        const codigo = inputCodigo.value.trim().toUpperCase();
        
        if (!codigo) {
            this.mostrarFeedbackCodigo('Ingrese un código válido', 'error');
            return;
        }
        
        try {
            const resultado = await this.validarCodigoPromocional(codigo);
            
            if (resultado.valido) {
                localStorage.setItem('codigoPromocionalAplicado', codigo);
                this.aplicarDescuentoCarrito();
                this.cerrarModalCodigoPromocional();
                this.mostrarNotificacionCodigoAplicado(resultado.codigo);
                
                // Actualizar modal del carrito
                if (typeof actualizarCarritoModal === 'function') {
                    actualizarCarritoModal();
                }
            } else {
                this.mostrarFeedbackCodigo(resultado.mensaje, 'error');
            }
        } catch (error) {
            this.mostrarFeedbackCodigo('Error al aplicar código', 'error');
        }
    }
    
    mostrarNotificacionCodigoAplicado(codigo) {
        const notificacion = document.createElement('div');
        notificacion.className = 'notificacion-promocion success';
        notificacion.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <span>¡Código aplicado! ${codigo.descripcion}</span>
        `;
        
        document.body.appendChild(notificacion);
        
        setTimeout(() => {
            notificacion.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notificacion.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notificacion);
            }, 300);
        }, 3000);
    }
    
    mostrarNotificacionDescuento(ahorro) {
        if (ahorro <= 0) return;
        
        const notificacion = document.createElement('div');
        notificacion.className = 'notificacion-promocion discount';
        notificacion.innerHTML = `
            <i class="fas fa-tag"></i>
            <span>¡Ahorraste S/. ${ahorro.toFixed(2)}!</span>
        `;
        
        document.body.appendChild(notificacion);
        
        setTimeout(() => {
            notificacion.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notificacion.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notificacion);
            }, 300);
        }, 4000);
    }
    
    actualizarPreciosPromocionales() {
        const productosConPromocion = document.querySelectorAll('[data-promocion]');
        
        productosConPromocion.forEach(elemento => {
            const promocionData = JSON.parse(elemento.getAttribute('data-promocion'));
            const precioElement = elemento.querySelector('.precio-promocional');
            const precioOriginalElement = elemento.querySelector('.precio-original');
            
            if (this.validarPromocionActiva(promocionData)) {
                const precioOriginal = parseFloat(promocionData.precio_original);
                const descuento = promocionData.tipo === 'porcentaje' ? 
                    precioOriginal * (promocionData.valor / 100) : 
                    promocionData.valor;
                const precioFinal = precioOriginal - descuento;
                
                this.animarCambioPrecio(precioElement, `S/. ${precioFinal.toFixed(2)}`);
                
                if (precioOriginalElement) {
                    precioOriginalElement.textContent = `S/. ${precioOriginal.toFixed(2)}`;
                }
            }
        });
    }
    
    animarCambioPrecio(elemento, nuevoPrecio) {
        if (!elemento) return;
        
        elemento.style.transform = 'scale(1.1)';
        elemento.style.color = '#e74c3c';
        
        setTimeout(() => {
            elemento.textContent = nuevoPrecio;
            elemento.style.transform = 'scale(1)';
            elemento.style.color = '';
        }, 200);
    }
    
    mostrarCountdownPromocion(elemento, fechaFin) {
        const actualizarCountdown = () => {
            const ahora = new Date().getTime();
            const tiempoRestante = fechaFin.getTime() - ahora;
            
            if (tiempoRestante > 0) {
                const dias = Math.floor(tiempoRestante / (1000 * 60 * 60 * 24));
                const horas = Math.floor((tiempoRestante % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutos = Math.floor((tiempoRestante % (1000 * 60 * 60)) / (1000 * 60));
                const segundos = Math.floor((tiempoRestante % (1000 * 60)) / 1000);
                
                elemento.innerHTML = `
                    <div class="countdown-container">
                        <div class="countdown-item">
                            <span class="countdown-number">${dias.toString().padStart(2, '0')}</span>
                            <span class="countdown-label">días</span>
                        </div>
                        <div class="countdown-separator">:</div>
                        <div class="countdown-item">
                            <span class="countdown-number">${horas.toString().padStart(2, '0')}</span>
                            <span class="countdown-label">hrs</span>
                        </div>
                        <div class="countdown-separator">:</div>
                        <div class="countdown-item">
                            <span class="countdown-number">${minutos.toString().padStart(2, '0')}</span>
                            <span class="countdown-label">min</span>
                        </div>
                        <div class="countdown-separator">:</div>
                        <div class="countdown-item">
                            <span class="countdown-number">${segundos.toString().padStart(2, '0')}</span>
                            <span class="countdown-label">seg</span>
                        </div>
                    </div>
                `;
            } else {
                elemento.innerHTML = '<span class="promocion-expirada">¡Promoción expirada!</span>';
                clearInterval(timer);
            }
        };
        
        const timer = setInterval(actualizarCountdown, 1000);
        this.countdownTimers.set(elemento, timer);
        actualizarCountdown();
    }
    
    validarPromocionActiva(promocion) {
        const ahora = new Date();
        const fechaInicio = new Date(promocion.fecha_inicio);
        const fechaFin = new Date(promocion.fecha_fin);
        
        return ahora >= fechaInicio && ahora <= fechaFin;
    }
    
    abrirModalCodigoPromocional() {
        const modal = document.getElementById('modalCodigoPromocional');
        if (modal) {
            modal.classList.add('active');
            document.getElementById('inputCodigoPromocional').focus();
        }
    }
    
    cerrarModalCodigoPromocional() {
        const modal = document.getElementById('modalCodigoPromocional');
        if (modal) {
            modal.classList.remove('active');
            document.getElementById('inputCodigoPromocional').value = '';
            this.mostrarFeedbackCodigo('', 'neutral');
        }
    }
    
    // Método para limpiar timers al destruir la instancia
    destruir() {
        this.countdownTimers.forEach(timer => clearInterval(timer));
        this.countdownTimers.clear();
    }
}

// Función global para mantener compatibilidad con app.js existente
function agregarAlCarritoConPromocion(id, nombre, precio, imagen, promocion = null) {
    // Usar la función existente como base
    if (typeof agregarAlCarrito === 'function') {
        agregarAlCarrito(id, nombre, precio, imagen);
        
        // Aplicar promociones si existen
        if (window.promocionManager) {
            window.promocionManager.aplicarDescuentoCarrito();
        }
    }
}

// Función para actualizar precios con descuento y animaciones
function actualizarPrecioConDescuento(elementoPrecio, precioOriginal, descuento) {
    if (!elementoPrecio) return;
    
    const precioFinal = precioOriginal - descuento;
    
    // Animación de cambio de precio
    elementoPrecio.style.transition = 'all 0.3s ease';
    elementoPrecio.style.transform = 'scale(1.1)';
    elementoPrecio.style.color = '#e74c3c';
    
    setTimeout(() => {
        elementoPrecio.textContent = `S/. ${precioFinal.toFixed(2)}`;
        elementoPrecio.style.transform = 'scale(1)';
        elementoPrecio.style.color = '';
    }, 150);
}

// Extender la función existente de calcular totales para incluir promociones
const calcularTotalesOriginal = window.calcularTotales;
function calcularTotales() {
    if (calcularTotalesOriginal) {
        calcularTotalesOriginal();
    }
    
    // Agregar cálculos de promociones
    const descuentoTotal = parseFloat(localStorage.getItem('descuentoTotal')) || 0;
    const ahorroTotal = parseFloat(localStorage.getItem('ahorroTotal')) || 0;
    
    const elementoDescuento = document.getElementById('descuento');
    const elementoAhorro = document.getElementById('ahorro');
    
    if (elementoDescuento && descuentoTotal > 0) {
        elementoDescuento.textContent = `S/. ${descuentoTotal.toFixed(2)}`;
        elementoDescuento.parentElement.style.display = 'block';
    }
    
    if (elementoAhorro && ahorroTotal > 0) {
        elementoAhorro.textContent = `S/. ${ahorroTotal.toFixed(2)}`;
        elementoAhorro.parentElement.style.display = 'block';
    }
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.promocionManager = new PromocionManager();
    
    // Reemplazar la función calcularTotales si existe
    if (typeof window.calcularTotales === 'function') {
        window.calcularTotales = calcularTotales;
    }
});

// Limpiar recursos al cerrar la página
window.addEventListener('beforeunload', () => {
    if (window.promocionManager) {
        window.promocionManager.destruir();
    }
});