<?php
namespace App\EventListener;

use App\Event\CommentCreatedEvent;

class CommentEventListener
{

    public function __construct()
    {}
    
    public function onNewCommentCreated(CommentCreatedEvent $event)
    {
        
    }
}

