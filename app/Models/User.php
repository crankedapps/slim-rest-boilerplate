<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Firebase\JWT\JWT;

class User extends Model {
    use SoftDeletes; // toggle soft deletes
    protected $table = 'users';
    protected $fillable = ['username', 'password']; // for mass creation
    protected $hidden = ['password', 'deleted_at']; // hidden columns from select results
    protected $dates = ['deleted_at']; // the attributes that should be mutated to dates
    public function categories() {
        return $this->hasMany('\App\Models\Category', 'user_id');
    }
    public function todos() {
        return $this->hasMany('\App\Models\Todo', 'user_id');
    }
    public function setPasswordAttribute($pass){
        $this->attributes['password'] = password_hash($pass, \App\Config\Config::auth()['hash']);
    }
    public function tokenCreate() {
        $expires = new \DateTime("+".(\App\Config\Config::auth()['expires'])." minutes"); // token expiration
        $payload = [
            "iat" => (new \DateTime())->getTimeStamp(), // initalized unix timestamp
            "exp" => $expires->getTimeStamp(), // expiration unix timestamp
            "sub" => $this->id // internal user identifier
        ];
        $token = JWT::encode($payload, \App\Config\Config::auth()['secret'], \App\Config\Config::auth()['jwt']);
        return [
            'token' => $token,
            'expires' => $expires->getTimestamp()
        ];
    }
}