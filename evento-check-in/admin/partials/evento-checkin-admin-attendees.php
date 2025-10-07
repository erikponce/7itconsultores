<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://7itconsultores.com/
 * @since      1.0.0
 *
 * @package    Evento_Checkin
 * @subpackage Evento_Checkin/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <form method="post">
        <?php
        $attendees_list_table = new Evento_Checkin_Attendees_List_Table();
        $attendees_list_table->prepare_items();
        $attendees_list_table->display();
        ?>
    </form>
</div>
