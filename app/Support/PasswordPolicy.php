<?php

namespace App\Support;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

/**
 * One definition of "a good password", shared by every place a password is set.
 *
 * The strength meter in resources/views/partials/password-strength.blade.php mirrors
 * rules() and COMMON so the ticks shown to the resident always match what the
 * server will actually accept - never stricter, never looser.
 */
class PasswordPolicy
{
    /**
     * Passwords that survive the composition rules but are still trivial to guess.
     * Kept short on purpose - it is a backstop, not a dictionary.
     */
    public const COMMON = [
        'password', 'password1', 'password1!', 'password123', 'passw0rd', 'p@ssw0rd', 'p@ssword1',
        'qwerty123', 'qwertyuiop', '1q2w3e4r', '1q2w3e4r5t', 'abc12345', 'abcd1234',
        'iloveyou1', 'letmein1!', 'welcome1!', 'welcome123', 'admin123!', 'administrator',
        'monkey123', 'dragon123', 'sunshine1', 'princess1', 'football1', 'superman1',
        'trustno1!', 'batman123', 'master123', 'michael1', 'shadow123', 'starwars1',
        'superhero', 'changeme1', 'secret123', 'test1234!', 'demo1234!', 'sample123',
        'january2026', 'february1', 'barangay1', 'sigla2026!', 'sigla1234', 'kwentoso1',
    ];

    /**
     * A password the resident must supply - registration.
     *
     * @return array<int, mixed>
     */
    public static function requiredRules(Request $request): array
    {
        return array_merge(['required'], self::base($request));
    }

    /**
     * A password the resident may leave blank to keep the current one - profile update.
     *
     * @return array<int, mixed>
     */
    public static function optionalRules(Request $request): array
    {
        return array_merge(['nullable'], self::base($request));
    }

    /**
     * Custom messages for whichever of the rules above fired.
     *
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'password.confirmed' => 'The two passwords do not match. Please retype them.',
        ];
    }

    /**
     * Length + composition come from the framework rule, everything else from the closure.
     *
     * @return array<int, mixed>
     */
    private static function base(Request $request): array
    {
        return [
            Password::min(8)->max(64)->mixedCase()->numbers()->symbols(),
            'confirmed',
            self::sanity($request),
        ];
    }

    /**
     * Checks the framework rules cannot express: dictionary words, keyboard runs,
     * and passwords built out of the resident's own details.
     */
    private static function sanity(Request $request): Closure
    {
        return function (string $attribute, mixed $value, $fail) use ($request) {
            if (! is_string($value) || $value === '') {
                return; /* 'required' / 'nullable' already reported an empty value. */
            }

            if (in_array(strtolower($value), self::COMMON, true)) {
                $fail('That password is used by thousands of people. Please pick a more original one.');

                return;
            }

            /* "aaaaaaaa" / "!!!!!" - five or more of the same character in a row. */
            if (preg_match('/(.)\1{4,}/u', $value)) {
                $fail('Avoid repeating the same character over and over.');

                return;
            }

            /* Straight keyboard or alphabet runs: 12345678, qwertyuiop, abcdefghij. */
            if (preg_match('/(?:0123456789|abcdefghij|qwertyuiop|asdfghjkl|zxcvbnm|abcdefghijkl)/i', $value)) {
                $fail('Avoid straight runs like "12345678" or "qwerty" - they are guessed instantly.');

                return;
            }

            $email = strtolower(trim((string) $request->input('email')));
            $local = str_contains($email, '@') ? trim(explode('@', $email, 2)[0]) : '';

            if (strlen($local) >= 3 && str_contains(strtolower($value), $local)) {
                $fail('Do not build your password out of your email address.');

                return;
            }

            $name = str_replace(' ', '', strtolower(trim((string) $request->input('name'))));

            if (strlen($name) >= 4 && str_contains(strtolower($value), $name)) {
                $fail('Do not use your own name as your password.');
            }
        };
    }
}
