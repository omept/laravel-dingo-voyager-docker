<?php

/**
 * Created by gem.
 * Date: Sun, 10 Jun 2018 21:01:26 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class ApiLog
 * 
 * @property int $id
 * @property string $url
 * @property string $method
 * @property string $data_param
 * @property string $response
 * @property \Carbon\Carbon $created
 * @property \Carbon\Carbon $modified
 *
 * @package App\Models
 */
class ApiLog extends Eloquent
{
	public $timestamps = false;

	protected $dates = [
		'created',
		'modified'
	];

	const CREATED_AT = "created";
	const UPDATED_AT = "modified";

	protected $fillable = [
		'url',
		'method',
		'data_param',
		'response',
		'created',
		'modified'
	];
}
