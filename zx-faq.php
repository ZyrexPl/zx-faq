<?php
/**
Plugin Name: FAQ ZYREX
Version: 1.0
Description: Pytania i odpowiedzi.
Author: Michał Żyrek
Author URI: http://zyrex.pl
Plugin URI: http://zyrex.pl/plugin/faq
 */

require_once 'class/class.php';
$zxfaq = new ZXFAQ_Faq();

function zxfaq_add_scripts_and_styles() {
    wp_enqueue_style('zxfaq-main-css', plugins_url( 'css/main.css', __FILE__ ));
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css');
}

add_action('wp_enqueue_scripts', 'zxfaq_add_scripts_and_styles');

function zxfaq_activation() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'zxfaq';

    if ($wpdb->get_var("SHOW TABLES LIKE '" . $table_name . "'") != $table_name) {
        $query = "CREATE TABLE " . $table_name . " (
        id int(9) NOT NULL AUTO_INCREMENT,
        pytanie TEXT NOT NULL,
        odpowiedz TEXT NOT NULL,
        PRIMARY KEY  (id)
        )";

        $wpdb->query($query);
    }
}

function zxfaq_activation_data() {
	global $wpdb;

	$pytanie = 'Moje pytanie';
	$odpowiedz = 'Odpowiedź';

	$table_name = $wpdb->prefix . 'zxfaq';

	$wpdb->insert(
		$table_name,
		array(
			'pytanie' => $pytanie,
			'odpowiedz' => $odpowiedz,
		)
	);
}

register_activation_hook(__FILE__, 'zxfaq_activation');
register_activation_hook(__FILE__, 'zxfaq_activation_data');

    function wyswietl_faq(){
        global $zxfaq;
        $zyrexFaq = $zxfaq->get_faq();
        if ($zyrexFaq) {
          echo '<div class="accordions">';
          $a = 0;
            foreach ($zyrexFaq as $p) {
              $a++;
              $pytanie = $p->pytanie;
              $odpowiedz = $p->odpowiedz;
              echo '
              <div class="accordion">
                <input type="checkbox" id="faq' . esc_html($a) . '" />
                <label for="faq' . esc_html($a) . '" class="acc-label">' . esc_html($pytanie) . '</label>
                <div class="acc-content">
                  <p>' . esc_html($odpowiedz) . '</p>
                </div>
              </div>
              ';
        }
        echo '</div>';
      }
    }

 add_shortcode('zxfaq', 'wyswietl_faq');
