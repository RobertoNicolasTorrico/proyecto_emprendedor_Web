<!--Modal para ver la ubicacion de la publicacion -->
<div class="modal fade" id="modal_ver_map" tabindex="-1" aria-labelledby="modal_ver_mapLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">

    <div class="modal-dialog">
        <div class="modal-content">

            <!--Header del modal -->
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modal_ver_mapLabel">Ubicacion</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!--Body del modal -->
            <div class="modal-body">
                <!--Div para mostrar el contenido del mapa -->
                <div id="map_publicaciones" style="height: 300px;"></div>
            </div>

            <!--Footer del modal -->
            <div class="modal-footer">
                <!--Boton para cerrar el modal -->
                <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>


<script>
    // Se obtiene el modal abrir el mapa
    const modal_ver_map = document.getElementById('modal_ver_map');

    //Agrega un evento cuando se muestra el modal
    modal_ver_map.addEventListener('shown.bs.modal', event => {

        //Se obtiene atributos del boton
        var button = event.relatedTarget;
        var latitud_bs = button.getAttribute('data-bs-latitud');
        var longitud_bs = button.getAttribute('data-bs-longitud');

        //LLama a la funcion para agregar el contenido del mapa al Div
        verUbicacionMapPublicacionesModal(latitud_bs, longitud_bs, 'map_publicaciones', "Ubicacion");
    });
</script>