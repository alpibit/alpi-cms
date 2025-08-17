<?php

class ActivityFeed
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Get recent activities from all tracked entities
     * @param int $limit Number of activities to fetch
     * @return array Array of recent activities
     */
    public function getRecentActivities($limit = 15)
    {
        $activities = [];

        $activities = array_merge($activities, $this->getContentActivities());
        $activities = array_merge($activities, $this->getCategoryActivities());
        $activities = array_merge($activities, $this->getBlockActivities());
        $activities = array_merge($activities, $this->getSettingsActivities());
        $activities = array_merge($activities, $this->getUserActivities());

        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        return array_slice($activities, 0, $limit);
    }


    private function getContentActivities($limit = 10)
    {
        $sql = "SELECT 
                    c.id,
                    c.title,
                    c.slug,
                    c.created_at,
                    c.updated_at,
                    c.is_active,
                    ct.name as content_type,
                    cat.name as category_name,
                    u.username
                FROM contents c
                LEFT JOIN content_types ct ON c.content_type_id = ct.id
                LEFT JOIN categories cat ON c.category_id = cat.id
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                   OR c.updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY GREATEST(c.created_at, c.updated_at) DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $contents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activities = [];
        foreach ($contents as $content) {
            $timeDiff = abs(strtotime($content['updated_at']) - strtotime($content['created_at']));
            $isNew = $timeDiff < 300;

            $author = $content['username'];
            if (!$author) {
                $isInstallationContent = strtotime($content['created_at']) >= strtotime('-1 hour');
                $author = $isInstallationContent ? 'System' : 'Unknown User';
            }

            if ($isNew) {
                $activities[] = [
                    'type' => $content['content_type'] . '_created',
                    'title' => $content['title'],
                    'description' => "New " . $content['content_type'] . " created" .
                        ($content['category_name'] ? " in {$content['category_name']}" : ""),
                    'timestamp' => $content['created_at'],
                    'status' => $content['is_active'] ? 'published' : 'draft',
                    'url' => $content['content_type'] . "s/edit_" . $content['content_type'] . ".php?id={$content['id']}",
                    'icon' => $content['content_type'],
                    'author' => $author
                ];
            }

            if (!$isNew && strtotime($content['updated_at']) >= strtotime('-30 days')) {
                $activities[] = [
                    'type' => $content['content_type'] . '_updated',
                    'title' => $content['title'],
                    'description' => ucfirst($content['content_type']) . " updated" .
                        ($content['category_name'] ? " in {$content['category_name']}" : ""),
                    'timestamp' => $content['updated_at'],
                    'status' => $content['is_active'] ? 'published' : 'draft',
                    'url' => $content['content_type'] . "s/edit_" . $content['content_type'] . ".php?id={$content['id']}",
                    'icon' => $content['content_type'],
                    'author' => $author
                ];
            }
        }

        return $activities;
    }


    private function getCategoryActivities($limit = 5)
    {
        $sql = "SELECT 
                    id,
                    name,
                    slug,
                    created_at,
                    updated_at
                FROM categories
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                   OR updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY GREATEST(created_at, updated_at) DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activities = [];
        foreach ($categories as $category) {
            $timeDiff = abs(strtotime($category['updated_at']) - strtotime($category['created_at']));
            $isNew = $timeDiff < 300;

            $isInstallationContent = strtotime($category['created_at']) >= strtotime('-1 hour');
            $author = $isInstallationContent ? 'System' : 'Admin';

            if ($isNew) {
                $activities[] = [
                    'type' => 'category_created',
                    'title' => $category['name'],
                    'description' => "New category created",
                    'timestamp' => $category['created_at'],
                    'status' => 'active',
                    'url' => "categories/edit_category.php?id={$category['id']}",
                    'icon' => 'category',
                    'author' => $author
                ];
            } elseif (!$isNew && strtotime($category['updated_at']) >= strtotime('-30 days')) {
                $activities[] = [
                    'type' => 'category_updated',
                    'title' => $category['name'],
                    'description' => "Category updated",
                    'timestamp' => $category['updated_at'],
                    'status' => 'active',
                    'url' => "categories/edit_category.php?id={$category['id']}",
                    'icon' => 'category',
                    'author' => 'Admin'
                ];
            }
        }

        return $activities;
    }


    private function getBlockActivities($limit = 5)
    {
        $sql = "SELECT 
                    b.id,
                    b.content_id,
                    b.type,
                    b.title,
                    b.created_at,
                    b.updated_at,
                    c.title as content_title,
                    c.id as content_id_ref,
                    ct.name as content_type
                FROM blocks b
                LEFT JOIN contents c ON b.content_id = c.id
                LEFT JOIN content_types ct ON c.content_type_id = ct.id
                WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                   OR b.updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY GREATEST(b.created_at, b.updated_at) DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activities = [];
        foreach ($blocks as $block) {
            $timeDiff = abs(strtotime($block['updated_at']) - strtotime($block['created_at']));

            $blockTitle = $block['title'] ?: ucfirst(str_replace('_', ' ', $block['type'])) . ' block';
            $contentInfo = $block['content_title'] ? " in {$block['content_title']}" : "";

            $isRecentActivity = strtotime($block['updated_at']) >= strtotime('-30 days') ||
                strtotime($block['created_at']) >= strtotime('-30 days');

            if (!$isRecentActivity) {
                continue;
            }

            $blockAge = time() - strtotime($block['created_at']);
            $isVeryRecent = $blockAge < 60;

            $isLikelyEdit = false;

            if ($block['content_title'] && $isVeryRecent) {
                $contentAgeQuery = "SELECT created_at FROM contents WHERE id = :content_id";
                $stmt = $this->db->prepare($contentAgeQuery);
                $stmt->bindParam(':content_id', $block['content_id'], PDO::PARAM_INT);
                $stmt->execute();
                $contentData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($contentData) {
                    $contentAge = time() - strtotime($contentData['created_at']);
                    $isLikelyEdit = $contentAge > 300;
                }
            }

            if ($isLikelyEdit) {
                $activityType = 'block_updated';
                $description = "{$block['type']} block updated{$contentInfo}";
                $timestamp = $block['created_at'];
                $author = 'Admin';
            } else {
                $activityType = 'block_created';
                $description = "New {$block['type']} block added{$contentInfo}";
                $timestamp = $block['created_at'];

                $isInstallationContent = $blockAge < 120;
                $author = $isInstallationContent ? 'System' : 'Admin';
            }

            $editUrl = $block['content_type'] ?
                "{$block['content_type']}s/edit_{$block['content_type']}.php?id={$block['content_id']}" :
                "posts/index.php";

            $activities[] = [
                'type' => $activityType,
                'title' => $blockTitle,
                'description' => $description,
                'timestamp' => $timestamp,
                'status' => 'active',
                'url' => $editUrl,
                'icon' => 'block',
                'author' => $author
            ];
        }

        return $activities;
    }


    private function getSettingsActivities($limit = 3)
    {
        $sql = "SELECT 
                    id,
                    setting_key,
                    created_at,
                    updated_at
                FROM settings
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                   OR updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY GREATEST(created_at, updated_at) DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activities = [];
        foreach ($settings as $setting) {
            $timeDiff = abs(strtotime($setting['updated_at']) - strtotime($setting['created_at']));
            $isNew = $timeDiff < 300;

            $settingName = ucfirst(str_replace('_', ' ', $setting['setting_key']));

            if (!$isNew && strtotime($setting['updated_at']) >= strtotime('-30 days')) {
                $activities[] = [
                    'type' => 'setting_updated',
                    'title' => $settingName,
                    'description' => "Site setting updated",
                    'timestamp' => $setting['updated_at'],
                    'status' => 'active',
                    'url' => "settings/index.php",
                    'icon' => 'setting',
                    'author' => 'Admin'
                ];
            }
        }

        return $activities;
    }


    private function getUserActivities($limit = 3)
    {
        $sql = "SELECT 
                    id,
                    username,
                    email,
                    role,
                    created_at
                FROM users
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY created_at DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $activities = [];
        foreach ($users as $user) {
            $activities[] = [
                'type' => 'user_created',
                'title' => $user['username'],
                'description' => "New {$user['role']} user registered",
                'timestamp' => $user['created_at'],
                'status' => 'active',
                'url' => "users/edit_user.php?id={$user['id']}",
                'icon' => 'user',
                'author' => 'System'
            ];
        }

        return $activities;
    }


    public function formatTimestamp($timestamp)
    {
        $time = strtotime($timestamp);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $minutes . ' minute' . ($minutes != 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours != 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days != 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $time);
        }
    }


    public function getActivityIcon($type)
    {
        switch ($type) {
            case 'post_created':
            case 'post_updated':
                return '📝';
            case 'page_created':
            case 'page_updated':
                return '📄';
            case 'category_created':
            case 'category_updated':
                return '🏷️';
            case 'block_created':
            case 'block_updated':
                return '🧩';
            case 'setting_updated':
                return '⚙️';
            case 'user_created':
                return '👤';
            default:
                return '📋';
        }
    }


    public function getActivityColorClass($type, $status = null)
    {
        if ($status === 'draft') {
            return 'alpi-activity-warning';
        }

        switch ($type) {
            case 'post_created':
            case 'page_created':
            case 'category_created':
            case 'block_created':
            case 'user_created':
                return 'alpi-activity-success';
            case 'post_updated':
            case 'page_updated':
            case 'category_updated':
            case 'block_updated':
            case 'setting_updated':
                return 'alpi-activity-info';
            default:
                return 'alpi-activity-default';
        }
    }
}
