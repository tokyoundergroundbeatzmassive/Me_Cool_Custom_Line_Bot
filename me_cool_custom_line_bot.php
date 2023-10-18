<?php
/*
Plugin Name: Me Cool Custom Line Bot
Description: A custom Chat bot for your website.
Version: 1.0
Author: Anonymous Producer
*/

// Define the path to the plugin (to call images)
define('MY_PLUGIN_PATH', plugin_dir_url(__FILE__));

function mcc_line_bot_enqueue_scripts()
{
    wp_enqueue_script('mcc_line_bot_script', plugins_url('dummy.js', __FILE__), array('jquery'), '1.0', true);

    wp_enqueue_style('mcc_line_bot_styles', plugins_url('static/styles.css', __FILE__), array(), '1.0', 'all');

    wp_localize_script(
        'mcc_line_bot_script',
        'mcc_line_bot_script_vars',
        array(
            'MY_PLUGIN_PATH' => MY_PLUGIN_PATH,
            'welcome_message' => get_option('welcome_message', 'Hello!'),
            'chat_button_text' => get_option('chat_button_text', 'Help'),
            'too_many_requests_message' => get_option('too_many_requests_message', 'Too many requests')
        )
    );
}
add_action('wp_enqueue_scripts', 'mcc_line_bot_enqueue_scripts');

function mcc_line_bot_menu()
{
    add_options_page(
        'MCC Line Bot Settings',
        // page title
        'MCC Line Bot',
        // menu title
        'manage_options',
        // capability
        'mcc-line-bot',
        // menu slug
        'mcc_line_bot_options_page'
    );
}
add_action('admin_menu', 'mcc_line_bot_menu');

function mcc_line_bot_options_page()
{
    ?>
    <div class="wrap">
        <h1>MCC Line Bot Settings</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('mcc_line_bot_options');
            do_settings_sections('mcc_line_bot');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Member ID</th>
                    <td><input type="text" name="member_id" value="<?php echo esc_attr(get_option('member_id', '')); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">APP URL</th>
                    <td><input type="text" name="mcc_line_bot_app_url"
                            value="<?php echo esc_attr(get_option('mcc_line_bot_app_url')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Chat Button Text</th>
                    <td><input type="text" name="chat_button_text"
                            value="<?php echo esc_attr(get_option('chat_button_text', 'Help')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Welcome Message</th>
                    <td><input type="text" name="welcome_message"
                            value="<?php echo esc_attr(get_option('welcome_message', 'Hello!')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Too Many Requests Message</th>
                    <td><input type="text" name="too_many_requests_message"
                            value="<?php echo esc_attr(get_option('too_many_requests_message', 'Too many requests')); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Enable Logging</th>
                    <td><input type="checkbox" name="mcc_line_bot_shortcode_logging" value="1" <?php checked(1, get_option('mcc_line_bot_shortcode_logging'), true); ?> /></td>
                </tr>
            </table>
            <?php
            submit_button('Save Settings');
            ?>
        </form>
        <?php
        $app_url_base = rtrim(get_option('mcc_line_bot_app_url'), '/');
        $member_id = esc_attr(get_option('member_id', ''));
        $config_url = $app_url_base . '/config/' . $member_id;
        ?>
        <a href="<?php echo $config_url; ?>" class="button button-secondary" target="_blank">Backend Config</a>
    </div>
    <?php
}

function mcc_line_bot_register_settings()
{
    register_setting('mcc_line_bot_options', 'member_id');
    register_setting('mcc_line_bot_options', 'mcc_line_bot_app_url');
    register_setting('mcc_line_bot_options', 'mcc_line_bot_shortcode_logging');
    register_setting('mcc_line_bot_options', 'chat_button_text');
    register_setting('mcc_line_bot_options', 'welcome_message');
    register_setting('mcc_line_bot_options', 'too_many_requests_message');
}
add_action('admin_init', 'mcc_line_bot_register_settings');

// The callback function that will replace [mcc_line_bot]
function mcc_line_bot_shortcode()
{
    ob_start();

    $ajax_handler_url = plugins_url('ajax-handler.php', __FILE__);

    // The form for the user to send a message
    ?>
    <div id="chat-container" class="chat-container">
        <div id="chat-window" class="chat-window" style="display:none;">
            <div id="chat-messages" class="chat-messages"></div>
            <div id="chat-input-area" class="chat-input-area">
                <form id="mcc_line_bot-form" action="" method="post" style="display: flex; position: relative;">
                    <textarea name="user_message" class="user_message" required></textarea>
                    <input type="hidden" name="user_id" id="user_id">
                    <input type="hidden" name="session_id" id="session_id">
                    <button type="submit" name="submit" class="submit_button">
                        <img src="<?php echo MY_PLUGIN_PATH; ?>static/paper-plane-svgrepo-com.svg" alt="Send"
                            class="send_icon">
                    </button>
                </form>
            </div>
        </div>
        <div class="chat-button-wrapper">
            <button id="chat-toggle" class="chat-button">
                <span class="chat-help-text">
                    <?php echo esc_html(get_option('chat_button_text', 'Help')); ?>
                </span>
                <img src="<?php echo esc_url(MY_PLUGIN_PATH); ?>static/icons8-bot-64.png" alt="bot" />
            </button>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
    <script>
        var userId;
        var sessionId = Math.random().toString(36).substring(2);

        // Try to use localStorage
        try {
            userId = localStorage.getItem('user_id');
            if (!userId) {
                userId = Math.random().toString(36).substring(2);
                localStorage.setItem('user_id', userId);
            }
        } catch (e) {
            // If localStorage is not available, use Cookies
            try {
                userId = Cookies.get('user_id');
                if (!userId) {
                    userId = Math.random().toString(36).substring(2);
                    Cookies.set('user_id', userId);
                }
            } catch (e) {
                // If Cookies are not available, show an error message
                alert('Please enable local storage or cookies in your browser settings.');
            }
        }

        if (userId) {
            document.getElementById('user_id').value = userId;
            document.getElementById('session_id').value = sessionId;
        } else {
            // Disable the chat form if no userId could be generated
            $('form').off('submit');
            $('[name="user_message"]').prop('disabled', true);
            $('[name="submit"]').prop('disabled', true);
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function () {
            var userId = document.getElementById('user_id').value;
            if (!userId) {
                // Disable the chat form if no userId could be generated
                $('form').off('submit');
                $('[name="user_message"]').prop('disabled', true);
                $('[name="submit"]').prop('disabled', true);
                return;  // Stop further execution of the script
            }

            var sessionId = document.getElementById('session_id').value;
            if (!sessionId) {
                // Disable the chat form if no sessionId could be generated
                $('form').off('submit');
                $('[name="user_message"]').prop('disabled', true);
                $('[name="submit"]').prop('disabled', true);
                return;  // Stop further execution of the script
            }

            var appUrl = "<?php echo esc_attr(get_option('mcc_line_bot_app_url')); ?>";  // PHPからAPP URLを取得
            var welcomeMessageShown = false;
            var chatButtonText = $('.chat-help-text').text();
            // グローバルスコープで変数を定義
            var memberId;

            $('.chat-button-wrapper').click(function () {
                memberId = "<?php echo esc_attr(get_option('member_id')); ?>";  // PHPからmember_idを取得

                if ($('#chat-window').is(':visible')) {
                    $('#chat-window').fadeOut(250);
                    $('#chat-toggle').html('<span class="chat-help-text">' + chatButtonText + '</span><img src="' + mcc_line_bot_script_vars.MY_PLUGIN_PATH + 'static/icons8-bot-64.png" alt="bot" />');
                    //console.log("Chatbot Closed", "Member ID:", memberId);  // チャットボットが閉じられたときのフラグ
                } else {
                    $('#chat-window').fadeIn(330);
                    $('#chat-toggle').html('<span class="chat-help-text">' + chatButtonText + '</span><span style="font-size: 25px; color: #FFF;">X</span>');
                    //console.log("Chatbot Opened", "Member ID:", memberId);  // チャットボットが開かれたときのフラグ

                    // フラグをチェックして、ウェルカムメッセージがまだ表示されていない場合にのみ表示します。
                    if (!welcomeMessageShown) {
                        mcc_line_bot_showWelcomeMessage();
                        welcomeMessageShown = true; // フラグを設定します。
                    }

                    // バックエンドにmemberIdを送信
                    $.ajax({
                        url: appUrl + '/get_cognito_id',
                        type: 'POST',
                        data: { member_id: memberId },
                        success: function (response) {
                            //console.log("Successfully sent memberId to backend:", response);
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log("Failed to send memberId to backend:", textStatus, errorThrown);
                        }
                    });

                    // メッセージボックスにフォーカスを戻します。
                    $('textarea[name="user_message"]').focus();
                }
            });

            var isComposing = false;

            $('textarea[name="user_message"]').on('keydown keypress compositionstart compositionend', function (e) {
                if (e.type === 'compositionstart') {
                    isComposing = true;
                } else if (e.type === 'compositionend') {
                    isComposing = false;
                } else if (e.key === 'Enter' && !e.shiftKey && !isComposing) {
                    e.preventDefault();
                    var user_message = $('[name="user_message"]').val().trim();
                    if (user_message !== '') {
                        // Simulate form submission by triggering a click event on the submit button
                        $('[name="submit"]').click();
                    } else {
                        // If the message is empty, trigger form validation
                        this.setCustomValidity('Please fill out this field.');
                        this.reportValidity();
                        this.setCustomValidity('');
                    }
                }
            });

            $('#mcc_line_bot-form').on('submit', function (e) {
                e.preventDefault();

                $('[name="user_message"]').prop('disabled', true);
                $('[name="submit"]').prop('disabled', true);

                var user_message = $('[name="user_message"]').val();
                var user_id = $('[name="user_id"]').val();
                var sessionId = $('[name="session_id"]').val();

                var user_message_bubble = '<div class="user-message-bubble">' + user_message + '</div>';
                $('#chat-messages').append(user_message_bubble);

                var typing_indicator = '.';
                var typing_indicator_bubble = $('<div class="ai-message-bubble"></div>');
                typing_indicator_bubble.text(typing_indicator);
                $('#chat-messages').append(typing_indicator_bubble);

                var typing_interval = setInterval(function () {
                    typing_indicator = typing_indicator === '...' ? '.' : typing_indicator + '.';
                    typing_indicator_bubble.text(typing_indicator);
                }, 500);

                setTimeout(function () {
                    $('#chat-messages').animate({ scrollTop: $('#chat-messages').prop('scrollHeight') }, 500);
                    $('[name="user_message"]').val('');

                    $.ajax({
                        url: '<?php echo $ajax_handler_url; ?>',
                        type: 'post',
                        data: { user_message: user_message, user_id: user_id, session_id: sessionId, member_id: memberId },
                        dataType: 'json',
                        success: function (response) {
                            //console.log("Received response from backend:", response);
                            clearInterval(typing_interval);
                            typing_indicator_bubble.remove();

                            // Check if the response contains the ready_for_stream flag
                            if (response.ready_for_stream) {
                                // EventSourceを使用してSSEをサポート
                                var source = new EventSource('<?php echo rtrim(get_option('mcc_line_bot_app_url'), '/'); ?>/stream_response');
                                //console.log("EventSource created and waiting for the first chunk...");

                                var ai_response_bubble = $('<div class="ai-message-bubble">Streaming...</div>');
                                $('#chat-messages').append(ai_response_bubble);
                                var isFirstChunk = true;

                                source.onmessage = function (event) {
                                    var data = JSON.parse(event.data);
                                    //console.log("Received data in real-time:", data);
                                    if (isFirstChunk) {
                                        // 最初のchunkの場合、"Loading..." テキストをクリア
                                        ai_response_bubble.empty();
                                        isFirstChunk = false;
                                    }
                                    if (data.content) {
                                        ai_response_bubble.append(data.content); // ここでchunkの内容を追加
                                    } else if (data.error) {
                                        ai_response_bubble.text(data.error);
                                    }

                                    $('#chat-messages').animate({ scrollTop: $('#chat-messages').prop('scrollHeight') }, 10);
                                    $('[name="user_message"]').prop('disabled', false);
                                    $('[name="submit"]').prop('disabled', false);
                                    $('[name="user_message"]').focus();
                                };

                                source.onerror = function (event) {
                                    clearInterval(typing_interval);
                                    typing_indicator_bubble.text('An error occurred. Please try again later.');
                                    $('[name="user_message"]').prop('disabled', false);
                                    $('[name="submit"]').prop('disabled', false);
                                    $('[name="user_message"]').focus();
                                    source.close();
                                };
                            } else if (response.error) {
                                // エラーメッセージがレスポンスに含まれている場合、それを表示します
                                typing_indicator_bubble.text(response.error);
                                $('[name="user_message"]').prop('disabled', false);
                                $('[name="submit"]').prop('disabled', false);
                                $('[name="user_message"]').focus();
                            } else {
                                // Handle the case where ready_for_stream is not true or not present
                                //console.log("Response does not contain ready_for_stream flag:", response);
                                // For example, display an error message to the user
                                clearInterval(typing_interval);
                                typing_indicator_bubble.text('An error occurred. Please try again later.');
                                $('[name="user_message"]').prop('disabled', false);
                                $('[name="submit"]').prop('disabled', false);
                                $('[name="user_message"]').focus();
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            clearInterval(typing_interval);

                            //console.log("HTTP Status Code:", jqXHR.status); // ステータスコードのログ出力
                            //console.log("Response Text:", jqXHR.responseText);

                            var responseJSON = jqXHR.responseJSON;
                            var error_message = responseJSON && responseJSON.error ? responseJSON.error : 'An error occurred. Please try again later.';

                            typing_indicator_bubble.text(error_message);

                            $('[name="user_message"]').prop('disabled', false);
                            $('[name="submit"]').prop('disabled', false);

                            // focus back to the text input field
                            $('[name="user_message"]').focus();
                        }
                    });
                });
            });
        });
        function mcc_line_bot_showWelcomeMessage() {
            var welcomeMessage = mcc_line_bot_script_vars.welcome_message;

            var typing_indicator = '.';
            var typing_indicator_bubble = $('<div class="ai-message-bubble"></div>');
            typing_indicator_bubble.text(typing_indicator);
            $('#chat-messages').append(typing_indicator_bubble);

            var typing_interval = setInterval(function () {
                typing_indicator = typing_indicator === '...' ? '.' : typing_indicator + '.';
                typing_indicator_bubble.text(typing_indicator);
            }, 500);

            setTimeout(function () {
                clearInterval(typing_interval);
                typing_indicator_bubble.remove();

                var bot_response = $('<div class="ai-message-bubble"></div>');
                bot_response.text(welcomeMessage);
                $('#chat-messages').append(bot_response);

                $('#chat-messages').animate({ scrollTop: $('#chat-messages').prop('scrollHeight') }, 500);
            }, 2000);  // 2回の". .. ..."を表示するために2000ミリ秒（2秒）の遅延を設定
        }
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('mcc_line_bot', 'mcc_line_bot_shortcode');