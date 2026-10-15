<?php

namespace App\Http\Controllers\Auth;

use App\Models\SchoolSmtpConfig;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Laravel\Fortify\Actions\CompletePasswordReset;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

/**
 * Remplace les routes natives de Fortify pour le mot de passe oublié
 * (désactivées dans config/fortify.php) : l'envoi du lien doit passer par
 * le SMTP de l'école de l'utilisateur, ou être refusé proprement si
 * l'école n'en a pas configuré.
 */
class SchoolPasswordResetController extends Controller
{
    public function showLinkRequestForm(): View
    {
        return view('pages::auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withInput()->withErrors([
                'email' => "Nous n'avons trouvé aucun compte avec cette adresse email.",
            ]);
        }

        $hasActiveSmtp = SchoolSmtpConfig::where('school_id', $user->school_id)
            ->where('is_active', true)
            ->exists();

        if (! $hasActiveSmtp) {
            return back()->with('contactAdmin', true);
        }

        $status = Password::broker()->sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Un lien de réinitialisation a été envoyé à votre adresse email.');
        }

        return back()->withInput()->withErrors(['email' => $this->statusMessage($status)]);
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('pages::auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|confirmed',
        ]);

        $status = Password::broker()->reset(
            $validated,
            function ($user) use ($request) {
                app(ResetsUserPasswords::class)->reset($user, $request->all());

                app(CompletePasswordReset::class)(auth()->guard('web'), $user);
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Votre mot de passe a été réinitialisé.');
        }

        return back()->withInput($request->only('email'))->withErrors(['email' => $this->statusMessage($status)]);
    }

    /**
     * Traduit les constantes de statut du PasswordBroker en français
     * (aucun fichier lang/fr/passwords.php n'existe dans ce projet).
     */
    private function statusMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_USER  => "Nous n'avons trouvé aucun compte avec cette adresse email.",
            Password::INVALID_TOKEN => 'Ce lien de réinitialisation est invalide ou a expiré.',
            Password::RESET_THROTTLED => 'Veuillez patienter avant de redemander un lien.',
            default => "Une erreur est survenue, merci de réessayer.",
        };
    }
}
