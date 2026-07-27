<?php

declare(strict_types=1);

namespace App\Application\Auth\Login;

use InvalidArgumentException;

final readonly class LoginInput
{
    public string $email;
    public string $password;

    public function __construct(
        string $email,
        string $password,
    ) {
        $normalizedEmail = mb_strtolower(trim($email));

        if (
            filter_var(
                $normalizedEmail,
                FILTER_VALIDATE_EMAIL,
            ) === false
        ) {
            throw new InvalidArgumentException(
                'The login email must be valid.'
            );
        }

        if ($password === '') {
            throw new InvalidArgumentException(
                'The login password cannot be empty.'
            );
        }

    $this->email = $normalizedEmail;
    $this->password = $password;
   } 
}
?>