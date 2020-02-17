<?php
namespace Depa\ActiveRecord\Paginator;

//‚use Depa\Core\DataModel\ActiveRecord\ActiveRecord;
//use Depa\Core\DataModel\ActiveRecord\Paginator\Adapter;
use Laminas\Paginator\Paginator;

/**
 *
 * @author fenrich
 *
 */
class ActiveRecordPaginator extends Paginator
{

    protected static $defaultItemCountPerPage = 10;





    /**
     * Setzt den "default item count per page" nur neu, wenn dieser > 0.
     *
     * @param int $count
     */
    public static function setDefaultItemCountPerPage($count)
    {
        if ((int) $count > 0) {
            static::$defaultItemCountPerPage = (int) $count;
        }
    }
   /**
    *
    * @param array $sort
    */
    public function setItemSort($sort)
    {
        if (is_array($sort)&&count($sort) > 0) {
            $this->getAdapter()->setSort($sort);
        }
    }
}
