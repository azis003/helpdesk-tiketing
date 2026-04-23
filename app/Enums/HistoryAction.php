<?php

namespace App\Enums;

enum HistoryAction: string
{
    case Created = 'created';
    case Verified = 'verified';
    case Assigned = 'assigned';
    case Reassigned = 'reassigned';
    case ReturnedToHelpdesk = 'returned_to_helpdesk';
    case Paused = 'paused';
    case Resumed = 'resumed';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case AutoClosed = 'auto_closed';
    case RejectedClosed = 'rejected_closed';
    case Reopened = 'reopened';
    case ApprovalRequested = 'approval_requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case RejectedByHelpdesk = 'rejected_by_helpdesk';
    case AutoClosedNoResponse = 'auto_closed_no_response';
}
