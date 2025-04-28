<div data-control="toolbar">    

    <button
        type="button"
        data-control="popup"
        data-handler="onLoadPopupForm"
        class="btn btn-primary oc-icon-plus">
        Добавить тип оплаты
    </button>


    <button
        class="btn btn-default oc-icon-trash-o"
        data-request="onDelete"
        data-request-confirm="<?= e(trans('backend::lang.list.delete_selected_confirm')) ?>"
        data-list-checked-trigger
        data-list-checked-request
        data-stripe-load-indicator>
        <?= e(trans('backend::lang.list.delete_selected')) ?>
    </button>

</div>
