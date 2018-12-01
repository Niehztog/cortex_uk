<?php
require_once __DIR__ . '/../../functions.php';
require_once __DIR__ . '/../DatabaseClient.class.php';
require_once __DIR__ . '/../ExperimentDataProvider.class.php';
require_once __DIR__ . '/TimeSlotTemporaryCollection.class.php';
require_once __DIR__ . '/TimeSlotPermanentCollection.class.php';

/**
 * Class CalendarDataProvider
 */
class CalendarDataProvider extends DatabaseClient {
	
	/**
	 * Öffnungszeiten eines Labores sammeln
	 * 
	 * @param int $labId
	 * @return TimeSlotPermanentCollection
	 */
	public function getOpeningHoursFor($labId, $sqlWhereAdd = null) {
		$sqlWhere = sprintf(
			'lab_id = %1$d AND exp = 0'
			, $labId
		);
		if(null !== $sqlWhereAdd) {
			$sqlWhere .= ' AND ' . $sqlWhereAdd;
		}
		return new TimeSlotPermanentCollection($sqlWhere);
	}

	/**
	 * Belegdaten eines Labores sammeln
	 *
	 * @param int $labId
	 * @return TimeSlotPermanentCollection
	 */
	public function getAllocationData($labId, $sqlWhereAdd = null, array $eventAttribues = null) {
		$sqlWhere = sprintf(
			'lab_id = %1$s AND NOT exp = 0'
			, $labId
		);
		if(null !== $sqlWhereAdd) {
			$sqlWhere .= ' AND ' . $sqlWhereAdd;
		}
		return new TimeSlotPermanentCollection($sqlWhere, true, $eventAttribues);
	}

    /**
     * @param null $dateSqlStart
     * @param null $dateSqlEnd
     * @return string
     */
	private function createSqlWhereStartEnd($dateSqlStart = null, $dateSqlEnd = null) {
		$sqlWhere = array('tag >= DATE(NOW())');
		if(null !== $dateSqlStart) {
			$sqlWhere[] = sprintf('tag >= "%1$s"', $dateSqlStart);
		}
		if(null !== $dateSqlEnd) {
			$sqlWhere[] = sprintf('tag <= "%1$s"', $dateSqlEnd);
		}
		return implode(' AND ', $sqlWhere);
	}
	
	/**
	 * Belegdaten für ein Experiment sammeln
	 * 
	 * @param int $expId
	 * @return TimeSlotPermanentCollection
	 */
	public function getBookedSessionsFor($expId) {
		$sqlWhere = sprintf(
			'exp = %1$d'
			, $expId
		);

		return new TimeSlotPermanentCollection($sqlWhere, true);
	}
	
	/**
	 * Manuell eingetragene Sitzungen für ein Experiment sammeln
	 *
	 * @param int $expId
	 */
	private function getPreConfiguredSessionsFor($expId) {
		$sqlWhere = sprintf(
			'exp = %1$s AND lab_id = 0'
			, $expId
		);
		
		return new TimeSlotPermanentCollection($sqlWhere, true, null, true);
	}

    /**
     * Returns list of timeslots for given experiment
     *
     * @param $expId
     * @return TimeSlotPermanentCollection|TimeSlotTemporaryCollection
     */
	public function getAvailableSessionsFor($expId) {

        //zuerst gucken ob das Exp automatische oder manuelle Terminvergabe hat
        $schedulingMode = $this->getSchedulingMode($expId);
        if('automatisch' === $schedulingMode) {
			return $this->calculateAvailableSessionsFor($expId);
		}
		return $this->getPreConfiguredSessionsFor($expId);
		
	}
	
	/**
	 * Terminvergabemodus automatisch
	 *
	 * @param int $expId
	 * @throws RuntimeException
	 */
	private function calculateAvailableSessionsFor($expId) {
	
		//daten über experiment sammeln
		$edp = new ExperimentDataProvider($expId);
		$expData = $edp->getExpData();
		if(!($expData['session_duration'] > 0)) {
			throw new RuntimeException('Experimentdauer darf an dieser Stelle nicht 0 sein.');
		}
		$slotDuration = new \DateInterval('PT' . $expData['session_duration'] . 'M');
		
		if((int)$expData['max_simultaneous_sessions'] > 0) {
			$expSessionAllocation = new TimeSlotPermanentCollection(
				sprintf('exp = %1$d', $expId)
				, true
			);
		}
		else {
			$expSessionAllocation = null;
		}
		
		$sqlWhereAdd = $this->createSqlWhereStartEnd(
			!empty($expData['exp_start']) ? $expData['exp_start'] : null,
			!empty($expData['exp_end']) ? $expData['exp_end'] : null
		);
		
		$availableSlots = new TimeSlotTemporaryCollection();
		//öffnungszeiten timeframes in verfügbare slots umwandeln
		foreach($expData['assigned_labs'] as $labId) {
			$labData = $this->getLabData($labId);
			$openinghours = $this->getOpeningHoursFor($labId, $sqlWhereAdd);
			$allocationData = $this->getAllocationData($labId, $sqlWhereAdd);
			
			$timeSlotAmount = $labData['capacity'];
			
			foreach($openinghours as $availableTimeFrame) {

				//sonst fehlt der letzte schritt der iteration:
				$availableTimeFrame->getEnd()->modify('+1 second');
				
				$datePeriod = new DatePeriod(
					$availableTimeFrame->getStart(),
					$slotDuration,
					$availableTimeFrame->getEnd(),
					DatePeriod::EXCLUDE_START_DATE
				);
				$timeSlotEndLast = $availableTimeFrame->getStart();
				foreach($datePeriod as $timeSlotEnd) {
					$timeSlotStart = $timeSlotEndLast;
					$timeSlotEndLast = $timeSlotEnd;
					
					$newTimeSlot = TimeSlotTemporary::getInstanceByStartEnd($timeSlotStart, $timeSlotEnd);
					$newTimeSlot->setAmount($timeSlotAmount);
					$newTimeSlot->setLabId($labId);
					
					//Entscheidung: soll der TimeSlot aufgenommen werden oder ist er Belegt
					while(
						$newTimeSlot->getAmount() > 0 &&
						$allocationData->subtract($newTimeSlot)
					);

					$freeSlotAmount = $newTimeSlot->getAmount();
					if(!($freeSlotAmount > 0)) {
						continue;
					}
					if(null !== $expSessionAllocation) {
						$alreadySignup = $expSessionAllocation->getSignUpCountForSession($newTimeSlot);
						$slotsLeft = (int)$expData['max_simultaneous_sessions'] - $alreadySignup;
						if($freeSlotAmount > $slotsLeft) {
							if(!($slotsLeft > 0)) {
								continue;
							}
							$newTimeSlot->setAmount($slotsLeft);
						}
					}
					
					$availableSlots->attachTimeSlot($newTimeSlot);
	
				}
				
			}

		}
	
		return $availableSlots;
	}

	/**
	 * Nimmt alle dem experiment zugewiesenen Labore, und durchsucht Öffnungszeiten/Belegdaten
	 * nach dem zu belegenden Slot. Gibt die id des laboers zurück, in dessen Zeitplan der slot
	 * passt. Sollte der Slot in keinem Labor mehr frei sein, wird eine Exception geworfen
	 *
	 * @param int $timeStampStart
	 * @param int $timeStampEnd
	 * @throws RuntimeException
	 */
	public function getFirstLabWhereTimeSlotFits($expId, DateTime $timeStart, DateTime $timeEnd) {
		if($timeStart >= $timeEnd) {
			throw new RuntimeException(
				sprintf(
					'Bei allen Zeitslots muss end grösser als start sein (%1$d >= %2$d)'
					, $timeStart->format('Y-m-d\TH:i:s')
					, $timeEnd->format('Y-m-d\TH:i:s')
				)
			);
		}
		
		$availableSessions = $this->calculateAvailableSessionsFor($expId);
		$labId = $availableSessions->locateFreeSlot($timeStart, $timeEnd);
		
		if(!$labId) {
			throw new RuntimeException('Kein freies Zeitfenster in irgendeinem zugewiesenen Labor');
		}
		
		return $labId;
	}
	
	/**
	 * öffnungszeiten zugewiesener labore sammeln
	 * 
	 * @param int $labId
	 */
	private function getLabData($labId) {
		$mysqli = $this->getDatabase();
		
		$sql = sprintf( '
			SELECT		lab.capacity,
						lab.active
			FROM		%1$s AS lab
			WHERE		lab.id = %2$d'
			, TABELLE_LABORE
			, $labId
		);
		$result = $mysqli->query($sql);
		if(false === $result || 0 === $result->num_rows) {
			trigger_error($mysqli->error, E_USER_WARNING);
			throw new RuntimeException(sprintf('Fehler lesen der Daten von Labor id %1$d', $labId));
		}
		$data = $result->fetch_assoc();

		if('false' === $data['active']) {
			return array();
		}
		
		$data['capacity'] = (int) $data['capacity'];
		
		return $data;
	}

    /**
     * @param $expId
     * @return mixed
     */
    private function getSchedulingMode($expId)
    {
        $mysqli = $this->getDatabase();

        $sql = sprintf('SELECT terminvergabemodus FROM %1$s WHERE id = %2$d', TABELLE_EXPERIMENTE, $expId);
        $result = $mysqli->query($sql);
        if (false === $result) {
            trigger_error($mysqli->error, E_USER_WARNING);
            throw new RuntimeException(sprintf('Fehler beim Lesen der Daten von Experiment id %1$d', $expId));
        }

        $row = $result->fetch_assoc();
        return $row['terminvergabemodus'];
    }

}