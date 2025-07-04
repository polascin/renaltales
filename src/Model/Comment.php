<?php
declare(strict_types=1);

namespace RenalTales\Model;

class Comment extends Model
{
    protected static function getTable(): string
    {
        return 'comments';
    }

    protected static function getFields(): array
    {
        return [
            'id',
            'story_id',
            'user_id',
            'parent_id',
            'content',
            'status',
            'created_at',
            'updated_at'
        ];
    }

    protected static function getValidationRules(): array
    {
        return [
            'story_id' => 'required|exists:stories,id',
            'user_id' => 'required|exists:users,id',
            'parent_id' => 'exists:comments,id',
            'content' => 'required|max:1000',
            'status' => 'required|in:pending,approved,rejected'
        ];
    }

    public function getStory(): ?Story
    {
        return Story::find($this->story_id);
    }

    public function getAuthor(): ?User
    {
        return User::find($this->user_id);
    }

    public function getParent(): ?self
    {
        return $this->parent_id ? self::find($this->parent_id) : null;
    }

    public function getReplies(): array
    {
        return self::where([
            'parent_id' => $this->id,
            'status' => 'approved'
        ]);
    }

    public function getAllReplies(): array
    {
        return self::where(['parent_id' => $this->id]);
    }

    public function approve(): void
    {
        $this->status = 'approved';
        $this->save();
    }

    public function reject(): void
    {
        $this->status = 'rejected';
        $this->save();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function reply(int $userId, string $content): self
    {
        $reply = new self([
            'story_id' => $this->story_id,
            'user_id' => $userId,
            'parent_id' => $this->id,
            'content' => $content,
            'status' => 'pending'
        ]);
        $reply->save();
        return $reply;
    }

    public static function getPendingCount(): int
    {
        $db = self::getDatabase();
        $sql = "SELECT COUNT(*) FROM comments WHERE status = 'pending'";
        return (int)$db->query($sql)->fetchColumn();
    }

    public static function getRecentComments(int $limit = 10): array
    {
        $db = self::getDatabase();
        $sql = "
            SELECT c.*, s.title, u.username
            FROM comments c
            JOIN stories s ON c.story_id = s.id
            JOIN users u ON c.user_id = u.id
            WHERE c.status = 'approved'
            ORDER BY c.created_at DESC
            LIMIT :limit
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
