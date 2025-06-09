<div class="container-fluid mt-3">
<?php
    // The $msg object is now available directly from the BaseController.
    // We check if there are messages, then display them once.
    if ($msg && $msg->hasMessages()) {
        echo $msg->display();
    }
?>
</div>
<div class="login-container text-center">
        <?php 
            if (file_exists('data/logo.png')) {
                echo '<a href="'.BASE_URL.'"><img id="logo" src="data/logo.png?'.time().'" alt="logo" class="img-fluid mb-3"></a>';
            } else {
                echo '<a href="'.BASE_URL.'"><h3>'.TITLE.' <i class="fa fa-code" aria-hidden="true"></i></h3></a>';
            }
        ?>
        <div class="card fade-in-fwd">
        <div class="card-body shadow-sm">
            <?= $form; ?>
            <a href="<?= BASE_URL ?>lost-password" class="text-muted"><?= $t->trans('I lost my password') ?></a>
        </div>
    </div>
</div>