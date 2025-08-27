<?php

namespace App\Exceptions;

use Exception;

class UserNotRegisteredException extends Exception
{
    protected $message = 'User is not registered; could not cancell registration';
}
