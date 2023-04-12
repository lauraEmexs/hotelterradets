<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<style>
    .col-md-3 {
        padding: 0;
    }
    .table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td {
        border-top: 1px solid #f4f4f4;
        vertical-align: middle;
    }
    #update_default > div:nth-child(1) > div > table > tbody > tr:nth-child(1) > td:nth-child(4) > div {
        margin-top: 15px;
    }
</style>

<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-globe fa-fw"></i><?php echo $title_section; ?>
    </div>
    <div class="panel-body">
        <!--<form action="/admin/idiomas/update_actived" method="POST" name="update_default"/> -->
        <?php echo form_open('admin/idiomas/update_actived', array('role' => 'form', 'id' => 'update_default', 'name' => 'update_default')); ?>

        <?php $session = \Config\Services::session();
        if($session->getFlashdata('message')):?>
            <?php echo $session->getFlashdata('message')?>
        <?php endif;?>
        <div class="row">
            <div class="col-lg-12">
                <p>Seleccionar los idiomas activados para el sitio:</p>
                <table class="table table-striped table-condensed">
                    <thead>
                    <tr>
                        <td>Idioma</td><td>Gestor</td><!--<td>Web</td>-->
                    </tr>
                    </thead>
                    <?php foreach ($languages as $language):?>
                        <tr>
                            <td><?php echo $language->name; ?></td>
                            <td><input type="checkbox" name="actived[]"
                                    <?= (ENVIRONMENT == 'production') ? 'onclick="return false;"' : '' ?>
                                       value="<?php echo $language->id;?>" <?php echo ($language->actived)?"checked":"";?>
                                    <?php echo $language->default == 1 ? 'onclick="return false;"' : "" ;?>></td>
                            <!--<td><input type="checkbox" name="actived_web[]" value="<?php echo $language->id;?>" <?php echo ($language->actived_web)?"checked":"";?>></td>-->
                        </tr>
                    <?php endforeach;?>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <a class="btn bg-black btn-flat" href="#" onclick="document.update_default.submit();"><i class="fa fa-save fa-fw"></i> Guardar</a>
            </div>
        </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>