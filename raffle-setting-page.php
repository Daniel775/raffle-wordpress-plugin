<?php
add_action('admin_menu', 'raffle_setting_page_creator');
add_action('admin_init', 'register_raffle_setting_page');

function register_raffle_setting_page() {
	register_setting('raffle-settings-group', 'raffle_reserve_page_link');
	register_setting('raffle-settings-group', 'raffle_payment_page_link');

	add_settings_section('raffle-settings-options', 'Configurações gerais', 'raffle_settings_options',
		'raffle_custom_options_page');

	add_settings_field('raffle-reserve-link', 'Link da página de reserva:', 'raffle_reserve_link',
		'raffle_custom_options_page', 'raffle-settings-options');
	add_settings_field('raffle-payment-link', 'Link da página de pagamento:', 'raffle_payment_link',
		'raffle_custom_options_page', 'raffle-settings-options');
}

function raffle_setting_page_creator() {
	add_submenu_page('edit.php?post_type=rifa', 'Configurações', 'Configurações', 'manage_options',
		'raffle_custom_options_page', 'raffle_setting_page_callback'); 
}

function raffle_setting_page_callback() {
	?>
	<div class="wrap"><div id="icon-tools" class="icon32"></div>
		<h2>Configurações das Rifas</h2>
		<br/>
		<?php settings_errors();?>
		<form action="options.php" method="post">
			<?php
			settings_fields('raffle-settings-group');
			do_settings_sections('raffle_custom_options_page');
			submit_button();
			?>
		</form>
	</div>
	<?php
}

function raffle_settings_options() {
	echo 'Defina as configuração gerais que serão usadas pos todos os posts do tipo rifa.';
}

function raffle_reserve_link() {
	$link = esc_attr(get_option('raffle_reserve_page_link'));

	echo '<input type="text" name="raffle_reserve_page_link" value="'.$link.'" />';
}

function raffle_payment_link() {
	$link = esc_attr(get_option('raffle_payment_page_link'));

	echo '<input type="text" name="raffle_payment_page_link" value="'.$link.'" />';
}

?>