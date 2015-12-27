<?php
class BackendUbicaciones extends FrontendUbicaciones
{
    function __construct()
    {
        add_action('init',       array($this, 'registrarPostType'));
        add_action('save_post',  array($this, 'guardarMeta')); 
        add_action('admin_menu', array($this, 'registrarMenu'));
        add_action('admin_init', array($this, 'registrarOpcionesMenu'));
    }

    public function registrarMenu()
    {
        add_submenu_page(
            'edit.php?post_type=ubicacion', 
            'Ajustes', 
            'Ajustes', 
            'activate_plugins', 
            'ajustes', 
            array($this, 'mostrarMenu')
            );
    }

    public function registrarOpcionesMenu()
    {
        $option_group = 'ajustes_ubicaciones';
        $option_name = 'ubicaciones_api_gmaps';
        $setting_section_id = 'setting_section_id';

        register_setting($option_group, $option_name, array($this, 'sanitize'));
        add_settings_section($setting_section_id, '', '', $option_group );

        add_settings_field(
            'ubicaciones_gmaps_api_key', // ID
            'Google Maps API Key', // Title 
            array( $this, 'gmapsApiInput' ), // Callback
            $option_group, // Page
            $setting_section_id // Section
        );
    }

    public function gmapsApiInput()
    {
        $id = 'ubicaciones_gmaps_api_key';
        $name = 'ubicaciones_api_gmaps';
        $valor = get_option('ubicaciones_api_gmaps');
        printf('<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" />', $id, $name, $valor[$id]);
        echo '<p class="description">Generá las credenciales en <a target="_blank" href="https://console.developers.google.com">Google Developers Console</a>.</p>';
    }

    public function sanitize($post)
    {
        $post['ubicaciones_gmaps_api_key'] = sanitize_text_field($post['ubicaciones_gmaps_api_key']);
        return $post;
    }

    public function mostrarMenu()
    {
        ?>
        <div class="wrap">
            <h1>Ajustes</h1>
            <form method="post" action="options.php">
                <?php settings_fields('ajustes_ubicaciones'); ?>
                <?php do_settings_sections('ajustes_ubicaciones'); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Guarda los metadatos del post
     */
    public function guardarMeta($post_id)
    {
        if (!isset( $_POST['mc_meta_nonce'])) return;

        if (!wp_verify_nonce( $_POST['mc_meta_nonce'], 'mc_save_meta_box_data')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        if (isset($_POST['_mc_meta_direccion'])) {
            $data = trim($_POST['_mc_meta_direccion']);
            $data = sanitize_text_field($_POST['_mc_meta_direccion']);
            update_post_meta( $post_id, '_mc_meta_direccion', $data );
        }

        if (isset($_POST['_mc_meta_coordenadas'])) {
            $data = str_replace(' ', '', $_POST['_mc_meta_coordenadas']);
            $data = sanitize_text_field($data);
            update_post_meta( $post_id, '_mc_meta_coordenadas', $data );
        }
    }
}
