<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $table = 'departamentos';

    public function provincias()
    {
        return $this->hasMany(Provincia::class);
    }
}
?>