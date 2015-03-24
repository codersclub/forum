function find_users() {
    url = "index.php?act=legends&CODE=finduser_one&s=" + session_id + "&entry=textarea&name=carbon_copy&sep=line";
    window.open(url, 'FindUsers', 'width=400,height=250,resizable=yes,scrollbars=yes');
}
function select_read(context) {
    $('.b-row[data-read="1"] .b-column_checkbox input:checkbox', context).prop('checked', true);
    $('.b-row[data-read="1"] .b-column_checkbox input:checkbox', context).trigger('change');
}

function unselect_all(context) {
    $('.b-row .b-column_checkbox input:checkbox', context).prop('checked', false);
    $('.b-row .b-column_checkbox input:checkbox', context).trigger('change');
}

function goto_inbox() {
    opener.document.location.href = js_base_url + 'act=Msg&amp;CODE=01';
    window.close();
}

function goto_this_inbox() {
    window.resizeTo('700', '500');
    document.location.href = js_base_url + 'act=Msg&CODE=01';
}

function go_read_msg() {
    window.resizeTo('700', '500');
    document.location.href = js_base_url + 'act=Msg&CODE=03&VID=in&MSID=' + mid;
}

$(document).ready(function () {
    $('.b-row .b-column_checkbox input:checkbox').change(function () {
        if ($(this).is(':checked')) {
            $(this).closest('tr').addClass('selected');
        } else {
            $(this).closest('tr').removeClass('selected');
        }
        //find unchecked
        if ($(this).closest('tbody').find('.b-column_checkbox input:checkbox:not(:checked)').length > 0) {
            $(this).closest('table').find('thead .b-column_checkbox input:checkbox').prop('checked', false);
        } else {
            $(this).closest('table').find('thead .b-column_checkbox input:checkbox').prop('checked', true);
        }
    });

    $('.b-header-row .b-column_checkbox input:checkbox').click(function () {
        $(this).closest('table').find('tbody .b-column_checkbox input:checkbox').prop('checked', $(this).prop('checked'));
        $(this).closest('table').find('tbody .b-column_checkbox input:checkbox').trigger('change');
    });
});
