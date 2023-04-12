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
                <form id="optimizeForm" action="<?php echo base_url('admin/imageoptimizer/optimize'); ?>" method="POST" name="update_default" />
                <div class="form-group">
                    <label for="rootDirectory">Directorio</label>
                    <select name="rootDirectory" id='rootDirectory' required="required" class="form-control">
                        <option value="" disabled selected>Selecciona un directorio</option>

                        <?php
                        foreach (glob('assets/themes/*',GLOB_ONLYDIR) as $domain_dir) {
                            if (!in_array(basename($domain_dir), config('App')->allowedDomains)) {
                                continue;
                            }
                            ?>

                            <optgroup label="<?php echo basename($domain_dir); ?>">
                                <option value="<?php echo $domain_dir; ?>"><?php echo basename($domain_dir); ?> TODOS</option>
                                <?php
                                foreach(glob($domain_dir .'/img/*',GLOB_ONLYDIR) as $filename){ ?>
                                    <option value="<?php echo $filename; ?>"><?php echo basename($filename); ?></option>
                                <?php } ?>
                                <option value="<?php echo $domain_dir .'/uploads/img/'; ?>">uploads</option>
                            </optgroup>

                        <?php } ?>

                    </select>
                </div>
                <input type="submit" class="btn btn-default" value="Optimizar imágenes" href="/admin/idiomas/listado"/>
                </form>
                <!-- Pintamos la cantidad de imágenes -->
                <small id='qttImatges'></small>
            </div>

            <div class="col-lg-12" id='progressbar-optimize' hidden>
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
    //Get images
    var imatgesOptimize;
    var qttImages;
    var thisProgress;
    var totalProgress = parseFloat(0);
    var messageInfo;
    var i;
    $( "#rootDirectory" ).change(function() {
        var form = $("#optimizeForm");
        $.ajax({
            url     : form.attr('action'),
            type    : form.attr('method'),
            data    : form.serialize(),
            dataType: "json",
            success : function( data ) {
                imatgesOptimize = data;
                qttImages = data.length;
                $("#qttImatges").html("Cantidad de imágenes que se optimizaran: <strong>"+ qttImages +"</strong>");
            },
            error   : function( xhr, err ) {
                $("#qttImatges").html("Cantidad de imágenes que se optimizaran: 0");
            }
        });
    });

    //Submit
    $(document).on('submit', '#optimizeForm', function() {
        $('.progress-bar').css('width', '10%').attr('aria-valuenow', 0);
        $('#progressbar-optimize').show();
        thisProgress = 100/qttImages;
        totalProgress = 0;
        messageInfo = "Se han optimizado todas las imágenes";
        i=0;
        saveImage(i);
        return false;
    });

    function saveImage(i){
        var res = imatgesOptimize[i].split("/");
        console.log (totalProgress);

        totalProgress = parseFloat(parseFloat(totalProgress) + parseFloat(thisProgress));
        $.ajax({
            url     : '<?php echo base_url('admin/imageoptimizer/optimizeImage'); ?>',
            type    : "POST",
            data    : {img:imatgesOptimize[i], root: res[2],},
            success : function( data ) {
                if ( i == (parseInt(qttImages-1))){
                    totalProgress = 100;
                    setTimeout(function(){
                        alert (messageInfo);
                        $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0);
                        $('#progressbar-optimize').hide();
                    }, 1000);
                }
                $('#messageProgress').html(totalProgress.toFixed(2)+"%");
                $('.progress-bar').css('width', totalProgress+'%').attr('aria-valuenow', totalProgress);
                $('#messageProgressInfo').html(imatgesOptimize[i]+"...");
                if (i < imatgesOptimize.length-1){
                    i++;
                    saveImage(i);
                } else {
                    return true;
                }
            }, error   : function( xhr, err ) {
                messageInfo = "S'han produït errors durant l'optimització. Torna a optimitzar-les.";
                alert (messageInfo);
                if ( i == (parseInt(qttImages-1))){
                    totalProgress = 100;
                    setTimeout(function(){
                        alert (messageInfo);
                        $('.progress-bar').css('width', '0%').attr('aria-valuenow', 0);
                        $('#progressbar-optimize').hide();
                    }, 1000);
                }
                $('#messageProgress').html(totalProgress.toFixed(2)+"%");
                $('.progress-bar').css('width', totalProgress+'%').attr('aria-valuenow', totalProgress);
                i++;
            }
        });
    }

</script>


<?= $this->endSection() ?>
