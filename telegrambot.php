<?php
// Replace YOUR_BOT_TOKEN with your actual bot token
define('BOT_TOKEN', '6212549422:AAEZUGgpeJOdXRp3kQdyfAGs41EQpmSQf2o');

// Set the URL for Telegram API
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');

// Initialize variables
$chat_id = '';
$smtp_host = '';
$smtp_port = '';
$smtp_username = '';
$smtp_password = '';
$from_name = '';
$subject = '';
$email_list = '';
$html_content = '';

// Define function to send messages through Telegram bot
function sendMessage($chat_id, $message)
{
    $url = API_URL . 'sendMessage?chat_id=' . $chat_id . '&text=' . urlencode($message);
    file_get_contents($url);
}

// Get updates from Telegram API
$update = json_decode(file_get_contents("php://input"), true);

// Check if user clicked "Start" button
if (isset($update['message']['text']) && $update['message']['text'] == '/start') {
    $chat_id = $update['message']['chat']['id'];
    sendMessage($chat_id, "Welcome to the bulk email sender bot! Please enter the SMTP details, email list, and HTML content that you want to use to send the emails. You can enter each input on a new line. To cancel, type /cancel at any time.\n\nPlease enter the SMTP host:");
} else {
    $chat_id = $update['message']['chat']['id'];
    $text = $update['message']['text'];

    switch ($text) {
        case '/cancel':
            sendMessage($chat_id, "Cancelled.");
            break;
        case $text:
            if ($smtp_host == '') {
                $smtp_host = $text;
                sendMessage($chat_id, "Please enter the SMTP port:");
            } elseif ($smtp_port == '') {
                $smtp_port = $text;
                sendMessage($chat_id, "Please enter the SMTP username:");
            } elseif ($smtp_username == '') {
                $smtp_username = $text;
                sendMessage($chat_id, "Please enter the SMTP password:");
            } elseif ($smtp_password == '') {
                $smtp_password = $text;
                sendMessage($chat_id, "Please enter the from name:");
            } elseif ($from_name == '') {
                $from_name = $text;
                sendMessage($chat_id, "Please enter the subject:");
            } elseif ($subject == '') {
                $subject = $text;
                sendMessage($chat_id, "Please enter the email list (one email address per line):");
            } elseif ($email_list == '') {
                $email_list = $text;
                sendMessage($chat_id, "Please enter the HTML content:");
            } elseif ($html_content == '') {
                $html_content = $text;
                sendMessage($chat_id, "Please confirm your inputs:\nSMTP Host: $smtp_host\nSMTP Port: $smtp_port\nSMTP Username: $smtp_username\nSMTP Password: $smtp_password\nFrom Name: $from_name\nSubject: $subject\nEmail List: $email_list\nHTML Content: $html_content\n\nTo proceed, type /send. To cancel, type /cancel.");
            } else {
                sendMessage($chat_id, "Invalid input.");
            }
            break;
        case '/send':
            $emails = explode("\n", $email_list);
            $mail_count = count($emails);
        // Send emails to each recipient
        for ($i = 0; $i < $mail_count; $i++) {
            $to_email = trim($emails[$i]);
            $headers = "From: $from_name <".$smtp_username.">\r\n";
            $headers .= "Reply-To: $from_name <".$smtp_username.">\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $message = $html_content;

            // Attempt to send email
            if (mail($to_email, $subject, $message, $headers, '-f'.$smtp_username)) {
                sendMessage($chat_id, "Email sent to $to_email.");
            } else {
                sendMessage($chat_id, "Failed to send email to $to_email.");
            }
        }
        break;
    default:
        sendMessage($chat_id, "Invalid command.");
        break;
}
}
?>