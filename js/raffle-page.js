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
	$('body').on('click', '.rf-reserved,.rf-paid', function(e) {
		const data = $(this).attr('data').split('\n');

		if (data.length < 2){
			return;
		}

		$('#raffle-data').html(`
			<h5>Nome:</h5>
			<p>${data[0]}</p>
			<h5>Número:</h5>
			<p>${data[1]}</p>
			<h5>Email:</h5>
			<p>${data[2] || '---'}</p>
		`);
		$('#raffle-data-modal').show();
	});

	$('body').on('click', '#rf-reserve-button', e => {
		e.preventDefault();

		if (!wpCustomData.selectedNumber){
			return;
		}

		if (!$('input[name=rf-register-name]').val() || !$('input[name=rf-register-phone]').val()){
			return;
		}

		$.ajax({
			type: 'POST',
			url: wpCustomData.ajax_url,
			dataType: 'json',
			data: {
				action: 'update_user_data',
				postId: wpCustomData.postId,
				phone: $('input[name=rf-register-phone]').val(),
				name: $('input[name=rf-register-name]').val(),
				email:  $('input[name=rf-register-email]').val(),
				selectedNumber:  wpCustomData.selectedNumber,
				newStatus: 'reserved',
			},
			success: (response) => {
				const args = {
					rf_buyer_selected_number: wpCustomData.selectedNumber,
					rf_buyer_phone: $('input[name=rf-register-phone]').val(),
					rf_buyer_email: $('input[name=rf-register-email]').val(),
					rf_buyer_name: $('input[name=rf-register-name]').val(),
				}

				const form = $('<form></form>');
				form.attr('method', 'post');
				form.attr('action', '//' + wpCustomData.reservePage);

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
		wpCustomData['selectedNumber'] = $(e.target).text().replace(/^0+/, '') || '0';
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

	$('#close-rf-data-modal').click(e => {
		$('#raffle-data-modal').hide();
	});

});