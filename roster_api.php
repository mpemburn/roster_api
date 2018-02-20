<?php

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

    public static function register()
    {
        $instance = new self;
        $instance->enqueueAssets();
        add_action( 'init', array( $instance, 'registerShortcodes' ) );
        // Set up AJAX handlers
        add_action('wp_ajax_roster_api_fetch', [$instance, 'fetchMemberFromRoster']);
        add_action('wp_ajax_roster_api_update', [$instance, 'updateMember']);
    }

    private function __construct()
    {
    }

    public function fetchMemberFromRoster()
    {
        $data = $_POST['data'];

        parse_str($data, $parsed);

        $response = $this->getMemberDataOrFail($parsed);

        wp_send_json([
            'success' => !empty($response),
            'action' => 'fetch',
            'data' => $response
        ]);

        die();
    }

    /**
     *
     */
    public function enqueueAssets()
    {
        wp_enqueue_style('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');
        wp_enqueue_style('roster_api', plugin_dir_url(__FILE__) . 'css/roster_api.css', '', '1.0');

        wp_enqueue_script('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js');        wp_register_script('dom-observer', plugin_dir_url(__FILE__) . 'js/dom_observer.js', '', '1.0', true);
        wp_register_script('roster_api', plugin_dir_url(__FILE__) . 'js/roster_api.js', '', '1.0', true);
        wp_enqueue_script('roster_api');

    }

    public function memberFormHandler( $att, $content ) {
        $prefixes = ['Mr.', 'Mrs.', 'Ms.', 'Hon.', 'Dr.'];
        $suffixes = ['Jr.', 'Sr.', 'II', 'III', 'MD', 'DDS', 'PA', 'JD', 'OD'];
        $states = ['DC', 'DE', 'MD', 'NJ', 'NY', 'PA', 'VA'];
        $user = $this->getMemberDataOrFail();

        $user->member_since_date = date('F j, Y', strtotime($user->member_since_date));

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
        $data = (is_null($data)) ? ['email' => '', 'zip' => ''] : $data;

        $url = 'https://cso_roster.test/api/user/' . $data['email'] . '/' . $data['zip'];
        $response = $this->makeApiCall('GET', $url);
        $member = json_decode($response['body']);

        return $member;
    }

    public function updateMember()
    {
        $data = $_POST['data'];
        parse_str($data, $parsed);

        $url = 'https://cso_roster.test/api/member/post';
        $response = $this->makeApiCall('POST', $url, $parsed);
        $success = json_decode($response['body']);

        wp_send_json([
            'success' => !empty($response['body']),
            'action' => 'update',
            'data' => $response['body']
        ]);

        die();
    }

    protected function makeApiCall($action, $url, $data = [])
    {
        $response = null;

        $username = 'your-username';
        $password = 'your-password';
        $headers = array( 'Authorization' => 'Basic ' . base64_encode( "$username:$password" ) );
        if ($action == 'GET') {
            $response = wp_remote_get( $url, [ 'headers' => $headers, 'sslverify' => false ] );
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
