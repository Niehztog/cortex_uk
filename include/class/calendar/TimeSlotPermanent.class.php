<?php
require_once __DIR__ . '/AbstractTimeSlot.class.php';

class TimeSlotPermanent extends AbstractTimeSlot {
	
	/**
	 * 
	 * @var int
	 */
	private $expId;
	
	/**
	 * 
	 * @var bool
	 */
	private $hideAmount = false;
	
	/**
	 * 
	 * @param int $expId
	 */
	public function setExpId($expId) {
		$this->expId = $expId;
	}
	
	/**
	 * 
	 * @return int
	 */
	public function getExpId() {
		return $this->expId;
	}
	
	/**
	 * 
	 * @param bool $hideAmount
	 */
	public function setHideAmount($hideAmount) {
		$this->hideAmount = $hideAmount;
	}
	
	public function getHideAmount() {
		return $this->hideAmount;
	}
	
	/**
	 * Erzeugt den Titel um den Slot als Kallender Event darzustellen
	 */
	protected function generateTitle() {
		if($this->getHideAmount()) {
			return '';
		}
		$capacity = $this->getAmount();
		return sprintf('%1$d Anmeldung%2$s', $capacity, $capacity > 1 ? 'en' : '');
	}
	
}