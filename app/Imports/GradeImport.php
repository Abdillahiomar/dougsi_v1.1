<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Lecture brute du fichier importé (voir grades/index.blade.php).
 * La validation ligne par ligne se fait dans le composant Livewire, pas ici,
 * pour pouvoir construire un aperçu avant confirmation.
 */
class GradeImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows): void
    {
        // Volontairement vide : la lecture se fait via Excel::toCollection(),
        // qui retourne les lignes sans dépendre de cette méthode.
    }
}
