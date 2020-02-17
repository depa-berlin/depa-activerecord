<?php
namespace Depa\ActiveRecord;

class ActiveRelation extends Relation
{
    protected $isPopulated;

    protected $relatedLink;

    public function __construct(ActiveRecord $primaryRecord, ActiveRecord $relatedModel, $link, $relatedLink)
    {
        $this->relatedLink = $relatedLink;
        $relatedModel::setAdapter($primaryRecord::getAdapter());
        parent::__construct($primaryRecord, $relatedModel, $link);
        $this->populateRelated();
    }

    public function findRelated($condition)
    {
        return $this->findByCondition($condition, true);
    }

    public function findAllRelated($condition)
    {
        return $this->findByCondition($condition, true);
    }

    protected function findByCondition($condition, $single)
    {
        $result = [];
        if (! $this->isPopulated === true) {
            $this->populateRelated();
        }
        if ($condition instanceof \Closure) {
            $result = array_filter($this->relatedRecords, $condition);
        }
        foreach ($this->relatedRecords as $record) {
            foreach ($condition as $attribute => $value) {
                if ($record->$attribute !== $value) {
                    continue 2;
                }
            }
            $result[] = $record;
            if ($single === true) {
                break;
            }
        }
        return $result;
    }

    protected function populateRelated()
    {
        $table = forward_static_call([$this->relatedModel, 'getTable']);
        $rowset = $table->select(function (\Laminas\Db\Sql\Select $select) {
            $select->where([ $this->relatedLink => $this->primaryRecord->__get($this->link) ]);
            if ($this->multiple === false) {
                $select->limit(1);
            }
        });
        $this->relatedRecords = $rowset;
    }

    public function save()
    {
        $this->primaryRecord->save();
        /** @var ActiveRecord $record */
        foreach ($this->relatedRecords as $record) {

            $record->save();
        }
    }
}
