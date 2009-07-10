<?php
declare(ENCODING = 'utf-8');

/*                                                                        *
 * This script belongs to the FLOW3 package "PHPCR".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @package PHPCR
 * @subpackage Observation
 * @version $Id: EventIteratorInterface.php 2636 2009-06-23 09:10:29Z k-fish $
 */

/**
 * Allows easy iteration through a list of Events with nextEvent as well as a
 * skip method inherited from RangeIterator.
 *
 * @package PHPCR
 * @subpackage Observation
 * @version $Id: EventIteratorInterface.php 2636 2009-06-23 09:10:29Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
interface PHPCR_Observation_EventIteratorInterface extends PHPCR_RangeIteratorInterface {

	/**
	 * Returns the next Event in the iteration.
	 *
	 * @return PHPCR_Observation_EventInterface the next Event in the iteration
	 * @throws OutOfBoundsException if iteration has no more Events
	 */
	public function nextEvent();

}

?>