<?php

//$logged_user = $this->session->userdata('logged_user');

?>
<!-- AdminLTE 2.4.5 -->
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Administración</title>
	<!-- Tell the browser to be responsive to screen width -->
	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<link rel="shortcut icon" href="<?php echo base_url(THEME_PATH .'img/favicon.png'); ?>" type="image/png"/>

	<?php
	if (!empty($meta))
		foreach ($meta as $name => $content) {
			echo "\n\t\t";
			?><meta name="<?php echo $name; ?>" content="<?php echo $content; ?>" /><?php
		} echo "\n";
	?>

	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css'); ?>">
	<!-- Font Awesome -->
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/bower_components/font-awesome/css/font-awesome.min.css'); ?>">
	<!-- Ionicons -->
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/bower_components/Ionicons/css/ionicons.min.css'); ?>">
	<!-- Select2 -->
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/bower_components/select2/dist/css/select2.min.css'); ?>">
	<!-- Bootstrap Toggle -->
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/bootstrap-toggle-master/css/bootstrap-toggle.min.css'); ?>">
	<!-- Simplelightbox -->
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/simplelightbox/simplelightbox.min.css'); ?>">
	<!-- Nestable2 -->
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/nestable2/jquery.nestable.min.css'); ?>">
	<!-- Datatables -->
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css'); ?>">
	<!-- Google SERP preview -->
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/jQuery-SEO-Preview-Plugin/css/jquery-seopreview.css'); ?>">
	<!-- bootstrap datepicker -->
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css'); ?>">
	<!-- colorpicker -->
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/spectrum-2.0.0/dist/spectrum.min.css'); ?>">
	<!-- timepicker -->
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/jquery-timepicker-1.3.5/jquery.timepicker.min.css'); ?>">
	<!-- Theme style -->
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/css/AdminLTE.min.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/css/skins/skin-black-light.min.css'); ?>">
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/css/custom.css?07022019'); ?>">

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->

	<!-- Google Font -->
	<link rel="stylesheet"
		  href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

	<link href="<?php echo base_url('assets/themes/adminlte/flags/flags.css'); ?>" rel="stylesheet">

	<!-- CodeMirror -->
	<script src="<?php echo base_url('assets/themes/adminlte/codemirror-5.41.0/lib/codemirror.js'); ?>"></script>
	<link rel="stylesheet" href="<?php echo base_url('assets/themes/adminlte/codemirror-5.41.0/lib/codemirror.css'); ?>">
	<script src="<?php echo base_url('assets/themes/adminlte/codemirror-5.41.0/addon/edit/matchbrackets.js'); ?>"></script>
	<script src="<?php echo base_url('assets/themes/adminlte/codemirror-5.41.0/mode/htmlmixed/htmlmixed.js'); ?>"></script>
	<script src="<?php echo base_url('assets/themes/adminlte/codemirror-5.41.0/mode/xml/xml.js'); ?>"></script>
	<script src="<?php echo base_url('assets/themes/adminlte/codemirror-5.41.0/mode/javascript/javascript.js'); ?>"></script>
	<script src="<?php echo base_url('assets/themes/adminlte/codemirror-5.41.0/mode/css/css.js'); ?>"></script>
	<script src="<?php echo base_url('assets/themes/adminlte/codemirror-5.41.0/mode/clike/clike.js'); ?>"></script>
	<script src="<?php echo base_url('assets/themes/adminlte/codemirror-5.41.0/mode/php/php.js'); ?>"></script>

	<!-- CKEditor -->
	<script src="<?php echo base_url('assets/themes/adminlte/ckeditor-4.11/ckeditor.js'); ?>"></script>

	<!--<link rel="shortcut icon" href="<?php echo base_url(THEME_PATH.'img/logo/xxx.png'); ?>" type="image/x-icon">-->

	<?php
    if(!empty ($css)) {
        foreach ($css as $file) {
            echo "\n\t\t";
            ?>
            <link rel="stylesheet" href="<?php echo $file; ?>" type="text/css" /><?php
        }
        echo "\n\t";
    }
	?>

	<!-- REQUIRED JS SCRIPTS -->
	<!-- jQuery 3 -->
	<script src="<?php echo base_url('assets/themes/adminlte/bower_components/jquery/dist/jquery.min.js'); ?>"></script>
	<script src="<?php echo base_url('assets/themes/adminlte/js/jquery-migrate.min.js'); ?>"></script>
	<!-- Bootstrap 3.3.7 -->
	<script src="<?php echo base_url('assets/themes/adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js'); ?>"></script>
	<!-- AdminLTE App -->
	<script src="<?php echo base_url('assets/themes/adminlte/js/adminlte.min.js'); ?>"></script>
	<!-- jQueryUI -->
	<script src="<?php echo base_url('assets/themes/adminlte/plugins/jQueryUI/jquery-ui.min.js'); ?>"></script>
	<!-- jQuery-sortable -->
	<script src="<?php echo base_url('assets/themes/adminlte/bower_components/jquery-ui/ui/minified/sortable.min.js'); ?>"></script>
	<!-- Moment -->
	<script src="<?php echo base_url('assets/themes/adminlte/bower_components/moment/min/moment-with-locales.min.js'); ?>"></script>
	<!-- Select2 -->
	<script src="<?php echo base_url('assets/themes/adminlte/bower_components/select2/dist/js/select2.full.min.js'); ?>"></script>
	<!-- Bootstrap Toggle -->
	<script src="<?php echo base_url('assets/themes/adminlte/bootstrap-toggle-master/js/bootstrap-toggle.min.js'); ?>"></script>
	<!-- Simplelightbox -->
	<script src="<?php echo base_url('assets/themes/adminlte/simplelightbox/simple-lightbox.min.js'); ?>"></script>
	<!-- Nestable2 -->
	<script src="<?php echo base_url('assets/themes/adminlte/nestable2/jquery.nestable.min.js'); ?>"></script>
	<!-- Datatables -->
	<script src="<?php echo base_url('assets/themes/adminlte/bower_components/datatables.net/js/jquery.dataTables.min.js'); ?>"></script>
	<script src="<?php echo base_url('assets/themes/adminlte/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js'); ?>"></script>
	<!-- Google SERP preview -->
	<script src="<?php echo base_url('assets/themes/adminlte/jQuery-SEO-Preview-Plugin/js/jquery-seopreview.min.js'); ?>"></script>
	<!-- bootstrap datepicker -->
	<script src="<?php echo base_url('assets/themes/adminlte/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js'); ?>"></script>
	<!-- colorpicker -->
	<script src="<?php echo base_url('assets/themes/adminlte/spectrum-2.0.0/dist/spectrum.min.js'); ?>"></script>
	<!-- timepicker -->
	<script src="<?php echo base_url('assets/themes/adminlte/jquery-timepicker-1.3.5/jquery.timepicker.min.js'); ?>"></script>

	<?php
    if(!empty($js)) {
        foreach ($js as $file) {
            echo "\n\t\t";?>
            <script src="<?php echo $file; ?>"></script>
            <?php
        } echo "\n\t";
    }
	?>

</head>

<body class="skin-black-light sidebar-mini">
<div class="wrapper">

	<!-- Main Header -->
	<header class="main-header">

		<!-- Logo -->
		<a href="<?php echo base_url('admin'); ?>" class="logo">
			<!-- mini logo for sidebar mini 50x50 pixels -->
			<span class="logo-mini"><b><?php echo config('App')->projectShortName; ?></b></span>
			<!-- logo for regular state and mobile devices -->
			<span class="logo-lg"><b><?php echo config('App')->projectName; ?></b></span>
		</a>

		<!-- Header Navbar -->
		<nav class="navbar navbar-static-top" role="navigation">
			<!-- Sidebar toggle button-->
			<a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
				<span class="sr-only">Toggle navigation</span>
			</a>
			<!-- Navbar Right Menu -->
			<div class="navbar-custom-menu">
				<ul class="nav navbar-nav">

					<!-- User Account Menu -->
					<li class="dropdown user user-menu">
						<!-- Menu Toggle Button -->
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<!-- hidden-xs hides the username on small devices so only the image appears. -->
							<span class="hidden-xs"><?php echo ucfirst($logged_user->username) ?></span>
						</a>
						<ul class="dropdown-menu">
							<!-- Menu Footer-->
							<li class="user-footer">
								<div class="pull-right">
									<a href="<?php echo base_url('admin/auth/logout'); ?>" class="btn btn-default btn-flat">Sign out</a>
								</div>
							</li>
						</ul>
					</li>

				</ul>
			</div>
		</nav>
	</header>

	<!-- Left side column. contains the logo and sidebar -->
	<aside class="main-sidebar">

		<!-- sidebar: style can be found in sidebar.less -->
		<section class="sidebar">

			<!-- Sidebar Menu -->
			<ul class="sidebar-menu" data-widget="tree">
				<li class="header">Gestión de contenidos</li>

				<?php
				$allowed_domains = config('App')->allowedDomains;
				if ($logged_user->group == 'domain') {
					foreach ($allowed_domains as $key => $allowed_domain) {
						if ($logged_user->domain != $allowed_domain) {
							unset($allowed_domains[$key]);
						}
					}
				}
				foreach ($allowed_domains as $domain_menu) : ?>
					<?php
                    $domain_preferences = $all_domain_preferences[$domain_menu];
					$domain_url_params = '';
					if (($menu == 'contenidos' || $menu == 'cmsBlock') && $selected_domain == $domain_menu) {
						$active = 'active';
					} else {
						$active = '';
					}

					?>
					<?php if (count($allowed_domains) > 1) :
						$domain_url_params = '?domain='. $domain_menu;
						?>
						<li class="treeview <?php echo $active; ?>">

						<a href="#"><i class="fa fa-globe"></i> <span><?php echo $domain_menu; ?></span>
							<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
						</a>
						<ul class="treeview-menu">
					<?php endif; ?>

					<?php

					$selected_admin_menu = (isset($domain_preferences->admin_menu)) ? explode(',', $domain_preferences->admin_menu) : [];

					foreach ($selected_admin_menu as $item) {

						if (isset($admin_menu_items[$item])) {
							$this_slug = explode('/', $item);
							$this_slug = $this_slug[count($this_slug)-1];
							?>
							<li class="<?php echo ($slug == $this_slug && $active != '') ? 'active' : ''; ?>">
								<a href="<?php echo base_url('admin/'. $item); ?><?php echo $domain_url_params; ?>"
								   class="pages"><i class="fa fa-th-list"></i> <?php echo $admin_menu_items[$item]; ?></a>
							</li>
						<?php }
					}

					?>
					<?php if (count($allowed_domains) > 1) : ?>
						</ul>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>

				<!-- End content section -- >
				<li class="<?php echo ($slug == 'formdata') ? 'active' : ''; ?>">
					<a href="<?php echo base_url('admin/formSubmits'); ?>" class="formdata"><i class="fa fa-book"></i> <span>Registro de formularios</span></a>
				</li>
				-->

				<?php if ($logged_user->group == 'admin') : ?>
					<li class="header">Idiomas</li>
					<li class="<?php echo ($slug == 'idiomas_activos') ? 'active' : ''; ?>">
						<a href="<?php echo base_url('admin/idiomas/listado'); ?>" class="idiomas_activos"><i class="fa fa-language"></i> <span>Idiomas activos</span></a>
					</li>
					<li class="<?php echo ($slug == 'idiomas_diccionarios') ? 'active' : ''; ?>">
						<a href="<?php echo base_url('admin/idiomas/dictionary'); ?>" class="idiomas_diccionarios"><i class="fa fa-book"></i> <span>Diccionarios</span></a>
					</li>
					<li class="header">Administración</li>
					<li class="<?php echo ($slug == 'users') ? 'active' : ''; ?>">
						<a href="<?php echo base_url('admin/users'); ?>" class="users"><i class="fa fa-users"></i> <span>Usuarios</span></a>
					</li>
					<li class="<?php echo ($slug == 'cms_blocks') ? 'active' : ''; ?>">
						<a href="<?php echo base_url('admin/cmsBlock/cms_blocks'); ?>" class="cms_blocks"><i class="fa fa-th-large"></i> <span>CMS Bloques</span></a>
					</li>

					<?php if (count($allowed_domains) > 1) : ?>
						<li class="treeview <?php echo ($slug == 'preferences') ? 'active' : ''; ?>">
							<a href="#"><i class="fa fa-globe"></i> <span>Preferencias</span>
								<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
							</a>
							<ul class="treeview-menu">
								<?php foreach ($allowed_domains as $domain_menu) :
									$domain_url_params = '?domain='. $domain_menu;
									?>

									<li class="<?php echo ($slug == 'preferences' && $selected_domain == $domain_menu) ? 'active' : ''; ?>">
										<a href="<?php echo base_url('admin/preferences'); ?><?php echo $domain_url_params; ?>" class="preferences"><i class="fa fa-cogs"></i> <?php echo $domain_menu; ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						</li>
					<?php else : ?>
						<li class="<?php echo ($slug == 'preferences') ? 'active' : ''; ?>">
							<a href="<?php echo base_url('admin/preferences'); ?>" class="preferences"><i class="fa fa-cogs"></i> Preferencias</a>
						</li>
				<li class="<?php echo ($slug == 'preferencesblocks') ? 'active' : ''; ?>">
                            <a href="<?php echo base_url('admin/preferencesblocks'); ?>" class="preferences"><i class="fa fa-object-group"></i> Preferencias de Bloques</a>
                        </li>
					<?php endif; ?>
					<li class="treeview <?php echo ($slug == 'imageoptimizer' || $slug == 'thumbregen') ? 'active' : ''; ?>">
						<a href="#"><i class="fa fa-book"></i> <span>Herramientas</span>
							<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
						</a>
						<ul class="treeview-menu">
							<li class="<?php echo ($slug == 'allmetas') ? 'active' : ''; ?>">
								<a href="<?php echo base_url('admin/dynamic/allmetas'); ?>" class="imageoptimizer"><i class="fa fa-file-image-o"></i> Exportar\importar todas Metas</a></li>
							<li class="<?php echo ($slug == 'imageoptimizer') ? 'active' : ''; ?>">
								<a href="<?php echo base_url('admin/imageoptimizer'); ?>" class="imageoptimizer"><i class="fa fa-file-image-o"></i> Optimizador de imágenes</a></li>
							<li class="<?php echo ($slug == 'thumbregen') ? 'active' : ''; ?>">
								<a href="<?php echo base_url('admin/thumbregen'); ?>" class="imageoptimizer"><i class="fa fa-file-image-o"></i> Regenerar miniaturas</a></li>
							<li class="<?php echo ($slug == 'webpregen') ? 'active' : ''; ?>">
								<a href="<?php echo base_url('admin/webpregen'); ?>" class="imageoptimizer"><i class="fa fa-file-image-o"></i> Regenerar WEBP</a></li>
						</ul>
					</li>
				<?php elseif ($logged_user->group == 'editor') : ?>
                    <li class="header">Idiomas</li>
                    <li class="<?php echo ($slug == 'idiomas_diccionarios') ? 'active' : ''; ?>">
                        <a href="<?php echo base_url('admin/idiomas/dictionary'); ?>" class="idiomas_diccionarios"><i class="fa fa-book"></i> <span>Diccionarios</span></a>
                    </li>
					<li class="treeview <?php echo ($slug == 'imageoptimizer' || $slug == 'thumbregen' || $slug == 'allmetas') ? 'active' : ''; ?>">
						<a href="#"><i class="fa fa-book"></i> <span>Herramientas</span>
							<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
						</a>
						<ul class="treeview-menu">
							<li class="<?php echo ($slug == 'allmetas') ? 'active' : ''; ?>">
								<a href="<?php echo base_url('admin/dynamic/allmetas'); ?>" class="imageoptimizer"><i class="fa fa-file-image-o"></i> Exportar\importar todas Metas</a></li>
							<li class="<?php echo ($slug == 'thumbregen') ? 'active' : ''; ?>">
								<a href="<?php echo base_url('admin/thumbregen'); ?>" class="imageoptimizer"><i class="fa fa-file-image-o"></i> Regenerar miniaturas</a></li>
							<li class="<?php echo ($slug == 'webpregen') ? 'active' : ''; ?>">
								<a href="<?php echo base_url('admin/webpregen'); ?>" class="imageoptimizer"><i class="fa fa-file-image-o"></i> Regenerar WEBP</a></li>
						</ul>
					</li>
				<?php endif; ?>

				<li class="treeview">
					<a href="#"><i class="fa fa-book"></i> <span>Optimizar Img</span>
						<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
					</a>
					<ul class="treeview-menu">
						<li class="<?php echo ($slug == 'webpregen') ? 'active' : ''; ?>">
							<a href="<?php echo base_url('admin/webpregen'); ?>" class="imageoptimizer"><i class="fa fa-file-image-o"></i> Generador formato WEBP</a></li>
						<li class="">
							<a target="_blank" href="https://shortpixel.com/online-image-compression" class="imageoptimizer"><i class="fa fa-file-image-o"></i> Shortpixel</a>
					</ul>
				</li>
				<li class="treeview">
					<a href="#"><i class="fa fa-book"></i> <span>Documentación</span>
						<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>
					</a>
					<ul class="treeview-menu">
						<li><a href="<?php echo base_url('documentacion/GestordecontenidosEmexs.pdf'); ?>" class="class_diagram" target="_blank"><i class="fa fa-bookmark-o"></i> Manual Gestor</a></li>
					</ul>
				</li>

			</ul>
			<!-- /.sidebar-menu -->
		</section>
		<!-- /.sidebar -->
	</aside>

	<!-- Content Wrapper. Contains page content -->
	<div class="content-wrapper">
		<!-- Content Header (Page header) -->
		<section class="content-header">
			<h1><?php echo $title_section; ?> <?php echo isset($domain) ? ' <small>('. $domain .')</small>' : ''; ?></h1>
			<ol class="breadcrumb">
				<li><a href="<?php echo base_url('admin'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
				<?php
				if ($menu == 'contenidos' || $menu == 'preferences') :
					?>
					<li><a href="#"><i class="fa fa-globe"></i> <?php echo $selected_domain; ?></a></li>
					<li><a href="<?php echo base_url('admin/dynamic/dlist/'. $slug .'?domain='. $selected_domain); ?>"><i class="fa fa-th-list"></i> <?php echo $title_section; ?></a></li>
				<?php elseif($menu == 'idiomas' && $slug != $menu2) : ?>
					<li><a href="<?php echo base_url('admin/idiomas/dictionary'); ?>"><i class="fa fa-book"></i> Diccionarios</a></li>
				<?php else : ?>
					<!--<li><a href="<?php echo base_url('admin/'. $slug); ?>"><i class="fa fa-th-list"></i> <?php echo $title_section; ?></a></li>-->
				<?php endif; ?>
				<li class="active"><?php
					if (isset($id)) {
						echo ($id != 0) ? "Editar" : "Nuevo";
					}
					?> <?php echo $title_section; ?></li>
			</ol>
		</section>

		<!-- Main content -->
		<section class="content container-fluid">

            <?= $this->renderSection('content') ?>

		</section>
		<!-- /.content -->
	</div>
	<!-- /.content-wrapper -->

	<!-- Main Footer -->
	<footer class="main-footer">
		<!-- To the right -->
		<div class="pull-right hidden-xs">
			Version 2.5
		</div>
		<!-- Default to the left -->
		<strong>Copyright &copy; <?php echo date('Y'); ?> <a href="https://emexs.es/">Emexs Marketing</a>.</strong> All rights reserved.
	</footer>
	
	
	<!-- Modal Galeria-->
	<div class="modal fade" id="galeria" tabindex="-1" role="dialog" aria-labelledby="galeria" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title" id="exampleModalLabel">Biblioteca de medios</h3>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="gallery-controls">
						<a class="gallery-home" href="javascript:void(null)" onclick="navigate_gallery_home ()">home</a>
						<a class="gallery-back" href="javascript:void(null)" onclick="navigate_gallery_back ()">back</a>
						<div class="gallery-path">path</div>
						<input type="text" class="gallery-search" onchange="navigate_gallery_search ()" onkeyup="navigate_gallery_search ()"  placeholder="Buscar" />
					</div>
					<div class="row gallery-list">
					</div>
				</div>
				<div class="modal-footer">
					<div class="gallery-paginator"></div>
					<button id="seleccionar" type="button" class="btn btn-secondary gallery-seleccionar" data-dismiss="modal">Subir imagen</button>
				</div>
			</div>
		</div>
	</div>

</div>
<!-- /#wrapper -->

<script>
	var lightbox = $('a.image_url').simpleLightbox();
	$(document).ready(function() {
		$('table.datatable').each(function(){
			var ordering = $(this).hasClass('datatable-sortable');
			$('table.datatable').DataTable({
				'paging'      : true,
				'lengthChange': true,
				'searching'   : true,
				'ordering'    : ordering,
				'info'        : true,
				'autoWidth'   : true,
				"language": {
					"url": "<?php echo base_url('assets/themes/adminlte/bower_components/datatables.net/i18n/dataTables.Spanish.json'); ?>"
				},
				'stateSave': true,
			})
		});
		$('#galeria').on('show.bs.modal', function (event) {
			var button = $(event.relatedTarget);
			var folder = button.data('folder');
			var name = button.data('name');
			window.gallery_home = folder;
			load_gallery_content (folder, name)
			event.stopPropagation();
		})
		$('#seleccionar').click(function (event) {
			document.getElementById(selected_file_target).value = selected_file_path;
		})
	})
	function load_gallery_content (folder,name) {
		$('#galeria .modal-body #preview').hide();
		$('.gallery-path').html('');
		var path = folder.split('/');
		var current_path = '';
		for (i=0; i<path.length-1; i++) {
			if ($('.gallery-path').html() != '') {
				$('.gallery-path').append (' / ');
				current_path = current_path + '/';
			}
			current_path = current_path + path[i];
			$('.gallery-path').append ('<a href="javascript:void(null)" onclick="load_gallery_content (\''+current_path+'\', \''+window.gallery_name+'\')">'+path[i]+'</a>');
		}
		if ($('.gallery-path').html() != '') {
			$('.gallery-path').append (' / ');
		}
		$('.gallery-path').append (path[i]);
		$.get( "<?php echo url_to('Admin\Gallery','files') ?>/"+folder, function( data ) {
			window.gallery_files = data.files;
			window.gallery_unfiltered = data.files;
			window.gallery_folder = folder;
			window.gallery_name = name;
			$('#galeria .modal-footer .gallery-paginator').html('');
			for (i = 0; i < window.gallery_files.length/12; i++) {
				$('#galeria .modal-footer .gallery-paginator').append('<a class="gallery-paginator-'+i+'" href="javascript:void(null)" onclick="display_gallery_content ('+i+')">'+(i+1)+'</a>');
			}
			display_gallery_content (0);
		}, "json" );
	}
	function display_gallery_content (page) {
		$('#galeria #seleccionar').removeClass('active');
		$('.gallery-paginator a').removeClass('current');
		$('.gallery-paginator-'+page).addClass('current');
		$('#galeria .modal-body .gallery-list').html('');
		max_item = window.gallery_files.length;
		if (max_item > (page+1)*12) max_item = (page+1)*12;
		for (i = page*12; i < max_item; i++) if (window.gallery_files[i]){
			if (window.gallery_files[i].type == 'folder') {
				bg_image = '/assets/themes/adminlte/img/carpeta.svg';
				$('#galeria .modal-body .gallery-list').append('<div class="col-xs-2"><a class="gallery-folder" style="background-image: url(\''+bg_image+'\')" href="javascript:void(null)" onclick="load_gallery_content (\''+window.gallery_folder+'/'+window.gallery_files[i].name+'\', \''+window.gallery_name+'\')"><span class="gallery-item-name">'+window.gallery_files[i].name+'</span></a></div>');
			} else {
				<?php if(isset($this->data['domain'])): ?>
				if (window.gallery_files[i].type == 'image') bg_image = '<?php echo '/assets/themes/'. $this->data['domain'] ?>/'+window.gallery_folder+'/'+window.gallery_files[i].name;
				$('#galeria .modal-body .gallery-list').append('<div class="col-xs-2"><a class="gallery-file" style="background-image: url(\''+bg_image+'\')" href="javascript:void(null)" onclick="preselect_gallery_content(\'<?php echo 'assets/themes/'. $this->data['domain'] ?>/'+window.gallery_folder+'/'+window.gallery_files[i].name+'\', \''+window.gallery_name+'\', this)"><span class="gallery-item-name">'+window.gallery_files[i].name+'</span></a></div>');
				<?php endif; ?>
			}
		}
	}
	function preselect_gallery_content (file, name, obj) {
		$('#galeria #seleccionar').addClass('active');
		$('.gallery-file').removeClass('current');
		$(obj).addClass('current');
		selected_file_path = file;
		selected_file_target = name+'_hidden';
	}
	function navigate_gallery_search () {
		window.gallery_files = [];
		for (i=0; i<window.gallery_unfiltered.length; i++) {
			if (window.gallery_unfiltered[i].name.indexOf ($('.gallery-search').val()) != -1) {
				window.gallery_files.push (window.gallery_unfiltered[i]);
			}
		};
		$('#galeria .modal-footer .gallery-paginator').html('');
		for (i = 0; i < window.gallery_files.length/12; i++) {
			$('#galeria .modal-footer .gallery-paginator').append('<a class="gallery-paginator-'+i+'" href="javascript:void(null)" onclick="display_gallery_content ('+i+')">'+(i+1)+'</a>');
		}
		display_gallery_content (0);
	}
	function navigate_gallery_home () {
		load_gallery_content (window.gallery_home, window.gallery_name)
	}
	function navigate_gallery_back () {
		if (window.gallery_home.length > window.gallery_folder.split ('/').slice(0,-1).join('/').length) return;
		load_gallery_content (window.gallery_folder.split ('/').slice(0,-1).join('/'), window.gallery_name)
	}
</script>
</body>
</html>
