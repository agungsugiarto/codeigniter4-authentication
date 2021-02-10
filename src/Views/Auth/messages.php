<?php if (session()->has('message')) : ?>
    <div class="alert alert-success" role="alert">
        <p class="mb-0"><?= session('message') ?></p>
    </div>
<?php endif ?>

<?php if (session()->has('error')) : ?>
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">Whoops! Something went wrong.</h4>
        <hr>
        <p class="mb-0"><?= session('error') ?></p>
    </div>
<?php endif ?>

<?php if (session()->has('errors')) : ?>
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">Whoops! Something went wrong.</h4>
        <hr>
        <?php foreach (session('errors') as $error) : ?>
            <li class="mb-0"><?= $error ?></li>
        <?php endforeach ?>
    </div>
<?php endif ?>