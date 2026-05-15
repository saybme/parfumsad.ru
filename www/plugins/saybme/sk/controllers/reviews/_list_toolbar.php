<div data-control="toolbar">

    <a
        href="<?= Backend::url('saybme/sk/reviews/create') ?>"
        class="btn btn-primary oc-icon-plus">
        <?= e(trans('backend::lang.form.create')) ?>
    </a>

    <button
        class="btn btn-default oc-icon-trash-o"
        data-request="onDelete"
        data-request-confirm="<?= e(trans('backend::lang.list.delete_selected_confirm')) ?>"
        data-list-checked-trigger
        data-list-checked-request
        data-stripe-load-indicator>
        <?= e(trans('backend::lang.list.delete_selected')) ?>
    </button>

    <button 
        class="btn btn-default oc-icon-check-circle-o" 
        data-request="onApprove" 
        data-request-confirm="Вы уверены, что хотите одобрить выбранные отзывы?"
        data-list-checked-trigger
        data-list-checked-request
        data-stripe-load-indicator>
        Одобрить выбранные отзывы
    </button>

</div>
