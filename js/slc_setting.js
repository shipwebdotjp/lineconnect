function showNewChannel() {
    jQuery("#new-channel-box").slideToggle();
    jQuery("#newChannelBtn").hide();
}

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
                checkAll: { text: 'すべて選択', title: 'すべて選択' },
                uncheckAll: { text: '選択解除', title: '選択解除' }
            },
            noneSelectedText: "未選択",
            selectedText: "# 個選択"
        });

    });
    $(".wrap").tooltip();
});