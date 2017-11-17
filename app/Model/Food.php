<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Food extends Model
{
    use SoftDeletes;

    protected $table = 'foods';
    protected $dates = ['deleted_at'];
    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * 访问器
     * @param $value
     * @return string
     */
    public function getStatusAttribute($value)
    {

        return $this->attributes['status']? 'onshelf': 'offshelf';
    }

    /**
     * 修改器
     * @param $value
     */
    public function setStatusAttribute($value)
    {

        $this->attributes['status'] = ($value == 'onshelf')? 1: 0;
    }
}
