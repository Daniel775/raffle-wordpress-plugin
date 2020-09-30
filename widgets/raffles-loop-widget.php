<?php
class RfLoopWidget extends WP_Widget {
	public function __construct() {
		$widget_options = array( 'classname' => 'rf-posts-loop',
			'description' => 'Mostra uma lista contendo as rifas cadastradas;' );
		parent::__construct('rf-posts-loop', 'Lista de Rifas', $widget_options);
	}

	public function widget($args, $instance){
		wp_enqueue_style( 'rf-posts-list', plugins_url('../css/rf-posts-list-style.css', __FILE__));
		wp_enqueue_script('jquery');
		wp_enqueue_script('rf-posts-list-behavior', plugins_url('../js/rf-posts-list-behavior.js', __FILE__));
		wp_localize_script('rf-posts-list-behavior', 'wpCustomData', array(
			'ajax_url' => admin_url('admin-ajax.php')
		));

?>
		<div id="rf-posts-list">
			<div id="spinner-container" style="display: none;">
				<img src="<?=plugin_dir_url(__file__).'../assets/Rolling-1s-88px.gif'?>">
			</div>
			<div id="rf-content-list">
			</div>
			<div id="rf-arrows-container">
				<div id="rf-arrow-box-l">
					<div id="rf-left-arrow"></div>
				</div>
				<div id="rf-arrow-box-r">
					<div id="rf-right-arrow"></div>
				</div>
			</div>
		</div>
<?php
	}
}

?>