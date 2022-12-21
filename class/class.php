<?php
class ZXFAQ_Faq {
    private $wpdb;
    private $table_name;

    function __construct(){
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'zxfaq';
        add_action( 'admin_menu', array( &$this, 'zx_add_menu' ));
    }

    function zx_add_menu(){
        add_menu_page( 'ZX FAQ', 'ZX FAQ', 'administrator', 'faq', array( &$this, 'zx_main_page'), '', 33 );
    }

    function zx_main_page(){
        if(isset($_POST['faq_action'])) {
            if($_POST['faq_action'] == 'add') {
                //Dodawanie wiadomości
                if($this->add_post($_POST['pytanie'], $_POST['odpowiedz'])) {
                    $notice = 'Dodano pytanie i odpowiedź';
                    $klasa = 'notice-success';
                } else {
                    $notice = 'Nie dodano';
                    $klasa = 'notice-error';
                }
            } else if($_POST['faq_action'] == 'edit') {
                //edycja wiadomości
                if($this->edit_post($_POST['faq_post_id'],$_POST['pytanie'], $_POST['odpowiedz'])) {
                    $notice = 'Edytowano';
                    $klasa = 'notice-success';
                } else {
                    $notice = 'Nie udało się zaktualizować';
                    $klasa = 'notice-error';
                }
            }
        }

        if(isset($_POST['faq_delete'])) {
            //usuwanie wiadomości
            if($this->delete_post($_POST['faq_post_id'])) {
                $notice = 'Usunięto';
                $klasa = 'notice-success';
            } else {
                $notice = 'Nie usunięto';
                $klasa = 'notice-error';
            }
        }

        //pobieram wiadomość do edycji
        $edit = FALSE;
        if(isset($_POST['faq_to_edit'])) {
            $edit = $this->get_faq_post($_POST['faq_post_id']);
        }

        ?>
        <div class="warp">
            <h2><span class="dashicons dashicons-welcome-write-blog"></span>FAQ Zyrex</h2>
            <h3>Shortcode: [zxfaq]</h3>
            <?php if (isset($notice)) {
              echo '<div class="notice ' . esc_html($klasa) . '">' . esc_html($notice ) . '</div>';
            }  else {
              echo '';
            } ?>
            <form method="POST" enctype='multipart/form-data'>
                <?php if ($edit != FALSE) {
                  echo '<input type="hidden" name="faq_post_id" value="' . esc_html($edit->id) . '" />';
                  echo '<input type="hidden" name="faq_action" value="edit"/>';
                  echo '<input type="text" name="pytanie" value="' . esc_html($edit->pytanie) . '" placeholder="Pytanie"/>';
                  echo '<input type="text" name="odpowiedz" value="' . esc_html($edit->odpowiedz) . '" placeholder="Odpowiedź"/>';
                  echo '<input type="submit" value="Edytuj" class="button-primary"/>';
                } else {
                  echo '<input type="hidden" name="faq_action" value="add"/>';
                  echo '<input type="text" name="pytanie" value="" placeholder="Pytanie"/>';
                  echo '<input type="text" name="odpowiedz" value="" placeholder="Odpowiedź"/>';
                  echo '<input type="submit" value="Dodaj" class="button-primary"/>';
                }  ?>
            </form>
            <hr>
            <?php
            $all_posts = $this->get_faq();
            if ($all_posts) {
                echo '<table class="widefat">';
                echo '<thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pytanie</th>
                                        <th>Odpowiedź</th>
                                        <td>Akcja</td>
                                    </tr>
                                </thead>';
                echo '<tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Pytanie</th>
                                        <th>Odpowiedź</th>
                                        <td>Akcja</td>
                                    </tr>
                                </tfoot>';
                echo '<tbody>';
                foreach ($all_posts as $p) {
                  $id = $p->id ;
                  $pytanie = $p->pytanie;
                  $odpowiedz = $p->odpowiedz;
                    echo '<tr>';
                    echo '<td>' . esc_html($id) . '</td>';
                    echo '<td>' . esc_html($pytanie) . '</td>';
                    echo '<td>' . esc_html($odpowiedz) . '</td>';
                    echo '<td><form method="POST">
                                        <input type="hidden" name="faq_post_id" value="' . esc_html($id) . '" />
                                        <input type="submit" name="faq_to_edit" value="Edytuj" class="button-primary" />
                                        <input type="submit" name="faq_delete" value="Usuń" class="button-secondary error" />
                                    </form></td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
            }
            ?>
            </div>
        <?php
    }

    function add_post($pytanie, $odpowiedz) {
        //sprawdzam czy nie pusty i czy jest zalogowany
        if(trim($pytanie) != ''){
            $pytanie = esc_sql($pytanie);
            $odpowiedz = esc_sql($odpowiedz);
            $this->wpdb->insert( $this->table_name, array('pytanie' => $pytanie, 'odpowiedz' => $odpowiedz) );

            return TRUE;
        }
        return FALSE;
    }

    function get_faq() {
        return $this->wpdb->get_results( $this->wpdb->prepare("SELECT * FROM $this->table_name") );
    }

    //funkcja służąca do pobrania wiadomości o konkretnym id
    //zwraca obiekt
    function get_faq_post($id) {
        $id = esc_sql($id);
        $faq_post = $this->wpdb->get_results( $this->wpdb->prepare( "SELECT * FROM $this->table_name WHERE id = %d", $id ) );
        if(isset($faq_post[0])){
            return $faq_post[0];
        } else {
            return FALSE;
        }
    }

    //funkcja edycji wiadomości pobiera id oraz nową treść
    function edit_post($id, $pytanie, $odpowiedz){
        if(trim($pytanie) != '') {
            $id = esc_sql($id);
            $pytanie = esc_sql($pytanie);
            $odpowiedz = esc_sql($odpowiedz);
            $res = $this->wpdb->update($this->table_name, array('pytanie' => $pytanie, 'odpowiedz' => $odpowiedz), array('id' => $id));
            return $res;
        }else {
            return FALSE;
        }
    }

    //funkcja odpowiedzialna za usuwanie wiadomości
    function delete_post($id) {
        $id = esc_sql($id);
        return $this->wpdb->delete($this->table_name, array('id' => $id));
    }
}
