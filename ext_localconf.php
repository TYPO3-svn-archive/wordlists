<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

t3lib_extMgm::addService($_EXTKEY,  'wordFilter' /* sv type */,  'tx_wordlists_sv1' /* sv key */,
		array(

			'title' => 'White lists/Black lists',
			'description' => 'This service is designed to filter words based on lists of allowed or forbidden words',

			'subtype' => 'wordlists',

			'available' => TRUE,
			'priority' => 50,
			'quality' => 50,

			'os' => '',
			'exec' => '',

			'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_wordlists_sv1.php',
			'className' => 'tx_wordlists_sv1',
		)
	);
?>