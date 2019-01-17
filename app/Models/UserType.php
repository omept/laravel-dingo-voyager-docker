<?php

/**
*
 * Date: Wed, 26 Dec 2018 16:18:24 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class UserType
 * 
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \Illuminate\Database\Eloquent\Collection $users
 *
 * @package App\Models
 */
class UserType extends Eloquent
{
	protected $fillable = [
		'name'
	];

	public function users()
	{
		return $this->hasMany(\App\Models\User::class);
	}
}
