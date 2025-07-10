<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class FichierRapport extends Model
{
    protected $table = 'fichiers_rapports';
    
    protected $fillable = [
        'rapport_id',
        'observation_id',
        'nom_original',
        'nom_stockage',
        'chemin_compresse',
        'version',
        'taille',
        'type_mime'
    ];

    public function rapport()
    {
        return $this->belongsTo(Rapport::class);
    }

    public function observation()
    {
        return $this->belongsTo(Observation::class);
    }

    public function getSize()
    {
        return $this->formatBytes($this->taille);
    }

    public function getPath()
    {
        return storage_path('app/private/reports/' . $this->nom_stockage);
    }

    public function getCompressedPath()
    {
        return $this->chemin_compresse ? storage_path('app/private/compressed/' . $this->chemin_compresse) : null;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
