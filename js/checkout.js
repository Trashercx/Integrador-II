/// Manejo de pasos del checkout con clases del sistema actual
function nextStep(nextSectionId) {
    const currentSection = document.querySelector('.checkout-section.active');
    const nextSection = document.getElementById(nextSectionId);

    if (validateCurrentSection(currentSection.id)) {
        currentSection.classList.remove('active');
        nextSection.classList.add('active');
    }
}

function prevStep(prevSectionId) {
    const currentSection = document.querySelector('.checkout-section.active');
    const prevSection = document.getElementById(prevSectionId);

    currentSection.classList.remove('active');
    prevSection.classList.add('active');
}

function validateCurrentSection(sectionId) {
    const section = document.getElementById(sectionId);
    const requiredFields = section.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('input-error');
            isValid = false;
        } else {
            field.classList.remove('input-error');
        }
    });

    if (!isValid) {
        alert('Por favor complete todos los campos requeridos');
    }
    return isValid;
}

function confirmOrder() {
    if (!document.getElementById('terms').checked) {
        alert('Debe aceptar los términos y condiciones para continuar');
        return;
    }

    alert('Pedido confirmado con éxito. Gracias por su compra!');
    localStorage.removeItem('carrito');
    window.location.href = 'gracias.php';
}

function updateTotal(shipping = 11.20) {
    const subtotalText = document.getElementById('order-subtotal')?.innerText || '0';
    const subtotal = parseFloat(subtotalText.replace('S/. ', '')) || 0;
    const total = subtotal + shipping;
    document.getElementById('order-total').textContent = `S/. ${total.toFixed(2)}`;
    document.getElementById('order-shipping').textContent = `S/. ${shipping.toFixed(2)}`;
}

document.addEventListener('DOMContentLoaded', function () {
    // Activar primera sección
    document.querySelector('#address-section').classList.add('active');

    // Listener para métodos de envío
    document.querySelectorAll('input[name="shipping"]').forEach(input => {
        input.addEventListener('change', () => {
            let shipping = 0;
            switch (input.value) {
                case 'standard': shipping = 11.20; break;
                case 'express': shipping = 18.50; break;
                case 'pickup': shipping = 0; break;
            }
            updateTotal(shipping);
        });
    });

    // Cargar total inicial
    updateTotal();
});
