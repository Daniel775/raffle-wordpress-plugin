<?php

/*
Plugin Name: Sorteio
Description: Plugin criado para a realização de sorteios.
Version: 0.3
Author: Daniel Bomfim
*/

include_once(plugin_dir_path(__FILE__).'raffle-setting-page.php');
include_once(plugin_dir_path(__FILE__).'raffle-schedule-tasks.php');
include_once(plugin_dir_path(__FILE__).'widgets/raffles-loop-widget.php');


add_action('widgets_init', 'register_raffles_widgets');

function register_raffles_widgets() {
	register_widget('RfLoopWidget');
}

add_action('init', 'create_raffle');

function create_raffle() {
	register_post_type( 'rifa',
		array(
			'labels' => array(
				'name' => 'Rifas',
				'singular_name' => 'Rifa',
				'add_new' => 'Adicionar nova',
				'add_new_item' => 'Adicionar nova rifa',
				'edit' => 'Editar',
				'edit_item' => 'Editar rifa',
				'new_item' => 'Nova rifa',
				'view' => 'Ver',
				'view_item' => 'Ver rifa',
				'search_items' => 'Procurar rifas',
				'not_found' => 'Nenhuma rifa encontrado',
				'not_found_in_trash' => 'Nenhuma rifa encontrado na lixeira',
			),
 
			'public' => true,
			'menu_position' => 5,
			'supports' => array( 'title', 'editor', 'thumbnail'),
			'taxonomies' => array( '' ),
			'menu_icon' => 'dashicons-tickets-alt',
		)
	);
}

add_action('admin_init', 'custom_metabox');

function custom_metabox() {
	add_meta_box('custom_metabox_01', 'Dados da rifa', 'custom_metabox_field', 'rifa', 'normal', 'low');
}

function custom_metabox_field() {
	global $post;

	$data = get_post_custom($post->ID);
	$number_price = isset($data['number_price']) ? esc_attr($data['number_price'][0]) : '';
	$max_number = isset($data['max_number']) ? esc_attr($data['max_number'][0]) : '';
	$status = isset($data['status']) ? esc_attr($data['status'][0]) : 'active';
	$reserve_limit = isset($data['reserve_limit']) ? esc_attr($data['reserve_limit'][0]) : '';
	?>
	<div id="acme-navigation">
		<h2 class="nav-tab-wrapper current" id="raffle-tabs">
			<a id="taffle-tab1" class="nav-tab nav-tab-active"
				onclick="tabsBehavior('#frag1', '#taffle-tab1');" href="javascript:">Dados</a>
			<a id="taffle-tab2" class="nav-tab" 
				onclick="tabsBehavior('#frag2', '#taffle-tab2');" href="javascript:">Buscar por número</a>
		</h2>
	</div>
	<div id="mytabs">
		<div class="shown" id="frag1">
			<p>Valor por número (R$):</p>
			<input type="text" name="number_price" value="<?=$number_price?>"
				onkeyup="toMoney(this);" required/>
			<p>Quantidade de números:</p>
			<input type="number" min="1" name="max_number" value="<?=$max_number?>" required/>
			<p>Status:</p>
			<div style="display: flex; flex-direction: row;">
				<select name="status" id="status">
					<option value="active" <?=$status == 'active' ? 'selected' : ''?>>Ativo</option>
					<option value="finished" <?=$status == 'finished' ? 'selected' : ''?>>Encerrado</option>
				</select>
				<input class='hidden' type="number" id="w_number" name="winner_number" placeholder="Número sorteado">
			</div>
			<p>Limite de dias de reserva de número:</p>
			<input type="number" min="0" name="reserve_limit" value="<?=$reserve_limit?>" required/>
		</div>
		<div class="hidden" id="frag2">
			<p>Buscar:</p>
			<div style="display: flex; flex-direction: row;">
				<input type="text" name="search_number" id="search_number" value="" novalidate />
				<button class="button button-primary button-large" id="rf-search">Pesquisar</button>
			</div>
			<div id="rf-search-result" class="hidden">
				<br/>
				<p>Pessoa:</p>
				<p id="rf-search-person-name"></p>
				<p>Contato:</p>
				<p id="rf-search-person-phone"></p>
				<p>Email:</p>
				<p id="rf-search-person-email"></p>
				<p>Estatus:</p>
				<div style="display: flex; flex-direction: row;">
					<select name="rf-number-status" id="rf-number-status" style="margin-right: 10px;">
						<option value="avaiable">Disponível</option>
						<option value="reserved">Pendente</option>
						<option value="paid">Pago</option>
					</select>
					<button class="button button-primary button-large hidden" id="rf-save-change">Salvar</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}

add_action('save_post', 'save_custom_data');

function save_custom_data() {
	global $post;

	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post -> ID;
	}

	if (!$post) {
		return;
	}

	update_post_meta($post->ID, 'number_price', $_POST['number_price']);
	update_post_meta($post->ID, 'max_number', $_POST['max_number']);
	update_post_meta($post->ID, 'reserve_limit', $_POST['reserve_limit']);
	update_post_meta($post->ID, 'status', $_POST['status']);
}

add_filter('single_template', 'custom_template');

function custom_template($single) {
	global $post;

	if ($post->post_type == 'rifa') {
		if (file_exists(plugin_dir_path(__FILE__) . '/single-raffle.php')) {
			return plugin_dir_path(__FILE__) . '/single-raffle.php';
		}
	}
}

add_action('wp_ajax_update_number_data','update_number_data');
add_action('wp_ajax_get_number_data','get_number_data');

add_action('wp_ajax_update_user_data', 'update_user_number_data');
add_action('wp_ajax_nopriv_update_user_data', 'update_user_number_data');

add_action('wp_ajax_get_user_numbers', 'get_user_numbers');
add_action('wp_ajax_nopriv_get_user_numbers', 'get_user_numbers');

add_action('wp_ajax_get_rf_post_list', 'get_rf_post_list');
add_action('wp_ajax_nopriv_get_rf_post_list', 'get_rf_post_list');

function update_number_data(){
	if (!is_admin() || !$_REQUEST['newStatus'] || !$_REQUEST['postId']){
		exit;
	}

	$data = get_post_meta($_REQUEST['postId'], 'numbers_data');

	if (!$data){
		$data = array();
	} else {
		$data = $data[0];
	}

	if (array_key_exists($_REQUEST['selectedNumber'], $data)){
		$data[$_REQUEST['selectedNumber']]['status'] = $_REQUEST['newStatus'];
	} else {
		$data[$_REQUEST['selectedNumber']] = array(
			status => $_REQUEST['newStatus'],
			user_phone => null,
			user_name => null,
			user_email => null,
		);
	}

	if ($_REQUEST['newStatus'] == 'reserved') {
		$current_date = getdate();
		$data[$_REQUEST['selectedNumber']]['r_date'] = $current_date['year'].'-'.$current_date['mon'].'-'.$current_date['mday'];
	}

	$result = update_post_meta($_REQUEST['postId'], 'numbers_data', $data);
	wp_send_json_success(array($_REQUEST['selectedNumber'] => $_REQUEST['newStatus']));
	exit;
}

function update_user_number_data(){
	if ($_REQUEST['newStatus'] !== 'reserved'){
		exit;
	}

	if (strlen($_REQUEST['phone']) > 60 || strlen($_REQUEST['name']) > 150 || strlen($_REQUEST['email']) > 60){
		exit;
	}

	$data = get_post_meta($_REQUEST['postId'], 'numbers_data');

	if (!$data){
		$data = array();
	} else {
		$data = $data[0];
	}

	if (array_key_exists($_REQUEST['selectedNumber'], $data) && $_REQUEST['newStatus'] == $data[$_REQUEST['selectedNumber']]['status']){
		wp_send_json_error(array('Erro' => 'Número já reservado, recarregue a página e tente novamente.'), 401);
		exit;
	}

	$current_date = getdate();

	$data[$_REQUEST['selectedNumber']] = array(
		'status' => $_REQUEST['newStatus'],
		'user_phone' => $_REQUEST['phone'],
		'user_name' => $_REQUEST['name'],
		'user_email' => $_REQUEST['email'],
		'r_date' => $current_date['year'].'-'.$current_date['mon'].'-'.$current_date['mday'],
	);

	$result = update_post_meta($_REQUEST['postId'], 'numbers_data', $data);
	wp_send_json_success(array('data' => $data[$_REQUEST['selectedNumber']]));
	exit;
}

function get_number_data(){
	if (!is_admin()){
		exit;
	}

	$result = get_post_meta($_REQUEST['postId'], 'numbers_data');

	if (!result){
		wp_send_json_success(array('data' => null));
		exit;
	}

	foreach ($result[0] as $index => $value){
		if ($index == $_REQUEST['number']){
			$value['selectedNumber'] = $index;
			wp_send_json_success(array('data' => $value));
			exit;
		}
	}

	wp_send_json_success(array('data' => null, 'selectedNumber' => $_REQUEST['number']));
	exit;
}

function get_user_numbers(){
	$result = get_post_meta($_REQUEST['postId'], 'numbers_data');
	$user_numbers = array();

	if (!result){
		wp_send_json_success(array('data' => $user_numbers));
		exit;
	}

	foreach ($result[0] as $index => $value){
		if ($value['user_phone'] == $_REQUEST['user']){
			$user_numbers[$index] = $value;
		}
	}

	wp_send_json_success(array('data' => $user_numbers));
	exit;
}

function get_rf_post_list(){
	$query = new WP_Query(array(
		'post_type' => 'rifa',
		'post_status' => 'publish',
		'posts_per_page' => 6,
		'paged' => $_REQUEST['page'],
	));

	$max_num_pages = $query->max_num_pages;

	$posts = array();

	while ($query->have_posts()){
		$query->the_post();
		$meta_data = get_post_custom(get_the_ID());
		$numbers_data = get_post_meta(get_the_ID(), 'numbers_data');
		$numbers_data = !$numbers_data ? array() : $numbers_data[0];
		$reserved = 0;

		foreach ($numbers_data as $key => $value) {
			if ($value['status'] !== 'avaiable'){
				$reserved += 1;
			}
		}

		$new_post = array(
			'image' => wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'medium')[0],
			'title' => get_the_title(),
			'price' => isset($meta_data['number_price']) ? esc_attr($meta_data['number_price'][0]) : '',
			'total_numbers' => isset($meta_data['max_number']) ? esc_attr($meta_data['max_number'][0]) : '0',
			'reserved' => $reserved,
			'status' => isset($meta_data['status']) ? esc_attr($meta_data['status'][0]) : 'active',
			'permalink' => get_the_permalink(),
		);
		$posts[] = $new_post;
	}

	wp_reset_postdata();
	wp_send_json_success(array('max_num_pages' => $max_num_pages, 'posts' => $posts));
	exit;
}

function enqueue_rf_plugin_scripts() {
	global $post;

	wp_enqueue_script( 'rf-edition-behavior', plugins_url('js/rf-edition-behavior.js', __FILE__));
	if ($post){
		wp_localize_script('rf-edition-behavior', 'wpCustomData', array('postId' => $post->ID));
	}
}

add_action('admin_enqueue_scripts', 'enqueue_rf_plugin_scripts');
?>