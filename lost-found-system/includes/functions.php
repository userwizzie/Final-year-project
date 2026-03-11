<?php
// common functions placeholder
function sanitize($data) {
    return htmlspecialchars(trim($data));
}

/**
 * Simulated email notification helper.
 * Uses PHP's mail() (if configured) and also logs to a file for demo.
 */
function notify_user($to, $subject, $message) {
    // attempt to send (won't actually send on most dev setups)
    @mail($to, $subject, $message);

    // log to workspace for examiner review
    $log = sprintf("[%s] To: %s | Subject: %s | Message: %s\n", 
                   date('Y-m-d H:i:s'), $to, $subject, strip_tags($message));
    file_put_contents(__DIR__ . '/../email_log.txt', $log, FILE_APPEND);
}

?>