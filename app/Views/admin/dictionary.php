<?= $this->extend('layout/admin') ?>

<?= $this->section('content') ?>
<div class="panel panel-default dictionary">
    <div class="panel-heading">
        <i class="fa fa-globe fa-fw"></i><?php echo $title_section;?>

        <div class="box-tools pull-right">
            <?= anchor('admin/idiomas/expimp', '<i class="fa fa-exchange"></i> Export\import', array('class' => 'dedit-pages-view-web', )) ?>
        </div>

    </div>
    <div class="panel-body">

        <?php $session = \Config\Services::session();
        if($session->getFlashdata('message')):?>
            <?php echo $session->getFlashdata('message')?>
        <?php endif;?>
        <form name="diccionarios" action="#">
        <?php foreach($all_keys as $key=>$kk) { if($key=='theme_' || $key == '') continue; ?>

        <div class="row">
            <div class="form-group">
                <div class="col-lg-12"><label for="text_title">Clave del diccionario [<?php echo $key;?>]</label></div>

                <?php foreach($languages as $language) { ?>
                    <div class="col-lg-11" style="margin-bottom:10px;">
                        <input type="text" name="<?php echo $language->id; ?>_<?php echo $key; ?>"
                               value="<?php echo (isset($dictionary[$language->id][$key])) ? htmlspecialchars($dictionary[$language->id][$key]) : ''; ?>"
                               id="<?php echo $language->id; ?>_<?php echo $key; ?>" class="form-control">
                    </div>
                    <div class="col-lg-1" style="margin-bottom:10px;" id="div_<?php echo $language->id;?>_<?php echo $key;?>" >
                        <a href="#" onclick="save_entry('<?php echo $language->id; ?>','<?php echo $key; ?>'); return false;" class="btn btn-default">
                            <img src="<?php echo base_url('assets/themes/adminlte/flags/blank.png'); ?>" class="flag flag-<?php echo ($language->id == 'en') ? 'us' : $language->id; ?>" alt="">
                        </a>
                    </div>
                <?php } ?>


            </div>
        </div>
        <br/>
        <?php } ?>

    </div>
</div>

<script>
	function save_entry(lang, key){
		var string = $('#'+ lang +'_'+ key).val();

		$.ajax({
            async : false,
            url   : '<?php echo base_url('admin/idiomas/dictionary_save'); ?>',
            type  : 'Post',
            data  : { klang: lang, kkey: key, kvalue: string },
            success:function(data) {
                var obj = jQuery.parseJSON(data);
                if (obj['status'] === 'true') {
                    $('#'+ lang +'_'+ key).removeClass('changed').addClass('saved');
                    initialValue[$('#'+ lang +'_'+ key).attr('name')] = string;
                    //$('#div_'+ lang +'_'+ key).html('<span><i class="fa fa-check"></i></span>');
                }
            }
        });
	}

	/* Mark changed inputs */
    var inputs = document.querySelectorAll("input");

    var initialValue = [];
    for (var i = 0; i < inputs.length; i++) {
        initialValue[inputs[i].name] = inputs[i].value;

        inputs[i].addEventListener("keyup", function() {
            if (initialValue[this.name] !== this.value) {
                this.classList.remove("saved");
                this.classList.add("changed");
            } else {
                this.classList.remove("saved");
                this.classList.remove("changed");
            }
        });
    }

</script>

<?= $this->endSection() ?>