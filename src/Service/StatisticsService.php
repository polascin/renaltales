<?php
declare(strict_types=1);

namespace RenalTales\Service;

use RenalTales\Repository\StoryRepository;
use RenalTales\Repository\StoryContentRepository;
use RenalTales\Repository\UserRepository;
use RenalTales\Repository\CommentRepository;
use RenalTales\Repository\StoryStatisticsRepository;

class StatisticsService extends Service
{
    private StoryRepository $storyRepository;
    private StoryContentRepository $contentRepository;
    private UserRepository $userRepository;
    private CommentRepository $commentRepository;
    private StoryStatisticsRepository $statsRepository;

    public function __construct()
    {
        parent::__construct();
        $this->storyRepository = new StoryRepository();
        $this->contentRepository = new StoryContentRepository();
        $this->userRepository = new UserRepository();
        $this->commentRepository = new CommentRepository();
        $this->statsRepository = new StoryStatisticsRepository();
    }

    public function getDashboardStatistics(): array
    {
        return [
            'users' => $this->getUserStatistics(),
            'stories' => $this->getStoryStatistics(),
            'translations' => $this->getTranslationStatistics(),
            'comments' => $this->getCommentStatistics(),
            'trending' => $this->getTrendingContent()
        ];
    }

    public function getUserStatistics(): array
    {
        $stats = $this->userRepository->getUserStatistics();
        $activeUsers = $this->userRepository->getActiveUsers(15); // last 15 minutes

        return [
            'total_users' => $stats['total_users'] ?? 0,
            'verified_users' => $stats['verified_users'] ?? 0,
            'translators' => $stats['translators'] ?? 0,
            'moderators' => $stats['moderators'] ?? 0,
            'active_last_30_days' => $stats['active_last_30_days'] ?? 0,
            'currently_online' => count($activeUsers),
            'recent_registrations' => $this->userRepository->count([
                'created_at >=' => date('Y-m-d', strtotime('-7 days'))
            ])
        ];
    }

    public function getStoryStatistics(): array
    {
        $stats = $this->storyRepository->getStoryStatistics();
        $categoryStats = $this->storyRepository->getStoryCounts();

        return [
            'total_stories' => $stats['total_stories'] ?? 0,
            'published_stories' => $stats['published_stories'] ?? 0,
            'pending_review' => $stats['pending_review'] ?? 0,
            'public_stories' => $stats['public_stories'] ?? 0,
            'created_last_30_days' => $stats['created_last_30_days'] ?? 0,
            'by_category' => $categoryStats,
            'most_viewed' => $this->statsRepository->getPopularStories('view_count', 5),
            'most_commented' => $this->statsRepository->getPopularStories('total_comments', 5)
        ];
    }

    public function getTranslationStatistics(): array
    {
        $stats = $this->contentRepository->getTranslationStatistics();
        $coverage = $this->getLanguageCoverageStatistics();

        return [
            'translation_stats' => $stats,
            'language_coverage' => $coverage,
            'recent_translations' => $this->contentRepository->getRecentTranslations(5),
            'incomplete_translations' => count($this->contentRepository->findIncompleteTranslations())
        ];
    }

    public function getCommentStatistics(): array
    {
        $stats = $this->commentRepository->getCommentStatistics();
        
        return [
            'total_comments' => $stats['total_comments'] ?? 0,
            'approved_comments' => $stats['approved_comments'] ?? 0,
            'pending_comments' => $stats['pending_comments'] ?? 0,
            'rejected_comments' => $stats['rejected_comments'] ?? 0,
            'last_24h_comments' => $stats['last_24h_comments'] ?? 0,
            'reply_count' => $stats['reply_count'] ?? 0,
            'active_discussions' => $this->commentRepository->getMostActiveDiscussions(5)
        ];
    }

    public function getTrendingContent(): array
    {
        return [
            'stories' => $this->statsRepository->getTrendingStories(7, 5), // last 7 days, top 5
            'discussions' => $this->commentRepository->getMostActiveDiscussions(5),
            'translations' => $this->contentRepository->getRecentTranslations(5)
        ];
    }

    public function getLanguageCoverageStatistics(): array
    {
        $supportedLanguages = $this->config->get('languages.supported');
        $coverage = array_fill_keys($supportedLanguages, 0);
        $totalStories = $this->storyRepository->count(['status' => 'published']);

        if ($totalStories === 0) {
            return $coverage;
        }

        $translationCounts = $this->contentRepository->getLanguageCoverage();
        
        foreach ($translationCounts as $lang => $count) {
            if (isset($coverage[$lang])) {
                $coverage[$lang] = round(($count / $totalStories) * 100, 2);
            }
        }

        return $coverage;
    }

    public function getContentGrowthTrend(string $period = 'month', int $limit = 12): array
    {
        $format = $period === 'month' ? '%Y-%m' : '%Y-%m-%d';
        $interval = $period === 'month' ? '-1 year' : '-30 days';

        $sql = "
            SELECT 
                DATE_FORMAT(created_at, '{$format}') as period,
                COUNT(*) as story_count,
                COUNT(DISTINCT user_id) as unique_authors
            FROM stories
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL {$interval})
            GROUP BY period
            ORDER BY period DESC
            LIMIT :limit
        ";

        return $this->storyRepository->executeQuery($sql, ['limit' => $limit]);
    }

    public function getUserEngagementMetrics(): array
    {
        return [
            'contributors' => [
                'authors' => $this->storyRepository->count([
                    'created_at >=' => date('Y-m-d', strtotime('-30 days'))
                ]),
                'translators' => $this->contentRepository->count([
                    'created_at >=' => date('Y-m-d', strtotime('-30 days')),
                    'translator_id IS NOT' => null
                ]),
                'commenters' => $this->commentRepository->count([
                    'created_at >=' => date('Y-m-d', strtotime('-30 days')),
                    'status' => 'approved'
                ])
            ],
            'retention' => [
                'returning_authors' => $this->getReturningAuthorsCount(),
                'active_translators' => $this->getActiveTranslatorsCount(),
                'regular_commenters' => $this->getRegularCommentersCount()
            ],
            'most_active' => [
                'authors' => $this->userRepository->getMostActiveUsers(5),
                'translators' => $this->getTopTranslators(5),
                'commenters' => $this->getTopCommenters(5)
            ]
        ];
    }

    private function getReturningAuthorsCount(): int
    {
        $sql = "
            SELECT COUNT(DISTINCT user_id) as count
            FROM stories
            WHERE user_id IN (
                SELECT user_id
                FROM stories
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY user_id
                HAVING COUNT(*) > 1
            )
        ";

        $result = $this->storyRepository->executeSingleResult($sql);
        return (int)($result['count'] ?? 0);
    }

    private function getActiveTranslatorsCount(): int
    {
        $sql = "
            SELECT COUNT(DISTINCT translator_id) as count
            FROM story_contents
            WHERE translator_id IS NOT NULL
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND status IN ('published', 'pending_review')
        ";

        $result = $this->contentRepository->executeSingleResult($sql);
        return (int)($result['count'] ?? 0);
    }

    private function getRegularCommentersCount(): int
    {
        $sql = "
            SELECT COUNT(DISTINCT user_id) as count
            FROM comments
            WHERE user_id IN (
                SELECT user_id
                FROM comments
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                AND status = 'approved'
                GROUP BY user_id
                HAVING COUNT(*) >= 5
            )
        ";

        $result = $this->commentRepository->executeSingleResult($sql);
        return (int)($result['count'] ?? 0);
    }

    private function getTopTranslators(int $limit): array
    {
        $sql = "
            SELECT 
                u.*,
                COUNT(*) as translation_count,
                COUNT(DISTINCT sc.language) as languages_count
            FROM users u
            JOIN story_contents sc ON u.id = sc.translator_id
            WHERE sc.status = 'published'
            AND sc.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY u.id
            ORDER BY translation_count DESC
            LIMIT :limit
        ";

        return $this->userRepository->executeQuery($sql, ['limit' => $limit]);
    }

    private function getTopCommenters(int $limit): array
    {
        $sql = "
            SELECT 
                u.*,
                COUNT(*) as comment_count,
                COUNT(DISTINCT c.story_id) as stories_commented
            FROM users u
            JOIN comments c ON u.id = c.user_id
            WHERE c.status = 'approved'
            AND c.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY u.id
            ORDER BY comment_count DESC
            LIMIT :limit
        ";

        return $this->userRepository->executeQuery($sql, ['limit' => $limit]);
    }
}
