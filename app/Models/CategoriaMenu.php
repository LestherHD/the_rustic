<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoriaMenu extends Model
{

    use SoftDeletes;
    use HasFactory;

    protected $table = 'categorias_menu';


    protected $fillable =
        [
        'nombre',
        'visible_publico',
        'activo'
    ];


    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts =
        [
        'id' => 'integer',
        'nombre' => 'string',
        'visible_publico' => 'string',
        'activo' => 'string',
        'created_at' => 'timestamp',
        'updated_at' => 'timestamp',
        'deleted_at' => 'timestamp',
    ];



    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules =
    [
        'nombre' => 'required',
        'visible_publico' => 'required',
        'activo' => 'required',
    ];


    /**
     * Custom messages for validation
     *
     * @var array
     */
    public static $messages =[

    ];


    /**
     * Accessor for relationships
     *
     * @var array
     */
        public function platos()
    {
        return $this->hasMany(Plato::class, 'categoria_id', 'id');
    }

}
