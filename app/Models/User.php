<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'menu_layout',
        'profile_completed',
        'invitation_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'profile_completed' => 'boolean',
        ];
    }

    /**
     * Get the classes where this user is enrolled (for students)
     */
    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_student', 'student_id', 'class_id')
                    ->withPivot('enrolled_at')
                    ->withTimestamps();
    }

    /**
     * Get the classes where this user teaches (for teachers)
     */
    public function teachingClasses()
    {
        return $this->belongsToMany(ClassModel::class, 'teacher_classes', 'teacher_id', 'class_id')
                    ->withPivot(['subject_id', 'is_main_teacher'])
                    ->withTimestamps();
    }

    /**
     * Get the classes where this user is the main teacher
     */
    public function mainClasses()
    {
        return $this->teachingClasses()->wherePivot('is_main_teacher', true);
    }

    /**
     * Get the invitations sent by this user
     */
    public function sentInvitations()
    {
        return $this->hasMany(UserInvitation::class, 'invited_by');
    }

    /**
     * Check if user needs to complete profile
     */
    public function needsProfileCompletion(): bool
    {
        return !$this->profile_completed || 
               empty($this->first_name) || 
               empty($this->last_name);
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }
        return $this->name;
    }
}
