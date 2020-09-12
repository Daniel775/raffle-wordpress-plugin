const populateUserNumbersList = ($, data) => {
	$('#rf-search-area').empty();

	if (jQuery.isEmptyObject(data)){
		$('#rf-search-area').text('Nenhum resultado.');
	}

	Object.keys(data).forEach((key) => {
		$('#rf-search-area').append(
			`<div class="raffle-number rf-${data[key].status}">${key}</div>`
		);
	});
	$('#rf-search-area').show();
}

jQuery(document).ready(($) => {
	$('body').on('click', '#rf-reserve-button', e => {
		e.preventDefault();

		if (!$('#rf-register-phone').val() || !wpCustomData.selectedNumber){
			return;
		}

		$.ajax({
			type: 'POST',
			url: wpCustomData.ajax_url,
			dataType: 'json',
			data: {
				action: 'update_user_data',
				postId: wpCustomData.postId,
				phone: $('#rf-register-phone').val(),
				selectedNumber:  wpCustomData.selectedNumber,
				newStatus: 'reserved',
			},
			success: (response) => {
				const args = {
					selected_number: wpCustomData.selectedNumber,
					phone: $('#rf-register-phone').val(),
				}

				const form = $('<form></form>');
				form.attr('method', 'post');
				form.attr('action', '//' + wpCustomData.paymentPage);

				$.each(args, function(key, value) {
					const field = $('<input></input>');

					field.attr('type', 'hidden');
					field.attr('name', key);
					field.attr('value', value);

					form.append(field);
				});
				$(form).appendTo('body').submit();
			},
			error: (response) => {
				console.log(response);
				if (response.status == 401){
					$('#rf-error-area').text(`Erro: ${response.responseJSON.data.Erro}`);
				}
			},
		});
	});

	$('body').on('click', '#rf-search-button', e => {
		e.preventDefault();

		if (!$('#rf-phone-search').val()){
			return;
		}

		$.ajax({
			type: 'POST',
			url: wpCustomData.ajax_url,
			dataType: 'json',
			data: {
				action: 'get_user_numbers',
				postId: wpCustomData.postId,
				user: $('#rf-phone-search').val(),
			},
			success: (response) => populateUserNumbersList($, response.data['data'])
		});
	});

	$('.raffle-number.rf-avaiable').click(e => {
		wpCustomData['selectedNumber'] = $(e.target).text().replace(/^0+/, '');
		$('#rf-payment-modal').show();
	});

	$('#rf-filter-all').click(e => {
		$('.raffle-number.rf-avaiable').show();
		$('.raffle-number.rf-reserved').show();
		$('.raffle-number.rf-paid').show();
	});

	$('#rf-filter-avaiable').click(e => {
		$('.raffle-number.rf-avaiable').show();
		$('.raffle-number.rf-reserved').hide();
		$('.raffle-number.rf-paid').hide();
	});

	$('#rf-filter-reserved').click(e => {
		$('.raffle-number.rf-avaiable').hide();
		$('.raffle-number.rf-reserved').show();
		$('.raffle-number.rf-paid').hide();
	});

	$('#rf-filter-paid').click(e => {
		$('.raffle-number.rf-avaiable').hide();
		$('.raffle-number.rf-reserved').hide();
		$('.raffle-number.rf-paid').show();
	});

	$('#rf-filter-my').click(e => {
		$('#rf-search-modal').show();
	});

	$('#rf-send-proof').click(e => {
		window.location.href = '//' + wpCustomData.paymentPage;
	});

	$('#close-rf-search-modal').click(e => {
		$('#rf-search-modal').hide();
		$('#rf-search-area').hide();
	});

	$('#close-rf-payment-modal').click(e => {
		$('#rf-payment-modal').hide();
		$('#rf-error-area').empty();
	});

});