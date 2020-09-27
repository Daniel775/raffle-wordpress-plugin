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
wp_enqueue_script('rf-template-behaviors', plugins_url('js/raffle-page.js', __FILE__));
wp_localize_script('rf-template-behaviors', 'wpCustomData', array(
	'postId' => $post->ID,
	'ajax_url' => admin_url('admin-ajax.php'),
	'paymentPage' => esc_attr(get_option('raffle_payment_page_link')),
));

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

$avaiable_number = 0;
$reserved_number = 0;
$paid_number = 0;

for($i = 0; $i <= $max_number-1; ++$i) {
	if (!array_key_exists($i, $numbers_data) || $numbers_data[$i]['status'] == 'avaiable'){
		$status = 'rf-avaiable';
		$avaiable_number += 1;
	} elseif ($numbers_data[$i]['status'] == 'reserved') {
		$status = 'rf-reserved';
		$reserved_number += 1;
	} else {
		$status = 'rf-paid';
		$paid_number += 1;
	}

	$raffle_elements = $raffle_elements.'<div class="raffle-number '.$status.'">'.sprintf("%0".$number_lenght."d", $i).'</div>';
}
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
					<div class="raffle-button" style="border-radius: 0; background-color: #222" id="rf-filter-avaiable">
						Disponíveis</br><?=$avaiable_number?>
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
			  		<p style="font-size: 22px; color: #dd3333;">R$ <?=$number_price?></p>
			  		<input type="text" name="rf-register-phone" id="rf-register-phone" placeholder="Contato telefônico"
			  		maxlength="60">
			  		<div id="rf-reserve-button"><b>Reservar Número</b></div>
			  		<p id="rf-error-area" style="color: red;"></p>
			  	</div>
			    <span id="close-rf-payment-modal" class="rf-modal-closer">&times;</span>
			  </div>
			</div>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
