<?php
// Copyright (C) 2017 Combodo SARL
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

/**
 * Class RequestTemplateFieldsHandler
 * Build the customfields, depending on the service of the user request
 *
 */
class RequestTemplateFieldsHandler extends TemplateFieldsHandler
{
	private static ?array $aPrerequisiteAttributes = null;

	/**
	 * @param null $sClass
	 * @return array
	 */
	public static function GetPrerequisiteAttributes($sClass = null)
	{
		if (!is_null(self::$aPrerequisiteAttributes)) {
			return self::$aPrerequisiteAttributes;
		}
		// Find potential query parameters (in the form :this->attcode)
		$oSearch = DBSearch::FromOQL("SELECT TemplateField AS f JOIN Template AS t ON f.template_id=t.id WHERE t.finalclass = 'RequestTemplate' AND f.input_type IN ('drop_down_list', 'radio_buttons') AND f.values LIKE 'SELECT %:this->%'");
		$oSet = new DBObjectSet($oSearch);
		$aParameters = array(); // Array of <parameter> => null
		while ($oField = $oSet->Fetch()) {
			$sValues = $oField->Get('values');
			if (strlen($sValues) > 0) {
				try {
					$oSearch = DBSearch::FromOQL($sValues);
					// This is a valid OQL: find any parameter in the form of :this->attcode
					$aParameters = array_merge($aParameters, $oSearch->GetQueryParams());
				} catch (Exception $e)
				{
					// Not an OQL: skip silently (should be a CSV list of values)
				}
			}
		}
		$aRet = array('servicesubcategory_id');
		foreach ($aParameters as $sParameter => $void)
		{
			static $sPrefix = 'this->';
			if (strpos($sParameter, $sPrefix) === 0)
			{
				$sAttCode = substr($sParameter, strlen($sPrefix));
				$aRet[] = $sAttCode;
			}
		}
		self::$aPrerequisiteAttributes = $aRet;
		return $aRet;
	}

	/**
	 * @return DBObjectSet of Templates
	 */
	protected function FindTemplates(DBObject $oHostObject) {
		$oSearch = DBSearch::FromOQL('SELECT RequestTemplate WHERE servicesubcategory_id = :servicesubcategory_id');
		$oSet = new DBObjectSet($oSearch, array(), array('servicesubcategory_id' => $oHostObject->Get('servicesubcategory_id')));

		return $oSet;
	}

	/**
	 * Write template data to the case log (legacy behavior)
	 *
	 * @param \DBObject $oHostObject
	 * @param array $aValues
	 * @param \TemplateExtraData|null $oExtraData
	 *
	 * @throws \ConfigException
	 * @throws \CoreException
	 */
	public function BeyondWriteValues(DBObject $oHostObject, $aValues, TemplateExtraData $oExtraData = null) {
		if (false === RequestTemplateUpdateHelper::IsObjectInUpdate($oHostObject)) {
			$sCopyToLog = utils::GetConfig()->GetModuleSetting('itop-request-template', 'copy_to_log', 'public_log');
			if (($sCopyToLog != '') && MetaModel::IsValidAttCode(get_class($oHostObject), $sCopyToLog)) {
				if ($oExtraData) {
					$aTemplateData = json_decode($aValues['template_data'], true);
					foreach ($aTemplateData['hidden_fields'] as $sFieldCode => $foo) {
						// Do not show this value in the case log
						unset($aValues['user_data'][$sFieldCode]);
					}

					// N°2922 : fields with display_condition not OK aren't part of the caselog content !
					$aDisplayConditions = Template::GetDisplayConditions($aTemplateData);
					$aFieldsWithDisplayConditionNotOk = array();
					foreach ($aValues['user_data'] as $sFieldCode => $value) {
						$bIsDisplayConditionOk = Template::IsFieldDisplayConditionOk($sFieldCode, $aDisplayConditions,
							$aValues['user_data']);
						if ($bIsDisplayConditionOk) {
							continue;
						}
						// cannot unset $aValues here cause Template::IsFieldDisplayConditionOk needs all the values !
						$aFieldsWithDisplayConditionNotOk[] = $sFieldCode;
					}
					foreach ($aFieldsWithDisplayConditionNotOk as $sFieldCode) {
						unset($aValues['user_data'][$sFieldCode]);
					}

					$aLines = array();
					foreach ($aValues['user_data'] as $sFieldCode => $value) {
						$aFieldData = $aTemplateData['fields'][$sFieldCode];
						$sLabel = $aFieldData['label'];
						$sDisplayValue = RequestTemplate::MakeHTMLValue($aFieldData, $value);
						if (array_key_exists($sFieldCode, $aValues['user_data_objclass'])) {
							$sDisplayValue = $aValues['user_data_objname'][$sFieldCode];
						}
						$aLines[] = '<b>'.$sLabel.'</b> : '.$sDisplayValue;
					}
					$sValuesAsText = '<ul><li>'.implode('</li><li>', $aLines).'</li></ul>';
					$sTemplateDesc = '<p>'.MetaModel::GetAttributeDef(get_class($oHostObject),
							$this->sAttCode)->GetLabel().' : '.$aTemplateData['label'].'</p>'.$sValuesAsText;

					// Mark the object for update because we are currently in DBUpdate and the case log might already have been written, and DBUpdate cannot be called from within this function.
					RequestTemplateUpdateHelper::ShelfTemplateForCaseLog($oHostObject, $sTemplateDesc);
				}
			}
		}
	}
}

class RequestTemplatePlugIn implements iApplicationObjectExtension, iApplicationUIExtension
{
	public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
	{
		if ($oObject instanceof RequestTemplate && $bEditMode === false) {
			if (version_compare(ITOP_DESIGN_LATEST_VERSION, "3.2", ">=")) {
				$oPage->LinkScriptFromAppRoot("js/forms-json-utils.js");
			} else {
				$oPage->add_linked_script("../js/forms-json-utils.js");
			}
		}
	}
	
	public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
	{
	}
	
	public function OnFormSubmit($oObject, $sFormPrefix = '')
	{
	}
	
	public function OnFormCancel($sTempId)
	{
	}
	
	public function EnumUsedAttributes($oObject)
	{

	}
	
	public function GetIcon($oObject)
	{
		return '';
	}
	
	public function GetHilightClass($oObject)
	{
		// Possible return values are:
		// HILIGHT_CLASS_CRITICAL, HILIGHT_CLASS_WARNING, HILIGHT_CLASS_OK, HILIGHT_CLASS_NONE
		return HILIGHT_CLASS_NONE;
	}
	
	public function EnumAllowedActions(DBObjectSet $oSet)
	{
		// No action
		return array();
	}

	public function OnIsModified($oObject)
	{
		return false;
	}

	public function OnCheckToWrite($oObject)
	{
		return array();
	}

	public function OnCheckToDelete($oObject)
	{
		return array();
	}

	public function OnDBUpdate($oObject, $oChange = null)
	{
		if (RequestTemplateUpdateHelper::HasShelvedTemplateForCaseLog($oObject))
		{
			$sTemplateDesc = RequestTemplateUpdateHelper::UnshelfTemplateForCaseLog($oObject);
			$sCopyToLog = utils::GetConfig()->GetModuleSetting('itop-request-template', 'copy_to_log', 'public_log');
			$oObject->Set($sCopyToLog, $sTemplateDesc);
			RequestTemplateUpdateHelper::MarkObjectAsInUpdate($oObject);
			$oObject->DBUpdate();
		}
	}

	public function OnDBInsert($oObject, $oChange = null)
	{
		if (RequestTemplateUpdateHelper::HasShelvedTemplateForCaseLog($oObject))
		{
			$sTemplateDesc = RequestTemplateUpdateHelper::UnshelfTemplateForCaseLog($oObject);
			$sCopyToLog = utils::GetConfig()->GetModuleSetting('itop-request-template', 'copy_to_log', 'public_log');
			$oObject->Set($sCopyToLog, $sTemplateDesc);
			RequestTemplateUpdateHelper::MarkObjectAsInUpdate($oObject);
			$oObject->DBUpdate();
		}
	}

	public function OnDBDelete($oObject, $oChange = null)
	{
	}
}

/**
 * Class RequestTemplateHelper
 *
 * Helper class introduced for compatibility with PHP 8.2
 *
 * @author Guillaume Lajarige <guillaume.lajarige@combodo.com>
 * @since 2.2.4
 */
class RequestTemplateUpdateHelper
{
	protected static $aRequestTemplateInUpdate = [];
	protected static $aRequestTemplateToCaseLog = [];

	/**
	 * Mark $oObject as being updated
	 *
	 * @param \DBObject $oObject
	 *
	 * @return void
	 */
	public static function MarkObjectAsInUpdate($oObject)
	{
		static::$aRequestTemplateInUpdate[static::MakeIdentifierFromObject($oObject)] = true;
	}

	/**
	 * @param DBObject $oObject
	 *
	 * @return bool True if $oObject is already flagged as being updated
	 */
	public static function IsObjectInUpdate($oObject)
	{
		return isset(static::$aRequestTemplateInUpdate[static::MakeIdentifierFromObject($oObject)]);
	}

	/**
	 * @param \DBObject $oObject
	 * @param string $sTemplateDesc Template description
	 *
	 * @return void
	 */
	public static function ShelfTemplateForCaseLog($oObject, $sTemplateDesc)
	{
		static::$aRequestTemplateToCaseLog[static::MakeIdentifierFromObject($oObject)] = $sTemplateDesc;
	}

	/**
	 * @param \DBObject $oObject
	 *
	 * @return string Template description
	 */
	public static function UnshelfTemplateForCaseLog($oObject)
	{
		$sObjectIdentifier = static::MakeIdentifierFromObject($oObject);
		$sTemplateDesc = static::$aRequestTemplateToCaseLog[$sObjectIdentifier];
		unset(static::$aRequestTemplateToCaseLog[$sObjectIdentifier]);

		return $sTemplateDesc;
	}

	/**
	 * @param \DBObject $oObject
	 *
	 * @return bool True if there is a template description shelved for $oObject
	 */
	public static function HasShelvedTemplateForCaseLog($oObject)
	{
		return isset(static::$aRequestTemplateToCaseLog[static::MakeIdentifierFromObject($oObject)]);
	}

	/**
	 * @param \DBObject $oObject
	 *
	 * @return string Unique identifier of the $oObject accros the DM
	 */
	protected static function MakeIdentifierFromObject($oObject)
	{
		return get_class($oObject) . '::' . $oObject->GetKey();
	}
}
