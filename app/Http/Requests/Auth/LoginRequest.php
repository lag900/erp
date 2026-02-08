<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // 1. التحقق من وجود البريد الإلكتروني (Anti-Enumeration is disabled as per user request for specific messages)
        $user = User::where('email', $this->email)->first();

        if (!$user) {
            $this->logFailure('Email not found');
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => __('البريد الإلكتروني غير صحيح.'),
            ]);
        }

        // 2. التحقق من حالة الحساب
        if (!$user->is_active) {
            $this->logFailure('Account de-activated', $user);
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => __('هذا الحساب غير مفعل حالياً. يرجى مراجعة الإدارة.'),
            ]);
        }

        // 3. التحقق من الصلاحيات (Role)
        if (!$user->role) {
            $this->logFailure('No role assigned', $user);
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email' => __('لا يملك هذا الحساب صلاحيات للدخول إلى النظام.'),
            ]);
        }

        // 4. التحقق من كلمة المرور باستخدام Laravel Auth القياسي
        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            $this->logFailure('Incorrect password', $user);
            RateLimiter::hit($this->throttleKey());
            
            throw ValidationException::withMessages([
                'password' => __('كلمة المرور غير صحيحة.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * تسجيل المحاولات الفاشلة للأمن
     */
    protected function logFailure(string $reason, ?User $user = null): void
    {
        Log::warning('Login Security Event: ' . $reason, [
            'attempted_email' => $this->email,
            'user_id' => $user?->id ?? 'anonymous',
            'ip' => $this->ip(),
            'user_agent' => $this->userAgent(),
        ]);
    }

    /**
     * منع هجمات Brute Force
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
