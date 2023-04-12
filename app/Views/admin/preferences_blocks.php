<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<?php $ids_codemirror=array(); ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-list fa-fw"></i> Preferencias de Bloques
    </div>
    <div class="panel-body">
        <?php $session = \Config\Services::session();
        if($session->getFlashdata('message')):?>
            <?php echo $session->getFlashdata('message')?>
        <?php endif;?>

        <?php echo form_open_multipart('admin/preferencesblocks/save'. $domain_url_params, array('role'=>'form','id'=>'myform')); ?>
        <div class="row">
            <?php foreach ($dynamic_types as $dynamic) : ?>
                <div class="col-md-12">
                    <h3><?= $dynamic ?> <small>Drag and drop</small></h3>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Sin utilizar</label><br>
                        <input type="hidden" name="blocks_<?= $dynamic ?>" id="blocks_<?= $dynamic ?>"
                               value=""/>
                        <input type="hidden" name="desactivated_<?= $dynamic ?>" id="desactivated_<?= $dynamic ?>"
                               value=""/>
                        <ul id="sortable1_<?= $dynamic ?>" class="connectedSortable sortable">
                            <?php if (isset(${'blocks_' . $dynamic})) : ?>
                                <?php foreach (${'blocks_' . $dynamic} as $block_id => $block) {
                                    if (in_array($block_id, ${'used_ids_' . $dynamic})) {
                                        continue;
                                    } ?>
                                    <li class="ui-state-default" data-blockid="<?php echo $block_id; ?>">
                                        <?php echo $block->title; ?>
                                        <p class="text-muted"><?php echo $block->description; ?></p>
                                    </li>
                                <?php } ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Utilizados</label><br>
                        <ul id="sortable2_<?= $dynamic ?>" class="connectedSortable sortable">
                            <?php if (isset(${'used_blocks_' . $dynamic})) : ?>
                                <?php foreach (${'used_blocks_' . $dynamic} as $block) { ?>
                                    <li class="ui-state-highlight" data-blockid="<?php echo $block->id; ?>">
                                        <?php echo $block->params->title; ?>
                                    </li>
                                <?php } ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            <?php endforeach; ?>
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
        <?php foreach ($dynamic_types as $dynamic) : ?>
            $("#sortable1_<?= $dynamic ?>, #sortable2_<?= $dynamic ?>").sortable({
                connectWith: ".connectedSortable"
            }).disableSelection();
        <?php endforeach; ?>

        $('#myform').on('submit', function () {
            <?php foreach ($dynamic_types as $dynamic) : ?>
                var blocks_<?= $dynamic ?> = new Array;
                var desactivated_<?= $dynamic ?> = new Array;
                $('#sortable2_<?= $dynamic ?> li').each(function () {
                    blocks_<?= $dynamic ?>.push($(this).data('blockid'));
                });
                $('#blocks_<?= $dynamic ?>').val(blocks_<?= $dynamic ?>.join(','));

                $('#sortable1_<?= $dynamic ?> li').each(function () {
                    desactivated_<?= $dynamic ?>.push($(this).data('blockid'));
                });
                $('#desactivated_<?= $dynamic ?>').val(desactivated_<?= $dynamic ?>.join(','));
            <?php endforeach; ?>
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
