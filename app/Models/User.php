<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class User extends Model
{	/**
	 * @var array
	 */	protected $table = 'users';
	/**
	 * The attributes that are mass assignable.
	 * 
	 * @var array
	 */	protected $fillable = [		'firstname',		'lastname',		'email',		'gender',		'is_approved',		'admission_date',	];
	/**
	 * @var array
	 */	protected $casts = [		'is_approved' => 'bool',		'admission_date' => 'date',	];
}