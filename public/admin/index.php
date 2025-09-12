<?php
require '../../config/database.php';
require '../../config/autoload.php';
require '../../config/config.php';
require './auth_check.php';

try {
    $db = new Database();
    $conn = $db->connect();
    if (!$conn instanceof PDO) {
        throw new Exception("Error establishing a database connection.");
    }
} catch (Exception $e) {
    die($e->getMessage());
}

$post = new Post($conn);
$page = new Page($conn);
$category = new Category($conn);
$activityFeed = new ActivityFeed($conn);

try {
    $postCount = $post->countPosts();
    $pageCount = $page->countPages();
    $categoryCount = $category->countCategories();
    $recentActivities = $activityFeed->getRecentActivities(3);
} catch (Exception $e) {
    die("Error retrieving data: " . $e->getMessage());
}

include '../../templates/header-admin.php';
?>

<h1 class="alpi-text-primary alpi-mb-lg">Admin Dashboard</h1>

<div class="alpi-admin-dashboard">
    <div class="alpi-admin-widget">
        <h2 class="alpi-admin-widget-title">Total Posts</h2>
        <p class="alpi-admin-widget-content"><?= htmlspecialchars($postCount, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/posts/index.php" class="alpi-btn alpi-btn-primary alpi-mt-md">Manage Posts</a>
    </div>

    <div class="alpi-admin-widget">
        <h2 class="alpi-admin-widget-title">Total Pages</h2>
        <p class="alpi-admin-widget-content"><?= htmlspecialchars($pageCount, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/pages/index.php" class="alpi-btn alpi-btn-primary alpi-mt-md">Manage Pages</a>
    </div>

    <div class="alpi-admin-widget">
        <h2 class="alpi-admin-widget-title">Total Categories</h2>
        <p class="alpi-admin-widget-content"><?= htmlspecialchars($categoryCount, ENT_QUOTES, 'UTF-8') ?></p>
        <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/categories/index.php" class="alpi-btn alpi-btn-primary alpi-mt-md">Manage Categories</a>
    </div>
</div>

<div class="alpi-admin-recent-activity alpi-mt-xl alpi-mb-md ">
    <h2 class="alpi-text-primary alpi-mb-md">Recent Activity</h2>
    <div class="alpi-card">
        <?php if (empty($recentActivities)): ?>
            <p>No recent activity to display.</p>
        <?php else: ?>
            <div class="alpi-activity-feed">
                <?php foreach ($recentActivities as $activity): ?>
                    <div class="alpi-activity-item <?= htmlspecialchars($activityFeed->getActivityColorClass($activity['type'], $activity['status']), ENT_QUOTES, 'UTF-8') ?>" data-activity-type="<?= htmlspecialchars($activity['type'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="alpi-activity-icon">
                            <?= $activityFeed->getActivityIcon($activity['type']) ?>
                        </div>
                        <div class="alpi-activity-content">
                            <div class="alpi-activity-title">
                                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/public/admin/<?= htmlspecialchars($activity['url'], ENT_QUOTES, 'UTF-8') ?>" class="alpi-activity-link">
                                    <?= htmlspecialchars($activity['title'], ENT_QUOTES, 'UTF-8') ?>
                                </a>
                                <?php if (isset($activity['status']) && $activity['status'] === 'draft'): ?>
                                    <span class="alpi-badge alpi-badge-warning alpi-badge-sm">Draft</span>
                                <?php elseif (isset($activity['status']) && $activity['status'] === 'published'): ?>
                                    <span class="alpi-badge alpi-badge-success alpi-badge-sm">Published</span>
                                <?php endif; ?>
                            </div>
                            <div class="alpi-activity-description">
                                <?= htmlspecialchars($activity['description'], ENT_QUOTES, 'UTF-8') ?>
                                <?php if ($activity['author']): ?>
                                    by <strong><?= htmlspecialchars($activity['author'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php endif; ?>
                            </div>
                            <div class="alpi-activity-time">
                                <?= htmlspecialchars($activityFeed->formatTimestamp($activity['timestamp']), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="alpi-activity-footer alpi-mt-md">
                <a href="#" id="loadMoreActivity" class="alpi-btn alpi-btn-secondary alpi-btn-sm">Load More</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loadMoreButton = document.getElementById('loadMoreActivity');
        const activityFeedContainer = document.querySelector('.alpi-activity-feed');
        let offset = 3;
        const limit = 5;

        if (loadMoreButton) {
            loadMoreButton.addEventListener('click', function(e) {
                e.preventDefault();

                fetch(`<?= BASE_URL ?>/public/admin/ajax/load_activities.php?offset=${offset}&limit=${limit}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.activities.length > 0) {
                            data.activities.forEach(activity => {
                                const activityItem = document.createElement('div');
                                activityItem.className = `alpi-activity-item ${activity.colorClass}`;
                                activityItem.dataset.activityType = activity.type;

                                let statusBadge = '';
                                if (activity.status === 'draft') {
                                    statusBadge = '<span class="alpi-badge alpi-badge-warning alpi-badge-sm">Draft</span>';
                                } else if (activity.status === 'published') {
                                    statusBadge = '<span class="alpi-badge alpi-badge-success alpi-badge-sm">Published</span>';
                                }

                                let authorHtml = '';
                                if (activity.author) {
                                    authorHtml = ` by <strong>${activity.author}</strong>`;
                                }

                                activityItem.innerHTML = `
                                <div class="alpi-activity-icon">${activity.icon}</div>
                                <div class="alpi-activity-content">
                                    <div class="alpi-activity-title">
                                        <a href="<?= BASE_URL ?>/public/admin/${activity.url}" class="alpi-activity-link">${activity.title}</a>
                                        ${statusBadge}
                                    </div>
                                    <div class="alpi-activity-description">
                                        ${activity.description}
                                        ${authorHtml}
                                    </div>
                                    <div class="alpi-activity-time">${activity.formattedTimestamp}</div>
                                </div>
                            `;
                                activityFeedContainer.appendChild(activityItem);
                            });
                            offset += data.activities.length;

                            if (data.activities.length < limit) {
                                loadMoreButton.style.display = 'none';
                            }
                        } else {
                            loadMoreButton.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading activities:', error);
                        loadMoreButton.style.display = 'none';
                    });
            });
        }
    });
</script>

<?php include '../../templates/footer-admin.php'; ?>