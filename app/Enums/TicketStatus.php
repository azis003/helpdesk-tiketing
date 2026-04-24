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
     * @return array<self>
     */
    public static function activeAssignmentCases(): array
    {
        return [
            self::InProgress,
            self::WaitingForInfo,
            self::WaitingThirdParty,
            self::PendingApproval,
        ];
    }

    /**
     * @return array<string>
     */
    public static function pausingValues(): array
    {
        return [
            self::WaitingForInfo->value,
            self::WaitingThirdParty->value,
        ];
    }

    /**
     * Status yang masih dianggap sebagai beban kerja aktif handler.
     *
     * @return array<string>
     */
    public static function activeAssignmentValues(): array
    {
        return array_map(
            static fn (self $status): string => $status->value,
            self::activeAssignmentCases(),
        );
    }

    public function isPaused(): bool
    {
        return in_array($this->value, self::pausingValues(), true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Closed, self::Rejected], true);
    }

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
