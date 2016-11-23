<?php
/**
 * Created by PhpStorm.
 * User: John
 * Date: 23/11/2016
 * Time: 12:59
 */

namespace App\Models;


class Sheep extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sheep';

    /**
     * The database table used by the model.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'move_on',
        'move_off',
        'off_how',
        'e_flock',
        'original_e_flock',
        'colour_flock',
        'e_tag',
        'e_tag_1',
        'e_tag_2',
        'original_e_tag',
        'colour_tag',
        'colour_tag_1',
        'colour_tag_2'
        ];

    /**
     * @param integer
     *
     * @return array
     *
     */
    public function user()
    {
        return $this->belongsTo('User');
    }


}