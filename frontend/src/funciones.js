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
