
function alertExito(){
    Swal.fire({
        icon: "success",
        title: "Realizado con éxito!",
        timer: 1700,
        padding: "3em",
        showConfirmButton: false,
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        
        location.reload(); // Recarga la página para actualizar la tabla
        
    });
    
}
