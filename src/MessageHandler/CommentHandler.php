<?php
namespace App\MessageHandler;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use App\Message\CommentMessage;
use Doctrine\ORM\EntityManagerInterface;

class CommentHandler implements MessageHandlerInterface
{
    public function __construct(EntityManagerInterface $em){
        
    }
    
    public function __invoke(CommentMessage $message) {
        ;
    }
}

