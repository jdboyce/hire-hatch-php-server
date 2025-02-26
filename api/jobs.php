<?php

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$dsn = 'sqlsrv:Server=DESKTOP-16CF8KG\SQLEXPRESS;Database=hire_hatch';

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

function mapJobFields($job) {
    return [
        'id' => $job['id'],
        'jobTitle' => $job['job_title'],
        'companyName' => $job['company_name'],
        'priority' => $job['priority'],
        'status' => $job['status'],
        'source' => $job['source'],
        'postingUrl' => $job['posting_url'],
        'notes' => $job['notes']
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query('SELECT * FROM jobs');
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $mappedJobs = array_map('mapJobFields', $jobs);
    echo json_encode($mappedJobs);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newJob = json_decode(file_get_contents("php://input"), true);

    if (!isset($newJob['id']) || !isset($newJob['jobTitle'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid job data. 'id' and 'jobTitle' are required."]);
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO jobs (job_title, company_name, priority, status, source, posting_url, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$newJob['jobTitle'], $newJob['companyName'], $newJob['priority'], $newJob['status'], $newJob['source'], $newJob['postingUrl'], $newJob['notes']]);
    $newJob['id'] = $pdo->lastInsertId();
    http_response_code(201);
    echo json_encode($newJob);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $updatedJob = json_decode(file_get_contents("php://input"), true);

    if (!isset($updatedJob['id']) || !isset($updatedJob['jobTitle'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid job data. 'id' and 'jobTitle' are required."]);
        exit;
    }

    $stmt = $pdo->prepare('UPDATE jobs SET job_title = ?, company_name = ?, priority = ?, status = ?, source = ?, posting_url = ?, notes = ? WHERE id = ?');
    $stmt->execute([$updatedJob['jobTitle'], $updatedJob['companyName'], $updatedJob['priority'], $updatedJob['status'], $updatedJob['source'], $updatedJob['postingUrl'], $updatedJob['notes'], $updatedJob['id']]);
    echo json_encode($updatedJob);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $urlParts = explode('/', $_SERVER['REQUEST_URI']);
    $jobId = end($urlParts);

    if (empty($jobId)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid job ID."]);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM jobs WHERE id = ?');
    $stmt->execute([$jobId]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Job not found."]);
        exit;
    }

    echo json_encode(["message" => "Job deleted successfully."]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
?>
