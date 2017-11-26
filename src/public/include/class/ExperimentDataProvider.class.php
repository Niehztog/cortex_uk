<?php
require_once __DIR__ . '/DatabaseClient.class.php';

/**
 * Class ExperimentDataProvider
 */
class ExperimentDataProvider extends DatabaseClient {

    /**
     * @var
     */
	private $expId;

    /**
     * ExperimentDataProvider constructor.
     * @param $expId
     */
	public function __construct($expId) {
		$this->setExpId($expId);
	}

    /**
     * @param $obfuscatedId
     * @return ExperimentDataProvider
     */
	public static function getInstanceByEncryptedId($obfuscatedId) {
		$instance = new self(null);
		$id = $instance->decryptId($obfuscatedId);
		$instance->setExpId($id);
		return $instance;
	}

    /**
     * @param $expId
     */
	public function setExpId($expId) {
		$this->expId = $expId;
	}

    /**
     * @return mixed
     */
	public function getExpId() {
		return $this->expId;
	}
	
	/**
	 * daten über experiment sammeln
	 *
     * @return array|mixed
     */
	public function getExpData() {
		static $cache = array();
		
		$expId = $this->getExpId();
		if(array_key_exists($expId, $cache)) {
			return $cache[$expId];
		}
	
		$mysqli = $this->getDatabase();
	
		$sql = sprintf( '
			SELECT		visible,
						IF(exp_start > "0000-00-00", exp_start, "") AS exp_start,
						IF(exp_end > "0000-00-00", exp_end, "") AS exp_end,
						exp.max_vp,
						exp.terminvergabemodus,
						exp.session_duration,
						exp.max_simultaneous_sessions,
						GROUP_CONCAT(DISTINCT e2l.lab_id) AS assigned_labs
			FROM		%1$s AS exp
			LEFT JOIN	%2$s AS e2l
				ON		exp.id = e2l.exp_id
			WHERE		exp.id = %3$d
			GROUP BY	exp.id'
				, TABELLE_EXPERIMENTE
				, TABELLE_EXP_TO_LAB
				, $expId
		);
		$result = $mysqli->query($sql);
		if(false === $result || 0 === $result->num_rows) {
			trigger_error($mysqli->error, E_USER_WARNING);
			throw new RuntimeException(sprintf('Fehler beim Lesen der Daten von Experiment id %1$d', $expId));
		}
		$data = $result->fetch_assoc();
		$data['assigned_labs'] = explode(',', $data['assigned_labs']);
		return $cache[$expId] = $data;
	}

    /**
     * @return bool
     */
	public function isSignUpAllowed() {
		$expId = $this->getExpId();
		$data = $this->getExpData($expId);
		
		if(1 != $data['visible']) {
			return false;
		}
		
		if('automatisch' === $data['terminvergabemodus']) {
			if(!empty($data['exp_end']) && false !== ($date = DateTime::createFromFormat('Y-m-d', $data['exp_end']))) {
				$now = new DateTime(null, new DateTimeZone(date_default_timezone_get()));
				if($date < $now) {
					return false;
				}
			}
			
			$vpcur = $this->getSignUpCountCurrent($expId);
			if($vpcur >= (int) $data['max_vp']) {
				return false;
			}
		}
		
		return true;
	}

    /**
     * @return int
     */
	public function getSignUpCountCurrent() {
		$mysqli = $this->getDatabase();
		$expId = $this->getExpId();
		
		$abfrage = sprintf(
			'SELECT COUNT(1) AS vpcur FROM %1$s WHERE `exp` = %2$d'
			, TABELLE_VERSUCHSPERSONEN
			, $expId
		);
		$result = $mysqli->query($abfrage);
        if(false === $result) {
            trigger_error($mysqli->error, E_USER_WARNING);
            throw new RuntimeException(sprintf('Fehler beim Lesen der Daten von Experiment id %1$d', $expId));
        }
		$data = $result->fetch_assoc();
		$vpcur = (int)$data['vpcur'];
		
		return $vpcur;
	}

    /**
     * @return int
     */
	public function getSignUpCountMax() {
		$expId = $this->getExpId();
		$data = $this->getExpData($expId);
		
		if('automatisch' === $data['terminvergabemodus']) {
			$vpmax = (int)$data['max_vp'];
		}
		else {
			$mysqli = $this->getDatabase();
			$abfrage2 = sprintf(
				'SELECT SUM(maxtn) AS vpmax FROM %1$s WHERE `exp` = %2$d'
				, TABELLE_SITZUNGEN
				, $expId
			);
			$result = $mysqli->query($abfrage2);
            if(false === $result) {
                trigger_error($mysqli->error, E_USER_WARNING);
                throw new RuntimeException(sprintf('Fehler beim Lesen der Daten von Experiment id %1$d', $expId));
            }
			$data2 = $result->fetch_assoc();
			$vpmax = (int)$data2['vpmax'];
		}
		
		return $vpmax;
	}

    /**
     * @return array
     */
	public function getExpListSignUpAllowed() {
		$mysqli = $this->getDatabase();
		$return = array();
		$abfrage = sprintf( '
			SELECT		`id`,
						`exp_name`,
						`exp_zusatz`
			FROM		%1$s
			WHERE		visible = "1"
				AND		show_in_list = "true"
			ORDER BY	`exp_name` ASC'
			, TABELLE_EXPERIMENTE
		);
		$result = $mysqli->query($abfrage);
        if(false === $result) {
            trigger_error($mysqli->error, E_USER_WARNING);
            throw new RuntimeException('Fehler beim Lesen der Daten der Experimente');
        }
		while($data = $result->fetch_assoc()) {
			$expId = (int)$data['id'];
			$this->setExpId($expId);
			if(!$this->isSignUpAllowed()) {
				continue;
			}
			$return[] = $data;
		}
		return $return;
	}

    /**
     * @param $obfuscatedId
     * @return bool
     */
	public function decryptId($obfuscatedId) {
		$mysqli = $this->getDatabase();
		$sql = sprintf('
			SELECT	id
			FROM	%1$s
			WHERE	md5(CONCAT(id, "%2$s")) = "%3$s"'
			, TABELLE_EXPERIMENTE
			, ID_OBFUSCATION_SALT
			, $mysqli->real_escape_string($obfuscatedId)
		);
		$result = $mysqli->query($sql);
		if(1 !== $result->num_rows) {
			return false;
		}
		$row = $result->fetch_assoc();
		return $row['id'];
	}
	
}