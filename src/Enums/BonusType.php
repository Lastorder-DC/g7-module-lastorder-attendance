<?php

namespace Modules\Lastorder\Attendance\Enums;

enum BonusType: string
{
    case RANK_1 = 'rank_1';
    case RANK_2 = 'rank_2';
    case RANK_3 = 'rank_3';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';
}
