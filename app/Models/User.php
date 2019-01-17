<?php

/**
 *
 * Date: Sun, 24 Jun 2018 10:20:30 +0000.
 */

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Zizaco\Entrust\Traits\EntrustUserTrait;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 *
 * @property int $id
 * @property int $operator_id
 * @property string $username
 * @property string $password
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property int $specialaccount
 * @property string $lastlogon
 * @property int $logincount
 * @property string $mobilephone
 * @property string $otherphones
 * @property string $address
 * @property boolean $blobdata
 * @property string $extra
 * @property bool $active
 * @property \Carbon\Carbon $created
 * @property \Carbon\Carbon $modified
 * @property int $createdby
 * @property int $modifiedby
 * @property \Carbon\Carbon $lastmodified
 * @property int $created_by
 * @property string $modified_by
 * @property bool $deleted
 * @property \Carbon\Carbon $deleted_date
 *
 * @property \Illuminate\Database\Eloquent\Collection $notices
 * @property \Illuminate\Database\Eloquent\Collection $properties
 * @property \Illuminate\Database\Eloquent\Collection $properties_photos
 * @property \Illuminate\Database\Eloquent\Collection $property_owners
 * @property \Illuminate\Database\Eloquent\Collection $roles
 * @property \Illuminate\Database\Eloquent\Collection $user_logins
 *
 * @package App\Models
 */
class User extends Authenticatable implements JWTSubject
{


    use Notifiable;


    protected $hidden = [
        'password'
    ];

    protected $fillable = [
        'password',
        'name',
        'email',
        'remember_token'
    ];


    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
