function showNewChannel() {
    jQuery("#new-channel-box").slideToggle();
    jQuery("#newChannelBtn").hide();
}
const { __ } = wp.i18n;
jQuery(function ($) {
    $("#stabs").tabs({ active: slc_json['active_tab'] });
    $(".slc-color-picker").each(
        function (index) {
            $(this).wpColorPicker({ defaultColor: $(this).attr("data-default-color") });
        }
    );
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
    $(".wrap").tooltip();
});