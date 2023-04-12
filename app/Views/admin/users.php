<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-list fa-fw"></i> <?php echo $title_section;?>
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
        <?php echo anchor('admin/users/uedit/0', '<i class="fa fa-plus fa-fw"></i> Añadir', 'class="btn bg-black btn-flat"');?>
    </div>
    <!-- /.panel-body -->
</div>
<!-- /.panel -->
<?= $this->endSection() ?>