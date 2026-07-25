import Swal from 'sweetalert2';

/**
 * Muestra alertas uniformes en formularios y operaciones del frontend.
 * `foco` permite devolver el cursor al campo que debe corregirse.
 * Sus consumidores se detallan en docs/GUIA_CODIGO_COMPARTIDO.md.
 */
export function show_alerta(mensaje, icono, foco=''){
    if(foco != ''){
        document.getElementById(foco).focus();
    }
    Swal.fire({
        title: mensaje,
        icon: icono,
        customClass: {confirmButton: 'btn btn-primary', popup:'animated zoomIn' },
        buttonsStyling: false,
    });
}

export async function confirm_logout(){
    const result = await Swal.fire({
        title: '¿Cerrar sesión?',
        text: 'Tendrás que volver a ingresar tus credenciales para acceder al sistema.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d72732',
        reverseButtons: true,
    });

    return result.isConfirmed;
}
