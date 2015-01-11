/**
 *
 */

function go_gadget_simple()
{
	window.location = "./?act=Search&mode=simple&f=" + current_forum;
}

function go_gadget_advanced()
{
	window.location = "./?act=Search&mode=adv&f=" + current_forum;
}

function win_pop()
{
    window.open('./?act=Search&CODE=explain','WIN','width=400,height=300,resizable=yes,scrollbars=yes');
}
function toggle_fulltext_features(hide) {
	var phrase    = $('input[name=space_determine][value=phrase]').parent();
	var sortSelect= $('select[name=sort_key]');
	var relevance = sortSelect.find('option[value=relevancy]');
	toggle_fulltext_features = function(hide) {
		if (hide) {
			phrase.hide();
			if (relevance.is(':selected')) {
				sortSelect.find('option:first').attr('selected', 'selected');
			}
			relevance.remove();
		} else {
			phrase.show();
			relevance.appendTo(sortSelect);
		}
	};
	return toggle_fulltext_features(hide);
}

function on_fulltext_change() {
	var ch = $(this);
	toggle_fulltext_features ( ! ch.is(':checked') );
}

$(window).load(function() {
	$('input[name=fulltext]')
		.change(on_fulltext_change)
		.change();
});


function checkvalues() {
	f = document.dateline;
	if (f.st_day.value < f.end_day.value) {
		alert(active_js_error);
		return false;
	}
	if (f.st_day.value == f.end_day.value) {
		alert(active_js_error);
		return false;
	}
}
