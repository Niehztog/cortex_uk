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
     * @return string
     */
	public function __toString() {
		$output = '';
		if(!empty($this->timeSlotList)) {
			$first = true;
			foreach($this->timeSlotList as $timeSlot) {
				if($first) {
					$first = false;
				}
				else {
					$output .= ',';
				}
				$output .= (string)$timeSlot;
			}
		}
		return $output;
	}
	
}