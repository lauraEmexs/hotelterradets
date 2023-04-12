<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-list fa-fw"></i> Listado de <?php echo $title_section;?>
        <?= $filter_info ?>

        <?php
        if (isset($export) && $export) {
            echo '&nbsp;&nbsp;'. anchor('admin/formSubmits/export/'. $slug .'/'. $domain_url_params, '
        <i class="fa fa-download fa-fw"></i> Exportar', 'class="btn bg-black btn-flat btn-xs"');
        }
        ?>
    </div>
    <!-- /.panel-heading -->
    <div class="panel-body">
        <?php $session = \Config\Services::session();
        if($session->getFlashdata('message')):?>
            <?php echo $session->getFlashdata('message')?>
        <?php endif;?>
        <form action="<?=gen_paged_link('','',['perpage','page'])?>">
            <div class="table-responsive">
                <div  class="dataTables_wrapper form-inline dt-bootstrap no-footer ">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="dataTables_length" id="menu_length">
                                <label>Mostrar
                                    <select name="perpage" aria-controls="menu" class="" onchange="location = '<?=gen_paged_link('perpage','',['page'])?>' + this.value">
                                        <?php foreach ($perpages as $option): ?>
                                            <option value="<?=$option ?>" <?php if($option == $perpage):?>selected="selected"<?php endif ?>><?=$option ?></option>
                                        <?php endforeach ?>
                                    </select> registros</label>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div id="menu_filter" class="dataTables_filter"><label>Buscar: <input name="q" value="<?=htmlentities(isset($_GET['q'])?$_GET['q']:'')?>" type="search" class="" placeholder="" aria-controls="menu"></label></div>
                        </div>
                    </div>
                </div>
                <?php echo $table->generate();?>
                <div class="dataTables_wrapper ">
                    <div class="dataTables_info" id="menu_info" role="status" aria-live="polite">
                        Mostrando registros del <?= $page*$perpage-$perpage+1 ?> al <?= $page*$perpage<$count?$page*$perpage:$count ?> de un total de <?= $count ?> registros
                    </div>
                    <div class="dataTables_paginate paging_simple_numbers" id="menu_paginate">
                        <ul class="pagination">
                        <?php
                        $start_page = $page-4;
                        if($start_page < 1) $start_page = 1;
                        $end_page = $start_page+9;
                        if ($end_page > ceil($count/$perpage)) $end_page = ceil($count/$perpage);
                        ?>
                        <li class="paginate_button previous<?php if ($page == 1): ?> disabled<?php endif; ?>"><a <?php if ($page > 1): ?>href="<?=gen_paged_link('page',$page-1)?>"<?php endif; ?> id="menu_previous">Anterior</a></li>
                        <?php for ($i_page = $start_page; $i_page<=$end_page; $i_page++): ?>
                            <li class="paginate_button<?php if($i_page == $page): ?> active<?php endif; ?>"><a <?php if($i_page != $page): ?>href="<?=gen_paged_link('page',$i_page)?>"<?php endif; ?>><?=$i_page?></a></li>
                        <?php endfor ; ?>
                        <li class="paginate_button next<?php if ($page == ceil($count/$perpage)): ?> disabled<?php endif; ?>"><a <?php if ($page != ceil($count/$perpage)): ?>href="<?=gen_paged_link('page',$page+1)?>"<?php endif; ?> tabindex="0" id="menu_next">Siguiente</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>
        <?php

        if (!isset($no_add_button) || !$no_add_button) {
            if ($logged_user->group != 'limited') {
                echo anchor('admin/dynamic/dedit/' . $slug . '/' . $idioma_original . '/0' . $domain_url_params, '
                        <i class="fa fa-plus fa-fw"></i> Añadir', 'class="btn bg-black btn-flat"');
            }
            if ($logged_user->group == 'limited' && $slug != 'pages') {
                echo anchor('admin/dynamic/dedit/' . $slug . '/' . $idioma_original . '/0' . $domain_url_params, '
                        <i class="fa fa-plus fa-fw"></i> Añadir', 'class="btn bg-black btn-flat"');
            }
        }
        if (isset($export) && $export) {
            echo anchor('admin/formSubmits/export/'. $slug .'/'. $domain_url_params, '
                    <i class="fa fa-download fa-fw"></i> Exportar', 'class="btn bg-black btn-flat"');
        }
        ?>
        <?php echo ($ordenar)?anchor('admin/dynamic/dorder/'. $slug .'/'. $idioma_original .''. $domain_url_params, '<i class="fa fa-arrows fa-fw"></i> Ordenar', 'class="btn bg-black btn-flat"'):'';?>
        <?php if ($logged_user->group != 'limited') : ?>
            <?php echo (!empty($metas_xls)) ? anchor('admin/dynamic/metas/'. $slug .'/'. $domain_url_params, '<i class="fa fa-exchange fa-fw"></i> Exportar\importar Metas', 'class="btn bg-black btn-flat pull-right"'):'';?>
        <?php endif; ?>
    </div>
    <!-- /.panel-body -->
</div>
<!-- /.panel -->

<script>
    $(function() {
        $('.pause-toggle').bootstrapToggle();
        $('table.datatable').on( 'page.dt', function () {
            setTimeout(function(){
                $('.pause-toggle').bootstrapToggle();
            }, 300);
        });

        $(document).on('click.bs.toggle', 'div.toggle-switch', function() {
            var $checkbox = $(this).children('input[type=checkbox]');
            if (confirm($checkbox.data('confirm-text'))) {
                window.location.replace($(this).parents('a').attr('href'));
            } else {
                $checkbox.bootstrapToggle('toggle');
            }
        });
    })
</script>
<?= $this->endSection() ?>

