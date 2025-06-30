// js/recomendaciones.js
// Sistema de Recomendaciones Interactivo para Zeus Importaciones
// Compatible con app.js existente

class RecomendacionManager {
    constructor() {
        this.carruseles = new Map();
        this.observadores = new Map();
        this.configuraciones = {
            itemsVisible: {
                mobile: 1,
                tablet: 2,
                desktop: 4,
                large: 5
            },
            autoSlide: false,
            slideInterval: 5000,
            lazyLoad: true,
            touchEnabled: true
        };
        this.init();
    }
    
    init() {
        this.detectarCarruseles();
        this.inicializarLazyLoading();
        this.configurarEventListeners();
        this.configurarObservadorInterseccion();
        console.log('RecomendacionManager inicializado correctamente');
    }
    
    detectarCarruseles() {
        const carruseles = document.querySelectorAll('.carrusel-recomendaciones');
        carruseles.forEach(carrusel => {
            this.crearCarruselRecomendaciones(carrusel);
        });
    }
    
    crearCarruselRecomendaciones(contenedor) {
        if (!contenedor) return;
        
        const carruselId = contenedor.id || `carrusel-${Date.now()}`;
        const configuracion = this.obtenerConfiguracionCarrusel(contenedor);
        
        const carruselData = {
            id: carruselId,
            contenedor: contenedor,
            configuracion: configuracion,
            currentIndex: 0,
            isTouch: false,
            startX: 0,
            currentX: 0,
            isDragging: false,
            autoSlideTimer: null
        };
        
        this.carruseles.set(carruselId, carruselData);
        this.setupCarrusel(carruselData);
        this.implementarNavegacionCarrusel(carruselData);
        
        if (configuracion.autoSlide) {
            this.iniciarAutoSlide(carruselData);
        }
        
        return carruselData;
    }
    
    obtenerConfiguracionCarrusel(contenedor) {
        const config = {...this.configuraciones};
        
        // Obtener configuración desde atributos data
        if (contenedor.dataset.autoSlide) {
            config.autoSlide = contenedor.dataset.autoSlide === 'true';
        }
        if (contenedor.dataset.slideInterval) {
            config.slideInterval = parseInt(contenedor.dataset.slideInterval);
        }
        if (contenedor.dataset.itemsDesktop) {
            config.itemsVisible.desktop = parseInt(contenedor.dataset.itemsDesktop);
        }
        if (contenedor.dataset.itemsTablet) {
            config.itemsVisible.tablet = parseInt(contenedor.dataset.itemsTablet);
        }
        if (contenedor.dataset.itemsMobile) {
            config.itemsVisible.mobile = parseInt(contenedor.dataset.itemsMobile);
        }
        
        return config;
    }
    
    setupCarrusel(carruselData) {
        const { contenedor, configuracion } = carruselData;
        
        // Crear estructura del carrusel si no existe
        let track = contenedor.querySelector('.carrusel-track');
        if (!track) {
            const items = contenedor.querySelectorAll('.recomendacion-item');
            track = document.createElement('div');
            track.className = 'carrusel-track';
            
            items.forEach(item => {
                track.appendChild(item);
            });
            
            contenedor.appendChild(track);
        }
        
        carruselData.track = track;
        carruselData.items = track.querySelectorAll('.recomendacion-item');
        carruselData.totalItems = carruselData.items.length;
        
        // Configurar estilos responsivos
        this.aplicarEstilosResponsivos(carruselData);
        
        // Crear controles de navegación
        this.crearControlesNavegacion(carruselData);
        
        // Crear indicadores
        this.crearIndicadores(carruselData);
    }
    
    aplicarEstilosResponsivos(carruselData) {
        const { track, configuracion } = carruselData;
        
        const updateLayout = () => {
            const width = window.innerWidth;
            let itemsVisible;
            
            if (width >= 1200) {
                itemsVisible = configuracion.itemsVisible.large;
            } else if (width >= 992) {
                itemsVisible = configuracion.itemsVisible.desktop;
            } else if (width >= 768) {
                itemsVisible = configuracion.itemsVisible.tablet;
            } else {
                itemsVisible = configuracion.itemsVisible.mobile;
            }
            
            carruselData.itemsVisible = itemsVisible;
            carruselData.maxIndex = Math.max(0, carruselData.totalItems - itemsVisible);
            
            // Aplicar estilos CSS
            const itemWidth = 100 / itemsVisible;
            carruselData.items.forEach(item => {
                item.style.minWidth = `${itemWidth}%`;
                item.style.flex = `0 0 ${itemWidth}%`;
            });
            
            // Ajustar posición actual si es necesaria
            if (carruselData.currentIndex > carruselData.maxIndex) {
                carruselData.currentIndex = carruselData.maxIndex;
                this.actualizarPosicionCarrusel(carruselData);
            }
        };
        
        updateLayout();
        window.addEventListener('resize', updateLayout);
    }
    
    crearControlesNavegacion(carruselData) {
        const { contenedor } = carruselData;
        
        // Verificar si ya existen controles
        if (contenedor.querySelector('.carrusel-control')) return;
        
        const btnPrev = document.createElement('button');
        btnPrev.className = 'carrusel-control carrusel-prev';
        btnPrev.innerHTML = '<i class="fas fa-chevron-left"></i>';
        btnPrev.setAttribute('aria-label', 'Anterior');
        
        const btnNext = document.createElement('button');
        btnNext.className = 'carrusel-control carrusel-next';
        btnNext.innerHTML = '<i class="fas fa-chevron-right"></i>';
        btnNext.setAttribute('aria-label', 'Siguiente');
        
        contenedor.appendChild(btnPrev);
        contenedor.appendChild(btnNext);
        
        carruselData.btnPrev = btnPrev;
        carruselData.btnNext = btnNext;
        
        // Event listeners
        btnPrev.addEventListener('click', () => this.navegarCarrusel(carruselData, 'prev'));
        btnNext.addEventListener('click', () => this.navegarCarrusel(carruselData, 'next'));
    }
    
    crearIndicadores(carruselData) {
        const { contenedor, totalItems, itemsVisible } = carruselData;
        
        if (totalItems <= itemsVisible) return;
        
        const indicadores = document.createElement('div');
        indicadores.className = 'carrusel-indicadores';
        
        const numIndicadores = Math.ceil(totalItems / itemsVisible);
        for (let i = 0; i < numIndicadores; i++) {
            const indicador = document.createElement('button');
            indicador.className = 'carrusel-indicador';
            if (i === 0) indicador.classList.add('active');
            
            indicador.addEventListener('click', () => {
                carruselData.currentIndex = i * itemsVisible;
                if (carruselData.currentIndex > carruselData.maxIndex) {
                    carruselData.currentIndex = carruselData.maxIndex;
                }
                this.actualizarPosicionCarrusel(carruselData);
                this.actualizarIndicadores(carruselData);
            });
            
            indicadores.appendChild(indicador);
        }
        
        contenedor.appendChild(indicadores);
        carruselData.indicadores = indicadores;
    }
    
    implementarNavegacionCarrusel(carruselData) {
        const { track, configuracion } = carruselData;
        
        if (!configuracion.touchEnabled) return;
        
        // Touch events
        track.addEventListener('touchstart', (e) => this.handleTouchStart(e, carruselData), { passive: true });
        track.addEventListener('touchmove', (e) => this.handleTouchMove(e, carruselData), { passive: false });
        track.addEventListener('touchend', (e) => this.handleTouchEnd(e, carruselData), { passive: true });
        
        // Mouse events para desktop
        track.addEventListener('mousedown', (e) => this.handleMouseDown(e, carruselData));
        track.addEventListener('mousemove', (e) => this.handleMouseMove(e, carruselData));
        track.addEventListener('mouseup', (e) => this.handleMouseUp(e, carruselData));
        track.addEventListener('mouseleave', (e) => this.handleMouseUp(e, carruselData));
        
        // Keyboard navigation
        track.addEventListener('keydown', (e) => this.handleKeyDown(e, carruselData));
        track.setAttribute('tabindex', '0');
    }
    
    handleTouchStart(e, carruselData) {
        carruselData.isTouch = true;
        carruselData.startX = e.touches[0].clientX;
        carruselData.currentX = carruselData.startX;
        carruselData.isDragging = true;
        
        this.detenerAutoSlide(carruselData);
    }
    
    handleTouchMove(e, carruselData) {
        if (!carruselData.isDragging) return;
        
        e.preventDefault();
        carruselData.currentX = e.touches[0].clientX;
        
        const diff = carruselData.currentX - carruselData.startX;
        const sensitivity = 0.3;
        
        carruselData.track.style.transform = `translateX(calc(-${carruselData.currentIndex * (100 / carruselData.itemsVisible)}% + ${diff * sensitivity}px))`;
    }
    
    handleTouchEnd(e, carruselData) {
        if (!carruselData.isDragging) return;
        
        const diff = carruselData.currentX - carruselData.startX;
        const threshold = 50;
        
        if (Math.abs(diff) > threshold) {
            if (diff > 0 && carruselData.currentIndex > 0) {
                this.navegarCarrusel(carruselData, 'prev');
            } else if (diff < 0 && carruselData.currentIndex < carruselData.maxIndex) {
                this.navegarCarrusel(carruselData, 'next');
            } else {
                this.actualizarPosicionCarrusel(carruselData);
            }
        } else {
            this.actualizarPosicionCarrusel(carruselData);
        }
        
        carruselData.isDragging = false;
        carruselData.isTouch = false;
        
        if (carruselData.configuracion.autoSlide) {
            this.iniciarAutoSlide(carruselData);
        }
    }
    
    handleMouseDown(e, carruselData) {
        if (carruselData.isTouch) return;
        
        e.preventDefault();
        carruselData.startX = e.clientX;
        carruselData.currentX = carruselData.startX;
        carruselData.isDragging = true;
        
        carruselData.track.style.cursor = 'grabbing';
        this.detenerAutoSlide(carruselData);
    }
    
    handleMouseMove(e, carruselData) {
        if (!carruselData.isDragging || carruselData.isTouch) return;
        
        e.preventDefault();
        carruselData.currentX = e.clientX;
        
        const diff = carruselData.currentX - carruselData.startX;
        const sensitivity = 0.5;
        
        carruselData.track.style.transform = `translateX(calc(-${carruselData.currentIndex * (100 / carruselData.itemsVisible)}% + ${diff * sensitivity}px))`;
    }
    
    handleMouseUp(e, carruselData) {
        if (!carruselData.isDragging || carruselData.isTouch) return;
        
        const diff = carruselData.currentX - carruselData.startX;
        const threshold = 30;
        
        if (Math.abs(diff) > threshold) {
            if (diff > 0 && carruselData.currentIndex > 0) {
                this.navegarCarrusel(carruselData, 'prev');
            } else if (diff < 0 && carruselData.currentIndex < carruselData.maxIndex) {
                this.navegarCarrusel(carruselData, 'next');
            } else {
                this.actualizarPosicionCarrusel(carruselData);
            }
        } else {
            this.actualizarPosicionCarrusel(carruselData);
        }
        
        carruselData.isDragging = false;
        carruselData.track.style.cursor = 'grab';
        
        if (carruselData.configuracion.autoSlide) {
            this.iniciarAutoSlide(carruselData);
        }
    }
    
    handleKeyDown(e, carruselData) {
        switch (e.key) {
            case 'ArrowLeft':
                e.preventDefault();
                this.navegarCarrusel(carruselData, 'prev');
                break;
            case 'ArrowRight':
                e.preventDefault();
                this.navegarCarrusel(carruselData, 'next');
                break;
            case 'Home':
                e.preventDefault();
                carruselData.currentIndex = 0;
                this.actualizarPosicionCarrusel(carruselData);
                break;
            case 'End':
                e.preventDefault();
                carruselData.currentIndex = carruselData.maxIndex;
                this.actualizarPosicionCarrusel(carruselData);
                break;
        }
    }
    
    navegarCarrusel(carruselData, direccion) {
        const { currentIndex, maxIndex } = carruselData;
        
        if (direccion === 'prev' && currentIndex > 0) {
            carruselData.currentIndex--;
        } else if (direccion === 'next' && currentIndex < maxIndex) {
            carruselData.currentIndex++;
        } else if (direccion === 'next' && currentIndex >= maxIndex && carruselData.configuracion.autoSlide) {
            // Volver al inicio en auto-slide
            carruselData.currentIndex = 0;
        }
        
        this.actualizarPosicionCarrusel(carruselData);
        this.actualizarControles(carruselData);
        this.actualizarIndicadores(carruselData);
    }
    
    actualizarPosicionCarrusel(carruselData) {
        const { track, currentIndex, itemsVisible } = carruselData;
        const translateX = -(currentIndex * (100 / itemsVisible));
        
        track.style.transition = 'transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        track.style.transform = `translateX(${translateX}%)`;
        
        // Remover transición después de la animación
        setTimeout(() => {
            if (!carruselData.isDragging) {
                track.style.transition = '';
            }
        }, 300);
    }
    
    actualizarControles(carruselData) {
        const { btnPrev, btnNext, currentIndex, maxIndex } = carruselData;
        
        if (btnPrev) {
            btnPrev.disabled = currentIndex === 0;
            btnPrev.classList.toggle('disabled', currentIndex === 0);
        }
        
        if (btnNext) {
            btnNext.disabled = currentIndex >= maxIndex;
            btnNext.classList.toggle('disabled', currentIndex >= maxIndex);
        }
    }
    
    actualizarIndicadores(carruselData) {
        const { indicadores, currentIndex, itemsVisible } = carruselData;
        
        if (!indicadores) return;
        
        const indicadoresItems = indicadores.querySelectorAll('.carrusel-indicador');
        const indicadorActivo = Math.floor(currentIndex / itemsVisible);
        
        indicadoresItems.forEach((indicador, index) => {
            indicador.classList.toggle('active', index === indicadorActivo);
        });
    }
    
    iniciarAutoSlide(carruselData) {
        this.detenerAutoSlide(carruselData);
        
        carruselData.autoSlideTimer = setInterval(() => {
            if (!carruselData.isDragging && !document.hidden) {
                this.navegarCarrusel(carruselData, 'next');
            }
        }, carruselData.configuracion.slideInterval);
    }
    
    detenerAutoSlide(carruselData) {
        if (carruselData.autoSlideTimer) {
            clearInterval(carruselData.autoSlideTimer);
            carruselData.autoSlideTimer = null;
        }
    }
    
    // Lazy Loading de recomendaciones
    inicializarLazyLoading() {
        if (!('IntersectionObserver' in window)) {
            // Fallback para navegadores sin soporte
            this.cargarTodasLasImagenes();
            return;
        }
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    this.cargarImagen(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: '50px 0px',
            threshold: 0.01
        });
        
        // Observar imágenes lazy
        const imagenesLazy = document.querySelectorAll('img[data-src]');
        imagenesLazy.forEach(img => imageObserver.observe(img));
        
        this.observadores.set('imagenes', imageObserver);
    }
    
    cargarImagen(img) {
        if (img.dataset.src) {
            img.src = img.dataset.src;
            img.classList.add('cargando');
            
            img.onload = () => {
                img.classList.remove('cargando');
                img.classList.add('cargada');
            };
            
            img.onerror = () => {
                img.classList.remove('cargando');
                img.classList.add('error');
                img.src = '/images/placeholder-product.jpg'; // Imagen de respaldo
            };
            
            delete img.dataset.src;
        }
    }
    
    cargarTodasLasImagenes() {
        const imagenesLazy = document.querySelectorAll('img[data-src]');
        imagenesLazy.forEach(img => this.cargarImagen(img));
    }
    
    // Configurar observer para cargar más recomendaciones
    configurarObservadorInterseccion() {
        const loadMoreObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const elemento = entry.target;
                    const tipo = elemento.dataset.loadMore;
                    const pagina = parseInt(elemento.dataset.pagina) || 1;
                    
                    this.cargarMasRecomendaciones(tipo, pagina + 1, elemento);
                }
            });
        }, {
            rootMargin: '100px 0px'
        });
        
        // Observar elementos de "cargar más"
        const elementosLoadMore = document.querySelectorAll('[data-load-more]');
        elementosLoadMore.forEach(elemento => loadMoreObserver.observe(elemento));
        
        this.observadores.set('loadMore', loadMoreObserver);
    }
    
    // Cargar recomendaciones vía AJAX
    async cargarRecomendacionesAJAX(tipo, params = {}) {
        const endpoint = '/api/recomendaciones.php';
        const defaultParams = {
            tipo: tipo,
            limite: 8,
            pagina: 1
        };
        
        const finalParams = { ...defaultParams, ...params };
        
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(finalParams)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error cargando recomendaciones:', error);
            return { productos: [], total: 0, error: true };
        }
    }
    
    async cargarMasRecomendaciones(tipo, pagina, elemento) {
        elemento.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando más productos...';
        
        try {
            const data = await this.cargarRecomendacionesAJAX(tipo, { pagina });
            
            if (data.productos && data.productos.length > 0) {
                const contenedor = elemento.closest('.seccion-recomendaciones').querySelector('.carrusel-track');
                
                data.productos.forEach(producto => {
                    const productoHTML = this.crearTarjetaRecomendacion(producto);
                    contenedor.appendChild(productoHTML);
                });
                
                // Actualizar datos del carrusel
                const carruselId = elemento.closest('.carrusel-recomendaciones').id;
                const carruselData = this.carruseles.get(carruselId);
                if (carruselData) {
                    carruselData.items = carruselData.track.querySelectorAll('.recomendacion-item');
                    carruselData.totalItems = carruselData.items.length;
                    carruselData.maxIndex = Math.max(0, carruselData.totalItems - carruselData.itemsVisible);
                    this.aplicarEstilosResponsivos(carruselData);
                }
                
                elemento.dataset.pagina = pagina;
                
                if (data.productos.length < 8) {
                    elemento.style.display = 'none'; // No hay más productos
                } else {
                    elemento.innerHTML = 'Cargar más productos';
                }
            } else {
                elemento.style.display = 'none';
            }
        } catch (error) {
            elemento.innerHTML = 'Error al cargar productos. <button onclick="location.reload()">Reintentar</button>';
        }
    }
    
    crearTarjetaRecomendacion(producto) {
        const item = document.createElement('div');
        item.className = 'recomendacion-item';
        item.innerHTML = `
            <div class="producto-card">
                <div class="producto-imagen">
                    <img data-src="${producto.imagen}" alt="${producto.nombre}" class="lazy-image">
                    ${producto.promocion ? `<span class="badge-promocion">${producto.promocion.etiqueta}</span>` : ''}
                </div>
                <div class="producto-info">
                    <h4 class="producto-nombre">${producto.nombre}</h4>
                    <div class="producto-precio">
                        ${producto.precio_promocional ? 
                            `<span class="precio-promocional">S/. ${producto.precio_promocional}</span>
                             <span class="precio-original">S/. ${producto.precio}</span>` :
                            `<span class="precio-actual">S/. ${producto.precio}</span>`
                        }
                    </div>
                    <button class="btn-agregar-carrito" 
                            onclick="agregarAlCarrito('${producto.id}', '${producto.nombre}', '${producto.precio_promocional || producto.precio}', '${producto.imagen}')">
                        <i class="fas fa-cart-plus"></i> Agregar al carrito
                    </button>
                </div>
            </div>
        `;
        
        // Configurar lazy loading para la nueva imagen
        const img = item.querySelector('img[data-src]');
        if (img && this.observadores.has('imagenes')) {
            this.observadores.get('imagenes').observe(img);
        }
        
        return item;
    }
    
    // Event listeners para configuración
    configurarEventListeners() {
        // Pausar auto-slide cuando la página no es visible
        document.addEventListener('visibilitychange', () => {
            this.carruseles.forEach(carruselData => {
                if (document.hidden) {
                    this.detenerAutoSlide(carruselData);
                } else if (carruselData.configuracion.autoSlide) {
                    this.iniciarAutoSlide(carruselData);
                }
            });
        });
        
        // Pausar auto-slide en hover (desktop)
        document.addEventListener('mouseenter', (e) => {
            const carrusel = e.target.closest('.carrusel-recomendaciones');
            if (carrusel) {
                const carruselData = this.carruseles.get(carrusel.id);
                if (carruselData) {
                    this.detenerAutoSlide(carruselData);
                }
            }
        }, true);
        
        document.addEventListener('mouseleave', (e) => {
            const carrusel = e.target.closest('.carrusel-recomendaciones');
            if (carrusel) {
                const carruselData = this.carruseles.get(carrusel.id);
                if (carruselData && carruselData.configuracion.autoSlide) {
                    this.iniciarAutoSlide(carruselData);
                }
            }
        }, true);
        
        // Quick view de productos
        document.addEventListener('click', (e) => {
            if (e.target.matches('.btn-quick-view') || e.target.closest('.btn-quick-view')) {
                e.preventDefault();
                const btn = e.target.matches('.btn-quick-view') ? e.target : e.target.closest('.btn-quick-view');
                const productoId = btn.dataset.productoId;
                this.mostrarQuickView(productoId);
            }
        });
    }
    
    async mostrarQuickView(productoId) {
        // Implementar modal de vista rápida del producto
        try {
            const response = await fetch(`/api/producto.php?id=${productoId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const producto = await response.json();
            
            if (producto.error) {
                throw new Error(producto.mensaje);
            }
            
            this.crearModalQuickView(producto);
            
        } catch (error) {
            console.error('Error cargando producto:', error);
            this.mostrarNotificacion('Error al cargar el producto', 'error');
        }
    }
    
    crearModalQuickView(producto) {
        // Crear modal dinámico para vista rápida
        const modal = document.createElement('div');
        modal.className = 'modal-quick-view';
        modal.innerHTML = `
            <div class="modal-content">
                <button class="modal-close">&times;</button>
                <div class="quick-view-content">
                    <div class="quick-view-imagen">
                        <img src="${producto.imagen}" alt="${producto.nombre}">
                    </div>
                    <div class="quick-view-info">
                        <h3>${producto.nombre}</h3>
                        <div class="producto-precio">
                            ${producto.precio_promocional ? 
                                `<span class="precio-promocional">S/. ${producto.precio_promocional}</span>
                                 <span class="precio-original">S/. ${producto.precio}</span>` :
                                `<span class="precio-actual">S/. ${producto.precio}</span>`
                            }
                        </div>
                        <p class="producto-descripcion">${producto.descripcion || ''}</p>
                        <button class="btn-agregar-carrito-modal" 
                                onclick="agregarAlCarrito('${producto.id}', '${producto.nombre}', '${producto.precio_promocional || producto.precio}', '${producto.imagen}')">
                            <i class="fas fa-cart-plus"></i> Agregar al carrito
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Event listeners del modal
        modal.querySelector('.modal-close').addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
        
        // Mostrar modal con animación
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }
    
    mostrarNotificacion(mensaje, tipo = 'info') {
        const notificacion = document.createElement('div');
        notificacion.className = `notificacion-recomendacion ${tipo}`;
        
        const iconos = {
            info: 'fa-info-circle',
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle'
        };
        
        notificacion.innerHTML = `
            <i class="fas ${iconos[tipo] || iconos.info}"></i>
            <span>${mensaje}</span>
        `;
        
        document.body.appendChild(notificacion);
        
        setTimeout(() => {
            notificacion.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notificacion.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(notificacion)) {
                    document.body.removeChild(notificacion);
                }
            }, 300);
        }, 3000);
    }
    
    // Método para limpiar recursos
    destruir() {
        // Limpiar timers de auto-slide
        this.carruseles.forEach(carruselData => {
            this.detenerAutoSlide(carruselData);
        });
        
        // Limpiar observers
        this.observadores.forEach(observer => {
            observer.disconnect();
        });
        
        this.carruseles.clear();
        this.observadores.clear();
    }
}

// Funciones globales para mantener compatibilidad

// Crear carrusel táctil
function crearCarruselTactil(contenedor, opciones = {}) {
    if (!window.recomendacionManager) {
        console.warn('RecomendacionManager no está inicializado');
        return null;
    }
    
    // Aplicar opciones al contenedor
    Object.keys(opciones).forEach(key => {
        contenedor.dataset[key] = opciones[key];
    });
    
    return window.recomendacionManager.crearCarruselRecomendaciones(contenedor);
}

// Cargar más recomendaciones programáticamente
async function cargarRecomendaciones(tipo, params = {}) {
    if (!window.recomendacionManager) {
        console.warn('RecomendacionManager no está inicializado');
        return null;
    }
    
    return await window.recomendacionManager.cargarRecomendacionesAJAX(tipo, params);
}

// Función para refrescar carruseles después de cambios dinámicos
function refrescarCarruseles() {
    if (window.recomendacionManager) {
        window.recomendacionManager.carruseles.forEach(carruselData => {
            window.recomendacionManager.aplicarEstilosResponsivos(carruselData);
        });
    }
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.recomendacionManager = new RecomendacionManager();
});

// Limpiar recursos al cerrar la página
window.addEventListener('beforeunload', () => {
    if (window.recomendacionManager) {
        window.recomendacionManager.destruir();
    }
});