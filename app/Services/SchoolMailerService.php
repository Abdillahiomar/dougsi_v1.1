<?php

namespace App\Services;

use App\Models\SchoolSmtpConfig;

/**
 * Résout, pour une école donnée, le mailer Laravel à utiliser pour ses
 * envois sortants — indépendamment de l'utilisateur actuellement connecté
 * (donc utilisable depuis une requête web, un job en file d'attente ou
 * une commande planifiée).
 *
 * Si l'école n'a pas (ou plus) de configuration SMTP active, on retombe
 * sur le mailer par défaut de la plateforme plutôt que d'échouer.
 */
class SchoolMailerService
{
    /**
     * Mailers déjà enregistrés dans cette exécution (évite de relire la
     * base de données et de ré-enregistrer la config à chaque appel).
     *
     * @var array<int, string>
     */
    private array $registered = [];

    /**
     * Nom du mailer Laravel à utiliser pour cette école (enregistré à la
     * volée dans la config si l'école a une configuration SMTP active).
     */
    public function mailerNameFor(int $schoolId): string
    {
        if (isset($this->registered[$schoolId])) {
            return $this->registered[$schoolId];
        }

        $smtp = SchoolSmtpConfig::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        if (! $smtp) {
            return $this->registered[$schoolId] = config('mail.default');
        }

        $name = "school_{$schoolId}";

        config(["mail.mailers.{$name}" => [
            'transport'  => 'smtp',
            'host'       => $smtp->host,
            'port'       => $smtp->port,
            'encryption' => $smtp->encryption !== 'none' ? $smtp->encryption : null,
            'username'   => $smtp->username,
            'password'   => $smtp->password,
        ]]);

        return $this->registered[$schoolId] = $name;
    }

    /**
     * Adresse et nom d'expéditeur à utiliser pour cette école (retombe sur
     * les valeurs par défaut de la plateforme si l'école n'a pas de config).
     *
     * @return array{0: ?string, 1: ?string} [email, nom]
     */
    public function fromAddressFor(int $schoolId): array
    {
        $smtp = SchoolSmtpConfig::where('school_id', $schoolId)
            ->where('is_active', true)
            ->first();

        if (! $smtp) {
            return [config('mail.from.address'), config('mail.from.name')];
        }

        return [$smtp->from_email, $smtp->from_name];
    }
}
