<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckListItemStatusHistory extends Model
{

    public $timestamps = false;
    protected $dates = [
        'created_at',
    ];

    protected $fillable = [
        'user_id',
        'status',
        'created_at',
    ];

    protected $hidden = ['check_list_item_id'];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->setEagerLoads([]);
    }
}
