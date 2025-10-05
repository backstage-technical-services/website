<?php

namespace App\Auth;

enum AuthError
{
    case UserNotFound;
    case MissingEmail;
    case EmailNotVerified;
    case AccountDisabled;

    public function getNotificationDetails(): ?string
    {
        return match ($this) {
            AuthError::EmailNotVerified => 'your email address not is verified',
            AuthError::AccountDisabled => 'your account is disabled',
            default => null,
        };
    }

    public function getLogMessage(): string
    {
        return match ($this) {
            AuthError::UserNotFound => 'did not find matching user in database',
            AuthError::MissingEmail => 'user does not have an associated email address',
            AuthError::EmailNotVerified => 'user\'s email address is not verified',
            AuthError::AccountDisabled => 'the user\'s account is disabled',
        };
    }
}
