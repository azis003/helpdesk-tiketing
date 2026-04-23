<?php

namespace App\Enums;

enum CommentType: string
{
    case Comment = 'comment';
    case Clarification = 'clarification';
    case ClarificationReply = 'clarification_reply';
}
