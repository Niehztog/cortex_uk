<?php
require_once __DIR__ . '/../../config.php';
require_once 'include/class/DatabaseFactory.class.php';
require_once 'include/class/menu/MenuItem.class.php';
require_once 'include/class/menu/MenuSeparator.class.php';
require_once 'include/class/user/AccessControl.class.php';
require_once __DIR__ . '/../ExperimentDataProvider.class.php';

/**
 * Class Menu
 */
class Menu {

    /**
     * @var mysqli
     */
	private $mysqli;

    /**
     * @var AccessControl
     */
	private $auth;

    /**
     * @var string
     */
	private $currentLink;

    /**
     * Menu constructor.
     * @param string $currentLink
     */
	public function __construct($currentLink = '') {
		$this->currentLink = $currentLink;
		$this->auth = new AccessControl();
		$dbf = new DatabaseFactory();
		$this->mysqli = $dbf->get();
	}

    /**
     * @return string
     */
	public function __toString() {
		try {
			$string = '';
			$items = $this->getStructure();
			$string .= '<ul class="menu">' . PHP_EOL;
			foreach( $items as $item ) {
				$string .= (string)$item . PHP_EOL;
			}
			$string .= '</ul>' . PHP_EOL;
			return $string;
		}
		catch(Exception $e) {
			return '<!--' . $e->getMessage() . '-->';
		}
	}

    /**
     * @param null $titel
     * @param null $link
     * @return MenuItem
     */
	private function getMenuItemInstance($titel = null, $link = null) {
		return new MenuItem($titel, $link, $this->currentLink);
	}

    /**
     * @return array
     */
	private function getStructure() {

		$menu = array();
		
		$experimente		= $this->getMenuItemInstance('Experimente', 'index.php');
		$experimente->setChildren($this->getSubMenuExperiments());
		$menu[] = $experimente;
		
		$administration		= $this->getMenuItemInstance('Administration', 'admin.php');
		if($this->auth->isGuest()) {
			$menu[] = $administration;
		}
		else {
            if($this->auth->mayAccessExpControl()) {
                $administration->setChildren($this->getSubMenuAdministration());
                $menu[] = $administration;
            }

            if($this->auth->mayAccessVpControl()) {
                $versuchspersonen = $this->getMenuItemInstance('Versuchspersonen', 'vpview.php');
                $versuchspersonen->setChildren($this->getSubMenuVersuchspersonen());
                $menu[] = $versuchspersonen;
            }

            if($this->auth->mayAccessLabControl()) {
                $labore = $this->getMenuItemInstance('Labore', 'admin.php?menu=lab');
                $labore->setChildren($this->getSubMenuLabore());
                $menu[] = $labore;
            }
		}
		
		return $menu;
		
	}

    /**
     * @return array
     */
	private function getSubMenuExperiments() {

		$subMenu = array();
	
		$edp = new ExperimentDataProvider(null);
		$laufendeExperimente = $edp->getExpListSignUpAllowed();
		
		foreach($laufendeExperimente as $data) {
			$subItem = $this->getMenuItemInstance(
				$data['exp_name'],
				"index.php?menu=experiment&expid=" . md5($data['id'] . ID_OBFUSCATION_SALT)
			);
			$subItem->incLevel();
			$subMenu[] = $subItem;
		}
	
		return $subMenu;
	
	}

    /**
     * @return array
     */
	private function getSubMenuAdministration() {
	
		$subMenu = array();
	
		$abfrage = sprintf( '
			SELECT		`id`,
						IF(exp_name="","[Namenlos]",exp_name) AS exp_name,
						visible
			FROM		%1$s
			WHERE		visible <= 1
			ORDER BY	`exp_name` ASC'
			, TABELLE_EXPERIMENTE
		);
		$result = $this->mysqli->query($abfrage);
        if(false === $result) {
            trigger_error($this->mysqli->error, E_USER_WARNING);
            throw new RuntimeException('Fehler beim Lesen der Daten der Experimente');
        }
		while ($data = $result->fetch_assoc())
		{
			$subItem = $this->getMenuItemInstance(
				$data['exp_name'],
				"admin.php?expid=" . $data['id']
			);
			$subItem->incLevel();
			$subItem->setEnabled('1'===$data['visible']);
			$subMenu[] = $subItem;
		}
		$subMenu[] = new MenuSeparator();
		$subItem = $this->getMenuItemInstance(
			'Experiment hinzufügen',
			"admin.php?action=create"
		);
		$subItem->incLevel();
		$subMenu[] = $subItem;
	
		return $subMenu;
	
	}

    /**
     * @return array
     */
	private function getSubMenuVersuchspersonen() {
	
		$subMenu = array();
			
		$abfrage = sprintf( '
			SELECT		`id`,
						IF(exp_name="","[Namenlos]",exp_name) AS exp_name,
						visible
			FROM		%1$s
			WHERE		visible <= 1
			ORDER BY	`exp_name` ASC'
			, TABELLE_EXPERIMENTE
		);
		$result = $this->mysqli->query($abfrage);
        if(false === $result) {
            trigger_error($this->mysqli->error, E_USER_WARNING);
            throw new RuntimeException('Fehler beim Lesen der Daten der Experimente');
        }
		while ($data = $result->fetch_assoc()) {
			$subItem = $this->getMenuItemInstance(
				$data['exp_name'],
				'vpview.php?expid=' . $data['id']
			);
			$subItem->incLevel();
			$subItem->setEnabled('1'===$data['visible']);
			$subMenu[] = $subItem;
		}
	
		return $subMenu;
	
	}

    /**
     * @return array
     */
	private function getSubMenuLabore() {
	
		$subMenu = array();
	
		$abfrage = sprintf( '
			SELECT	id,
					IF(label=\'\',\'[undefined]\',label) AS label,
					address,
					room_number,
					capacity,
					active
			FROM	%1$s'
				, TABELLE_LABORE
		);
	
		$result = $this->mysqli->query($abfrage);
        if(false === $result) {
            trigger_error($this->mysqli->error, E_USER_WARNING);
            throw new RuntimeException('Fehler beim Lesen der Daten der Labore');
        }
		while ($data = $result->fetch_assoc())
		{
			$subItem = $this->getMenuItemInstance(
				$data['label'],
				"admin.php?menu=lab&id=" . $data['id']
			);
			$subItem->incLevel();
			$subItem->setEnabled('true'===$data['active']);
			$subMenu[] = $subItem;
		}
		if($this->auth->mayEditLabInfo()) {
            $subMenu[] = new MenuSeparator();
            $subItem = $this->getMenuItemInstance(
                'Labor hinzufügen',
                "admin.php?menu=lab&action=create"
            );
            $subItem->incLevel();
            $subMenu[] = $subItem;
        }
		return $subMenu;
	
	}
	
}