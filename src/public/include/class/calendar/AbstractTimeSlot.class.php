<?php

/**
 * Class AbstractTimeSlot
 */
abstract class AbstractTimeSlot {
	
	/**
	 * Id des TimeSlots, die für jede start-end Kombination einmalig ist
	 * @var string
	 */
	private $id;
	
	/**
	 * Beginn des slots
	 * @var DateTime
	 */
	private $start;
	
	/**
	 * Ende des Slots
	 * @var DateTime
	 */
	private $end;
	
	/**
	 * Die Kapazität des Slots, gewöhnlich 1
	 * @var int
	 */
	private $amount = 1;
	
	/**
	 * 
	 * @var bool
	 */
	private $editable;
	
	/**
	 * 
	 * @var string
	 */
	private $backgroundColor;
	
	/**
	 * @param int $id 
	 * @param DateTime $start
	 * @param DateTime $end
     * @throws InvalidArgumentException
	 */
	public function __construct($id, DateTime $start, DateTime $end) {
		
		$this->validateStartEnd($start, $end);
		
		$this->setId($id);
		$this->setStart($start);
		$this->setEnd($end);
	}

	/**
	 * 
	 * @param DateTime $start
	 * @param DateTime $end
     * @throws InvalidArgumentException
	 */
	private function validateStartEnd(DateTime $start, DateTime $end) {
		if($start >= $end) {
			throw new InvalidArgumentException(sprintf('Bei allen Zeitslots muss end grösser als start sein (%1$s >= %2$s)', $start->format('Y-m-d\TH:i:s'), $end->format('Y-m-d\TH:i:s')));
		}
	}
	
	/**
	 *
	 * @param string $start
	 */
	protected function setId($id) {
		$this->id = $id;
	}
	
	/**
	 * 
	 * @param DateTime $start
	 */
	protected function setStart(DateTime $start) {
		$this->start = $start;
	}
	
	/**
	 * 
	 * @param DateTime $end
	 */
	protected function setEnd(DateTime $end) {
		$this->end = $end;
	}
	
	/**
	 *
	 * @param int $amount
	 */
	public function setAmount($amount) {
		$this->amount = $amount;
	}
	
	/**
	 * Kapazität um 1 erhöhen
	 */
	public function incAmount() {
		return $this->amount++;
	}
	
	/**
	 * Kapazität um 1 veringern
	 */
	public function decAmount() {
		return $this->amount--;
	}
	
	/**
	 * 
	 * @param bool $editable
	 */
	public function setEditable($editable) {
		$this->editable = $editable;
	}
	
	/**
	 * 
	 * @param string $backgroundColor
	 */
	public function setBackgroundColor($backgroundColor) {
		$this->backgroundColor = $backgroundColor;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 *
	 */
	public function getStart() {
		return $this->start;
	}
	
	/**
	 *
	 */
	public function getEnd() {
		return $this->end;
	}
	
	/**
	 *
	 */
	public function getAmount() {
		return $this->amount;
	}
	
	/**
	 *
	 */
	public function getEditable() {
		return $this->editable;
	}
	
	/**
	 *
	 */
	public function getBackgroundColor() {
		return $this->backgroundColor;
	}

	/**
	 * Erzeugt den Titel um den Slot als Kallender Event darzustellen
	 */
	abstract protected function generateTitle();

	/**
	 * Gibt den TimeSlot in einem fullcalendar (javascript)
	 * kompatiblen Format aus
	 */
	public function __toString()
    {
        return $this->asJson();
    }

    private function asJson()
    {
        $res = array(
            'id' => $this->getId(),
            'title' => $this->generateTitle(),
            'start' => $this->getStart()->format('Y-m-d\TH:i:s'),
            'end' => $this->getEnd()->format('Y-m-d\TH:i:s'),
            'allDay' => false
        );

        if(null !== $this->getEditable()) {
            $res['editable'] = (bool)$this->getEditable();
        }

        if(null !== $this->getBackgroundColor()) {
            $res['backgroundColor'] = $this->getBackgroundColor();
        }

        return json_encode($res);
    }
	
}