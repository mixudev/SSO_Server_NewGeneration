<?php

namespace App\Domain\OAuth\Enums;

enum AuthorizationTransactionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Denied = 'denied';
    case Expired = 'expired';
    case Consumed = 'consumed';
}
