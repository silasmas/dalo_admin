<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserStatus;
use App\Models\Appt\Availability;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * ⚙️ Table & clé primaire
     * - ta table ne s'appelle pas "users" mais "main_users"
     * - la PK est un int auto-incrément (pas "bigint")
     */
    protected $table      = 'main_users';
    protected $guard_name = 'admin'; // ⬅️ même guard que ton panel Filament

    protected $primaryKey = 'id';
    public $incrementing  = true;
    protected $keyType    = 'int';

    /**
     * ⏱️ Timestamps
     * - ta table a bien created_at/updated_at en datetime => on laisse $timestamps = true
     * - SoftDeletes utilise deleted_at déjà présent
     */
    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
        'password',
        'otp',
        'public_token',
        'fcm_token',
    ];
    protected $attributes = [
        'status'       => 1,
        'default_role' => 1,
    ];

    /**
     * 🧠 Casts automatiques (dates, enum, etc.)
     */
    protected $casts = [
        'status'        => UserStatus::class, // ➜ enum typé (voir App\Enums\UserStatus)
        'birth_date'    => 'date',
        'last_activity' => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    /**
     * ➕ Accessor "full_name" (firstname + lastname)
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn() => trim(($this->firstname ?? '') . ' ' . ($this->lastname ?? '')));
    }

    /**
     * ✨ Normaliser les noms (Première lettre en majuscule) via mutators
     */
    protected function firstname(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value ? Str::title(Str::of($value)->lower()) : null
        );
    }
    protected function lastname(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value ? Str::upper($value) : null
        );
    }
    /**
     * 🤝 Relation vers le créateur du compte (self-relation)
     */
    public function creator()
    {
        return $this->belongsTo(self::class, 'created_by');
    }
public function client()
{
    return $this->belongsTo(User::class, 'user_id')->where('default_role', 5);
}
    /**
     * 🔎 Scopes pratiques
     */
    public function scopeActive($q)
    {return $q->where('status', UserStatus::Activated);}
    public function scopePending($q)
    {return $q->where('status', UserStatus::Pending);}
    public function scopeDisabled($q)
    {return $q->where('status', UserStatus::Disabled);}

    public function getFilamentName(): string
    {
        // return $this->firstname;
        return "{$this->firstname} {$this->lastname}";
    }
    /**
     * (Optionnel mais pratique)
     * Fournit un attribut virtuel "name" pour tout code / package qui s'attend à "name".
     * Aucune colonne "name" n'est requise en base.
     */
    public function getNameAttribute(): string
    {
        return $this->firstname;
    }
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if ($model->status === null) {
                $model->status = 1;
            }
        });
    }
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'status'            => 'integer',
        ];
    }
    public function getProfileUrlAttribute(): string
    {
        if (! $this->profile) {
            return asset('assets/images/avatar-default.png'); // place ce fichier dans public/images/
        }

        // Bucket public → URL directe
        if (config('filesystems.disks.s3.visibility') === 'public' || env('AWS_URL')) {
            return Storage::disk('s3')->url($this->profile);
        }

        // Bucket privé → URL signée 5 minutes
        return Storage::disk('s3')->temporaryUrl($this->profile, now()->addMinutes(5));
    }
     public function avalability()
    {
        return $this->belongsTo(Availability::class, 'id');
    }
}
