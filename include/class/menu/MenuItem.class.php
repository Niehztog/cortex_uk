<?php

class MenuItem {
	
	/**
	 * Bezeichnung des Items
	 * @var string
	 */
	private $titel;
	
	/**
	 * Wohin das Item linken soll
	 * @var string
	 */
	private $link;
	
	private $currentLink;

	private $level = 1;
	
	private $enabled = true;
	
	/**
	 * Enthält Untermenüpunkte
	 * Falls dies ein Oberpunkt zu mehreren Unterpunkten ist,
	 * steht hier ein array mit allen Unterpunkten
	 * @var array
	 */
	private $children = array();
	
	public function __toString() {
	
		$hasChildren = $this->hasChildren();
		$isActive = $this->isActive();
	
		if( $hasChildren ) {
			$subMenu = PHP_EOL . '<ul class="sub-menu">' . PHP_EOL;
			foreach($this->getChildren() as $child) {
				$subMenu .= (string)$child . PHP_EOL;
			}
			$subMenu .= '</ul>' . PHP_EOL;
		}
		else {
			$subMenu = '';
		}
		
		$string = sprintf(
			'<li class="menu-item%1$s%2$s"><a href="%3$s">%4$s</a>%5$s</li>',
			$this->isEnabled() ? '' : ' menu_item_disabled',
			$isActive ? ' menu_item_active' : '',
			htmlspecialchars($this->getLink()),
			$this->getTitel(),
			$subMenu
		);
	
		return $string;
	
	}
	
	public function __construct($titel = null, $link = null, $currentLink = null) {
		if(null!==$titel) {
			$this->setTitel($titel);
		}
		if(null!==$link) {
			$this->setLink($link);
		}
		if(null!==$currentLink) {
			$this->setCurrentLink($currentLink);
		}
	}
	
	public function setTitel($titel) {
		$this->titel = $titel;
	}
	
	public function setLink($link) {
		$this->link = $link;
	}
	
	public function setCurrentLink($currentLink) {
		$this->currentLink = $currentLink;
	}
	
	public function setChildren($children) {
		$this->children = $children;	
	}
	
	public function incLevel() {
		$this->level++;
	}
	
	public function getLevel() {
		return $this->level;
	}
	
	public function setEnabled($enabled) {
		if(!is_bool($enabled)) {
			throw new InvalidArgumentException(var_export($enabled,true) . ' must be boolean');
		}
		$this->enabled = $enabled;
	}
	
	public function getTitel() {
		return $this->titel;
	}
	
	public function getLink() {
		return $this->link;
	}
	
	public function getCurrentLink() {
		return $this->currentLink;
	}
	
	public function getChildren() {
		return $this->children;
	}
	
	public function hasChildren() {
		return count($this->children) > 0;
	}
	
	public function isEnabled() {
		return $this->enabled;
	}
	
	public function isActive() {
	
		$requestUri = $this->getCurrentLink();
		if(empty($requestUri)) {
			return false;
		}
		$link = $this->getLink();
	
		if(($link === $requestUri || 0 === strpos($requestUri, $link . '&') || 0 === strpos($requestUri, $link . '?')) && !(0 === strpos($requestUri, 'admin.php?menu=lab') && false === strpos($link, 'admin.php?menu=lab'))) {
			return true;
		}
		if($this->hasChildren()) {
			$children = $this->getChildren();
			foreach($children as $child) {
				if($child instanceof MenuItem) {
					if($child->isActive()) {
						return true;
					}
					//damit kind beim späteren iterieren sich nicht nochmal für aktiv hält
					$child->setCurrentLink('');
				}
			}
		}
	
		return false;
	
	}
	
}