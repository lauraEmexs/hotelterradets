<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-th-list fa-fw"></i> Ordenar <?php echo $title_section;?>
    </div>
    <!-- /.panel-heading -->
    <div class="panel-body">
        <?php $session = \Config\Services::session();
        if($session->getFlashdata('message')):?>
            <?php echo $session->getFlashdata('message')?>
        <?php endif;?>
        <div id="mes"></div>
        <div class="">
            <ol id="sortable-items">
                <?php foreach ($data as $item) { ?>
                    <li data-name="<?php echo $item->position;?>" data-id="<?php echo $item->id;?>">
                        <span class="btn bg-gray btn-flat">
                            <i class="fa fa-arrows fa-fw"></i>  <?php echo substr(trim(strip_tags($item->$first_element_text)),0,155);?>
                        </span>
                    </li>
                <?php } ?>
            </ol>
        </div>
        <br>
        <?php echo anchor('admin/dynamic/dlist/'.$slug . $domain_url_params, 'Volver atrás', 'class="btn bg-black btn-flat"');?>
    </div>
    <!-- /.panel-body -->
</div>
<!-- /.panel -->

<style>
    #sortable-items li {
        margin: 10px 0px;
    }
</style>
<script>
    $(document).ready(function(){
        var sortable = $("#sortable-items");
        sortable.sortable({
            group: 'serialization',
            delay: 500,
            stop: function() {
                var data = sortable.sortable('toArray', {attribute: "data-id"});
                var arr = [];
                data.forEach(function(item, index){
                    arr[index] = {id: item};
                });
                var jsonString = JSON.stringify([arr]);
                actualizar(jsonString);
            },
        });
    });

    function actualizar(data){
        $.ajax({
            url : '<?php echo base_url('admin/dynamic/dorder_update/'.$slug) ?>?domain=<?php echo $domain; ?>',
            type : 'Post',
            data : {
                orden: data.toString(),
                test: "123"
            },
            success:function(data)
            {
                var obj = jQuery.parseJSON(data);
                if(obj['status'] === 'true')
                {
                    $('#mes').addClass('alert alert-success');
                    $('#mes').html('Reordenado con exito');
                }
                else
                {
                    $('#mes').addClass('alert alert-warning');
                    $('#mes').html('Error en reordenar');
                }
            }
        });
    }
</script>
<?= $this->endSection() ?>