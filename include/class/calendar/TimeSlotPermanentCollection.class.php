<?php
require_once __DIR__ . '/TimeSlotPermanent.class.php';
require_once __DIR__ . '/AbstractTimeSlotCollection.class.php';

class TimeSlotPermanentCollection extends AbstractTimeSlotCollection {
	
	public function __construct($sqlWhere, $readApplicationCount = false, array $eventAttribues = null) {
		$this->readSessionsFromDatabase($sqlWhere, $readApplicationCount, $eventAttribues);
	}
	
	public function getSignUpCountForSession($timeSlot) {
		foreach($this->timeSlotList as $id => $currentTimeSlot) {
			if(		$currentTimeSlot->getStart() == $timeSlot->getStart()
				&&	$currentTimeSlot->getEnd() == $timeSlot->getEnd()
			) {
				return $currentTimeSlot->getAmount();
			}
		}
	}
	
	/**
	 * Minuend minus Subtrahend gleich Wert der Differenz
	 * 
	 * @param TimeSlotTemporary $timeSlotSubtrahend
	 * @throws RuntimeException
	 * @return boolean
	 */
	public function subtract(TimeSlotTemporary $timeSlotSubtrahend, $onlyExpId = null){
		$subStart = $timeSlotSubtrahend->getStart();
		$subEnd = $timeSlotSubtrahend->getEnd();
		
		//Minuend(en) finden...
		foreach($this->timeSlotList as $id => $timeSlotMinuend) {
			if($onlyExpId > 0 && $onlyExpId !== $timeSlotMinuend->getExpId()) {
				continue;
			}
			$minStart = $timeSlotMinuend->getStart();
			$minEnd = $timeSlotMinuend->getEnd();
			
			//Lage von Minuend und Subtrahend zueinander ermitteln
			
			// 1) keine Überschneidung
			if($minEnd <= $subStart || $subEnd <= $minStart) {
				continue;
			}
			
			// 2) sub = min
			elseif($minStart == $subStart && $minEnd == $subEnd) {
				$this->decrementTimeSlot($id);
			}
			
			// 3) sub innerh. min
			elseif($minStart < $subStart && $subEnd < $minEnd) {
				$this->timeSlotList[] = new TimeSlotPermanent($id, $minStart, $subStart);
				$this->timeSlotList[] = new TimeSlotPermanent($id, $subEnd, $minEnd);
				$this->decrementTimeSlot($id);
			}
			
			// 4) min innerh. sub
			elseif($subStart < $minStart && $minEnd < $subEnd) {
				$newSubtrahend1 = TimeSlotTemporary::getInstanceByStartEnd($subStart, $minStart);
				$newSubtrahend2 = TimeSlotTemporary::getInstanceByStartEnd($minEnd, $subEnd);
				$this->subtract($newSubtrahend1, $timeSlotMinuend->getExpId());
				$this->subtract($newSubtrahend2, $timeSlotMinuend->getExpId());
				$this->decrementTimeSlot($id);
			}
			
			// 5) Überschneidung, sub zuerst
			elseif($subStart < $minStart && $subEnd > $minStart) {
				$this->timeSlotList[] = new TimeSlotPermanent($id, $subEnd, $minEnd);
				$newSubtrahend = TimeSlotTemporary::getInstanceByStartEnd($subStart, $minStart);
				$this->subtract($newSubtrahend, $timeSlotMinuend->getExpId());
				$this->decrementTimeSlot($id);
			}
			
			// 6) Überschneidung, min zuerst
			elseif($minStart < $subStart && $minEnd > $subStart) {
				$this->timeSlotList[] = new TimeSlotPermanent($id, $minStart, $subStart);
				$newSubtrahend = TimeSlotTemporary::getInstanceByStartEnd($minEnd, $subEnd);
				$this->subtract($newSubtrahend, $timeSlotMinuend->getExpId());
				$this->decrementTimeSlot($id);
			}
			
			// 7) Start gleich, min länger
			elseif($minStart = $subStart && $minEnd > $subEnd) {
				$this->timeSlotList[] = new TimeSlotPermanent($id, $subEnd, $minEnd);
				$this->decrementTimeSlot($id);
			}
			
			// 8) Start gleich, sub länger
			elseif($minStart = $subStart && $minEnd < $subEnd) {
				$newSubtrahend = TimeSlotTemporary::getInstanceByStartEnd($minEnd, $subEnd);
				$this->subtract($newSubtrahend, $timeSlotMinuend->getExpId());
				$this->decrementTimeSlot($id);
			}
			
			// 9) Ende gleich, min länger
			elseif($minEnd = $subEnd && $minStart < $subStart) {
				$this->timeSlotList[] = new TimeSlotPermanent($id, $minStart, $subStart);
				$this->decrementTimeSlot($id);
			}
			
			// 10) Ende gleich, sub länger
			elseif($minEnd = $subEnd && $minStart > $subStart) {
				$newSubtrahend = TimeSlotTemporary::getInstanceByStartEnd($subStart, $minStart);
				$this->subtract($newSubtrahend, $timeSlotMinuend->getExpId());
				$this->decrementTimeSlot($id);
			}
			else {
				throw new RuntimeException(
					sprintf(
						'Unbehandelter Fall bei der Lagebestimmung von Minuend und Subtrahend: minS %1$s, minE %2$s, subS %3$s, subE %4$s'
						, $minStart
						, $minEnd
						, $subStart
						, $subEnd
					)
				);
			}
			
			$timeSlotSubtrahend->decAmount();

			return true;
		}
		return false;
	}
	
	private function decrementTimeSlot($id) {
		if(!array_key_exists($id, $this->timeSlotList)) {
			throw new InvalidArgumentException(sprintf('Es wurde versucht einen nicht existenten TimeSlot (%1$d) zu dezimieren', $id));
		}
		$this->timeSlotList[$id]->decAmount();
		if($this->timeSlotList[$id]->getAmount() <= 0) {
			unset($this->timeSlotList[$id]);
		}
	}
	
	private function readSessionsFromDatabase($sqlWhere, $readApplicationCount = false, array $eventAttribues = null) {
		$mysqli = $this->getDatabase();
	
		$sql = sprintf( '
			SELECT	id,
					CONCAT(tag,"T",session_s) as start,
					CONCAT(tag,"T",session_e) as end,
					exp
			FROM	%1$s
			WHERE	%2$s'
				, TABELLE_SITZUNGEN
				, $sqlWhere
		);
		$result = $mysqli->query($sql);
		if(false === $result) {
			trigger_error($mysqli->error, E_USER_WARNING);
			throw new RuntimeException(sprintf('Fehler beim Lesen der Sitzungsdaten(%1$s)', $sqlWhere));
		}
	
		$this->timeSlotList = array();
		while($termin = $result->fetch_assoc() ) {
			$id = (int)$termin['id'];

			if(array_key_exists($id, $this->timeSlotList)) {
				throw new RuntimeException(sprintf('Zwei permanente TimeSlots mit der selben id %1$d', $id));
			}
			
			$start = DateTime::createFromFormat('Y-m-d?H:i:s', $termin['start']);
			$end = DateTime::createFromFormat('Y-m-d?H:i:s', $termin['end']);
			
			$item = new TimeSlotPermanent($id, $start, $end);
			if($readApplicationCount) {
				$amount = $this->getApplicationCountForSession($id);
				
				if(0 === $amount) {
					continue;
				}
				$item->setAmount($amount);
			}
			else {
				$item->setHideAmount(true);
			}
			
			if(!empty($termin['exp'])) {
				$item->setExpId((int)$termin['exp']);
			}
			
			if(is_array($eventAttribues)) {
				foreach($eventAttribues as $setter => $value) {
					if(is_callable(array($item, $setter))) {
						$item->$setter($value);
					}
				}
			}
			
			$this->timeSlotList[$id] = $item;
		}

	}
	
	private function getApplicationCountForSession($sitzungsId) {
		$mysqli = $this->getDatabase();
	
		$sql = sprintf( '
			SELECT	COUNT(id) AS count
			FROM	%1$s
			WHERE	termin = %2$d'
			, TABELLE_VERSUCHSPERSONEN
			, $sitzungsId
		);
		$result = $mysqli->query($sql);
		if(false === $result || 0 === $result->num_rows) {
			trigger_error($mysqli->error, E_USER_WARNING);
			throw new RuntimeException(sprintf('Fehler lesen der Belegdaten von Sitzung mit id %1$d', $sitzungsId));
		}
		$data = $result->fetch_assoc();
	
		return (int)$data['count'];
	}
	
	public function getTimeSlotList() {
		return $this->timeSlotList;
	}
	
	public function unite(TimeSlotPermanentCollection $tspc) {
		$this->timeSlotList = array_merge($this->timeSlotList, $tspc->getTimeSlotList());
	}
	
}