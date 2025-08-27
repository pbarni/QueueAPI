<?php

namespace App;

enum RegistrationStatus: string
{
    case REGISTERED = 'registered';
    case QUEUED = 'queued';
    case CANCELLED = 'cancelled';
}
