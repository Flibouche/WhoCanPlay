<?php

namespace App\Service;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class HoneypotChecker
{
    public function checkHoneypot(string $honeypotValue): void
    {
        if (!empty($honeypotValue)) {
            throw new AuthenticationException('Bot detected !!!!!!!');
        }
    }
}