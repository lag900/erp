<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            // 1. تنفيذ محاولة الدخول (يقوم بالتحقق من البريد، الحالة، وكلمة المرور)
            $request->authenticate();

            /** @var User $user */
            $user = Auth::user();

            // 2. التحقق من وجود المستخدم وصحة بياناته بعد النجاح الأولي
            if (!$user || !$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => __('هذا الحساب غير مفعل حالياً.'),
                ]);
            }

            // 3. نظام التحقق الثنائي (2FA Intercept) للرتب الإدارية
            if ($user->hasTwoFactorEnabled()) {
                $user->generateOtp();
                
                Log::notice('Security: 2FA Challenge issued.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip()
                ]);

                $request->session()->put('auth.2fa.user_id', $user->id);
                $request->session()->put('auth.2fa.remember', $request->boolean('remember'));
                
                Auth::logout();
                
                return redirect()->route('two-factor.challenge');
            }

            // 4. تأمين الجلسة ضد هجمات الـ Session Fixation
            $request->session()->regenerate();

            // 5. إدارة الأقسام (Department Auto-Selection)
            $departments = $user->departments()->get();
            $defaultDepartment = $user->defaultDepartment();

            if ($defaultDepartment) {
                // إذا كان لديه قسم افتراضي، يتم اختياره فوراً
                $request->session()->put('selected_department_id', $defaultDepartment->id);
            } elseif ($departments->count() === 1) {
                // إذا كان لديه قسم واحد فقط، يتم اختياره تلقائياً
                $request->session()->put('selected_department_id', $departments->first()->id);
            } else {
                // إذا كان لديه أكثر من قسم ولا يوجد افتراضي، نوجهه لصفحة الاختيار
                // لكن يجب التأكد أننا لا نوجه الـ Super Admin إذا لم يكن مطلوباً
                if ($departments->count() > 1) {
                    return redirect()->route('departments.select');
                }
            }

            // 5. تسجيل نجاح العملية في سجلات الأمان
            Log::info('Security: User authenticated successfully.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);

            return $this->redirectBasedOnRole($user);

        } catch (ValidationException $e) {
            // إعادة توجيه أخطاء التحقق لـ Inertia لعرضها في حقول الإدخال
            throw $e;
        } catch (\Exception $e) {
            // التقاط أي استثناء غير متوقع ومنع ظهور Error 500
            Log::critical('Login System Crash Prevented: ' . $e->getMessage(), [
                'ip' => $request->ip(),
                'email' => $request->email,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'email' => __('حدث خطأ غير متوقع في نظام الأمان. يرجى مراجعة الدعم الفني.'),
            ]);
        }
    }

    /**
     * Redirect logic based on roles
     */
    protected function redirectBasedOnRole($user): RedirectResponse
    {
        if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
