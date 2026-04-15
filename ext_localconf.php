<?php

use OliverKlee\Oelib\Testing\TestingFrameworkCleanup;

defined('TYPO3') or die('Access denied.');

// @deprecated #2216 will be removed in oelib 7.0
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['oelib']['testingFrameworkCleanUp'][] = TestingFrameworkCleanup::class;
