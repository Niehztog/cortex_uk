<?php

/**
 * Class MenuSeparator
 */
class MenuSeparator {

    /**
     * @return string
     */
	public function __toString() {
		return sprintf('<li class="menu-item"><img src="images/line.gif" style="height:1px;width:170px;" alt="" /></li>');
	}
	
}