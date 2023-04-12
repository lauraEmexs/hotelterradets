<?= $this->extend('layout/frontend') ?>

<?= $this->section('content') ?>
        <?php

        if (isset($blocks)) {
            $blocks_to_output = [];
            foreach ($blocks as $block) {
                $position = $block['position'];
                while (isset($blocks_to_output[$position])) {
                    $position++;
                }
                $blocks_to_output[$position] = $block['html'];
            }

            ksort($blocks_to_output);
            foreach ($blocks_to_output as $html) {
                echo $html;
            }
        }

        if (isset($sections)) {
            foreach ($sections as $section) {
                echo view('section/'.$section);
            }
        }

        ?>

<?= $this->endSection() ?>