const moveToReservePage = () => {
	const args = {
		rf_buyer_selected_numbers: wpCustomData.selectedNumbers,
		rf_buyer_phone: jQuery('input[name=rf-register-phone]').val(),
		rf_buyer_email: jQuery('input[name=rf-register-email]').val(),
		rf_buyer_name: jQuery('input[name=rf-register-name]').val(),
	}

	let link = '';

	try {
		const url = new URL(wpCustomData.reservePage);
		link = url.host + url.pathname;
	} catch (_) {
		link = wpCustomData.reservePage;
	}

	const form = jQuery('<form></form>');
	form.attr('method', 'post');
	form.attr('action', '//' + link);

	jQuery.each(args, function(key, value) {
		const field = jQuery('<input></input>');

		field.attr('type', 'hidden');
		field.attr('name', key);
		field.attr('value', value);

		form.append(field);
	});
	jQuery(form).appendTo('body').submit();
}

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
		const data = $(this).attr('data');

		if (!data){
			return;
		}

		$('#raffle-data').html(`
			<h5>Nome:</h5>
			<p>${data}</p>
		`);
		$('#raffle-data-modal').show();
	});

	$('body').on('click', '#rf-reserve-button', e => {
		e.preventDefault();

		if (!wpCustomData.selectedNumbers.length){
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
				selectedNumbers:  wpCustomData.selectedNumbers,
				newStatus: 'reserved',
			},
			success: (response) => {
				$('#rf-payment-modal').hide();

				if (response.data.alreadyReserved.length > 0){
					const alreadyReserved = response.data.alreadyReserved.join(', ');
					const reserved = response.data.reserved.join(', ');

					let message = `<p>Não é possível reservar os seguintes números:</p>
						<p style="color: red;">${alreadyReserved}</p>`;

					if (reserved){
						$('#rf-move').css('visibility', 'initial');
						message = `<p>Os seguintes números foram reservados:</p>
							<p style="color: green;">${reserved}</p>` + message + 'Deseja seguir para a página de pagamentos?';
					} else {
						$('#rf-move').css('visibility', 'hidden');
					}

					$('#rf-modal-error-area').html(message);
					$('#raffle-error-modal').show();
					return;
				}
				moveToReservePage();
			},
			error: (response) => {
				console.log(response);
				if (response.status == 401){
					$('#rf-error-area').text(`Erro: ${response.responseJSON.data.Erro}`);
				}
			},
		});
	});

	$('body').on('click', '#rf-select-number', e => {
		const selectedNumber = parseInt($('#number-selector').val())

		if (wpCustomData.availableNumbers.indexOf(selectedNumber) < 0){
			$('#number-selector').val('');
			return;
		}

		if (wpCustomData.selectedNumbers.indexOf(selectedNumber) >= 0){
			$('#number-selector').val('');
			return;
		}

		$('#rf-reserve-area').append(
			`<div class="raffle-number rf-available">${selectedNumber}<span class="rf-remove-number">&times;</span></div>`
		);
		$('#number-selector').val('');
		wpCustomData.selectedNumbers.push(selectedNumber);
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

	$('body').on('click', '.rf-remove-number', function(e) {
		const number = parseInt($(this).parent().text());
		wpCustomData.selectedNumbers = wpCustomData.selectedNumbers.filter(e => e !== number);
		$(this).parent().remove();
	});

	$('#rf-reserve-numbers').click(e => {
		wpCustomData.selectedNumbers = []
		$('#rf-reserve-area').empty();
		$('#rf-payment-modal').show();
	});

	$('#rf-filter-all').click(e => {
		$('.raffle-number.rf-available').show();
		$('.raffle-number.rf-reserved').show();
		$('.raffle-number.rf-paid').show();
	});

	$('#rf-filter-available').click(e => {
		$('.raffle-number.rf-available').show();
		$('.raffle-number.rf-reserved').hide();
		$('.raffle-number.rf-paid').hide();
	});

	$('#rf-filter-reserved').click(e => {
		$('.raffle-number.rf-available').hide();
		$('.raffle-number.rf-reserved').show();
		$('.raffle-number.rf-paid').hide();
	});

	$('#rf-filter-paid').click(e => {
		$('.raffle-number.rf-available').hide();
		$('.raffle-number.rf-reserved').hide();
		$('.raffle-number.rf-paid').show();
	});

	$('#rf-filter-my').click(e => {
		$('#rf-search-modal').show();
	});

	$('#rf-send-proof').click(e => {
		try {
			const url = new URL(wpCustomData.paymentPage);
			const link = url.host + url.pathname;
			window.location.href = '//' + link;
		} catch (_) {
			window.location.href = '//' + wpCustomData.paymentPage;
		}
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

	$('#close-rf-error-modal').click(e => {
		$('#raffle-error-modal').hide();
	});

});