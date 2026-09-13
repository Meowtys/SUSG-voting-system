<?php

declare(strict_types=1);

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
