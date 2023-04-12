<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-list fa-fw"></i> Listado de <?php echo $title_section;?>
    </div>
    <!-- /.panel-heading -->
    <div class="panel-body">
        <?php $session = \Config\Services::session();
        if($session->getFlashdata('message')):?>
            <?php echo $session->getFlashdata('message')?>
        <?php endif;?>
        <div class="table-responsive">
            <?php echo $table->generate();?>
        </div>
        <?php echo anchor('admin/cmsBlock/create_cms_block', '<i class="fa fa-plus fa-fw"></i> Añadir', 'class="btn bg-black btn-flat"');?>
    </div>
    <!-- /.panel-body -->
</div>
<!-- /.panel -->


<script>
    $(function() {
        $('.pause-toggle').change(function() {
            window.location.replace($(this).parents('a').attr('href'));
        })
    })
</script>
<?= $this->endSection() ?>