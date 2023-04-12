
<section class="dev_section m-section">
    <hr>
    <div class="container">
        <div class="row justify-content-around">

            <div class="col-12">
            	<h2>Test Block</h2>
                <?php foreach ( $testblock as $key => $value ) : ?>
                    <span><b><?php echo $key ?></b> : <?php print_r($value); ?></span><br/>

                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <hr>
</section>