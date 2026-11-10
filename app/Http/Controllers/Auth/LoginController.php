<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Giriş — sadece kimlik no + şifre gerektirir.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|max:50',
            'password'    => 'required|string|size:6',
        ]);

        $user = User::where('employee_id', $validated['employee_id'])->first();

        if (! $user) {
            return back()->withInput()->withErrors([
                'employee_id' => 'Bu kimlik no ile kayıtlı bir hesap bulunamadı. Önce "Kayıt Ol" ile hesap oluşturmalısınız.',
            ]);
        }

        if (! Hash::check($validated['password'], $user->password)) {
            return back()->withInput()->withErrors([
                'password' => 'Şifre hatalı.',
            ]);
        }

        session(['user_id' => $user->id]);

        return redirect()->route('home')->with('success', 'Hoş geldiniz, ' . $user->name . '.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('user_id');
        $request->session()->regenerate();

        return redirect()->route('login')->with('success', 'Çıkış yapıldı.');
    }

    /**
     * Kayıt Ol — kimlik no artık otomatik, sıralı olarak atanır (000001, 000002, ...).
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'password' => ['required', 'string', 'size:6', $this->passwordDigitRule(), 'confirmed'],
        ]);

        // Aynı isimle başka bir hesap açılmasını engelle
        $nameTaken = User::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($validated['name']))])->exists();

        if ($nameTaken) {
            return back()->withInput()->withErrors([
                'name' => 'Bu ad soyad ile zaten kayıtlı bir kullanıcı var.',
            ]);
        }

        $employeeId = $this->generateNextEmployeeId();

        $user = User::create([
            'employee_id' => $employeeId,
            'name'        => $validated['name'],
            'email'       => $employeeId . '@sirket.local',
            'password'    => Hash::make($validated['password']),
            'role'        => 'personel',
        ]);

        session(['user_id' => $user->id]);

        return redirect()->route('home')->with(
            'success',
            'Hoş geldiniz, ' . $user->name . '! Şirket Kimlik Numaranız: ' . $employeeId . ' — bir sonraki girişleriniz için bu numarayı saklayın.'
        );
    }

    /**
     * Sıradaki 6 haneli, sıfırla dolgulu kimlik numarasını üretir (000001, 000002, ...).
     */
    private function generateNextEmployeeId(): string
    {
        $last = User::orderByRaw('CAST(employee_id AS UNSIGNED) DESC')->first();
        $next = $last ? ((int) $last->employee_id) + 1 : 1;

        return sprintf('%06d', $next);
    }

    /**
     * Şifremi Unuttum - Adım 1: Kimlik no + ad soyad doğrulama formu.
     */
    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    public function verifyForgotPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|max:50',
            'name'        => 'required|string|max:255',
        ]);

        $user = User::where('employee_id', $validated['employee_id'])->first();

        if (! $user || mb_strtolower(trim($user->name)) !== mb_strtolower(trim($validated['name']))) {
            return back()->withInput()->withErrors([
                'employee_id' => 'Girilen kimlik no ve ad soyad eşleşen bir kayıt bulunamadı.',
            ]);
        }

        session(['password_reset_user_id' => $user->id]);

        return redirect()->route('password.reset-form');
    }

    public function showResetPasswordForm(): View|RedirectResponse
    {
        $userId = session('password_reset_user_id');

        if (! $userId || ! User::find($userId)) {
            return redirect()->route('password.forgot')->withErrors([
                'employee_id' => 'Önce kimlik doğrulaması yapmanız gerekiyor.',
            ]);
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $userId = session('password_reset_user_id');
        $user = $userId ? User::find($userId) : null;

        if (! $user) {
            return redirect()->route('password.forgot')->withErrors([
                'employee_id' => 'Oturum süresi doldu, lütfen tekrar deneyin.',
            ]);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'size:6', $this->passwordDigitRule(), 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        session()->forget('password_reset_user_id');

        return redirect()->route('login')->with('success', 'Şifreniz güncellendi. Yeni şifrenizle giriş yapabilirsiniz.');
    }

    /**
     * Şifre kuralı: tam 6 haneli rakam, ama bir rakam en fazla 2 kez tekrar edebilir.
     * (örn: 112233 geçerli, 111234 geçersiz çünkü '1' 3 kez tekrar ediyor)
     */
    private function passwordDigitRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! preg_match('/^\d{6}$/', $value)) {
                $fail('Şifre 6 haneli rakamlardan oluşmalıdır.');
                return;
            }

            $counts = array_count_values(str_split($value));

            foreach ($counts as $count) {
                if ($count > 2) {
                    $fail('Şifrede bir rakam en fazla 2 kez tekrar edebilir.');
                    return;
                }
            }
        };
    }
}