<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since Twenty Nineteen 1.0
 */

global $post;
wp_enqueue_style('rf-template-style', plugins_url('css/single-template.css', __FILE__));
wp_enqueue_script('jquery');

get_header();

add_action('wp_enqueue_scripts', 'enqueue_rf_template_scripts');

$max_number = get_post_meta($post->ID, 'max_number')[0];
$number_price = get_post_meta($post->ID, 'number_price')[0];
$numbers_data = get_post_meta($post->ID, 'numbers_data');

if (!$numbers_data){
	$numbers_data = array();
} else {
	$numbers_data = $numbers_data[0];
}

$number_lenght = strlen((string)$max_number-1);
$raffle_elements = '';

$available_list = array();
$available_number = 0;
$reserved_number = 0;
$paid_number = 0;

for($i = 0; $i <= $max_number-1; ++$i) {
	$include_data = true;
	$data = '';

	if (!array_key_exists($i, $numbers_data) || $numbers_data[$i]['status'] == 'available'){
		$status = 'rf-available';
		$available_number += 1;
		$include_data = false;
		$available_list[] = $i;
	} elseif ($numbers_data[$i]['status'] == 'reserved') {
		$status = 'rf-reserved';
		$reserved_number += 1;
	} else {
		$status = 'rf-paid';
		$paid_number += 1;
	}

	/*if (is_user_logged_in() && $include_data){*/
	if ($include_data){
		$data = $numbers_data[$i]['user_name'];
	}

	$raffle_elements = $raffle_elements.'<div data="'.$data.'" class="raffle-number '.$status.'">'.sprintf("%0".$number_lenght."d", $i).'</div>';
}

wp_enqueue_script('rf-template-behaviors', plugins_url('js/raffle-page.js', __FILE__));
wp_localize_script('rf-template-behaviors', 'wpCustomData', array(
	'postId' => $post->ID,
	'ajax_url' => admin_url('admin-ajax.php'),
	'paymentPage' => esc_attr(get_option('raffle_payment_page_link')),
	'reservePage' => esc_attr(get_option('raffle_reserve_page_link')),
	'availableNumbers' => $available_list,
	'price' => $number_price,
	'selectedNumbers' => array(),
));
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main">
			<div id="raffle-container">
				<div id="raffle-image-container">
					<img src="<?=get_the_post_thumbnail_url($post->ID)?>">
				</div>
				<div id="raffle-text-container">
					<h1 id="raffle-title"><?=$post->post_title?></h1><br/>
					<p id="raffle-description"><?=$post->post_content?></p>
				</div>
			</div>
			<div id="raffle-area-container">
				<div class="raffle-filters-container">
					<div class="raffle-button" style="border-radius: 3px 0 0 3px; background-color: #0095ff" id="rf-filter-all">
						Todos</br><?=$max_number?>
					</div>
					<div class="raffle-button" style="border-radius: 0; background-color: #222" id="rf-filter-available">
						Disponíveis</br><?=$available_number?>
					</div>
					<div class="raffle-button" style="border-radius: 0; background-color: #f9a443" id="rf-filter-reserved">
						Reservados</br><?=$reserved_number?>
					</div>
					<div class="raffle-button" style="border-radius: 0 3px 3px 0; background-color: #81d742" id="rf-filter-paid">
						Pagos</br><?=$paid_number?>
					</div>
				</div>
				<div class="raffle-filters-container">
					<div class="raffle-button" style="border-radius: 3px 0 0 3px; background-color: #dc3545" id="rf-filter-my">
						Ver meu(s) número(s)
					</div>
					<div class="raffle-button" style="border-radius: 0 3px 3px 0; background-color: gray" id="rf-send-proof">
						Enviar comprovante
					</div>
					<br/>
					<div class="raffle-button" style=" break-before: always; border-radius: 3px; background-color: #35a269; visibility: hidden;" id="rf-reserve-numbers">
						Reservar números
					</div>
				</div>
				<br/>
				<div id="raffle-numbers-container">
					<?=$raffle_elements?>
				</div>
			</div>
			<div id="rf-search-modal" class="modal">
				<div class="modal-content">
					<div style="width: 100%; padding: 10px;">
						<p>Dígite seu contato telefônico:</p>
						<input type="text" name="rf-phone-search" id="rf-phone-search" value="">
						<div style="display: none;" id="rf-search-area"></div>
						<button id="rf-search-button">Buscar</button>
					</div>
					<span id="close-rf-search-modal" class="rf-modal-closer">&times;</span>
				</div>
			</div>
			<div id="rf-payment-modal" class="modal">
				<div class="modal-content">
					<div style="width: 100%; padding: 10px;">
						<p id="rf-p-total" style="font-size: 22px; color: #dd3333;">R$ <?=$number_price?></p>
						<input type="text" name="rf-register-name" class="rf-register-inputs" placeholder="Nome completo"
						maxlength="150" required>
						<input type="text" name="rf-register-phone" class="rf-register-inputs" placeholder="Contato telefônico"
						maxlength="60" required>
						<input type="email" name="rf-register-email" class="rf-register-inputs" placeholder="Email (Opcional)"
						maxlength="60" novalidate>
						<div id="rf-reserve-button"><b>Reservar Número</b></div>
						<p id="rf-error-area" style="color: red;"></p>
					</div>
					<span id="close-rf-payment-modal" class="rf-modal-closer">&times;</span>
				</div>
			</div>
			<div id="raffle-data-modal" class="modal">
				<div class="modal-content">
					<div id="raffle-data" style="width: 100%; padding: 10px;"></div>
					<span id="close-rf-data-modal" class="rf-modal-closer">&times;</span>
				</div>
			</div>
			<div id="raffle-error-modal" class="modal">
				<div class="modal-content">
					<div style="width: 100%; display: flex; flex-direction: column;">
						<div id="rf-modal-error-area" style="width: 100%; padding: 10px;"></div>
						<div style="width: 100%; display: flex; flex-direction: row; justify-content: space-around;">
							<div id="rf-cancel" onclick="jQuery('#raffle-error-modal').hide()">Permanecer nessa página</div>
							<div id="rf-move" onclick="moveToReservePage()">Continuar</div>
						</div>
					</div>
					<span id="close-rf-error-modal" class="rf-modal-closer">&times;</span>
				</div>
			</div>
			<div id="bottom-modal">
				<div id="raffle-bottom-modal">
					<div style="width: 100%; display: flex; flex-direction: column;">
						<div style="width: 100%; display: flex; flex-direction: column;">
							<div id="rf-selected-grid"></div>
							<div style="display: flex;flex-direction: row; justify-content: space-between;">
								<p style="font-size: 20px;" id="rf-total"></p>
								<div id="rf-finish" onclick="">✓ Concluir</div>
							</div>
						</div>
					</div>
					<span id="close-rf-bottom-modal" class="rf-modal-closer">&times;</span>
				</div>
			</div>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
