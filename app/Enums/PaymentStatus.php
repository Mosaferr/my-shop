<?php

namespace App\Enums;

class PaymentStatus
{
    const IN_PROGRESS = 'Przetwarzanie';
    const SUCCESS = 'Zrealizowane';
    const FAIL = 'Niezrealizowane';

    const TYPES = [
        self::IN_PROGRESS,
        self::SUCCESS,
        self::FAIL
    ];
}
