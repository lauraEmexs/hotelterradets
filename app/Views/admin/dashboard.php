<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="panel panel-default">
	<div class="panel-heading">
		<i class="fa fa-list fa-fw"></i> Dashboard
	</div>
	<!-- /.panel-heading -->
	<div class="panel-body">
        <div class="tab-content">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tabMain" data-toggle="tab">Informe Web</a></li>
                <li><a href="#tabHistory" data-toggle="tab">Historial de cambios</a></li>
            </ul>

            <div class="tab-pane fade in active" id="tabMain">
                <div class="page-parts">
                    <?php
                    foreach ($site_preferences as $preferences) {
                        if (!$preferences->scripts_dashboard) {
                            continue;
                        }

                        echo '<h4>'. $preferences->domain .'</h4>';
                        echo $preferences->scripts_dashboard;
                    }
                    ?>
                </div>
            </div>
            <div class="tab-pane fade" id="tabHistory">
                <div class="page-parts">
                    <h4>Historial de cambios</h4>
                    <div class="table-responsive">
                        <?php echo $table->generate();?>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<!-- /.panel-body -->
</div>
<!-- /.panel -->
<script>
	$(document).ready(function() {
		$('#last_changes').DataTable({
			'paging'      : true,
			'lengthChange': true,
			'searching'   : true,
			'ordering'    : true,
			'info'        : true,
			'autoWidth'   : true,
			"order": [[ 6, "desc" ]],
			"language": {
				"url": "<?php echo base_url('assets/themes/adminlte/bower_components/datatables.net/i18n/dataTables.Spanish.json'); ?>"
			}
		})
	} );
</script>
<?= $this->endSection() ?>