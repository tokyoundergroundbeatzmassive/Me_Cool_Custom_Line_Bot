<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;

// 一般的なパス
$common_path = dirname(__FILE__) . '/../../../wp-load.php';

// Lightsailでの特別なパス
$lightsail_path = '/opt/bitnami/wordpress/wp-load.php';

// ファイルが存在するかどうかで読み込むパスを切り替える
if (file_exists($common_path)) {
    require_once($common_path);
} elseif (file_exists($lightsail_path)) {
    require_once($lightsail_path);
} else {
    // エラーハンドリング
    error_log("wp-load.php could not be found in either the common or Lightsail-specific paths.");
    if (function_exists('wp_die')) {
        wp_die("Critical error: wp-load.php not found.");
    } else {
        die("Critical error: wp-load.php not found.");
    }
}

// GuzzleHttpのクライアントを初期化
$client = new Client(['headers' => ['Content-Type' => 'application/json; charset=utf-8']]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POSTリクエストの処理
    error_log(print_r($_POST, true));
    $user_message = filter_var($_POST['user_message'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $user_id = filter_var($_POST['user_id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $session_id = filter_var($_POST['session_id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $member_id = filter_var($_POST['member_id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // The URL of Web App
    $app_url_base = rtrim(get_option('mcc_line_bot_app_url'), '/');
    $app_url = $app_url_base . '/message';

    // Prepare the POST data
    $post_data = array(
        'message' => array(
            'text' => $user_message
        ),
        'user_id' => $user_id,
        'session_id' => $session_id,
        'member_id' => $member_id,
        'stream' => true
    );

    // Send a POST request to the backend web app
    try {
        $response = $client->request('POST', $app_url, [
            'body' => json_encode($post_data),
            'timeout' => 60
        ]);

        $response_body = $response->getBody()->getContents();
        echo $response_body;

    } catch (Exception $e) {
        $error_message = $e->getMessage();

        // Get the status code directly from the exception
        $status_code = $e->getCode();

        // Extract the status code from the error message
        //preg_match('/\b\d{3}\b/', $error_message, $matches);
        //$status_code = $matches[0] ?? null;
        $locale = get_locale();
        $serverOverloadedMessage;
        if ($locale == 'ja') {
            $serverOverloadedMessage = 'サーバーが過負荷状態です。後ほど再試行してください。';
        } else {
            $serverOverloadedMessage = 'The server is overloaded or not ready yet. Please try again later.';
        }
        $response_message = "Something went wrong: " . $error_message;
        if ($status_code == 429) {
            $response_message = get_option('too_many_requests_message', 'Too many requests');
        } elseif ($status_code == 503) {
            $response_message = $serverOverloadedMessage;
        }
        http_response_code($status_code);
        echo json_encode(['error' => $response_message]);
        exit;
    }

    exit;
}

?>