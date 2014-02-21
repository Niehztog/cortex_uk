<?php
require_once __DIR__ . '/../DatabaseClient.class.php';

abstract class AbstractTimeSlotCollection extends DatabaseClient implements IteratorAggregate {
	
	protected $timeSlotList = array();
	
	public function getIterator() {
		return new ArrayIterator($this->timeSlotList);
	}
	
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