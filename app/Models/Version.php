<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $table = 'versiones';
    public $timestamps = false; 
    protected $fillable = ['numero', 'comentarios', 'activa'];

    public static function actual(): ?self
    {
        $path = config('app.release_file', base_path('release.json'));

        if (is_file($path)) {
            $release = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

            foreach (['numero', 'comentarios', 'fecha_creacion', 'commit'] as $field) {
                if (!isset($release[$field]) || !is_string($release[$field])) {
                    throw new \RuntimeException('Los datos de la entrega son inválidos.');
                }
            }

            return (new self)->forceFill([
                'numero' => $release['numero'],
                'comentarios' => $release['comentarios'],
                'fecha_creacion' => $release['fecha_creacion'],
                'commit' => $release['commit'],
                'activa' => true,
            ]);
        }

        // Compatibilidad con instalaciones anteriores y desarrollo local.
        return self::where('activa', 1)->latest('id')->first();
    }
}
