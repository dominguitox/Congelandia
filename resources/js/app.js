import 'bootstrap';
window.toggleDetalle = function(id, boton) {


    const fila = document.getElementById(
        'detalle-' + id
    );


    if (fila.style.display === "table-row") {


        fila.style.display = "none";

        boton.innerHTML = "▼";


    } else {


        fila.style.display = "table-row";

        boton.innerHTML = "▲";

    }

}
window.toggleFormularioCliente = function(){

    const formulario = document.getElementById(
        'formularioCliente'
    );


    if(formulario.style.display === "none"){

        formulario.style.display = "block";

    }
    else{

        formulario.style.display = "none";

    }

}