<?php

namespace App\Enum;

/** Service du midi ou du soir. */
enum ServiceType: string
{
    case MIDI = 'midi';
    case SOIR = 'soir';
}
