<?php Block::put('breadcrumb') ?>
    <ul>
        <li><a href="<?= Backend::url('saybme/sk/categories') ?>">Categories</a></li>
        <li><?= e($this->pageTitle) ?></li>
    </ul>
<?php Block::endPut() ?>

<?php if (!$this->fatalError): ?>

    <div class="">
        <?= $this->formRenderDesign() ?>
    </div>

<?php else: ?>
    <p class="flash-message static error"><?= e(trans($this->fatalError)) ?></p>
    <p><a href="<?= Backend::url('saybme/sk/categories') ?>" class="btn btn-default"><?= e(trans('backend::lang.form.return_to_list')) ?></a></p>
<?php endif ?>