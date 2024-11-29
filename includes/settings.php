<?php

add_action('admin_menu', 'feedtune_add_settings_menu');
function feedtune_add_settings_menu()
{
    add_options_page(
        'FeedTune Settings',
        'FeedTune',
        'manage_options',
        'feedtune',
        'feedtune_settings_page'
    );
}

function feedtune_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Handle form submission with nonce verification
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'feedtune_save_settings')) {
            wp_die(esc_html__('Nonce verification failed. Please try again.', 'feedtune'));
        }

        // Save feed enable/disable settings
        $feeds = [
            'rss' => isset($_POST['feedtune_enabled_feeds']['rss']),
            'rss2' => isset($_POST['feedtune_enabled_feeds']['rss2']),
            'atom' => isset($_POST['feedtune_enabled_feeds']['atom']),
            'comments' => isset($_POST['feedtune_enabled_feeds']['comments']),
        ];

        // Save redirection settings
        $http_code = isset($_POST['feedtune_redirect_http_code'])
            ? intval($_POST['feedtune_redirect_http_code'])
            : 301; // Default
        $redirect_option = isset($_POST['feedtune_feed_redirect'])
            ? sanitize_text_field(wp_unslash($_POST['feedtune_feed_redirect']))
            : 'none'; // Default

        // Update options
        update_option('feedtune_enabled_feeds', $feeds);
        update_option('feedtune_feed_redirect', $redirect_option);
        update_option('feedtune_redirect_http_code', $http_code);

        // Flush rewrite rules
        flush_rewrite_rules();

        echo '<div class="updated"><p>' . esc_html__('Settings saved and permalinks refreshed.', 'feedtune') . '</p></div>';
    }

    // Get current options
    $enabled_feeds = get_option('feedtune_enabled_feeds', []);
    $all_selected = !in_array(false, $enabled_feeds, true);
    $redirect_option = get_option('feedtune_feed_redirect', 'none');
    $redirect_http_code = get_option('feedtune_redirect_http_code', 301);

?>
    <div class="wrap">
        <h1><?php echo esc_html__('FeedTune Settings', 'feedtune'); ?></h1>
        <form method="POST" id="feedtune-settings-form">
            <?php wp_nonce_field('feedtune_save_settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php echo esc_html__('Enable/Disable All Feeds', 'feedtune'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" id="feedtune_enable_all" <?php checked($all_selected); ?>>
                            <?php echo esc_html__('Quickly enable or disable all feeds at once.', 'feedtune'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('RSS Feed', 'feedtune'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" class="feedtune-individual" name="feedtune_enabled_feeds[rss]" value="1" <?php checked($enabled_feeds['rss']); ?>>
                            <?php echo esc_html__('Control the visibility of the RSS feed.', 'feedtune'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('RSS2 Feed', 'feedtune'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" class="feedtune-individual" name="feedtune_enabled_feeds[rss2]" value="1" <?php checked($enabled_feeds['rss2']); ?>>
                            <?php echo esc_html__('Control the visibility of the RSS2 feed.', 'feedtune'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Atom Feed', 'feedtune'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" class="feedtune-individual" name="feedtune_enabled_feeds[atom]" value="1" <?php checked($enabled_feeds['atom']); ?>>
                            <?php echo esc_html__('Control the visibility of the Atom feed.', 'feedtune'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Comments Feed', 'feedtune'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" class="feedtune-individual" name="feedtune_enabled_feeds[comments]" value="1" <?php checked($enabled_feeds['comments']); ?>>
                            <?php echo esc_html__('Control the visibility of the comments feed.', 'feedtune'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php echo esc_html__('Feed URL Redirection', 'feedtune'); ?></th>
                    <td>
                        <fieldset id="feedtune-redirection-section" <?php if ($all_selected) echo 'disabled'; ?>>
                            <label>
                                <input type="radio" name="feedtune_feed_redirect" value="none" <?php checked($redirect_option, 'none'); ?>>
                                <strong><?php echo esc_html__('No Redirection:', 'feedtune'); ?></strong>
                                <?php echo esc_html__('Keep the feed URLs active.', 'feedtune'); ?>
                            </label><br>
                            <label>
                                <input type="radio" name="feedtune_feed_redirect" value="home" <?php checked($redirect_option, 'home'); ?>>
                                <strong><?php echo esc_html__('Redirect to Home Page:', 'feedtune'); ?></strong>
                                <?php echo esc_html__('Send all feed URLs to the homepage.', 'feedtune'); ?>
                            </label><br>
                            <label>
                                <input type="radio" name="feedtune_feed_redirect" value="404" <?php checked($redirect_option, '404'); ?>>
                                <strong><?php echo esc_html__('Redirect to 404 Page:', 'feedtune'); ?></strong>
                                <?php echo esc_html__('Send feed URLs to the 404 page.', 'feedtune'); ?>
                            </label><br>
                            <label>
                                <input type="radio" name="feedtune_feed_redirect" value="parent" <?php checked($redirect_option, 'parent'); ?>>
                                <strong><?php echo esc_html__('Redirect to Parent URL:', 'feedtune'); ?></strong>
                                <?php echo esc_html__('Redirect feed URLs to their parent', 'feedtune'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="feedtune_redirect_http_code"><?php echo esc_html__('Redirect HTTP Code', 'feedtune'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="feedtune_redirect_http_code" name="feedtune_redirect_http_code" value="<?php echo esc_attr($redirect_http_code); ?>" min="300" max="399" <?php if ($all_selected) echo 'disabled'; ?>>
                        <?php echo esc_html__('Set the HTTP status code for feed redirections (e.g., 301 for permanent, 302 for temporary).', 'feedtune'); ?>
                    </td>
                </tr>
            </table>
            <p>
                <input type="submit" value="<?php echo esc_attr__('Save Settings', 'feedtune'); ?>" class="button button-primary">
            </p>
        </form>
    </div>

    <script>
        const toggleAllCheckbox = document.getElementById('feedtune_enable_all');
        const individualCheckboxes = document.querySelectorAll('.feedtune-individual');
        const redirectionSection = document.getElementById('feedtune-redirection-section');
        const redirectFields = document.querySelectorAll('#feedtune-settings-form input[name="feedtune_feed_redirect"], #feedtune_redirect_http_code');

        // Update the "Enable/Disable All" checkbox state
        const updateToggleAllState = () => {
            const allChecked = [...individualCheckboxes].every(checkbox => checkbox.checked);
            toggleAllCheckbox.checked = allChecked;
        };

        // Enable/Disable the redirection section
        const updateRedirectionState = () => {
            const anyDisabled = [...individualCheckboxes].some(checkbox => !checkbox.checked);
            redirectionSection.disabled = !anyDisabled;
            redirectFields.forEach(field => {
                field.disabled = !anyDisabled;
            });
        };

        // Handle "Select/Deselect All" toggle
        toggleAllCheckbox.addEventListener('change', () => {
            const isChecked = toggleAllCheckbox.checked;
            individualCheckboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
            });
            updateRedirectionState();
        });

        // Handle individual checkbox changes
        individualCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                updateToggleAllState();
                updateRedirectionState();
            });
        });

        // Initialize on page load
        updateToggleAllState();
        updateRedirectionState();
    </script>

<?php
}
