<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Checks a Cloudflare Turnstile token with Cloudflare, see config/services.php.
 *
 * @see https://developers.cloudflare.com/turnstile/get-started/server-side-validation/
 */
class Turnstile implements ValidationRule
{
    public const string VerifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(private ?string $ipAddress = null) {}

    /**
     * Turnstile is used when both keys are configured, otherwise the form falls back to a honeypot.
     */
    public static function isEnabled(): bool
    {
        return filled(config('services.turnstile.site_key')) && filled(config('services.turnstile.secret_key'));
    }

    public static function siteKey(): ?string
    {
        return config('services.turnstile.site_key');
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '' || strlen($value) > 2048) {
            $fail(__('Please confirm that you are not a robot.'));

            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post(self::VerifyUrl, array_filter([
                    'secret' => config('services.turnstile.secret_key'),
                    'response' => $value,
                    'remoteip' => $this->ipAddress,
                ]));
        } catch (Throwable $exception) {
            Log::warning('Turnstile could not be reached.', ['exception' => $exception->getMessage()]);

            $fail(__('We could not check that you are not a robot. Please try again.'));

            return;
        }

        if ($response->json('success') !== true) {
            $fail(__('Please confirm that you are not a robot.'));
        }
    }
}
