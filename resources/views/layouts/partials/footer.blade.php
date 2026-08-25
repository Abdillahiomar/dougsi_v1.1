{{-- ============================================================
     resources/views/layouts/partials/footer.blade.php
     Footer minimal — ne bouge jamais
     ============================================================ --}}
<footer class="app-footer">
    <span class="footer-copy">
        DOUGSI &copy; {{ date('Y') }} — Plateforme de gestion scolaire
    </span>
    <span class="footer-school">
        {{ auth()->user()->school?->name ?? '' }}
    </span>
</footer>
