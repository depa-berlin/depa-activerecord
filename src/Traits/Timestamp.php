<?php

namespace Depa\ActiveRecord\Traits;

trait Timestamp
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    public $dateFormat = 'Y-m-d H:i:s';

    public $timeZone = 'UTC';



    /**
     * Update the creation and update timestamps.
     *
     * @return void
     */
    protected function updateTimestamps()
    {
        $time = $this->newTimestamp();

        if (! is_null(static::UPDATED) /*&& ! $this->isDirty(static::UPDATED)*/) {
            $this->setUpdatedAt($time);
        }

        if (! $this->rowExistsInDatabase() && ! is_null(static::CREATED) /*&&
            ! $this->isDirty(static::CREATED)*/) {
            $this->setCreatedAt($time);
        }
    }

    /**
     * Set the value of the "created at" attribute.
     *
     * @param  mixed  $value
     * @return $this
     */
    public function setCreatedAt($value)
    {
        $this->{static::CREATED} = $value;

        return $this;
    }

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param  mixed  $value
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        $this->{static::UPDATED} = $value;

        return $this;
    }

    /**
     * Get a new timestamp.
     *
     * @return string
     */
    public function newTimestamp()
    {
        $date = new \DateTime('now');
        $date->setTimezone(new \DateTimeZone($this->timeZone));
        return $date->format($this->dateFormat);
    }




    /**
     * Get the name of the "created at" column.
     *
     * @return string
     */
    public function getCreatedAtColumn()
    {
        return static::CREATED;
    }

    /**
     * Get the name of the "updated at" column.
     *
     * @return string
     */
    public function getUpdatedAtColumn()
    {
        return static::UPDATED;
    }
}
