<?php
require '../../../config/database.php';
require '../../../config/autoload.php';
require '../../../config/config.php';
require '../auth_check.php';

header('Content-Type: application/json');

$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

try {
    $db = new Database();
    $conn = $db->connect();
    if (!$conn instanceof PDO) {
        throw new Exception("Error establishing a database connection.");
    }

    $activityFeed = new ActivityFeed($conn);
    $activities = $activityFeed->getRecentActivities($limit, $offset);

    foreach ($activities as &$activity) {
        $activity['formattedTimestamp'] = $activityFeed->formatTimestamp($activity['timestamp']);
        $activity['icon'] = $activityFeed->getActivityIcon($activity['type']);
        $activity['colorClass'] = $activityFeed->getActivityColorClass($activity['type'], $activity['status']);
    }

    echo json_encode(['success' => true, 'activities' => $activities]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
