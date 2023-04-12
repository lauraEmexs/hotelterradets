<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?><div class="panel panel-default">
    <div class="panel-heading">
        <i class="fa fa-book fa-fw"></i> <?php echo $title_section;?>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col-lg-12">
                <div class="row">
                    <?php foreach($data_struct as $struct) :
                        if (in_array($struct['name'], ['id', 'language', 'domain', 'status', 'position', 'updater_id'])) {
                            continue;
                        }
                        ?>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <?php echo form_label($struct['label'], $struct['name']);?>
                                <p><?php
                                    if (isset($data->{$struct['name']})) {
                                        switch ($struct['type']) {
                                            case 'document' :
                                                $url = ((!empty($_SERVER['HTTPS'])) ? 'https://' : 'http://');
                                                $url .= $this->selected_domain .'/'. html_escape($data->{$struct['name']});
                                                echo '<a href="'. $url .'" target="_blank">Descargar</a>';
                                                break;
                                            default :
                                                echo htmlentities($data->{$struct['name']});
                                                break;
                                        }
                                    } else {
                                        echo '-';
                                    }
                                    ?> </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>