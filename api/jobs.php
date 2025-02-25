<?php
$input = file_get_contents("php://input");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$jobsFile = __DIR__ . '/../data/jobs.json';
$jobs = file_exists($jobsFile) ? json_decode(file_get_contents($jobsFile), true) : ["jobs" => []];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($jobs["jobs"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newJob = json_decode(file_get_contents("php://input"), true);

    if (!isset($newJob['id']) || !isset($newJob['jobTitle'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid job data. 'id' and 'jobTitle' are required."]);
        exit;
    }

    $jobs['jobs'][] = $newJob;

    file_put_contents($jobsFile, json_encode($jobs));
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

    $jobFound = false;
    foreach ($jobs['jobs'] as &$job) {
        if ($job['id'] === $updatedJob['id']) {
            $job = $updatedJob;
            $jobFound = true;
            break;
        }
    }

    if (!$jobFound) {
        http_response_code(404);
        echo json_encode(["error" => "Job not found."]);
        exit;
    }

    file_put_contents($jobsFile, json_encode($jobs));
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

    $jobFound = false;
    foreach ($jobs['jobs'] as $key => $job) {
        if ($job['id'] === $jobId) {
            unset($jobs['jobs'][$key]);
            $jobFound = true;
            break;
        }
    }

    if (!$jobFound) {
        http_response_code(404);
        echo json_encode(["error" => "Job not found."]);
        exit;
    }

    $jobs['jobs'] = array_values($jobs['jobs']);

    file_put_contents($jobsFile, json_encode($jobs));
    echo json_encode(["message" => "Job deleted successfully."]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
?>
