<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Francois Suter (Cobweb) <typo3@cobweb.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_t3lib . 'class.t3lib_svbase.php');


/**
 * Service "White lists/Black lists" for the "wordlists" extension.
 *
 * @author		Francois Suter (Cobweb) <typo3@cobweb.ch>
 * @package		TYPO3
 * @subpackage	tx_wordlists
 *
 * $Id$
 */
class tx_wordlists_sv1 extends t3lib_svbase {
	public $prefixId = 'tx_wordlists_sv1';		// Same as class name
	public $scriptRelPath = 'sv1/class.tx_wordlists_sv1.php';	// Path to this script relative to the extension dir.
	public $extKey = 'wordlists';	// The extension key.
	protected $configuration;
	protected $listType = 'black';
	protected $words = array();
	
	/**
	 * This method checks whether the service is able to perform its duty or not
	 *
	 * @return	boolean		TRUE or FALSE depending on availability of service
	 */
	public function init()	{
			// Read the extension's configuration
		$this->configuration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
			// Nothing can make this service unavailable
		return TRUE;
	}

	/**
	 * This method loads a given wordlists record for checking
	 * The $parameters array is expected to contain a "uid" index which
	 * points to the uid of a wordlists record
	 *
	 * @param	array	$parameters: list of parameters, as needed by the service
	 * @return	void
	 */
	public function load($parameters) {
			// Reset the type of list and the list of words
			// Note that these default values make it so that every word is valid
			// (an empty black list means that nothing is excluded)
		$this->listType = 'black';
		$this->words = array();
			// Load the new list given its uid
		$uid = intval($parameters['uid']);
			// The give id is empty, issue warning
		if (empty($uid)) {
			if (!empty($this->configuration['debug'])) {
				t3lib_div::devLog('No uid given. All words will be allowed.', $this->extKey, 2);
			}
		} else {
			$record = array();
			$fields = '*';
			$table = 'tx_wordlists_lists';
			$where = "uid = '" . $uid . "'";
				// If in the FE context, use overlays API to retrieve record
				// so that we get it automatically overlayed with current language
			if ($GLOBALS['TYPO3_MODE'] == 'FE') {
				$records = tx_overlays::getAllRecordsForTable($fields, $table, $where);
				if (count($records) > 0) {
					$record = $records[0];
				}

				// If operating in another context, get record as is
			} else {
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where);
				if ($res && $GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
					$record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				}
			}
				// If a record was retrieved, load its content into the internal words list
			if (count($record) > 0) {
				$this->listType = $record['type'];
				$lines = t3lib_div::trimExplode("\n", $record['words'], TRUE);
				foreach ($lines as $aLine) {
					$words = t3lib_div::trimExplode(',', $aLine, TRUE);
					foreach ($words as $aWord) {
						$this->words[] = $aWord;
					}
				}
				$this->words = array_unique($this->words);

				// No record was found, issue a warning
			} else {
				if (!empty($this->configuration['debug'])) {
					t3lib_div::devLog('No word list found for uid ' . $uid . '. All words will be allowed.', $this->extKey, 2);
				}
			}
		}
	}

	/**
	 * This method checks whether a given word is valid or not
	 * according to the loaded white list of black list
	 *
	 * @param	string		$word: the word to check
	 * @return	boolean		TRUE or FALSE depending on the assessed validity of the word
	 */
	public function isValidWord($word) {
		$isValid = FALSE;
		switch ($this->listType) {
				// White list: the word is valid if it is in the list of words
			case 'white':
				$isValid = in_array($word, $this->words);
				break;

				// Black list: the word is valid if it is *NOT* in the list of words
			default:
				$isValid = !in_array($word, $this->words);
				break;
		}
		return $isValid;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wordlists/sv1/class.tx_wordlists_sv1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/wordlists/sv1/class.tx_wordlists_sv1.php']);
}

?>