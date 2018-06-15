<?php
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Todo extends Model {
    use SoftDeletes;
    protected $table = 'todo';
    protected $fillable = ['user_id', 'category_id', 'name']; // allow mass assignment
    protected $hidden = ['deleted_at']; // hidden columns from select results
    protected $dates = ['deleted_at']; // the attributes that should be mutated to dates
    public function user() {
        return $this->belongsTo('\App\Models\User', 'user_id');
    }
    public function category() {
        return $this->belongsTo('\App\Models\Category', 'category_id');
    }
}