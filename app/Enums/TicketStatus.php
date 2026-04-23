<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Verification = 'verification';
    case InProgress = 'in_progress';
    case WaitingForInfo = 'waiting_for_info';
    case WaitingThirdParty = 'waiting_third_party';
    case PendingApproval = 'pending_approval';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Rejected = 'rejected';

    /**
     * Label untuk ditampilkan di UI
     */
    public function label(): string
    {
        return match($this) {
            self::Open => 'Open',
            self::Verification => 'Verification',
            self::InProgress => 'In Progress',
            self::WaitingForInfo => 'Waiting for Info',
            self::WaitingThirdParty => 'Waiting Third Party',
            self::PendingApproval => 'Pending Approval',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Rejected => 'Rejected',
        };
    }
}
