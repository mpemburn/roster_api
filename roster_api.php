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

    protected $apiUrl = 'http://chesapeakespokesclub.org/cso_roster/public/api';
    //protected $apiUrl = 'https://cso_roster.test';

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
        $version = '1.03';
        wp_enqueue_style('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css');
        wp_enqueue_style('roster_api', plugin_dir_url(__FILE__) . 'css/roster_api.css', '', $version);

        wp_enqueue_script('bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js');
        wp_register_script('roster_api', plugin_dir_url(__FILE__) . 'js/roster_api.js', '', $version, true);
        wp_enqueue_script('roster_api');

    }

    public function memberFormHandler( $att, $content ) {
        $prefixes = ['Mr.', 'Mrs.', 'Ms.', 'Hon.', 'Dr.'];
        $suffixes = ['Jr.', 'Sr.', 'II', 'III', 'MD', 'DDS', 'PA', 'JD', 'OD'];
        $states = ['DC', 'DE', 'MD', 'NJ', 'NY', 'PA', 'VA'];
        $member = $this->getMemberDataOrFail();

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

        $url = $this->apiUrl . '/user/' . $data['email'] . '/' . $data['zip'];
        $response = $this->makeApiCall('GET', $url);
        $member = json_decode($response['body']);

        if (!empty($member)) {
            $member->member_since_date = date('F j, Y', strtotime($member->member_since_date));
        }
        return $member;
    }

    public function updateMember()
    {
        $data = $_POST['data'];
        parse_str($data, $parsed);

        $url = $this->apiUrl . '/member/post';
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
