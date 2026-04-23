<?php

namespace App\Enums;

enum PauseReason: string
{
    case WaitingForInfo = 'waiting_for_info';
    case WaitingThirdParty = 'waiting_third_party';
}
