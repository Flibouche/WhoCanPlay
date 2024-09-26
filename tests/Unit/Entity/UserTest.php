<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testCanGetAndSetData(): void
    {
        $user = (new User())
            ->setPseudo('JohnDoe')
            ->setEmail('johndoe@mail.fr')
            ->setPassword('password');


        self::assertSame('JohnDoe', $user->getPseudo());
        self::assertSame('johndoe@mail.fr', $user->getEmail());
        self::assertSame('password', $user->getPassword());
    }
}
