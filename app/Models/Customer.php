<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Model
{
    use HasFactory, Notifiable;
    public $timestamps = true;
    protected $table = "customer";
    // protected $fillable = [nama_kategori];
    protected $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'nama',
        'email',
        'status',
        'role',
        'password',
        'hp',
        'alamat',
        'pos',
        'foto',
        'google_id',
        'google_token',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($customer) {
            // Hapus user yang terkait ketika customer dihapus
            if ($customer->user) {
                $customer->user->delete();
            }
        });
    }
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
