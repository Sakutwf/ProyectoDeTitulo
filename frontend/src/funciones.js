import Swal from 'sweetalert2';
import axios from 'axios';

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


export async function enviarSolicitud(metodo, parametros, url, mensaje){
    await axios({method: metodo, url: url, data: parametros}).then(function(respuesta){
        var status = respuesta.data[0]['status'];
        console.log('entro al enviarSolicitud');
        console.log(status);
        console.log(respuesta.data);
        if(status === 'success'){
            console.log('entra');
            show_alerta(mensaje, 'success');
            window.setTimeout(function(){
                window.location.href='/';
            }, 1000);
        }
        else{
            var listado='';
            var errores = respuesta.data[1]['errors'];
            Object.keys(errores).forEach(
                key => listado += errores[key][0] + '.'
            );
            show_alerta(listado, 'error');
        }
    }).catch(function(error){
        show_alerta('Error en la solicitud', 'error');
    });   
}