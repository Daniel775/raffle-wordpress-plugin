let rfListPage = 1;

const getRFPost = (data) => {
	const active = data.status == 'active';
	const element = `
		<a href="${data.permalink}">
		<div class="rf-post-element">
			<div class="rf-post-image" style="background-image: url(${data.image});">
				<p>R$ ${data.price}</p>
			</div>
			<div style="height: 50%; position: relative;">
				<p class="rf-post-title">${data.title}</p>
				<div class="rf-post-footer">
					<p class="rf-total-number">Números<br/>${data.total_numbers}</p>
					<p class="rf-reserved-number">Reservados<br/>${data.reserved}</p>
					<p class="rf-avaiable-number">Restantes<br/>${data.total_numbers - data.reserved}</p>
				</div>
				<div class="rf-footer-button" style="background-color: ${active ? '#81d742' : '#dc3545'};">
					${active ? 'COMPRAR RIFA' : 'RIFA ENCERRADA'}
				</div>
			</div>
		</div>
		</a>
	`;
	return element;
};

const getRFPostList = () => {
	jQuery('#spinner-container').show();

	jQuery.ajax({
		type: 'POST',
		url: wpCustomData.ajax_url,
		dataType: 'json',
		data: {
			action: 'get_rf_post_list',
			page: rfListPage,
		},
		success: (response) => {
			if (rfListPage == 1){
				jQuery('#rf-left-arrow').attr('disabled', 'disabled');
			} else {
				jQuery('#rf-left-arrow').removeAttr('disabled');
			}

			if (rfListPage == wpCustomData.max_num_pages){
				jQuery('#rf-right-arrow').attr('disabled', 'disabled');
			} else {
				jQuery('#rf-right-arrow').removeAttr('disabled');
			}

			wpCustomData.max_num_pages = response.data.max_num_pages;
			jQuery('#rf-content-list').empty();

			if (jQuery.isEmptyObject(response.data.posts)){
				jQuery('#rf-content-list').html('<p>Nenhum resultado encontrado.</p>');
				jQuery('#spinner-container').hide();
				return;
			}

			let elements = '';

			response.data.posts.forEach(element => {
				elements = elements + getRFPost(element);
			});

			jQuery('#rf-content-list').html(elements);
			jQuery('#spinner-container').hide();
		},
		error: (response) => console.log(response)
	});
};

jQuery(document).ready(($) => {
	getRFPostList();

	$('body').on('click', '#rf-arrow-box-r', e => {
		if (rfListPage == wpCustomData.max_num_pages){
			return;
		}

		rfListPage += 1;
		getRFPostList();
	});

	$('body').on('click', '#rf-arrow-box-l', e => {
		if (rfListPage == 1){
			return;
		}

		rfListPage -= 1;
		getRFPostList();
	});
});