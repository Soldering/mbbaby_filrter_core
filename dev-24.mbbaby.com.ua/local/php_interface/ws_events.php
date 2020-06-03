<?

    /*catalog*/
    AddEventHandler("catalog", "OnProductUpdate", Array("CCatalogHandler", "OnProductUpdate"));
    /*iblock*/
    AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", Array("CIblockHandler", "OnBeforeIBlockElementUpdate"));
    AddEventHandler("iblock", "OnBeforeIBlockElementAdd", Array("CIblockHandler", "OnBeforeIBlockElementAdd"));
    AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("CIblockHandler", "OnAfterIBlockElementUpdate"));
    AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("CIblockHandler", "NullPriceOrder"));
    AddEventHandler("iblock", "OnBeforeIBlockSectionUpdate", Array("CIblockHandler", "OnBeforeIBlockSectionUpdateFixIsOnline"));
    AddEventHandler("iblock", "OnBeforeIBlockPropertyUpdate", Array("CIblockHandler", "OnBeforeIBlockPropertyUpdateHandler"));
    AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("CIblockHandler", "CheckActive"));
    AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("CIblockHandler", "AbotrPropertyValue"));
    /*main*/
    AddEventHandler('main', 'OnBeforeProlog', array('CMainHandler','OnBeforeProlog'));
    AddEventHandler("main", "OnBeforeEventAdd", array("CMainHandler", "OnBeforeEventAddHandler"));
    AddEventHandler("main", "OnBuildGlobalMenu", array("CMainHandler", "SettiningSKU"));
    AddEventHandler("main", "OnEpilog", array("CMainHandler","SaveFileCache"));
    \Bitrix\Main\EventManager::getInstance()->addEventHandler('main', "onGetPublicView", array("CMainHandler", "onGetPublicView"));

    //\Bitrix\Main\EventManager::getInstance()->addEventHandler('main',"onGetPublicEdit", array("CMainHandler", "onGetPublicEdit") );
    /*****************************************************************************************************************/

    /*crm*/
    AddEventHandler("crm", "OnBeforeCrmDealAdd", Array("CCrmHandler", "OnBeforeCrmDealAdd"));
    AddEventHandler("crm", "OnAfterExternalCrmDealAdd", array("CCrmHandler","OnAfterExternalCrmDealAdd"));
    AddEventHandler("crm", "OnAfterCrmDealProductRowsSave", array("CCrmHandler","OnAfterCrmDealProductRowsSave"));
    AddEventHandler("crm", "OnAfterCrmContactUpdate", array("CCrmHandler","OnAfterCrmContactUpdate"));
    AddEventHandler("crm", "OnAfterCrmDealUpdate", array("CCrmHandler","OnAfterCrmDealUpdate"));
    AddEventHandler("crm", "OnBeforeCrmDealUpdate", array("CCrmHandler","OnBeforeCrmDealUpdate"));
    AddEventHandler("crm", "OnAfterCrmDealAdd", array("CCrmHandler","CheckPaymentOrder"));
    
    

/*****************************************************************************************************************/
