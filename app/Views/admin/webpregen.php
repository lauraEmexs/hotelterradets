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
                <form id="webpregen" action="#" />
                    <p><input type="checkbox" class="btn btn-default" name="onlyNew" value="1" checked /> Solo los que no existen</p>
                    <p><input type="submit" class="btn btn-default" value="Regenerar WEBP de galerias" /></p>
                </form>
            </div>

            <div class="col-lg-12" id='progress' hidden>
                <br>
                <label>Progreso</label>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%">
                        <span id='messageProgress'></span>
                    </div>

                </div>
                <div style="clear:both"></div>
                <span id='messageProgressInfo'></span>

            </div>
        </div>
    </div>
</div>

<script>
    var qttImages = <?= $images_cnt ?>;
    var qttBlockImages = <?= $blocks_cnt ?>;
    var qttDynamicImages = <?= $dynamic_cnt ?>;
    var qttTotal = qttImages + qttBlockImages + qttDynamicImages;
    var thisProgress;
    var totalProgress;
    var messageInfo;
    var i;
    var itotal;
    var onlyNew;

    //Submit
    $(document).on('submit', '#webpregen', function(e) {
        e.preventDefault();
        $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0);
        $('#progress').show();
        thisProgress = 100/qttTotal;
        totalProgress = 0;
        messageInfo = "Se han recreado todas las imagenes en WEBP";
        onlyNew = $('[name="onlyNew"]:checked').val();

        regenerate(0, 0, 'default', onlyNew);
        return false;
    });

    function regenerate(i, itotal, type, onlyNew) {
        totalProgress = parseFloat(parseFloat(totalProgress) + parseFloat(thisProgress));
        $.ajax({
            url     : '<?php echo base_url('admin/webpregen/regenerate'); ?>',
            type    : "POST",
            data    : {
                num: i,
                type: type,
                onlyNew: onlyNew
            },
            success : function(data) {
                if (itotal == (parseInt(qttTotal-1))) {
                    totalProgress = 100;
                    setTimeout(function(){
                        alert (messageInfo);
                        $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0);
                        $('#progress').hide();
                    }, 1000);
                }
                $('#messageProgress').html(totalProgress.toFixed(2)+"%");
                $('.progress-bar').css('width', totalProgress+'%').attr('aria-valuenow', totalProgress);

                if (itotal < qttTotal-1){
                    if (itotal === qttImages) {
                        type = 'block';
                        i = 0;
                    }else if (itotal === qttImages + qttBlockImages) {
                        type = 'dynamic';
                        i = 0;
                    } else {
                        i++;
                    }
                    itotal++;
                    regenerate(i, itotal, type, onlyNew);
                } else {
                    return true;
                }
            }
        });
    }

</script>

<?= $this->endSection() ?>
