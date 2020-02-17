<?php
namespace Depa\ActiveRecord\Api;

use Depa\ActiveRecord\ActiveRecord;
use Laminas\ApiTools\ApiProblem\ApiProblem;

class Api extends \Depa\Core\Api\AbstractApi
{
    /**
     * @var ActiveRecord
     */
    protected $recordClass;

    protected $adapter;

    public function __construct(ActiveRecord $recordClass, $adapter)
    {
        $this->recordClass = $recordClass;
        $recordClass::loadConfig();
        $this->adapter = $adapter;
        $recordClass::setAdapter($adapter);
        parent::__construct();
    }

    public function create($data, $metadata)
    {
        $recordDataArray = [$data];
        if (array_intersect(array_keys($data), forward_static_call([$this->recordClass, 'getConfig'])['attributes']) === []) {
            $recordDataArray = $data;
        }
        $result = [];
        foreach ($recordDataArray as $recordData) {
            /** @var TYPE_NAME $record */
            $record = new $this->recordClass($this->adapter);
            $ret = $this->setData($record, (array) $recordData);
            if ($ret instanceof ApiProblem) {
                return $ret;
            }
            $record->save();
            $result[] = $this->apiReturnRequestedFields($metadata, $this->getData($record));
        }
        return $result;
    }

    public function read($data, $metadata)
    {
        $total = 0;
        $condition = null;
        //Alle records?
        $recordArray = [];
        //if (isset($metadata['filter']) && isset($metadata['filterValue']) && $this['recordClass']::hasAttribute($metadata['filter']))
        if (array_key_exists('filter', $metadata) && (array_key_exists('filterValue', $metadata)) && in_array($metadata['filter'], forward_static_call([$this->recordClass, 'getConfig'])['attributes'])) {
            $condition = [$metadata['filter'] => $metadata['filterValue']];
            $recordArray = forward_static_call([$this->recordClass, 'findAll'], $condition);
        }
        if (array_key_exists('limit', $metadata) && (array_key_exists('page', $metadata) || array_key_exists('start', $metadata))) {
            if (count($recordArray) === 0) {
                $page = null;
                $start = null;
                if (array_key_exists('page', $metadata)) {
                    $page = $metadata['page'];
                }
                if (array_key_exists('start', $metadata)) {
                    $start = $metadata['start'];
                }
                if ((int) $metadata['limit'] < 1 || ( (int) $start < 0 && (int) $page < 0 )) {
                    return new ApiProblem(400, 'Invalid pagination parameters!');
                }
                $recordArray = forward_static_call([$this->recordClass, 'getRecordsLimitedBy'], $page, $start, $metadata['limit']);

                unset($metadata['page']);
                unset($metadata['start']);
                unset($metadata['limit']);
            }
            $total = forward_static_call([$this->recordClass, 'getRecordCount'], $condition);
        } elseif (empty($recordArray)) {
            $recordArray = forward_static_call([$this->recordClass, 'getRecords']);
        }
        $result = [];
        foreach ($recordArray as $record) {
            $result[] = $this->apiReturnRequestedFields($metadata, $this->getData($record));
        }
        $result = (array) $this->apiPaginate($result, $metadata);
        if ($total > 0) {
            $result['total'] = $total;
        }
        return $result;
    }

    public function update($data, $metadata)
    {
        $result = [];
        $recordDataArray = [$data];
        if (array_intersect(array_keys($data), forward_static_call([$this->recordClass, 'getPrimaryKeys'])) === []) {
            $recordDataArray = $data;
        }
        foreach ($recordDataArray as $recordData) {
            $recordData = (array) $recordData;
            $record = $this->createRecordFromData($recordData);
            if ($record instanceof ApiProblem) {
                return $record;
            }
            $ret = $this->setData($record, (array) $recordData);
            if ($ret instanceof ApiProblem) {
                return $ret;
            }
            $record->save();
            $result[] = $this->apiReturnRequestedFields($metadata, $this->getData($record));
        }
        return $result;
    }

    public function destroy($data, $metadata)
    {
        $result = [];
        $recordDataArray = [$data];
        if (array_intersect(array_keys($data), forward_static_call([$this->recordClass, 'getPrimaryKeys'])) === []) {
            $recordDataArray = $data;
        }
        foreach ($recordDataArray as $recordData) {
            $recordData = (array) $recordData;
            $record = $this->createRecordFromData($recordData);
            if ($record === false) {
                return new ApiProblem(404, 'Record with Primary Key(s) not found!');
            }
            if ($record instanceof ApiProblem) {
                return $record;
            }
            $record->delete();
            if (array_keys(array_diff($recordData, $this->getData($record))) === $record::getPrimaryKeys()) {
                $result[] = $data;
            } else {
                $result[] = $this->apiReturnRequestedFields($metadata, $this->getData($record));
            }
        }
        return $result;
    }

    protected function setData(ActiveRecord $record, $data)
    {
        foreach ($data as $attribute => $value) {
            if (! $record->hasAttribute($attribute)) {
                return new ApiProblem(400, 'Unable to set data: invalid Attribute: '.$attribute);
            }
            $record->{$attribute} = $value;
        }
    }

    protected function getData(ActiveRecord $record)
    {
        $result = [];
        foreach ($record->attributes as $attribute) {
            $result[$attribute] = $record->$attribute;
        }
        return $result;
    }

    protected function createRecordFromData($data)
    {
        $primaryKeyData = [];
        foreach (forward_static_call([$this->recordClass, 'getPrimaryKeys']) as $primaryKey) {
            if (! array_key_exists($primaryKey, $data)) {
                return new ApiProblem(400, 'Unable to create record: missing primary key data!');
            }
            $primaryKeyData[$primaryKey] = $data[$primaryKey];
        }
        $record = forward_static_call([$this->recordClass, 'find'], $primaryKeyData);
        return $record;
    }
}
