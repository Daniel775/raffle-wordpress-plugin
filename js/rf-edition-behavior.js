const tabsBehavior = (current, button) => {
	jQuery(document).ready(($) => {
		$("#raffle-tabs .nav-tab-active").removeClass('nav-tab-active');
		$(`#raffle-tabs ${button}`).addClass('nav-tab-active');
		$(`#mytabs .shown`).removeClass('shown').addClass('hidden');
		$(`#mytabs ${current}`).removeClass('hidden').addClass('shown');
	});
}

const toMoney = (i) => {
	var v = i.value.replace(/\D/g,'');
	v = (v/100).toFixed(2) + '';
	v = v.replace(".", ",");
	v = v.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
	v = v.replace(/(\d)(\d{3}),/g, "$1.$2,");
	i.value = v;
}

const showSearchResult = ($, value) => {
	wpCustomData.currentNumber = value.selectedNumber;

	if (value === null){
		$('#rf-search-result').removeClass('hidden');
		$('#rf-search-person').text('---');
		$('#rf-number-status').val('avaiable');
		$('#rf-save-change').addClass('hidden');
		return;
	}

	$('#rf-search-result').removeClass('hidden');
	$('#rf-search-person').text(value.user || '---');
	$('#rf-number-status').val(value.status);
	$('#rf-save-change').addClass('hidden');
}

jQuery(document).ready(($) => {
	$('body').on('click', '#rf-search', e => {
		e.preventDefault();

		if (!$('#search_number').val()){
			return;
		}

		const selectedNumber = $('#search_number').val();

		$.ajax({
			type: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				action: 'get_number_data',
				postId: wpCustomData.postId,
				number: selectedNumber,
			},
			success: (response) => {
				showSearchResult($, response.data['data']);
			}
		});
	});

	$('body').on('click', '#rf-save-change', e => {
		e.preventDefault();

		if (!wpCustomData.currentNumber){
			return;
		}

		$.ajax({
			type: 'POST',
			url: ajaxurl,
			dataType: 'json',
			data: {
				action: 'update_number_data',
				postId: wpCustomData.postId,
				selectedNumber: wpCustomData.currentNumber,
				newStatus: $('#rf-number-status option').filter(':selected').val(),
			},
			success: (response) => {
				$('#rf-save-change').addClass('hidden');
				console.log(response)
			}
		});
	});

	$(document).on('change', '#status', function() {
		const selected = $(this).find("option:selected").attr('value');

		if (selected == 'finished'){
			$("#w_number").prop('required', true);
			$("#w_number").removeClass('hidden');
		} else {
			$("#w_number").prop('required', false);
			$("#w_number").addClass('hidden');
		}
	});

	$(document).on('change', '#rf-number-status', () => {
		$('#rf-save-change').removeClass('hidden');
	});
});