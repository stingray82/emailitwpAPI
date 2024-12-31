<?php
/**
 * Plugin Name:       EmailIt API Interface
 * Plugin URI:        https://github.com/stingray82/
 * Description:       Interface for configuring EmailIt API settings.
 * Version:           1.0
 * Author:            Stingray82
 * Author URI:        https://github.com/stingray82/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       emailit-api-interface
 * Domain Path:       /languages
 */

// Constants for default values
define( 'EMAILIT_DEFAULT_FROM_NAME', 'WordPress' );
define( 'EMAILIT_DEFAULT_FROM_EMAIL', 'from@email.com' );

/**
 * Register plugin settings.
 */
function emailit_register_api_settings() {
    register_setting( 'emailit_api_options', 'emailit_api_key' );
    register_setting( 'emailit_api_options', 'emailit_from_name' );
    register_setting( 'emailit_api_options', 'emailit_from_email' );
    register_setting( 'emailit_api_options', 'emailit_debug' );
}
add_action( 'admin_init', 'emailit_register_api_settings' );

/**
 * Add a settings page under Tools.
 */
add_action( 'plugins_loaded', function() {
    function emailit_api_menu() {
        add_management_page(
            'EmailIt API Settings',
            'EmailIt API Settings',
            'manage_options',
            'emailit-api-settings',
            'emailit_api_settings_page'
        );
    }
    add_action( 'admin_menu', 'emailit_api_menu', 20 );
});

/**
 * Render the settings page.
 */
function emailit_api_settings_page() {
    ?>
    <div class="wrap">
       <h1><svg id="Layer_2" class="h-7" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 53.02 45.95" style="height: 30px; vertical-align: middle; margin-left: 10px;">
                <g id="Layer_1-2">
                    <g id="mail-send-envelope--envelope-email-message-unopened-sealed-close">
                        <g id="Subtract">
                            <path d="m7.83,45.41c3.85.27,9.96.55,18.68.54,8.72,0,14.84-.29,18.68-.56,3.85-.27,6.9-3.18,7.25-7.06.29-3.34.58-8.38.57-15.36,0-.44,0-.88,0-1.3-3.14,1.45-6.31,2.83-9.51,4.13-2.95,1.19-6.15,2.39-9.11,3.29-2.91.89-5.74,1.55-7.88,1.55s-4.98-.66-7.88-1.55c-2.96-.9-6.16-2.1-9.11-3.29C6.31,24.5,3.14,23.13,0,21.68c0,.43,0,.86,0,1.31,0,6.98.29,12.02.58,15.36.34,3.89,3.4,6.79,7.25,7.06Z" fill="#15c182" stroke-width="0"></path>
                            <path d="m.05,17.8c.1-4.37.31-7.74.52-10.19C.91,3.73,3.97.83,7.82.56,11.67.29,17.78,0,26.5,0c8.72,0,14.84.28,18.68.54,3.85.27,6.91,3.17,7.25,7.06.22,2.45.43,5.81.53,10.19-.04.02-.08.03-.12.05l-.05.02-.16.08c-.98.46-1.95.91-2.94,1.35-2.48,1.12-4.98,2.19-7.51,3.22-2.9,1.17-6,2.33-8.82,3.19-2.87.88-5.27,1.4-6.85,1.4s-3.98-.52-6.85-1.39c-2.82-.86-5.92-2.02-8.82-3.19-3.52-1.42-7.01-2.95-10.45-4.56l-.16-.08-.05-.02s-.08-.04-.12-.05h0Z" fill="#007b5e" stroke-width="0"></path>
                        </g>
                    </g>
                </g>
            </svg>  EmailIt API Settings</h1>
            <h3> This doesn't use SMTP and is therefore suitable for situations where your SMTP ports are blocked this uses the CURL API and it requires an API key NOT an SMTP KEY </h3>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'emailit_api_options' );
            do_settings_sections( 'emailit_api_options' );
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="emailit_api_key">API Key</label></th>
                    <td>
                        <input type="password" id="emailit_api_key" name="emailit_api_key" value="<?php echo esc_attr( get_option( 'emailit_api_key' ) ); ?>" class="regular-text" />
                        <p class="description">Your EmailIt API key.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="emailit_from_name">From Name</label></th>
                    <td>
                        <input type="text" id="emailit_from_name" name="emailit_from_name" value="<?php echo esc_attr( get_option( 'emailit_from_name', EMAILIT_DEFAULT_FROM_NAME ) ); ?>" class="regular-text" />
                        <p class="description">The name that will appear as the sender.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="emailit_from_email">From Email</label></th>
                    <td>
                        <input type="email" id="emailit_from_email" name="emailit_from_email" value="<?php echo esc_attr( get_option( 'emailit_from_email', EMAILIT_DEFAULT_FROM_EMAIL ) ); ?>" class="regular-text" />
                        <p class="description">The email address that will appear as the sender.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Intercept and send emails via EmailIt API.
 */
function emailit_intercept_and_send_email_via_api($phpmailer) {
    $apiKey = trim( get_option( 'emailit_api_key' ) );
    $from = get_option( 'emailit_from_name', EMAILIT_DEFAULT_FROM_NAME ) . ' <' . get_option( 'emailit_from_email', EMAILIT_DEFAULT_FROM_EMAIL ) . '>';
    $url = 'https://api.emailit.com/v1/emails';

    // Extract email details from PHPMailer
    $toAddresses = $phpmailer->getToAddresses();
    $subject = $phpmailer->Subject;
    $htmlBody = $phpmailer->Body;

    // Prepare recipients list
    $to = [];
    foreach ($toAddresses as $address) {
        $to[] = $address[0]; // Extract email address
    }
    $to = implode(',', $to); // Convert to comma-separated string

    // Prepare headers
    $headers = array(
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    );

    // Prepare payload
    $payload = json_encode(array(
        'from' => $from,
        'to' => $to,
        'subject' => $subject,
        'html' => $htmlBody
    ));

    // Use cURL to send the email via EmailIt API
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    // Handle cURL errors
    if (curl_errno($ch)) {
        //error_log('EmailIt API cURL Error: ' . curl_error($ch));
    } else {
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //error_log('EmailIt API Response Code: ' . $responseCode);
        //error_log('EmailIt API Response: ' . $response);
    }

    curl_close($ch);

    // Prevent PHPMailer from sending the email
    $phpmailer->ClearAllRecipients();
    $phpmailer->ClearAttachments();
}
add_action('phpmailer_init', 'emailit_intercept_and_send_email_via_api');

