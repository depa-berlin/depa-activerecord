<?php
namespace Depa\ActiveRecord;

class Relation
{
    protected $multiple;

    protected $link;

    protected $primaryRecord;

    protected $relatedRecords;

    protected $relatedModel;

    public function __construct(ActiveRecord $primaryRecord, $relatedModel, $link)
    {
        $this->primaryRecord = $primaryRecord;
        if (! class_exists($relatedModel)) {
            throw new Exception('Invalid related model!');
        }
        $this->relatedModel = $relatedModel;
        $this->linkBy($link);
    }

    public function getPrimaryRecord()
    {
        if (isset($this->primaryRecord)) {
            return $this->primaryRecord;
        }
    }

    public function addRelated($record)
    {
        if (! $record instanceof $this->relatedModel) {
            throw new Exception('Record is not instance of specified model');
        }
        if (count($this->relatedRecords >= 1) && $this->multiple === false) {
            throw new Exception('Unable to add record');
        }
        $link = $this->getLinkValue($record);
        $this->relatedRecords[$link] = $record;
    }

    public function removeRelated($record)
    {
        $link = $this->getLinkValue($record);
        if (array_key_exists($link, $this->relatedRecords)) {
            unset($this->relatedRecords[$link]);
            return $record;
        }
        return null;
    }

    public function setRelated($related)
    {
        foreach ($related as $record) {
            if (count($this->relatedRecords === 1) && $this->multiple === false) {
                return;
            }
            $this->addRelated($record);
        }
    }

    public function linkBy($link)
    {
        if ($link instanceof \Closure && func_num_args($link) !== 1) {
            throw new Exception('Provided callback has invalid number of required parameters!');
        }
        $this->link = $link;
    }

    public function getLinkValue($record)
    {
        if ($this->link instanceof \Closure) {
            return $this->link($record);
        }
        return $record->$this->link;
    }

    public function getByLink($link)
    {
        if (array_key_exists($link, $this->relatedRecords)) {
            return $this->relatedRecords[$link];
        }
        return null;
    }

    public function getRelated()
    {
        return $this->relatedRecords;
    }
}
