var Mapa = {
    map: '',
    geocoder: '',
    markerBounds: '',
    infowindow: '',
    init: function() {
        this.crearMapa();
        this.geocoder     = new google.maps.Geocoder();
        this.markerBounds = new google.maps.LatLngBounds();
        this.infowindow   = new google.maps.InfoWindow({content: ""});
        this.crearMarcadores();
    },
    crearMapa: function() {
        this.map = new google.maps.Map(document.getElementById('mapa-ubicaciones'));
    },
    crearMarcadores: function() {
        for (var i = 0; i < lugares.length; i++) {
            var lugar = lugares[i];
            if (typeof lugar.coordenadas !== 'undefined' && lugar.coordenadas.length > 0) {
                this.crearMarcadorDesdeCoord(lugar);
            } else if (typeof lugar.direccion !== 'undefined' && lugar.direccion.length > 0) {
                this.crearMarcadorDesdeDireccion(lugar);

            }
        }
    },
    crearMarcadorDesdeCoord: function(lugar) {
        if (typeof lugar.direccion !== 'undefined') {
            var direccion = lugar.direccion;
        } else {
            var direccion = '';
        }
        var coords = lugar.coordenadas.split(',');
        var latLng = new google.maps.LatLng(coords[0], coords[1]);
        var marker = new google.maps.Marker({
            position: latLng,
            map: this.map
        });
        this.unirInfoWindow(marker, lugar.nombre, direccion, lugar.descripcion);
        this.markerBounds.extend(latLng);
        this.map.fitBounds(this.markerBounds);
    },
    crearMarcadorDesdeDireccion: function(lugar) {
        var direccion = lugar.direccion + ',Concordia,Entre Rios,Argentina';
        this.geocoder.geocode({'address': direccion}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                var latLng = results[0].geometry.location;
                var marker = new google.maps.Marker({
                    position: latLng,
                    map: Mapa.map
                });
                Mapa.unirInfoWindow(marker, lugar.nombre, lugar.direccion, lugar.descripcion);
                Mapa.markerBounds.extend(latLng);
                Mapa.map.fitBounds(Mapa.markerBounds);
            } else {
                console.log('Geocode was not successful for the following reason: ' + status);
            }
        });
    },
    unirInfoWindow: function(marker, nombre, direccion, descripcion) {
        var contenido = this.armarContenidoInfoWindow(nombre, direccion, descripcion);
        marker.addListener('click', function() {
            Mapa.infowindow.setContent(contenido);
            Mapa.infowindow.open(Mapa.map, this);
        });
    },
    armarContenidoInfoWindow: function(nombre, direccion, descripcion) {
        var retorno = '';

        if (direccion.length > 0) {
            retorno = '<div class="mapa-ubicaciones-direccion">' + direccion + '</div>';
        }

        if (nombre.length > 0) {
            retorno += '<h1 class="mapa-ubicaciones-nombre">' + nombre + '</h1>';
        }

        if (descripcion.length > 0) {
            retorno += '<div class="mapa-ubicaciones-contenido">' + descripcion + '</div>';
        }

        return '<div class="mapa-ubicaciones-contenedor">' + retorno + '</div>';
    }
}

function initMap() {
    Mapa.init()
}

google.maps.event.addDomListener(window, "load", initMap);
