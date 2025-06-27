const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
});
function redirigir(event) {
    event.preventDefault(); // Prevenir el comportamiento por defecto del formulario
    window.location.href = 'index.html'; // Redirigir a aindex.html
}