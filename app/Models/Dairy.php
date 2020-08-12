<?php
namespace App\Models
use Illuminate\Database\Eloquent\Modelclass Dairy extends Model
{	/**
	 * @var array
	 */	/**
	 * The attributes that are mass assignable.
	 * 
	 * @var array
	 */	protected $fillable = [		'name',		'type',		'date_of_manu',	];
	/**
	 * @var array
	 */	protected $casts = [		'date_of_manu' => 'date',	];
}