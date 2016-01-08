<?php
class FrontendUbicaciones
{
    private $clave;
    private $ubicaciones;

    function __construct()
    {
        $this->cargarCredencialesAPI();
        add_action('init',                 array($this, 'registrarPostType'));
        add_action('wp_enqueue_scripts',   array($this, 'enqueue'));
        add_shortcode('ubicaciones',       array($this, 'mostrarMapa'));
        add_shortcode('ubicaciones-lista', array($this, 'mostrarLista'));
    }

    public function enqueue()
    {
        wp_enqueue_style('pda-css', plugin_dir_url( __FILE__ ) . 'ubicaciones.css');
        wp_enqueue_script('pda-js', plugin_dir_url( __FILE__ ) . 'ubicaciones.js', array(), null, true);

        $url = 'https://maps.googleapis.com/maps/api/js?';
        if (!empty($this->clave)) {
            $url .= 'key=' . $this->clave;
        }
        wp_enqueue_script('pda-gmaps-api', $url);
    }

    public function registrarPostType()
    {
        $post_type = 'ubicacion';
        $args = array(
            'labels' => array(
                'name'               => 'Ubicaciones',
                'singular_name'      => 'Ubicación',
                'menu_name'          => 'Ubicaciones',
                'name_admin_bar'     => 'Ubicación',
                'add_new'            => 'Añadir nueva',
                'add_new_item'       => 'Añadir nueva ubicación',
                'new_item'           => 'Nueva ubicación',
                'edit_item'          => 'Editar ubicación',
                'view_item'          => 'Ver ubicación',
                'all_items'          => 'Todas las ubicaciones',
                'search_items'       => 'Buscar ubicaciones',
                'not_found'          => 'No se encontraron ubicaciones',
                'not_found_in_trash' => 'No se encontraron ubicaciones en la papelera',
            ),
            'public'          => true,
            'menu_position'   => 21,
            'menu_icon'       => 'dashicons-location-alt',
            'supports'        => array('title', 'editor'),
            'rewrite'         => array('slug' => 'ubicacion'),
            'register_meta_box_cb' => array($this, 'registrarMetabox')
            );
        register_post_type($post_type, $args);
    }

    public function mostrarMapa()
    {
        $ubicaciones = $this->obtenerUbicaciones();
        $ubicaciones = $this->prepararObjectJS($ubicaciones);
        $mapa = '<div id="mapa-ubicaciones"></div>';
        return $ubicaciones . $mapa;
    }

    public function mostrarLista()
    {
        $ubicaciones = $this->obtenerUbicaciones();
        $retorno = '';
        $li = '<li>%s - %s &mdash; %s</li>';
        foreach ($ubicaciones as $ubicacion) {
            $contenido = $ubicacion['nombre'];
            if (!empty($ubicacion['direccion'])) {
                $contenido .= ' - ' . $ubicacion['direccion'];
            }
            if (!empty($ubicacion['descripcion'])) {
                $contenido .= ' <div>' . $ubicacion['descripcion'].'</div>';
            }
            $retorno .= '<li>' . $contenido . '</li>';
        }
        return '<ul>'.$retorno.'</ul>';
    }

    public function registrarMetabox()
    {
        add_meta_box('datos-pda-dir', 'Dirección', array($this, 'agregarMetaDireccion'), 'ubicacion', 'side', 'high');
        add_meta_box('datos-pda-coord', 'Coordenadas', array($this, 'agregarMetaCoordenadas'), 'ubicacion', 'side', 'high');
    }

    public function agregarMetaCoordenadas($post)
    {
        wp_nonce_field('mc_save_meta_box_data', 'mc_meta_nonce' );
        $coords = get_post_meta($post->ID, '_mc_meta_coordenadas', true);
        ?>
        <input type="text" id="mc_meta_coordenadas" name="_mc_meta_coordenadas" style="width:100%" value="<?php echo $coords ?>"/><br>
        <a class="mc_meta_coordenadas" target="_blank" href="https://www.google.com/maps?q=<?php echo str_replace(' ', '', $coords) ?>&z=16" data-coords="">Probar coordenadas</a>
        <?php
        $this->agregarJavascriptMeta();
    }

    public function agregarMetaDireccion($post)
    {
        $direccion = get_post_meta($post->ID, '_mc_meta_direccion', true); ?>
        <input type="text" id="mc_meta_direccion" name="_mc_meta_direccion" style="width:100%" value="<?php echo $direccion ?>"/>
        <small>Formato: Direccion, Ciudad, Provincia, Pais</small>
        <br>
        <a class="mc_meta_direccion" target="_blank" href="https://www.google.com.ar/maps/place/<?php echo urlencode($direccion) ?>" data-direccion="">Probar direccion</a>
        <?php
    }

    private function agregarJavascriptMeta()
    {
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                $('#mc_meta_coordenadas, #mc_meta_direccion')
                    .on('keyup keydown keypress', function(event) {
                        $this = $(this);
                        id = $this.attr('id');
                        if (id == 'mc_meta_coordenadas') {
                            coordenadas = $this.val().replace(' ', '');
                            $link = $('a.' + id);
                            if ($link.attr('data-coords') != coordenadas) {
                                $link.attr('data-coords', coordenadas);
                                $link.attr('href', 'https://www.google.com/maps?q=' + coordenadas + '&z=16');
                            }
                        } else if (id == 'mc_meta_direccion') {
                            direccion = $this.val()
                            direccion = escape(direccion);
                            $link = $('a.' + id);
                            if ($link.attr('data-direccion') != direccion) {
                                $link.attr('data-direccion', direccion);
                                $link.attr('href', 'https://www.google.com.ar/maps/place/' + direccion);
                            }
                        }
                    });

            })
        </script>
        <?php
    }

    /**
     * Convierte las ubicaciones en un objeto javascript
     * @return [type] [description]
     */
    protected function obtenerUbicaciones()
    {
        if (empty($this->ubicaciones)) {
            $args = array(
                'post_type' => 'ubicacion',
                'cache_results' => false,
                );
            $query = new WP_Query($args);
            $retorno = array();
            foreach ($query->posts as $post) {
                $meta = get_post_meta($post->ID);

                $retorno[] = array(
                    'nombre'      => $post->post_title,
                    'descripcion' => $post->post_content,
                    'direccion'   => $meta['_mc_meta_direccion'][0],
                    'coordenadas' => $meta['_mc_meta_coordenadas'][0]
                    );
            }
            $this->ubicaciones = $retorno;
        }
        return $this->ubicaciones;
    }

    protected function prepararObjectJS($ubicaciones)
    {
        $objeto = "{
            nombre: '%s',
            direccion: '%s',
            descripcion: '%s',
            coordenadas: '%s'
        },\n";
        $retorno = '';
        foreach ($ubicaciones as $ubicacion) {
            $retorno .= sprintf($objeto,
                $ubicacion['nombre'],
                $ubicacion['direccion'],
                $ubicacion['descripcion'],
                $ubicacion['coordenadas']
                );
        }
        $retorno = '<script type="text/javascript">
            var lugares = ['.$retorno.'];
        </script>';

        return $retorno;
    }

    protected function cargarCredencialesAPI()
    {
        if (false === ($clave = get_option('ubicaciones_api_gmaps'))) {
            $this->clave = '';
        } else {
            $this->clave = $clave['ubicaciones_gmaps_api_key'];
        }
    }
}
