$(document).on('click', '[data-repeater-prompt]', function() {
    var $repeater = $(this).closest('[data-control="repeater"]');
    setTimeout(function() {
        $repeater.find('[data-repeater-item]:last').find('input:first').focus();
    }, 100);
});
