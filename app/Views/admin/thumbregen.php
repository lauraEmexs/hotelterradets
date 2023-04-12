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
                <form id="thumbregen" action="#" />
                    <input type="submit" class="btn btn-default" value="Regenerar miniaturas de galerias" />
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
    var qttImages = <?php echo count($images); ?>;
    var thisProgress;
    var totalProgress;
    var messageInfo;
    var i;

    //Submit
    $(document).on('submit', '#thumbregen', function(e) {
        e.preventDefault();
        $('.progress-bar').css('width', '1%').attr('aria-valuenow', 1);
        $('#progress').show();
        thisProgress = 100/qttImages;
        totalProgress = 0;
        messageInfo = "Se han recreado todas las miniaturas";

        regenerate(0);
        return false;
    });

    function regenerate(i) {
        totalProgress = parseFloat(parseFloat(totalProgress) + parseFloat(thisProgress));
        $.ajax({
            url     : '<?php echo base_url('admin/thumbregen/regenerate'); ?>',
            type    : "POST",
            data    : { num: i },
            success : function( data ) {
                if ( i == (parseInt(qttImages-1))){
                    totalProgress = 100;
                    setTimeout(function(){
                        alert (messageInfo);
                        $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0);
                        $('#progress').hide();
                    }, 1000);
                }
                $('#messageProgress').html(totalProgress.toFixed(2)+"%");
                $('.progress-bar').css('width', totalProgress+'%').attr('aria-valuenow', totalProgress);

                if (i < qttImages-1){
                    i++;
                    regenerate(i);
                } else {
                    return true;
                }
            }
        });
    }

</script>

<?= $this->endSection() ?>
