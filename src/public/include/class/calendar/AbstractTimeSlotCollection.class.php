<?php
require_once __DIR__ . '/../DatabaseClient.class.php';

/**
 * Class AbstractTimeSlotCollection
 */
abstract class AbstractTimeSlotCollection extends DatabaseClient implements IteratorAggregate {

    /**
     * @var array
     */
	protected $timeSlotList = array();

    /**
     * @return ArrayIterator
     */
	public function getIterator() {
		return new ArrayIterator($this->timeSlotList);
	}

    /**
     * @return array
     */
	public function asArray() {
	    $result = array();
        foreach ($this as $termin) {
            $result[] = $termin->asArray();
        }
        return $result;
    }

}