<?php

include 'settings.php';

/*
 * @wordpress-plugin
 * Plugin Name: RosterAPI Plugin
 * Description: Interface with CSO Roster API
 * Version: 1.0 Alpha
 * Author: Mark Pemburn
 * Author URI: http://www.pemburnia.com/
*/

class RosterAPI
{

    protected $apiUrl;
    protected $paypalSandboxKey;
    protected $paypalProductionKey;
    protected $paypalAmounts;
    protected $duesAmount;
    protected $devMode = false;
    protected $devApiUrl = 'https://cso_roster.test/api';

    public static function register()
    {
        $instance = new self;
        if (is_admin()) {
            new \AdminSettings();
        } else {
            $instance->loadSettings();
        }
        $instance->enqueueAssets();
        add_action( 'init', array( $instance, 'registerShortcodes' ) );
        // Set up AJAX handlers
        add_action('wp_ajax_roster_api_fetch', [$instance, 'fetchMemberFromRoster']);
        add_action('wp_ajax_roster_api_update', [$instance, 'updateMember']);
        add_action('wp_ajax_nopriv_roster_api_fetch', [$instance, 'fetchMemberFromRoster']);
        add_action('wp_ajax_nopriv_roster_api_update', [$instance, 'updateMember']);
    }

    private function __construct()
    {
    }

    public function fetchMemberFromRoster()
    {
        $post = $_POST['data'];

        // Parse query string into array
        parse_str($post, $data);
        $data['email'] = $data['member_email'];
        $data['zip'] = $data['member_zip'];

        $this->loadSettings();

        $response = $this->getMemberDataOrFail($data);

        wp_send_json([
            'success' => $response->success,
            'action' => 'fetch',
            'data' => $this->getFetchMessage($response->success)
        ]);

        die();
    }

    /**
     *
     */
    public function enqueueAssets()
    {
        $version = '1.01';
        wp_enqueue_style( 'jquery-ui'. 'http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css' );
        wp_enqueue_style('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');
        wp_enqueue_style('roster_api', plugin_dir_url(__FILE__) . 'css/roster_api.css', '', $version);

        wp_enqueue_script('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_script('paypal', 'https://www.paypalobjects.com/api/checkout.js');
        wp_register_script('validate', plugin_dir_url(__FILE__) . 'js/validate.js', '', $version, true);
        wp_enqueue_script('validate');
        wp_enqueue_style( 'wp-jquery-ui-dialog' );
        wp_register_script('roster_api', plugin_dir_url(__FILE__) . 'js/roster_api.js', '', $version, true);
        wp_enqueue_script('roster_api');
        wp_register_script('paypal_button', plugin_dir_url(__FILE__) . 'js/paypal_button.js', '', $version, true);
        wp_enqueue_script('paypal_button');

        wp_register_script('ajax-js', null);
        wp_localize_script('ajax-js', 'jsNamespace', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'paypalSandboxKey' => $this->paypalSandboxKey,
            'paypalProductionKey' => $this->paypalProductionKey,
            'duesAmount' => $this->duesAmount
        ]);
        wp_enqueue_script('ajax-js');
    }

    public function memberFormHandler( $att, $content ) {
        $prefixes = ['Mr.', 'Mrs.', 'Ms.', 'Hon.', 'Dr.'];
        $suffixes = ['Jr.', 'Sr.', 'II', 'III', 'MD', 'DDS', 'PA', 'JD', 'OD'];
        $states = ['DC', 'DE', 'MD', 'NJ', 'NY', 'PA', 'VA'];
        $dues = $this->duesAmount;
        $payments = $this->getPaypalAmounts();
        $legal = $this->getLegalContent();

        ob_start();
        include 'member_form.php';
        $output = ob_get_clean();

        return $output;
    }

    public function registerShortcodes() {
        add_shortcode( 'memberform', array( $this, 'memberFormHandler' ));
    }

    public function getMemberDataOrFail($data = null)
    {
        if (!is_null($data)) {
            $url = $this->apiUrl . '/user/' . $data['email'] . '/' . $data['zip'];
            $response = $this->makeApiCall('GET', $url);
            $member = json_decode($response['body']);

            return $member;
        }

        return null;
    }

    public function updateMember()
    {
        $data = $_POST['data'];
        parse_str($data, $parsed);

        $this->loadSettings();

        $url = $this->apiUrl . '/member/post';
        $response = $this->makeApiCall('POST', $url, $parsed);
        $responseBody = json_decode($response['body']);
        $success = (!empty($responseBody) && (!property_exists($responseBody, 'errors')));

        wp_send_json([
            'success' => $success,
            'action' => 'update',
            'data' => $responseBody
        ]);

        die();
    }

    protected function getFetchMessage($success)
    {
        if ($success) {
            $message = 'Thanks! Please continue your renewal process by clicking the <strong>PayPal</strong> button.';
        } else {
            $message = 'Sorry! We were unable to find a member with that email address and zip code in our database.
                You may have used a different email address to sign up originally.
                Please contact our <a href="/contact">Club Secretary</a>';
        }

        return $message;
    }

    protected function getPaypalAmounts()
    {
        $amounts = [];

        if (!empty($this->paypalAmounts)) {
            $lines = explode(PHP_EOL, $this->paypalAmounts);
            foreach ($lines as $line) {
                $lineParts = explode(':', $line);
                $label = trim($lineParts[0]);
                $amount = trim($lineParts[1]);
                if (stristr($label, 'dues') !== false) {
                    $this->duesAmount = $amount;
                }

                $amounts[$label] = $amount;
            }
        }

        return $amounts;
    }

    protected function getLegalContent()
    {
        $content_post = get_post(11);
        $content = $content_post->post_content;
        $content = apply_filters('the_content', $content);
        $content = str_replace(']]>', ']]&gt;', $content);

        return $content;
    }

    protected function loadSettings()
    {
        $option = get_option('roster_option_name');

        $settings = (!empty($option)) ? (object) $option : null;

        if (!is_null($settings)) {
            $this->apiUrl = (!$this->devMode) ? $settings->api_uri : $this->devApiUrl;
            $this->paypalSandboxKey = $settings->paypal_sandbox;
            $this->paypalProductionKey = $settings->paypal_production;
            $this->duesAmount = $settings->dues_amount;
            $this->paypalAmounts = $settings->paypal_amounts;

        }

    }

    protected function makeApiCall($action, $url, $data = [])
    {
        $response = null;

        $username = 'your-username';
        $password = 'your-password';
        $headers = array( 'Authorization' => 'Basic ' . base64_encode( "$username:$password" ) );
        if ($action == 'GET') {
            $response = wp_remote_get( $url, [
                'headers' => $headers,
                'sslverify' => false
            ] );
        }
        if ($action == 'POST') {
            $response = wp_remote_post( $url, [
                'headers' => $headers,
                'body' => $data,
                'sslverify' => false
            ] );
        }

        return $response;
    }

}
// Load as singleton to add actions and enqueue assets
RosterAPI::register();
