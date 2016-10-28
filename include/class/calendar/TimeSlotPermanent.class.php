<?php
require_once __DIR__ . '/AbstractTimeSlot.class.php';

/**
 * Class TimeSlotPermanent
 * Represents a preconfigured timeslot for experiments in manual mode
 */
class TimeSlotPermanent extends AbstractTimeSlot {
	
	/**
	 * 
	 * @var int
	 */
	private $expId;
	
	/**
	 * Determines whether to display the signup amount of the timeslot in the title
	 * @var bool
	 */
	private $hideAmount = false;

    /**
     * Determines how many slots the permanent timeslot holds.
     *
     * @var int
     */
    private $capacity = null;
	
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
	 * Setter for property hideAmount
     * Determines whether to display the signup amount of the timeslot in the title
     *
	 * @param bool $hideAmount
	 */
	public function setHideAmount($hideAmount) {
		$this->hideAmount = $hideAmount;
	}

    /**
     * Getter for property hideAmount
     * Determines whether to display the signup amount of the timeslot in the title
     *
     * @return bool
     */
	public function getHideAmount() {
		return $this->hideAmount;
	}

    /**
     * @return int
     */
    public function getCapacity()
    {
        return $this->capacity;
    }

    /**
     * @param int $capacity
     */
    public function setCapacity($capacity)
    {
        $this->capacity = $capacity;
    }
	
	/**
	 * Erzeugt den Titel um den Slot als Kallender Event darzustellen
	 */
	protected function generateTitle() {
		if($this->getHideAmount()) {
			return '';
		}
		$amount = $this->getAmount();
        $capacity = $this->getCapacity();
		return $capacity === null
            ? sprintf('%1$d Anmeldung%2$s', $amount, $amount > 1 ? 'en' : '')
            : sprintf('%1$d %2$s frei', $capacity - $amount, $capacity - $amount > 1 ? 'Plätze' : 'Platz');
	}
	
}