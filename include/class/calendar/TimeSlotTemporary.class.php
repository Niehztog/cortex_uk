<?php
require_once __DIR__ . '/AbstractTimeSlot.class.php';

class TimeSlotTemporary extends AbstractTimeSlot {
	
	/**
	 * 
	 * @var int
	 */
	private $labId;
	
	public static function getInstanceByTemporaryId($tempId) {
		$data = self::decodeTemporaryId($tempId);
		$start = DateTime::createFromFormat('Y-m-d?H:i:s', $data['start']);
		$end = DateTime::createFromFormat('Y-m-d?H:i:s', $data['end']);
		
		return new self($tempId, $start, $end);
	}
	
	public static function getInstanceByStartEnd(DateTime $start, DateTime $end) {
		$tempId = TimeSlotTemporary::generateTemporaryId($start, $end);
		return new self($tempId, $start, $end);
	}
	
	public function setLabId($labId) {
		$this->labId = $labId;
	}
	
	public function getLabId() {
		return $this->labId;
	}
	
	/**
	 * Es geht hier um einen 2-way hash, also Rückkonvertierbar
	 *
	 * @param string $hash
	 */
	private static function decodeTemporaryId($tempId) {
		$str = base64_decode($tempId);
		$arr = explode('|', $str);
		return array('start' => $arr[0], 'end' => $arr[1]);
	}
	
	/**
	 * Erzeugt eine temporäre Id des TimeSlots, die für jede start-end Kombination einmalig ist
	 * @return string
	 */
	private static function generateTemporaryId(DateTime $start, DateTime $end) {
		return base64_encode(
			$start->format('Y-m-d\TH:i:s')
			. '|'
			. $end->format('Y-m-d\TH:i:s')
		);
	}
	
	/**
	 * Erzeugt den Titel um den Slot als Kallender Event darzustellen
	 */
	protected function generateTitle() {
		$capacity = $this->getAmount();
		return sprintf('%1$d %2$s frei', $capacity, $capacity > 1 ? 'Plätze' : 'Platz');
	}
	
}