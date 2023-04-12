<section class="section-blog-list">
    <div class="container">
        <div class="row">
            <!-- BLOC -->
            <?php foreach ( $blogpostlist->posts as $single_post) : ?>
                <?php if(!isset($blogpost->post) || isset($blogpost->post) && ($blogpost->post->id != $single_post->id)) : ?>
                <div class="col-md-4">
                    <div class="block-img">
                        <?php
                        if(!empty($single_post->image_principal)) : ?>
                            <img class="img-fluid"
                                 src="<?php echo base_url(thumb($single_post->image_principal, 'medium'))?>"
                                 alt="<?php echo $single_post->text_title_web ?: ''; ?>"
                                 title="<?php echo $single_post->text_title_web ?: ''; ?>">
                        <?php
                        endif; ?>

                    </div>
                    <div class="block-text" data-wow-duration="2s">
                        <?php if(!empty($single_post->text_title_web)) : ?>
                            <h4 ><?php echo $single_post->text_title_web; ?></h4>
                        <?php endif; ?>
                        <?php if(!empty($single_post->textarea_descripcion)) : ?>
                            <?php echo $single_post->textarea_descripcion; ?>
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo $single_post->url; ?>"
                       class="btn">
                        LEER MÁS
                    </a>
                    <hr>
                </div>

                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
