<?php
add_action('show_user_profile', 'custom_password_reset_field');
add_action('edit_user_profile', 'custom_password_reset_field');
function custom_password_reset_field($user)
{ ?>
    <h3>Passwort zurücksetzen</h3>
    <table class="form-table">
        <tr>
            <th>Reset-Link generieren</th>
            <td>
                <button class="button generate-reset-link" data-user-id="<?php echo esc_attr($user->ID); ?>">Reset Link
                    generieren
                </button>
                <br><br>
                <input type="text" class="reset-link regular-text" readonly="readonly">
                <button class="button copy-reset-link" type="button" onclick="copyToClipboard(this)">In Zwischenablage
                    kopieren
                </button>
            </td>
        </tr>
    </table>
    <?php
}
add_action('admin_footer', function () {
    ?>
    <script type="text/javascript">
        var $ = jQuery;
        jQuery(document).ready(function ($) {
            $('.generate-reset-link').click(function (e) {
                e.preventDefault();
                let resetLinkInput = $(this).closest('td').find('.reset-link');
                $.post(ajaxurl, {
                    'action': 'generate_reset_link',
                    'user_id': $(this).data('user-id')
                }, function (response) {
                    resetLinkInput.closest('td').find('.reset-link').val(response);
                });
            });
        });

        function copyToClipboard(element) {
            let $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(element).closest('td').find('.reset-link').val()).select();
            document.execCommand("copy");
            $temp.remove();
            alert("Link erfolgreich in die Zwischenablage kopiert.");
        }
    </script>;
    <?php
});
add_action('wp_ajax_generate_reset_link', function () {
    if (!is_user_logged_in() || !current_user_can('administrator')) {
        wp_die();
    }
    $user_id = intval($_POST['user_id']);
    if ($user_id <= 0) {
        wp_die();
    }
    $user = get_user_by('id', $user_id);
    if (!$user) {
        wp_die();
    }
    $reset_key = get_password_reset_key($user);
    if (is_wp_error($reset_key)) {
        wp_die();
    }
    echo add_query_arg(['action' => 'rp', 'key' => $reset_key, 'login' => rawurlencode($user->user_login)], network_site_url('wp-login.php', 'login'));
    wp_die();
});