<?php
namespace Depa\ActiveRecord\Traits;

/**
 *
 * @author fenrich
 *
 */
trait SoftDeletes
{
    /**
     * Zeigt an, ob das Model aktuell das Löschen erzwingt
     *
     * @var bool
     */
    protected $forceDeleting = false;


    /**
     * Erzwingt das Löschen
     */
    public function forceDelete()
    {
        $this->forceDeleting = true;
        parent::delet();
        $this->forceDeleting = false;
    }

    public function delete()
    {
        //muss die column irgendwo im active record definiert werden?


        $time = (new \DateTime('NOW')) -> format(\DateTime::ATOM);
        $this->{$this->getDeletedAtColumn()} = $time;
        if (is_null($this->getUpdatedAtColumn())) {
                $this->{$this->getUpdatedAtColumn()} = $time;
        }
    }


    /**
     * Stellt eine Model aus dem Papierkorb wieder her
     * @return unknown
     */
    public function restore()
    {
        $this->{$this->getDeletedAtColumn()} = null;
        return parent::save();
    }
    /**
     * Stellt fest, ob das Model im Papierkorb liegt
     */
    public function trashed()
    {
        return ! is_null($this->{$this->getDeletedAtColumn()});
    }

    /**
     * Stellt fest, ob das Model aktuell auf direktes Löschen (force delete) eingestellt ist
     * @return string
     */
    public function isForceDeleting()
    {
        return $this->forceDeleting;
    }


    /**
     * Fibt den Namen der DELETED_AT Spalte zurück.
     *
     * @return string
     */
    public function getDeletedAtColumn()
    {
        //was macht dieser Befehlszeile genau? defined?
        return defined('static::DELETED_AT') ? static::DELETED_AT : 'deleted_at';
    }
}
