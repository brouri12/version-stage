# Forgot Password Feature Setup

## Overview
The forgot password feature has been successfully implemented using the SymfonyCasts Reset Password Bundle. This allows users to request a password reset via email.

## Features Implemented

### 1. Password Reset Request
- **Route**: `/reset-password`
- **Controller**: `ResetPasswordController::request()`
- **Template**: `templates/reset_password/request.html.twig`
- **Form**: `ResetPasswordRequestFormType`

### 2. Email Confirmation
- **Route**: `/reset-password/check-email`
- **Controller**: `ResetPasswordController::checkEmail()`
- **Template**: `templates/reset_password/check_email.html.twig`

### 3. Password Reset
- **Route**: `/reset-password/reset/{token}`
- **Controller**: `ResetPasswordController::reset()`
- **Template**: `templates/reset_password/reset.html.twig`
- **Form**: `ResetPasswordFormType`

### 4. Email Template
- **Template**: `templates/reset_password/email.html.twig`

## Database Changes
- Created `reset_password_request` table with the following fields:
  - `id` (Primary Key)
  - `user_id` (Foreign Key to Client)
  - `selector` (VARCHAR 20)
  - `hashed_token` (VARCHAR 100)
  - `requested_at` (DATETIME)
  - `expires_at` (DATETIME)

## Configuration

### 1. Bundle Configuration
File: `config/packages/reset_password.yaml`
```yaml
symfonycasts_reset_password:
    request_password_repository: App\Repository\ResetPasswordRequestRepository
```

### 2. Mailer Configuration
File: `config/packages/framework.yaml`
```yaml
framework:
    mailer:
        envelope:
            sender: 'noreply@moodeek.com'
```

### 3. Services Configuration
File: `config/services.yaml`
```yaml
parameters:
    app.mailer_from: 'noreply@moodeek.com'
```

## Usage

### For Users
1. Go to the login page (`/login`)
2. Click on "Mot de passe oublié ?" link
3. Enter your email address
4. Check your email for the reset link
5. Click the link in the email
6. Enter your new password
7. Submit the form

### For Developers
The feature is fully integrated and ready to use. The system will:
- Generate secure reset tokens
- Send emails with reset links
- Validate tokens and allow password changes
- Clean up expired tokens automatically

## Security Features
- Tokens expire after a configurable time (default: 1 hour)
- Tokens are hashed and stored securely
- One-time use tokens (deleted after use)
- CSRF protection on all forms
- Email validation and sanitization

## Email Configuration
To make the email functionality work in production, you need to:

1. Configure your `MAILER_DSN` environment variable
2. Update the `app.mailer_from` parameter in `config/services.yaml`
3. Ensure your email service is properly configured

Example for Gmail:
```env
MAILER_DSN=gmail+smtp://username:password@default
```

## Testing
You can test the feature by:
1. Creating a user account
2. Going to `/reset-password`
3. Entering the user's email
4. Checking the email (or logs in development)
5. Following the reset link
6. Setting a new password

## Files Created/Modified

### New Files
- `src/Entity/ResetPasswordRequest.php`
- `src/Repository/ResetPasswordRequestRepository.php`
- `src/Controller/ResetPasswordController.php`
- `src/Form/ResetPasswordRequestFormType.php`
- `src/Form/ResetPasswordFormType.php`
- `templates/reset_password/request.html.twig`
- `templates/reset_password/check_email.html.twig`
- `templates/reset_password/reset.html.twig`
- `templates/reset_password/email.html.twig`

### Modified Files
- `templates/security/login.html.twig` - Added link to forgot password
- `config/packages/reset_password.yaml` - Configured repository
- `config/packages/framework.yaml` - Added mailer configuration
- `config/services.yaml` - Added mailer_from parameter

## Dependencies
- `symfonycasts/reset-password-bundle` - Main bundle for password reset functionality 