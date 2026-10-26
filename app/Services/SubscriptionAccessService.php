<?php

namespace App\Services;

use App\Models\School;

/**
 * Détermine si l'accès d'une école doit être bloqué faute d'abonnement à
 * jour. Utilisé à la fois à la connexion (FortifyServiceProvider) et en
 * continu (EnsureSchoolSubscriptionActive) pour éviter deux logiques qui
 * divergent.
 */
class SubscriptionAccessService
{
    /**
     * Délai de grâce après la date de fin d'abonnement avant blocage réel.
     * Le statut peut déjà être "expired" avant ce délai (voir
     * ExpireSubscriptions) — seul le blocage d'accès est différé.
     */
    private const GRACE_DAYS = 7;

    public static function isBlocked(?School $school): bool
    {
        if (! $school) {
            return false;
        }

        $subscription = $school->subscriptions()->latest()->first();

        if (! $subscription) {
            // Pas d'abonnement du tout : pas de blocage par défaut, tant que
            // le superadmin n'a pas créé d'abonnement pour cette école.
            return false;
        }

        // Suspension : action délibérée du superadmin, bloque immédiatement.
        if ($subscription->status === 'suspended') {
            return true;
        }

        if (! $subscription->ends_at) {
            return false;
        }

        return now()->isAfter(
            $subscription->ends_at->copy()->addDays(self::GRACE_DAYS)
        );
    }
}
