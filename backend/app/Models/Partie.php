<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partie extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'batiment_id',
        'nom',
        'type',
        'isICPE',
        'isPrivative',
        'activites_erp'
    ];

    protected $casts = [
        'isICPE' => 'boolean',
        'isPrivative' => 'boolean',
        'createdAt' => 'datetime',
        'modifiedAt' => 'datetime',
    ];
    
    public function batiment()
    {
        return $this->belongsTo(Batiment::class);
    }
    
    public function niveaux()
    {
        return $this->belongsToMany(Niveau::class, 'partie_niveau')
                    ->withPivot([
                        'libelle',
                        'effectif_public',
                        'personnel',
                        'surface_exploitation',
                        'surface_gla',
                        'surface_accessible_public'
                    ])
                    ->withTimestamps();
    }

    public function droitsPartie()
    {
        return $this->hasMany(DroitsPartie::class);
    }

    public function lots()
    {
        return $this->belongsToMany(Lot::class, 'partie_lot')
                    ->withPivot('libelle', 'type')
                    ->withTimestamps();
    }
    
    /**
     * Vérifie si une partie privative est autorisée selon la typologie du bâtiment
     */
    public function isPrivativeAllowed(): bool
    {
        // Les parties privatives ne sont pas autorisées pour les bâtiments HAB
        return $this->batiment->type !== 'HAB';
    }
    
    /**
     * Met à jour le statut ICPE du bâtiment si nécessaire
     */
    public function updateBatimentICPE(): void
    {
        if ($this->isICPE && !$this->batiment->isICPE) {
            $this->batiment->update(['isICPE' => true]);
        }
    }
    
    /**
     * Vérifie les conflits de lots (avertissement seulement selon consignes)
     */
    public function checkLotConflicts(int $lotId): array
    {
        // Vérifier si le lot est déjà utilisé par d'autres parties
        $existingUsage = static::whereHas('lots', function ($query) use ($lotId) {
            $query->where('lots.id', $lotId);
        })->where('id', '!=', $this->id)->get();
        
        // Retourner les noms des parties qui utilisent déjà ce lot
        return $existingUsage->pluck('nom')->toArray();
    }
}