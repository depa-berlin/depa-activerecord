<?php
namespace Depa\ActiveRecord;

//use Depa\Core\DataModel\ActiveRecord\ActiveRecord;
//‚use Depa\Core\DataModel\ActiveRecord\Adapter\ActiveRecordAdapter;
//use Depa\Core\Api\Hal;
use Depa\Stdlib\HalableInterface;
use Laminas\Paginator\Paginator;
use Laminas\Diactoros\Uri;

/**
 *
 * @author fenrich
 *
 */
class ActiveRecordPaginator extends Paginator implements HalableInterface
{

    protected static $defaultItemCountPerPage = 10;


    /**
     * Constructor.
     *
     * @param AdapterInterface|AdapterAggregateInterface $adapter
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(ActiveRecord $activeRecord, $conditions = null, $sort = null)
    {
        $adapter = new ActiveRecordAdapter($activeRecord, $conditions, $sort);
        parent::__construct($adapter);
    }
    /**
     *
     * {@inheritDoc}
     * @see \Depa\Core\Interfaces\Halable::toHal()
     */

    public function toHal(Uri $requestUri)
    {
        $apiHal = new Hal();
        $apiHal->addElement('count', $this->getTotalItemCount());
        $this->_makeLink($apiHal, $requestUri);

        foreach ($this->getCurrentItems() as $record) {
            // Query aus Uri entfernen
            $requestUriTmp = $requestUri->withQuery("");
            // Path mit ID ergänzen
            $path = $requestUriTmp->getPath();
            $requestUriTmp = $requestUriTmp->withPath($path . "/" . $record->id);

            $apiHal->addEmbed((new \ReflectionClass($record))->getShortName(), $record->toHal($requestUriTmp));
        }

        return $apiHal->getHal();
    }

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

    protected function _makeLink($apiHal, Uri $requestUri)
    {
        $queryArray = explode('&', $requestUri->getQuery());

        // page Information aus QueryString entfernen
        $newQueryArray = [];
        foreach ($queryArray as $key => $value) {
            // if (stripos($value, 'page') === FALSE) {
            if ($value != 'page') {
                if (! empty($value)) {
                    $newQueryArray[] = $value;
                }
            }
        }

        $currentPageNumber = $this->getCurrentPageNumber();

        $pageCount = $this->count();
        if ($pageCount == 0) {
            $pageCount = 1;
        }
        $apiHal->addLink('self', $this->_makeUri($requestUri, $newQueryArray, 'page=' . $currentPageNumber));
        $apiHal->addLink('first', $this->_makeUri($requestUri, $newQueryArray, 'page=1'));

        $apiHal->addLink('last', $this->_makeUri($requestUri, $newQueryArray, 'page=' . $pageCount));
        // Previous and next
        if ($currentPageNumber - 1 > 0) {
            $apiHal->addLink('prev', $this->_makeUri($requestUri, $newQueryArray, 'page=' . ($currentPageNumber - 1)));
        }

        if ($currentPageNumber + 1 <= $pageCount) {
            $apiHal->addLink('next', $this->_makeUri($requestUri, $newQueryArray, 'page=' . ($currentPageNumber + 1)));
        }
    }

    protected function _makeUri(Uri $uri, $queryArray, $newQuery)
    {
        $queryArray[] = $newQuery;
        return $uri->withQuery(implode("&", $queryArray));
    }
}
