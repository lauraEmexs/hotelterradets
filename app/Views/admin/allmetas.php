<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-gear fa-fw"></i><?php echo $title_section; ?>
    </div>
    <div class="panel-body">
        <?php $session = \Config\Services::session();
        if($session->getFlashdata('message')):?>
            <?php echo $session->getFlashdata('message')?>
        <?php endif;?>

        <div class="row">
            <div class="col-lg-6">
                <?php echo form_open_multipart('admin/dynamic/allmetas'.$domain_url_params, array('role'=>'form',)); ?>
                    <input type="hidden" name="export" value="1">
                    <p><input type="submit" class="btn btn-default" value="Exportar todos Metas" /></p>
                </form>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-3">
                <?php echo form_open_multipart('admin/dynamic/allmetas'. $domain_url_params, array('role'=>'form',)); ?>
                    <div class="row">
                        <?php echo view('admin/input/document', array('col' => 12, 'name' => 'metas', 'objects' => ['metas' => [
                            'label' => 'Metas',
                            'value' => '',
                        ]
                        ])); ?>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <p><input type="submit" class="btn btn-default" name="import" value="Importar todos Metas" /></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?= $this->endSection() ?>