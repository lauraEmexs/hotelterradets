<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<?php $ids_codemirror=array(); ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-list fa-fw"></i> Preferencias generales
    </div>
    <div class="panel-body">
        <?php $session = \Config\Services::session();
        if($session->getFlashdata('message')):?>
            <?php echo $session->getFlashdata('message')?>
        <?php endif;?>

        <?php echo form_open_multipart('admin/preferences/save'. $domain_url_params, array('role'=>'form','id'=>'myform')); ?>
        <input type="hidden" name="action" id="action" value=""/>

        <div class="row">
            <div class="col-lg-12">
                <div class="form-group">
                    <?php $ids_codemirror[] = 'scripts_head'; ?>
                    <?php echo form_label('Contenido para todas las páginas en HEAD','Contenido para todas las páginas (antes de </head>)');?>
                    <?php echo form_textarea(array('name'=>'scripts_head','id'=>'scripts_head','value'=>isset($data->scripts_head)?$data->scripts_head:'','class'=>'form-control') );?>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="form-group">
                    <?php $ids_codemirror[] = 'scripts_footer'; ?>
                    <?php echo form_label('Contenido para todas las páginas en FOOTER','Contenido para todas las páginas (antes de </footer>)');?>
                    <?php echo form_textarea(array('name'=>'scripts_footer','id'=>'scripts_footer','value'=>isset($data->scripts_footer)?$data->scripts_footer:'','class'=>'form-control') );?>
                </div>
            </div>
			<div class="col-lg-12">
				<div class="form-group">
					<?php $ids_codemirror[] = 'scripts_dashboard'; ?>
					<?php echo form_label('Contenido pagina Dashboard de gestor','scripts_dashboard');?>
					<?php echo form_textarea(array('name'=>'scripts_dashboard','id'=>'scripts_dashboard','value'=>isset($data->scripts_dashboard)?$data->scripts_dashboard:'','class'=>'form-control') );?>
				</div>
			</div>
        </div>
        <?php /*
        <div class="row">
            <div class="col-md-12">
                <h3>Logotipos</h3>
            </div>

            <?php echo view('admin/input/text', array('col' => 4, 'name' => 'menu_logo', 'objects' => ['menu_logo' => [
            'label' => 'Menu logo',
            'value' => isset($data->menu_logo) ? $data->menu_logo : '',
        ]
        ])); ?>
            <?php echo view('admin/input/text', array('col' => 4, 'name' => 'menu_mobile_logo', 'objects' => ['menu_mobile_logo' => [
            'label' => 'Menu mobile logo',
            'value' => isset($data->menu_mobile_logo) ? $data->menu_mobile_logo : '',
        ]
        ])); ?>
            <?php echo view('admin/input/text', array('col' => 4, 'name' => 'footer_logo', 'objects' => ['footer_logo' => [
            'label' => 'Footer logo',
            'value' => isset($data->footer_logo) ? $data->footer_logo : '',
        ]
        ])); ?>

        </div>
        */ ?>
        <div class="row">
            <div class="col-md-12">
                <h3>Custom</h3>
            </div>

            <?php echo view('admin/input/text', array('col' => 4, 'name' => 'custom_css', 'objects' => ['custom_css' => [
                'label' => 'Custom css nombre de archivo (Ejemplo: file1.css;file2.css)',
                'value' => isset($data->custom_css) ? $data->custom_css : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 4, 'name' => 'custom_js', 'objects' => ['custom_js' => [
                'label' => 'Custom js nombre de archivo Ejemplo: file1.js;file2.js',
                'value' => isset($data->custom_js) ? $data->custom_js : '',
            ]
            ])); ?>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h3>Datos de contacto</h3>
            </div>

            <?php echo view('admin/input/text', array('col' => 4, 'name' => 'hotel_phone', 'objects' => ['hotel_phone' => [
                'label' => 'Teléfono',
                'value' => isset($data->hotel_phone) ? $data->hotel_phone : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 4, 'name' => 'hotel_email', 'objects' => ['hotel_email' => [
                'label' => 'Email principal (##EMAIL_HOTEL##)',
                'value' => isset($data->hotel_email) ? $data->hotel_email : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 4, 'name' => 'restaurant_mail', 'objects' => ['restaurant_mail' => [
                'label' => 'Email de restaurante (##EMAIL_RESTAURANTE##)',
                'value' => isset($data->restaurant_mail) ? $data->restaurant_mail : '',
            ]
            ])); ?>

            <?php echo view('admin/input/text', array('col' => 12, 'name' => 'hotel_address', 'objects' => ['hotel_address' => [
                'label' => 'Dirección',
                'value' => isset($data->hotel_address) ? $data->hotel_address : '',
            ]
            ])); ?>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h3>Ubicación</h3>
            </div>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'hotel_latitude', 'objects' => ['hotel_latitude' => [
                'label' => 'Hotel Latitud',
                'value' => isset($data->hotel_latitude) ? $data->hotel_latitude : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'hotel_longitude', 'objects' => ['hotel_longitude' => [
                'label' => 'Hotel Longitud',
                'value' => isset($data->hotel_longitude) ? $data->hotel_longitude : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'gmap_api_key', 'objects' => ['gmap_api_key' => [
                'label' => 'Google maps api key',
                'value' => isset($data->gmap_api_key) ? $data->gmap_api_key : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'google_maps_url', 'objects' => ['google_maps_url' => [
                'label' => 'Google maps url (como llegar)',
                'value' => isset($data->google_maps_url) ? $data->google_maps_url : '',
            ]
            ])); ?>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h3>Servicios externos</h3>
            </div>

            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'booking_engine_url', 'objects' => ['booking_engine_url' => [
                'label' => 'Url a motor de reservas (parte generica)',
                'value' => isset($data->booking_engine_url) ? $data->booking_engine_url : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 2, 'name' => 'booking_engine_id', 'objects' => ['booking_engine_id' => [
                'label' => 'ID motor de reservas',
                'value' => isset($data->booking_engine_id) ? $data->booking_engine_id : '',
            ]
            ])); ?>

            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'google_tag_manager', 'objects' => ['google_tag_manager' => [
                'label' => 'Google tag manager code',
                'value' => isset($data->google_tag_manager) ? $data->google_tag_manager : '',
            ]
            ])); ?>

            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'mailchimp_api_key', 'objects' => ['mailchimp_api_key' => [
                'label' => 'Mailchimp api key',
                'value' => isset($data->mailchimp_api_key) ? $data->mailchimp_api_key : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'mailchimp_list_id', 'objects' => ['mailchimp_list_id' => [
                'label' => 'Mailchimp list id',
                'value' => isset($data->mailchimp_list_id) ? $data->mailchimp_list_id : '',
            ]
            ])); ?>
        </div>
        <div class="row">
            <?php echo view('admin/input/enum', array('col' => 2, 'name' => 'recaptcha_version', 'objects' => ['recaptcha_version' => [
                'label' => 'Recaptcha version',
                'value' => isset($data->recaptcha_version) ? $data->recaptcha_version : '',
            ]
            ], 'values' => ['3' => '3', '2' => '2', ]));
            ?>
            <?php echo view('admin/input/text', array('col' => 5, 'name' => 'recaptcha_public_key', 'objects' => ['recaptcha_public_key' => [
                'label' => 'Recaptcha public key',
                'value' => isset($data->recaptcha_public_key) ? $data->recaptcha_public_key : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 5, 'name' => 'recaptcha_private_key', 'objects' => ['recaptcha_private_key' => [
                'label' => 'Recaptcha private key',
                'value' => isset($data->recaptcha_private_key) ? $data->recaptcha_private_key : '',
            ]
            ])); ?>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h3>Redes sociales</h3>
            </div>

            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'instagram', 'objects' => ['instagram' => [
                'label' => 'Url Instagram (hotel)',
                'value' => isset($data->instagram) ? $data->instagram : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'instagram_user', 'objects' => ['instagram_user' => [
                'label' => 'Instagram id usuario (hotel)',
                'value' => isset($data->instagram_user) ? $data->instagram_user : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'facebook', 'objects' => ['facebook' => [
                'label' => 'Url Facebook',
                'value' => isset($data->facebook) ? $data->facebook : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'twitter', 'objects' => ['twitter' => [
                'label' => 'Url Twitter',
                'value' => isset($data->twitter) ? $data->twitter : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'pinterest', 'objects' => ['pinterest' => [
                'label' => 'Url Pinterest',
                'value' => isset($data->pinterest) ? $data->pinterest : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'tripadvisor', 'objects' => ['tripadvisor' => [
                'label' => 'Url Tripadvisor',
                'value' => isset($data->tripadvisor) ? $data->tripadvisor : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'youtube', 'objects' => ['youtube' => [
                'label' => 'Url youtube',
                'value' => isset($data->youtube) ? $data->youtube : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'spotify', 'objects' => ['spotify' => [
                'label' => 'Url spotify',
                'value' => isset($data->spotify) ? $data->spotify : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'linkedin', 'objects' => ['linkedin' => [
                'label' => 'Url linkedin',
                'value' => isset($data->linkedin) ? $data->linkedin : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'whatsapp', 'objects' => ['whatsapp' => [
                'label' => 'Whatsapp',
                'value' => isset($data->whatsapp) ? $data->whatsapp : '',
            ]
            ])); ?>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h3>Home page</h3>
            </div>
            <?php echo view('admin/input/enum', array('col' => 6, 'name' => 'home_page_id', 'objects' => ['home_page_id' => [
                'label' => 'Pagina home',
                'value' => isset($data->home_page_id) ? $data->home_page_id : '',
            ]
            ], 'values' => $all_pages)); ?>

            <?php echo view('admin/input/enum', array('col' => 6, 'name' => 'error_page_id', 'objects' => ['error_page_id' => [
                'label' => 'Pagina error',
                'value' => isset($data->error_page_id) ? $data->error_page_id : '',
            ]
            ], 'values' => $all_pages)); ?>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h3>Menu gestor <small>Drag and drop</small></h3>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Sin utilizar</label><br>
                    <input type="hidden" name="admin_menu" id="admin_menu"
                           value="<?php echo (isset($data->admin_menu)) ? $data->admin_menu : ''; ?>"/>
                    <ul id="sortable3" class="connectedSortable2 sortable">
                        <?php foreach ($admin_menu_items as $admin_menu_id => $admin_menu) {
                            if (in_array($admin_menu_id, $used_admin_menu)) {
                                continue;
                            } ?>
                            <li class="ui-state-default" data-admin_menu_id="<?php echo $admin_menu_id; ?>">
                                <?php echo $admin_menu; ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Utilizados</label><br>
                    <ul id="sortable4" class="connectedSortable2 sortable">
                        <?php foreach ($used_admin_menu as $admin_menu_id) { ?>
                            <li class="ui-state-highlight" data-admin_menu_id="<?php echo $admin_menu_id; ?>">
                                <?php echo $admin_menu_items[$admin_menu_id]; ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>

        <!--<div class="row">
            <div class="col-md-12">
                <h3>Cms Bloques <small>Drag and drop</small></h3>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Sin utilizar</label><br>
                    <input type="hidden" name="blocks" id="blocks"
                           value="<?php echo (isset($data->blocks)) ? $data->blocks : ''; ?>"/>
                    <ul id="sortable1" class="connectedSortable sortable">
                        <?php foreach ($blocks as $block_id => $block) {
                            if (in_array($block_id, $used_blocks)) {
                                continue;
                            } ?>
                            <li class="ui-state-default" data-blockid="<?php echo $block_id; ?>">
                                <?php echo $block->title; ?>
                                <p class="text-muted"><?php echo $block->description; ?></p>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Utilizados</label><br>
                    <ul id="sortable2" class="connectedSortable sortable">
                        <?php foreach ($used_blocks as $block_id) { ?>
                            <li class="ui-state-highlight" data-blockid="<?php echo $block_id; ?>">
                                <?php echo $blocks[$block_id]->title; ?>
                                <p class="text-muted"><?php echo $blocks[$block_id]->description; ?></p>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>-->

        <div class="row">
            <div class="col-md-12">
                <h3>Idiomas activos en la web <small>Drag and drop</small></h3>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Sin utilizar</label><br>
                    <input type="hidden" name="selected_languages" id="selected_languages"
                           value="<?php echo (isset($data->selected_langs)) ? $data->selected_langs : ''; ?>"/>
                    <ul id="sortable5" class="connectedSortable sortable">
                        <?php foreach ($selected_langs as $lang_id => $lang) {
                            if (in_array($lang, $used_langs)) {
                                continue;
                            } ?>
                            <li class="ui-state-default" data-langid="<?php echo $lang_id; ?>">
                                <?php echo $lang->name; ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Utilizados</label><br>

                    <ul id="sortable6" class="connectedSortable sortable">
                        <?php foreach ($used_langs as $lang_id) { ?>
                            <li class="ui-state-highlight" data-langid="<?php echo $lang_id->id; ?>">
                                <?php echo $lang_id->name; ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h3>Mail</h3>
            </div>

            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'mail_from_mail', 'objects' => ['mail_from_mail' => [
                'label' => 'Desde email (info@yourweb.com)',
                'value' => isset($data->mail_from_mail) ? $data->mail_from_mail : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'mail_from_name', 'objects' => ['mail_from_name' => [
                'label' => 'Desde nombre (Nombre empresa)',
                'value' => isset($data->mail_from_name) ? $data->mail_from_name : '',
            ]
            ])); ?>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h3>Otros</h3>
            </div>

            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'max_upload_size', 'objects' => ['max_upload_size' => [
                'label' => 'Máximo peso para subir archivos (KB)',
                'value' => isset($data->max_upload_size) ? $data->max_upload_size : '',
            ]
            ])); ?>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h3>Imágenes</h3>
            </div>
            <?php echo view('admin/input/text', array('col' => 3, 'name' => 'thumbn_size_high', 'objects' => ['thumbn_size_high' => [
                'label' => 'Resolución Alta (ancho)',
                'value' => isset($data->thumbn_size_high) ? $data->thumbn_size_high : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 3, 'name' => 'thumbn_size_medium', 'objects' => ['thumbn_size_medium' => [
                'label' => 'Resolución Media (ancho)',
                'value' => isset($data->thumbn_size_medium) ? $data->thumbn_size_medium : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 3, 'name' => 'thumbn_size_low', 'objects' => ['thumbn_size_low' => [
                'label' => 'Resolución Baja (ancho)',
                'value' => isset($data->thumbn_size_low) ? $data->thumbn_size_low : '',
            ]
            ])); ?>
			<?php echo view('admin/input/text', array('col' => 2, 'name' => 'thumbn_size_lowest', 'objects' => ['thumbn_size_lowest' => [
					'label' => 'Resolución extra Baja (ancho)',
					'value' => isset($data->thumbn_size_lowest) ? $data->thumbn_size_lowest : '',
			]
			])); ?>
            <?php echo view('admin/input/enum', array('col' => 3, 'name' => 'auto_shortpixel', 'objects' => ['auto_shortpixel' => [
                'label' => 'Aplicar shortpixel al subir',
                'value' => isset($data->auto_shortpixel) ? $data->auto_shortpixel : '',
            ]
            ], 'values' => ['0' => 'No', '1' => 'Si'])); ?>

        </div>
        <div class="row">
			<div class="col-md-12">
				<h3>Favicon</h3>
			</div>
			<?php echo view('admin/input/image', array('col' => 3, 'name' => 'favicon', 'objects' => ['favicon' => [
					'label' => 'Favicon',
					'value' => isset($data->favicon) ? $data->favicon : '',
			]
			])); ?>
		</div>
        <div class="row">
            <div class="col-md-12">
                <h3>SEO general</h3>
            </div>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'og_site_name', 'objects' => ['og_site_name' => [
                'label' => 'og:site_name',
                'value' => isset($data->og_site_name) ? $data->og_site_name : '',
            ]
            ])); ?>
            <?php echo view('admin/input/text', array('col' => 6, 'name' => 'og_image', 'objects' => ['og_image' => [
                'label' => 'og:image',
                'value' => isset($data->og_image) ? $data->og_image : '',
            ]
            ])); ?>
        </div>
        <div class="row">
            <div class="col-lg-2">
                <div class="form-group">
                    <br>
                    <?php echo form_button(array('id'=>'generar', 'type'=>'submit', 'class'=>'btn bg-black btn-flat', 'content' => '<i class="fa fa-save fa-fw"></i> Guardar'))?>
                </div>
            </div>
        </div>

        <?php echo form_close(); ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        <?php foreach($ids_codemirror as $id) { ?>
        get_editor('<?php echo $id;?>');
        <?php } ?>
    });

    function get_editor(editor_id){
        editor = CodeMirror.fromTextArea(document.getElementById(editor_id), {
            lineNumbers: true,
            matchBrackets: true,
            mode: "text/html",
            indentUnit: 2,
            indentWithTabs: true,
            enterMode: "keep",
            tabMode: "shift"
        });
    }

    $(function () {
        $("#sortable1, #sortable2").sortable({
            connectWith: ".connectedSortable"
        }).disableSelection();

        $("#sortable3, #sortable4").sortable({
            connectWith: ".connectedSortable2"
        }).disableSelection();

        $("#sortable5, #sortable6").sortable({
            connectWith: ".connectedSortable"
        }).disableSelection();

        $('#myform').on('submit', function () {
            var blocks = new Array;
            $('#sortable2 li').each(function () {
                blocks.push($(this).data('blockid'));
            });
            $('#blocks').val(blocks.join(','));

            var admin_menu_items = new Array;
            $('#sortable4 li').each(function () {
                admin_menu_items.push($(this).data('admin_menu_id'));
            });
            $('#admin_menu').val(admin_menu_items.join(','));

            var langs = new Array;
            $('#sortable6 li').each(function () {
                langs.push($(this).data('langid'));
            });
            $('#selected_languages').val(langs.join(','));
        });

    });
</script>

<style>
    .sortable{
        background: transparent;
    }

    .sortable {
        border: 1px solid #eee;
        min-height: 200px;
        list-style-type: none;
        margin: 0;
        padding: 5px 0 0 0;
        float: left;
        margin-right: 10px;
        min-width: 100% !important;
        height: 300px;
        overflow-y: auto;
    }

    .sortable li, .sortable li {
        margin: 0 5px 5px 5px;
        padding: 5px;
        font-size: 1.2em;
        min-width: 150px !important;
        cursor: move;
        border: 2px dotted #eee;
    }
</style>


<?= $this->endSection() ?>
