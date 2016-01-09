jQuery(function($){
    var Mapa = {
        map: '',
        centro: {lat: -34.813, lng: -65.214},
        marcador: '',
        eventoDrag: '',
        coordenadas: '',
        $boton: $('#cargarMapa'),
        estado: null,
        init: function() {
            Mapa.$boton.click(Mapa.abrirMapa);
        },
        actualizarCoordenadas: function(elemento) {
            if (typeof elemento.getPosition == 'function') {
                lat = elemento.getPosition().lat();
                lng = elemento.getPosition().lng();
            } else {
                lat = this.getPosition().lat();
                lng = this.getPosition().lng();
            }
            Mapa.coordenadas = lat + ',' + lng;
        },
        crearMapa: function() {
            $('#mapaLocalizar').css({
                'height': '300px',
                'margin-top': '4px',
                'width': '100%'
            })
            Mapa.map = new google.maps.Map(
                document.getElementById('mapaLocalizar'),
                {
                    zoom: 4,
                    center: Mapa.centro,
                    scrollwheel: true,
                });
        },
        crearMarcador: function(posicion) {
            if (typeof posicion == 'undefined') {
                argsMarker = {
                    position: Mapa.centro,
                    map: Mapa.map,
                    draggable: true
                }
            } else {
                argsMarker = {
                    position: posicion,
                    map: Mapa.map,
                    draggable: true
                }
            }

            Mapa.marcador = new google.maps.Marker(argsMarker);
            Mapa.marcador.setMap(Mapa.map);

            Mapa.eventoDrag = google.maps.event.addListener(
                Mapa.marcador, 
                'dragend', 
                Mapa.actualizarCoordenadas
                );

            google.maps.event.addListener(Mapa.map, 'click', function(event) {
                google.maps.event.removeListener(Mapa.eventoDrag)
                Mapa.marcador.setMap(null);
                Mapa.crearMarcador(event.latLng);
                Mapa.actualizarCoordenadas(Mapa.marcador);
            });
        },
        abrirMapa: function() {
            Mapa.$boton.unbind('click');
            Mapa.$boton.click(Mapa.cerrarMapa);
            if (Mapa.estado == 'iniciado') {
                $('#mapaLocalizar').css({
                    'height': '300px',
                    'margin-top': '4px',
                    'width': '100%'
                })
            } else {
                var coords = $('#mc_meta_coordenadas').val();
                    console.log(coords);
                if (coords.length > 0) {
                    coords = coords.split(',');
                    Mapa.centro.lat = parseFloat(coords[0]);
                    Mapa.centro.lng = parseFloat(coords[1]);
                    console.log(Mapa.centro)
                }
                Mapa.crearMapa();
                Mapa.crearMarcador();
            }
            Mapa.estado = 'iniciado';
            Mapa.$boton.html('Usar ubicación y cerrar');
            return false;
        },
        cerrarMapa: function() {
            $('#mapaLocalizar').css({
                'height': '0',
                'margin-top': '0',
            })
            $('#mc_meta_coordenadas').val(Mapa.coordenadas).trigger('keypress');
            Mapa.$boton.html('Buscar ubicación');
            Mapa.init();
            return false;
        }
    }

    Mapa.init();

    $('#mc_meta_coordenadas, #mc_meta_direccion').on('keyup keydown keypress', function(event) {
        $this = $(this);
        id = $this.attr('id');
        if (id == 'mc_meta_coordenadas') {
            contenido = $this.val().replace(' ', '');
            data = 'data-coords';
            url = 'https://www.google.com/maps?q=' + contenido + '&z=16';

        } else if (id == 'mc_meta_direccion') {
            contenido = $this.val()
            contenido = escape(contenido);
            data = 'data-direccion';
            url = 'https://www.google.com.ar/maps/place/' + contenido;
        }

        $link = $('a.' + id);
        if ($link.attr(data) != contenido) {
            $link.attr(data, contenido);
            $link.attr('href', url);
        }
    });
})
