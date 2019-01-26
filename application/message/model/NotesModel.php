<?php
/**
 * Created by PhpStorm.
 * User: gravel
 * Date: 2018/4/13
 * Time: 14:34
 */
namespace app\message\model;
use think\Model;

class NotesModel extends Model
{
    protected $name = 'notes';

    public static function getNotesInfo($condition)
    {
        return self::where($condition)->limit(3)->select();

    }

    public static function updateNotes($condition,$data)
    {
        return self::where($condition)->update($data);
    }

    public static function setFieldNotes($condition,$setField)
    {
        return self::where($condition)->setField($setField);

    }

}