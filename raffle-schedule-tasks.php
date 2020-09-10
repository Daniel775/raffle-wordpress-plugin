<?php
register_activation_hook(plugin_dir_path(__FILE__).'main.php', 'register_rf_cron_jobs');
register_deactivation_hook(plugin_dir_path(__FILE__).'main.php', 'remove_rf_cron_jobs');

function register_rf_cron_jobs() {
	if (!wp_next_scheduled('clear_old_raffle_reserves')) {
	wp_schedule_event(time(), 'daily', 'clear_old_raffle_reserves');
	}
}

add_action('clear_old_raffle_reserves', 'remove_old_raffle_reserves');

function remove_old_raffle_reserves() {
	$posts = get_posts(array(
		'fields' => 'ids',
		'post_type' => 'rifa',
		'posts_per_page' => -1,
	));

	foreach ($posts as $key => $value) {
		$numbers_data = get_post_meta($value, 'numbers_data');
		$limit_days = get_post_meta($value, 'reserve_limit');
		$status = get_post_meta($value, 'status');

		if (!$numbers_data || !$limit_days || $status[0] !== 'active'){
			continue;
		}

		clear_raffle_reserves($value, $numbers_data[0], $limit_days[0]);
	}
}

function clear_raffle_reserves($post_id, $numbers_data, $limit_days) {
	foreach ($numbers_data as $key => $value) {
		if (!array_key_exists('r_date', $value) || $value['status'] !== 'reserved'){
			continue;
		}

		$r_date = DateTime::createFromFormat('Y-m-d', $value['r_date']);
		$l_date = date_add($r_date, date_interval_create_from_date_string($limit_days.' days'));
		
		if (new DateTime('now') > $l_date){
			unset($numbers_data[$key]);
		}
	}

	update_post_meta($post_id, 'numbers_data', $numbers_data);
}

function remove_rf_cron_jobs() {
	wp_clear_scheduled_hook('clear_old_raffle_reserves');
}
?>