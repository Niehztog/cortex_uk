<?php
require_once __DIR__ . '/../../functions.php';
require_once 'include/class/DatabaseFactory.class.php';
require_once 'include/class/menu/MenuItem.class.php';
require_once 'include/class/menu/MenuSeparator.class.php';
require_once __DIR__ . '/../ExperimentDataProvider.class.php';

class Menu {
	
	private $mysqli;
	
	private $adminMode = false;
	
	private $currentLink;
	
	//private $itemId = 0;
	
	public function __construct($currentLink = '', $adminMode = false) {
		$this->currentLink = $currentLink;
		$this->adminMode = $adminMode;
		$dbf = new DatabaseFactory();
		$this->mysqli = $dbf->get();
	}
	
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
			echo '<!--' . $e . '-->';
		}
		
	}
	
	private function getMenuItemInstance($titel = null, $link = null) {
		return new MenuItem($titel, $link, $this->currentLink);
	}
	
	private function getStructure() {

		$menu = array();
		
		$experimente		= $this->getMenuItemInstance('Experimente', 'index.php');
		$experimente->setChildren($this->getSubMenuExperiments());
		$menu[] = $experimente;
		
		$administration		= $this->getMenuItemInstance('Administration', 'admin.php');
		if(!$this->adminMode) {
			$menu[] = $administration;
		}
		else {
			$administration->setChildren($this->getSubMenuAdministration());
			$menu[] = $administration;
			
			$versuchspersonen	= $this->getMenuItemInstance('Versuchspersonen', 'vpview.php');
			$versuchspersonen->setChildren($this->getSubMenuVersuchspersonen());
			$menu[] = $versuchspersonen;
			
			$labore				= $this->getMenuItemInstance('Labore', 'admin.php?menu=lab');
			$labore->setChildren($this->getSubMenuLabore());
			$menu[] = $labore;
		}
		
		$impressum			= $this->getMenuItemInstance('Impressum', 'impressum.php');
		
		$menu[] = $impressum;

		return $menu; 
		
	}
	
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
		$erg = $this->mysqli->query($abfrage) or die($mysqli->error);
		while ($data = $erg->fetch_assoc())
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
		$erg = $this->mysqli->query($abfrage);
		while ($data = $erg->fetch_assoc()) {
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
	
		$erg = $this->mysqli->query($abfrage) OR die($mysqli->error);
		while ($data = $erg->fetch_assoc())
		{
			$subItem = $this->getMenuItemInstance(
				$data['label'],
				"admin.php?menu=lab&id=" . $data['id']
			);
			$subItem->incLevel();
			$subItem->setEnabled('true'===$data['active']);
			$subMenu[] = $subItem;
		}
		$subMenu[] = new MenuSeparator();
		$subItem = $this->getMenuItemInstance(
			'Labor hinzufügen',
			"admin.php?menu=lab&action=create"
		);
		$subItem->incLevel();
		$subMenu[] = $subItem;
	
		return $subMenu;
	
	}
	
}