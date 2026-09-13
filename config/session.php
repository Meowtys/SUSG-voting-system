<?php

declare(strict_types=1);

const SESSION_IDLE_TIMEOUTS = [
    'voter' => 30,
    'comelec' => 30,
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrf_token(): string
{
    return $_SESSION['csrf_token'];
}

function validate_csrf_token(bool $jsonResponse = false): void
{
    $submittedToken = $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? $_POST['csrf_token']
        ?? '';

    if (
        !is_string($submittedToken)
        || $submittedToken === ''
        || !isset($_SESSION['csrf_token'])
        || !is_string($_SESSION['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], $submittedToken)
    ) {
        http_response_code(403);

        if ($jsonResponse) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Invalid CSRF token'
            ]);
            exit;
        }

        exit('Invalid CSRF token');
    }
}

function validate_session_activity(
    string $role,
    string $loginRedirect,
    bool $jsonResponse = false
): void {
    $roleConfig = [
        'voter' => [
            'activity_key' => 'voter_last_activity',
            'authenticated' => isset($_SESSION['user']),
            'session_keys' => [
                'user',
                'selectedVotes',
                'voter_last_activity',
            ],
        ],
        'comelec' => [
            'activity_key' => 'comelec_last_activity',
            'authenticated' => !empty($_SESSION['is_comelec_logged_in']),
            'session_keys' => [
                'is_comelec_logged_in',
                'comelec_name',
                'comelec_last_activity',
            ],
        ],
    ];

    if (!isset($roleConfig[$role])) {
        throw new InvalidArgumentException('Unsupported session role');
    }

    $config = $roleConfig[$role];

    if (!$config['authenticated']) {
        return;
    }

    $now = time();
    $lastActivity = $_SESSION[$config['activity_key']] ?? null;
    $timeout = SESSION_IDLE_TIMEOUTS[$role];

    if (!is_int($lastActivity) && !ctype_digit((string)$lastActivity)) {
        $_SESSION[$config['activity_key']] = $now;
        return;
    }

    if (($now - (int)$lastActivity) > $timeout) {
        foreach ($config['session_keys'] as $sessionKey) {
            unset($_SESSION[$sessionKey]);
        }

        $message = "You've been logged out due to inactivity.";

        if ($jsonResponse) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $message,
            ]);
            exit;
        }

        $_SESSION['error_message'] = $message;
        header('Location: ' . $loginRedirect);
        exit;
    }

    $_SESSION[$config['activity_key']] = $now;
}

function validate_voter_election(PDO $pdo, string $loginRedirect, bool $jsonResponse = false): void
{
    if (!isset($_SESSION['user'])) {
        return;
    }

    $stmt = $pdo->query(
        "SELECT election_id FROM elections WHERE is_current = 1 LIMIT 1"
    );
    $currentElectionId = $stmt->fetchColumn();
    $sessionElectionId = $_SESSION['user']['election_id'] ?? null;

    if ((string)$sessionElectionId === (string)$currentElectionId) {
        return;
    }

    unset($_SESSION['user'], $_SESSION['selectedVotes']);

    if ($jsonResponse) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'The election has changed, please log in again.'
        ]);
        exit;
    }

    $_SESSION['error_message'] = 'The election has changed, please log in again.';
    header('Location: ' . $loginRedirect);
    exit;
}
