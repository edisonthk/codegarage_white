<?php namespace App\Edisonthk\Exception;

use Exception;

class PermissionDenied extends Exception{

    protected $message = "User don't have permission to carry out this action.";
}