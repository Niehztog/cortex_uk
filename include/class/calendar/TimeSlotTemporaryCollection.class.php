<?php
require_once __DIR__ . '/TimeSlotTemporary.class.php';
require_once __DIR__ . '/AbstractTimeSlotCollection.class.php';

class TimeSlotTemporaryCollection extends AbstractTimeSlotCollection {
	
	public function locateFreeSlot(DateTime $timeStart, DateTime $timeEnd) {
		foreach($this->timeSlotList as $timeSlot) {
			if($timeSlot->getStart() == $timeStart && $timeSlot->getEnd() == $timeEnd) {
				return $timeSlot->getLabId();
			}
		}
		return;
	}
	
	public function attachTimeSlot(TimeSlotTemporary $slot) {
		$tempId = $slot->getId();
		$this->timeSlotList[$tempId] = $slot;
	}

}