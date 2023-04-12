<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>

<?php

/* Make child pages array */
$pages = [];
foreach ($data as $page) {
    $path_parts = explode('.', $page->path);
    $path_parent = implode('.', array_slice($path_parts, 0, count($path_parts) -1));
    $pages[$path_parent][] = $page;
}

function draw_level($pages, $path_parent)
{
    /* If there is no children */
    if (!isset($pages[$path_parent]) || empty($pages[$path_parent])) {
        return false;
    }

    echo '<ol class="dd-list">';

    $this_indent = substr_count($path_parent, '.');
    foreach ($pages[$path_parent] as $page) {
        if (substr_count($page->path, '.') > $this_indent + 1) {
            continue;
        }
    ?>
        <li data-path="<?php echo $page->path; ?>" data-parent_id="<?php echo $page->parent_id; ?>" data-id="<?php echo $page->id;?>" class="dd-item">
            <div class="dd-handle"><?php echo substr(trim(strip_tags($page->text_title)),0,155); ?></div>
            <?php
                draw_level($pages, $page->path);
            ?>
        </li>
    <?php }

    echo '</ol>';
}

?>

<div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-th-list fa-fw"></i> Ordenar <?php echo $title_section;?>
    </div>
    <!-- /.panel-heading -->
    <div class="panel-body">

        <div class="alert alert-info">Orden actualizado correctamente</div>
        <div class="alert alert-warning">Se ha producido un error, inténtalo nuevamente.</div>

        <div class="dd">
            <ol class="dd-list">

                <?php
                foreach ($data as $page) {
                    if (substr_count($page->path, '.') > 0) {
                        continue;
                    }
                ?>
                    <li data-path="<?php echo $page->path; ?>" data-parent_id="<?php echo $page->parent_id; ?>" data-id="<?php echo $page->id;?>" class="dd-item">
                        <div class="dd-handle"><?php echo substr(trim(strip_tags($page->text_title)),0,155); ?></div>
                        <?php
                            draw_level($pages, $page->path);
                        ?>
                    </li>
                <?php } ?>
            </ol>
        </div>

        <br>
        <?php echo anchor('admin/dynamic/dlist/'. $slug . $domain_url_params, 'Volver atrás', 'class="btn bg-black btn-flat"');?>
    </div>
    <!-- /.panel-body -->
</div>
<!-- /.panel -->

<style>
    .alert {
        display: none;
    }
    .sortable li {
        margin: 10px 0px;
        padding: 5px;
        font-size: 1.2em;
    }

    .ui-state-default,
    .ui-widget-content .ui-state-default,
    .ui-widget-header .ui-state-default,
    .ui-button,

        /* We use html here because we need a greater specificity to make sure disabled
        works properly when clicked or hovered */
    html .ui-button.ui-state-disabled:hover,
    html .ui-button.ui-state-disabled:active {
        border: 1px solid #c5c5c5;
        background: #f6f6f6;
        font-weight: normal;
        color: #454545;
    }
    .ui-state-default a,
    .ui-state-default a:link,
    .ui-state-default a:visited,
    a.ui-button,
    a:link.ui-button,
    a:visited.ui-button,
    .ui-button {
        color: #454545;
        text-decoration: none;
    }
    .ui-state-highlight { height: 1.5em; line-height: 1.2em; }
    .ui-state-highlight,
    .ui-widget-content .ui-state-highlight,
    .ui-widget-header .ui-state-highlight {
        border: 1px solid #dad55e;
        background: #fffa90;
        color: #777620;
    }
    .ui-state-checked {
        border: 1px solid #dad55e;
        background: #fffa90;
    }
    .ui-state-highlight a,
    .ui-widget-content .ui-state-highlight a,
    .ui-widget-header .ui-state-highlight a {
        color: #777620;
    }
</style>

<script>
    $(document).ready(function(){

        $('.dd').nestable({
            /* config options */
            scroll: true,
        })
        .on('change', function(){
            $('.alert').hide();
            update($('.dd').nestable('toArray'));
        });

    });

    function update(data){
        $.ajax({
            url : '<?php echo base_url('admin/dynamic/dorder_update_pages');?>?domain=<?php echo $domain; ?>',
            method: 'post',
            data : {
                data: data,
            },
            success : function(result) {
                if (result === 'true') {
                    $('.alert-info').show();
                } else {
                    $('.alert-warning').show();
                }
            }
        });
    }
</script>
<?= $this->endSection() ?>