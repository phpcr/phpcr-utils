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
 * @subpackage Query
 * @version $Id: JoinInterface.php 2191 2009-05-07 19:49:06Z k-fish $
 */

/**
 * Performs a join between two node-tuple sources.
 *
 * @package PHPCR
 * @subpackage Query
 * @version $Id: JoinInterface.php 2191 2009-05-07 19:49:06Z k-fish $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 */
interface PHPCR_Query_QOM_JoinInterface extends PHPCR_Query_QOM_SourceInterface {

	/**
	 * Gets the left node-tuple source.
	 *
	 * @return PHPCR_Query_QOM_SourceInterface the left source; non-null
	 */
	public function getLeft();

	/**
	 * Gets the right node-tuple source.
	 *
	 * @return PHPCR_Query_QOM_SourceInterface the right source; non-null
	 */
	public function getRight();

	/**
	 * Gets the join type.
	 *
	 * @return string one of QueryObjectModelConstants.JCR_JOIN_TYPE_*
	 */
	public function getJoinType();

	/**
	 * Gets the join condition.
	 *
	 * @return JoinCondition the join condition; non-null
	 */
	public function getJoinCondition();
}

?>