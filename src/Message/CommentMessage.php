<?php
namespace App\Message;

class CommentMessage
{
    private int $userId;
    
    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }
    /**
     * @return int $userId
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

}

