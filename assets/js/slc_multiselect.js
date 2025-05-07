const { __ } = wp.i18n;
jQuery(function ($) {
    $(document).ready(function () {
        $(".slc-multi-select").multiselect({
            selectedList: 5,
            linkInfo: {
                checkAll: { text: __('Check All', 'lineconnect'), title: __('Check All', 'lineconnect') },
                uncheckAll: { text: __('UnCheck All', 'lineconnect'), title: __('UnCheck All', 'lineconnect') }
            },
            noneSelectedText: __('Select options', 'lineconnect'),
            selectedText: __('# checked', 'lineconnect')
        });

    });
});