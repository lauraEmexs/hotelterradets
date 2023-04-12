<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<?php
$password = '';
if ($id == 0) {
    $password = random_string('alnum', 16);
}
$domains = array_combine(config('App')->allowedDomains, config('App')->allowedDomains);
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-book fa-fw"></i> <?php echo ($id != 0) ? "Editar":"Nuevo";?> <?php echo $title_section;?>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-lg-12">
                <?php echo form_open_multipart('admin/users/usave/'. $id, array('role'=>'form')); ?>
                <div class="row">
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo form_label('Username','Username');?>
                            <?php echo form_input(array('name'=>'username','id'=>'username','value'=>isset($user->username)?$user->username:'','type'=>'text','class'=>'form-control') );?>
                            <?php echo form_error('username');?>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo form_label('Group','Group');?>
                            <?php echo form_dropdown('group',
                                array('admin'=>'Admin','editor'=>'Editor','limited'=>'Limited','domain'=>'Usuario de dominio'/*,'user'=>'User'*/),
                                isset($user->group)?$user->group:'','class=form-control');?>
                            <?php echo form_error('group');?>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo form_label('Password (leave blank for no change)','Password');?>
                            <?php echo form_input(array('name'=>'password','id'=>'password','value'=>$password,'type'=>'text','class'=>'form-control') );?>
                            <?php echo form_error('password');?>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <?php echo form_label('Domain','Domain');?>
                            <?php echo form_dropdown('domain', array_merge([''=>''], $domains) ,isset($user->domain) ? $user->domain :'','class=form-control');?>
                            <?php echo form_error('domain');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <?php echo form_button(array('id'=>'enviar', 'type'=>'submit', 'class'=>'btn bg-black btn-flat','content' =>'<i class="fa fa-save fa-fw"></i> Guardar'))?>
                    </div>
                </div>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>