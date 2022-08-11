<?php

namespace CustomD\EloquentModelEncrypt\Exceptions;

class UnknownGrammerException extends \Exception
{

    protected $message = 'Unknown Grammar Class, unable to define Encrypted Type. Use Blob/Binary/Text instead';
}
