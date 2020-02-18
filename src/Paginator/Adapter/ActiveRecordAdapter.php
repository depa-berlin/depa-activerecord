<?php
namespace Depa\ActiveRecord\Paginator\Adapter;

use Depa\ActiveRecord\ActiveRecord;
use Laminas\Paginator\Adapter\AdapterInterface;

/**
 *
 * @author fenrich
 *
 */
class ActiveRecordAdapter implements AdapterInterface
{
    /**
     * @var ActiveRecord
     */
    protected $activeRecord;

    /**
     *
     * @var array||NULL
     */
    protected $conditions;

    protected $sort;

    /**
     *
     * @param ActiveRecord $activeRecord
     * @param array||NULL $conditions
     * @param unknown $sort
     */
    public function __construct(ActiveRecord $activeRecord, $conditions = null, $sort = null)
    {
        $this->activeRecord = $activeRecord;
        $conditionsTmp = null;

        if (! is_null($conditions)) {
            $conditionsTmp = [];
            foreach ($conditions as $key => $value) {
                if ($activeRecord->hasAttribute($key)) {
                    $conditionsTmp[$key] = $value;
                }
            }
        }
        $this->conditions = $conditionsTmp;

        $this->sort = $sort;
    }



    /**
     * Returns an array of items for a page.
     *
     * @param int $offset
     *            Page offset
     * @param int $itemCountPerPage
     *            Number of items per page
     * @return array
     * @see \Laminas\Paginator\Adapter\AdapterInterface::getItems()
     */
    public function getItems($offset, $itemCountPerPage)
    {
        // Resultset von ActiveRecord holen
        // offset ist das errechnete Element wo ich beginne (Seitenzahl * element je seite)
        $resultSet = forward_static_call([
            $this->activeRecord,
            'getRecords'
        ], $offset, $itemCountPerPage, $this->conditions, $this->sort);


        return iterator_to_array($resultSet);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Countable::count()
     *
     */
    public function count()
    {
        // Gesamtzahl der Elemente in DB
        return (forward_static_call([
            $this->activeRecord,
            'getRecordCount'
        ], $this->conditions));
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
    }
}
