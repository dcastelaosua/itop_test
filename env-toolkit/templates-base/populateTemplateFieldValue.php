#!/usr/bin/php
<?php
// Copyright (c) 2010-2019 Combodo SARL
//
//   This file is part of iTop.
//
//   iTop is free software; you can redistribute it and/or modify
//   it under the terms of the GNU Affero General Public License as published by
//   the Free Software Foundation, either version 3 of the License, or
//   (at your option) any later version.
//
//   iTop is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU Affero General Public License for more details.
//
//   You should have received a copy of the GNU Affero General Public License
//   along with iTop. If not, see <http://www.gnu.org/licenses/>
//

//TODO: compute the old TemplateField/TemplateExtraData into TemplateFieldValue



require_once('../../approot.inc.php');
require_once(APPROOT.'/application/application.inc.php');
require_once(APPROOT.'/application/startup.inc.php');

if (!utils::IsModeCLI())
{
	echo "This page can be run ONLY in command line mode";
	exit;
}

$bAppend = false;
$bReset = false;
$bForce = false;

foreach($argv as $sArg)
{
	if ($sArg == '--append')
	{
		$bAppend = true;
	}

	if ($sArg == '--reset')
	{
		$bReset = true;
	}
}

try
{
	$oTemplateFieldValuePopulates = new TemplateFieldValuePopulates($bAppend, $bReset);
	$oTemplateFieldValuePopulates->Populates();
}
catch (Exception $e)
{
	echo "\nError Encountered, maybe you should re-launch this tool!\n\n".$e->getMessage()."\n".$e->getTraceAsString();
}

class TemplateFieldValuePopulates
{
	const COMMIT_FREQUENCY = 100;

	/** @var array */
	private $aCacheAttDefPerClass;
	private $bAppend;
	private $bReset;

	public function __construct($bAppend, $bReset)
	{
		if (!$bAppend && !$bReset)
		{
			die('You must either "--append" or "--reset"'."\n");
		}

		if ($bAppend && $bReset)
		{
			die('You cannot both "--append" and "--reset"'."\n");
		}

		if ($bReset)
		{
			echo 'Please confirm that you want to "--reset". Keep in mind that it will truncate the "TemplateFieldValues" tables and re-create them. it may take a while!';
			echo "\n  Type 'yes' to continue: ";
			$handle = fopen ("php://stdin","r");
			$line = strtolower(trim(fgets($handle)));
			if($line != 'yes'){
				die("Aborting since '{$line}' != 'yes'\n");
			}
		}

		$this->bAppend = $bAppend;
		$this->bReset = $bReset;
	}

	public function Populates()
	{
		if ($this->bReset)
		{
			$this->Reset();
		}
		if ($this->bAppend)
		{
			$this->DeleteOldTemplateId();
		}

		$this->WriteValues();

		echo "Done!\n";
	}

	private function Reset()
	{
		$aResetClasses = MetaModel::EnumChildClasses('TemplateFieldValue', ENUM_CHILD_CLASSES_ALL);

		CMDBSource::Query('START TRANSACTION');

		foreach ($aResetClasses as $sClass)
		{
			$sOql = "SELECT `{$sClass}`";
			$oFilter = DBSearch::FromOQL($sOql);
			$sSQL = $oFilter->MakeDeleteQuery();

			CMDBSource::Query($sSQL);

			$iAffectedRows = CMDBSource::AffectedRows();
			echo "Reset: $iAffectedRows {$sClass} Deleted\n";
		}

		CMDBSource::Query('COMMIT');
	}

	private function DeleteOldTemplateId()
	{
		$sTemplateExtraDataTable = MetaModel::DBGetTable('TemplateExtraData');
		$sTemplateExtraDataCollObjClass = MetaModel::GetAttributeDef('TemplateExtraData', 'obj_class')->Get('sql');
		$sTemplateExtraDataCollObjKey = MetaModel::GetAttributeDef('TemplateExtraData', 'obj_key')->Get('sql');
		$sTemplateExtraDataCollTemplateId = MetaModel::GetAttributeDef('TemplateExtraData', 'template_id')->Get('sql');

		$sTemplateFieldValueTable = MetaModel::DBGetTable('TemplateFieldValue');
		$sTemplateFieldValueCollObjClass = MetaModel::GetAttributeDef('TemplateFieldValue', 'obj_class')->Get('sql');
		$sTemplateFieldValueCollObjKey = MetaModel::GetAttributeDef('TemplateFieldValue', 'obj_key')->Get('sql');
		$sTemplateFieldValueCollTemplateId = MetaModel::GetAttributeDef('TemplateFieldValue', 'template_id')->Get('sql');


		$aResetClasses = MetaModel::EnumChildClasses('TemplateFieldValue', ENUM_CHILD_CLASSES_EXCLUDETOP);

		CMDBSource::Query('START TRANSACTION');

		foreach ($aResetClasses as $sClass)
		{
			$sChildTable = MetaModel::DBGetTable($sClass);

			//Whe have no choice but to use SQL directly. This is a bad practice that should be avoided!!
			$sSQL = "DELETE  
	                 `Child`
				  FROM 
				    `{$sChildTable}` AS `Child` 
			      INNER JOIN 
					`{$sTemplateFieldValueTable}` AS `TemplateFieldValue` ON `TemplateFieldValue`.id = `Child`.id  
			      INNER JOIN 
					`{$sTemplateExtraDataTable}` AS `TemplateExtraData`
				  WHERE 
				                0															!= `TemplateExtraData`.`{$sTemplateExtraDataCollTemplateId}`
				            AND `TemplateFieldValue`.`{$sTemplateFieldValueCollTemplateId}` != `TemplateExtraData`.`{$sTemplateExtraDataCollTemplateId}`
				            AND `TemplateFieldValue`.`{$sTemplateFieldValueCollObjClass}`    = `TemplateExtraData`.`{$sTemplateExtraDataCollObjClass}` 
				            AND `TemplateFieldValue`.`{$sTemplateFieldValueCollObjKey}`      = `TemplateExtraData`.`{$sTemplateExtraDataCollObjKey}`
				  ";
			CMDBSource::Query($sSQL);

			$iAffectedRows = CMDBSource::AffectedRows();
			echo "Old templates: $iAffectedRows {$sClass} Deleted\n";
		}

		//Whe have no choice but to use SQL directly. This is a bad practice that should be avoided!!
		$sSQL = "DELETE  
	                 `TemplateFieldValue`
				  FROM 
				    `{$sTemplateFieldValueTable}` AS `TemplateFieldValue`
			      INNER JOIN 
					`{$sTemplateExtraDataTable}` AS `TemplateExtraData`
				  WHERE 
				                0                                                           != `TemplateExtraData`.`{$sTemplateExtraDataCollTemplateId}`
				            AND `TemplateFieldValue`.`{$sTemplateFieldValueCollTemplateId}` != `TemplateExtraData`.`{$sTemplateExtraDataCollTemplateId}`
				            AND `TemplateFieldValue`.`{$sTemplateFieldValueCollObjClass}`    = `TemplateExtraData`.`{$sTemplateExtraDataCollObjClass}` 
				            AND `TemplateFieldValue`.`{$sTemplateFieldValueCollObjKey}`      = `TemplateExtraData`.`{$sTemplateExtraDataCollObjKey}`
				  ";
		CMDBSource::Query($sSQL);

		$iAffectedRows = CMDBSource::AffectedRows();
		echo "Old templates: $iAffectedRows TemplateFieldValue Deleted\n";

		CMDBSource::Query('COMMIT');
	}


	private function WriteValues()
	{
		echo "WriteValues: running";

		$oSQLResult = $this->GetAddDataSqlResult();

		CMDBSource::Query('START TRANSACTION');
		$i = 0;
		$cptPerClass = array();
		while ($aTemplateExtraData = $oSQLResult->fetch_array(MYSQLI_ASSOC))
		{
			$sObjClass = $aTemplateExtraData['sObjClass'];
			$iObjKey = $aTemplateExtraData['iObjKey'];

			$oTemplateExtraData = TemplateExtraData::FindByObject($sObjClass, $iObjKey);

			$oHandler = $this->GetHandler($sObjClass);

			$aValues = $oHandler->CreateValuesFromTemplateExtraData($oTemplateExtraData);

			$oHandler->WriteTemplateFieldValues($sObjClass, $iObjKey, $aValues);

			if (++$i % self::COMMIT_FREQUENCY == 0)
			{
				CMDBSource::Query('COMMIT');
				CMDBSource::Query('START TRANSACTION');
				echo ".";
			}
			if (!isset($cptPerClass[$sObjClass]))
			{
				$cptPerClass[$sObjClass] = 0;
			}
			$cptPerClass[$sObjClass]++;
		}
		CMDBSource::Query('COMMIT');

		echo "\n";
		foreach ($cptPerClass as $sObjClass => $total)
		{
			echo "WriteValues: {$total} {$sObjClass} synchronized \n";
		}
	}

	/**
	 * @return \mysqli_result|null
	 * @throws \CoreException
	 * @throws \MySQLException
	 * @throws \MySQLHasGoneAwayException
	 */
	private function GetAddDataSqlResult()
	{
		$sSql = $this->GetAddDataSqlQuery();

		$oSQLResult = CMDBSource::Query($sSql);

		return $oSQLResult;
	}

	private function GetAddDataSqlQuery()
	{
		$sTemplateExtraDataTable = MetaModel::DBGetTable('TemplateExtraData');
		$sTemplateExtraDataCollObjClass = MetaModel::GetAttributeDef('TemplateExtraData', 'obj_class')->Get('sql');
		$sTemplateExtraDataCollObjKey = MetaModel::GetAttributeDef('TemplateExtraData', 'obj_key')->Get('sql');
		$sTemplateExtraDataCollTemplateId = MetaModel::GetAttributeDef('TemplateExtraData', 'template_id')->Get('sql');

		$sTemplateFieldValueTable = MetaModel::DBGetTable('TemplateFieldValue');
		$sTemplateFieldValueCollObjClass = MetaModel::GetAttributeDef('TemplateFieldValue', 'obj_class')->Get('sql');
		$sTemplateFieldValueCollObjKey = MetaModel::GetAttributeDef('TemplateFieldValue', 'obj_key')->Get('sql');
		$sTemplateFieldValueCollTemplateId = MetaModel::GetAttributeDef('TemplateFieldValue', 'template_id')->Get('sql');

		//Whe have no choice but to use SQL directly because there are no possible way to either uses left join with custom ON condition or to perform Not Exists (SELECT ...)
		// This is a bad practice that should be avoided!!
		$sql =  "SELECT 
       			`TemplateExtraData`.`{$sTemplateExtraDataCollObjClass}` AS sObjClass, `TemplateExtraData`.`{$sTemplateExtraDataCollObjKey}` AS iObjKey
			  FROM 
			    `{$sTemplateExtraDataTable}` AS `TemplateExtraData`
		         ";

		if ($this->bAppend)
		{
			$sql .=  " WHERE NOT EXISTS 
			     (
				SELECT 1 FROM 
				  `{$sTemplateFieldValueTable}` AS `TemplateFieldValue` 
				  WHERE 
			                `TemplateFieldValue`.`{$sTemplateFieldValueCollTemplateId}` = `TemplateExtraData`.`{$sTemplateExtraDataCollTemplateId}`
			            AND `TemplateFieldValue`.`{$sTemplateFieldValueCollObjClass}`=`TemplateExtraData`.`{$sTemplateExtraDataCollObjClass}` 
			            AND `TemplateFieldValue`.`{$sTemplateFieldValueCollObjKey}`=`TemplateExtraData`.`{$sTemplateExtraDataCollObjKey}`
				)
			  ";
		}

		return $sql;
	}

	/**
	 * @param $sObjClass
	 *
	 * @return \TemplateFieldsHandler
	 *
	 * @throws \CoreException
	 */
	private function GetHandler($sObjClass)
	{
		$this->InitCacheClassAttDef($sObjClass);

		return $this->aCacheAttDefPerClass[$sObjClass]['oHandler'];
	}

	/**
	 * @param $sObjClass
	 *
	 * @throws \CoreException
	 * @throws \Exception
	 */
	private function InitCacheClassAttDef($sObjClass)
	{
		if (isset($this->aCacheAttDefPerClass[$sObjClass]))
		{
			return;
		}

		$aClassAttDef = MetaModel::ListAttributeDefs($sObjClass);

		foreach ($aClassAttDef as $sAttCode => $oAttDef)
		{
			if (!$oAttDef instanceof AttributeCustomFields)
			{
				continue;
			}

			/** @var \TemplateFieldsHandler $oHandler */
			$oHandler = $oAttDef->GetHandler();

			if (!$oHandler instanceof TemplateFieldsHandler)
			{
				continue;
			}

			$this->aCacheAttDefPerClass[$sObjClass] = array(
				'sAttCode' => $sAttCode,
				'oAttDef' => $oAttDef,
				'oHandler' => $oHandler,
			);

			return;
		}

		throw new Exception("TemplateFieldsHandler not found for class {$sObjClass}.");
	}



}

