<?
/**
 *
 */
class CIblockHandler
{
    const SECTION_DONT_SHOW = 9435;
    const CRM_IBLOCK_ID = 26;
    const CRM_SKU_IBLOCK_ID = 30;
    const PROPERTY_NEW_VALUE = 400;
    const ACTION_ENUM_ID = 401;
    const PROPERTY_HIT = 332;
    const TIME_NEW = "-5 days";
    const PROPERTY_LAST_SIZE_Y = 9453;

    function OnBeforeIBlockPropertyUpdateHandler(&$arFields) {
        self::DontUpdateRasmerSd($arFields);
    }

    function OnBeforeIBlockSectionUpdate(&$arFields) {
        self::DisabledUpdateSort($arFields);
    }
    
    function OnBeforeIBlockElementUpdate(&$arFields) {
        self::CheckDontShowSection($arFields);
    }

    function OnAfterIBlockElementUpdate(&$arFields) {
        self::AddAction($arFields);
        self::ChangeImportMaterial($arFields);
        self::NullPriceOrder($arFields);

    }

    function OnBeforeIBlockElementAdd(&$arFields){
        if($arFields["IBLOCK_ID"]==self::CRM_IBLOCK_ID){
            $arFields["PROPERTY_VALUES"][self::PROPERTY_HIT][]["VALUE"]=self::PROPERTY_NEW_VALUE;
        }

    }

    function CheckDontShowSection(&$arFields){
        if(in_array(self::SECTION_DONT_SHOW, $arFields["IBLOCK_SECTION"])){
            $arFields["IBLOCK_SECTION"]=[self::SECTION_DONT_SHOW];
        }
    }

    function ClearNew(){
        CModule::IncludeModule("iblock");
        $arSelect = Array("ID", "IBLOCK_ID","PROPERTY_HIT");
        $arFilter = Array(
            "IBLOCK_ID"=>self::CRM_IBLOCK_ID,
            "<DATE_CREATE"=>date("d.m.Y H:i:s",strtotime(self::TIME_NEW)),
            "PROPERTY_HIT"=>self::PROPERTY_NEW_VALUE,
        );

        $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
        while($ob = $res->GetNextElement())
        {
            $arFields = $ob->GetFields();
            $arProps = $ob->GetProperties();

        	$values =[];
        	foreach($arProps["HIT"]["VALUE_ENUM_ID"] as $value){
        		if($value!=self::PROPERTY_NEW_VALUE)
        			$values[$value] = $value;
        	}

            if(empty($values)){
                $values = false;
            }

        	CIBlockElement::SetPropertyValuesEx($arFields["ID"], self::CRM_IBLOCK_ID, array("HIT" => $values));
        }

        return "CIblockHandler::ClearNew();";
    }

    function ClearLastSize(){
        CModule::IncludeModule("iblock");
        $arSelect = Array("ID", "IBLOCK_ID","PROPERTY_LAST_SIZE", "CATALOG_QUANTITY");
        $arFilter = Array("IBLOCK_ID"=>self::CRM_SKU_IBLOCK_ID, "<CATALOG_QUANTITY"=>1,"PROPERTY_LAST_SIZE"=> self::PROPERTY_LAST_SIZE_Y, "ACTIVE"=>"Y");

        $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
        $el = new CIBlockElement;
        while($arFields = $res->GetNext())
        {
            $arLoadProductArray = Array(
                "ACTIVE"         => "N",            // активен
            );

            $el->Update($arFields["ID"], $arLoadProductArray);
        }

        return "CIblockHandler::ClearLastSize();";
    }

    function AddAction(&$arFields){
        global $USER;
        if($arFields["IBLOCK_ID"]==self::CRM_IBLOCK_ID){
            $arUserGroups = [2];
            $arDiscounts = CCatalogDiscount::GetDiscountByProduct( $arFields["ID"], $arUserGroups, "N", 2, "s2" );
            $rsElement = CIBlockElement::GetList(
                $arOrder  = array('SORT' => 'ASC'),
                $arFilter = array(
                    'ID' => $arFields["ID"],
                    "IBLOCK_ID"=>self::CRM_IBLOCK_ID
                ),
                false,
                false,
                $arSelectFields = array('ID','NAME','IBLOCK_ID','CODE','PROPERTY_HIT')
            );
            while($ob = $rsElement->GetNextElement())
            {
                $arFields = $ob->GetFields();
                $arProps = $ob->GetProperties();

                $values =[];
                foreach($arProps["HIT"]["VALUE_ENUM_ID"] as $value){
                    $values[$value] = $value;
                }

                if(empty($values)){
                    $values = false;
                }


            }

            if(!empty($arDiscounts)){
                $values[self::ACTION_ENUM_ID]=self::ACTION_ENUM_ID;
            }else{
                unset($values[self::ACTION_ENUM_ID]);
            }

            CIBlockElement::SetPropertyValuesEx($arFields["ID"], self::CRM_IBLOCK_ID, array("HIT" => $values));
        }
    }

    function ClearAction($page){
        CModule::IncludeModule("iblock");
        $arSelect = Array("ID", "IBLOCK_ID","PROPERTY_HIT");
        $arFilter = Array("IBLOCK_ID"=>self::CRM_IBLOCK_ID,"PROPERTY_HIT"=>self::ACTION_ENUM_ID, "ACTIVE"=>"Y");

        $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50, "iNumPage"=>$page), $arSelect);
       
        if($page>$res->NavPageCount){
            return "CIblockHandler::ClearAction(1);";
        }

        while($ob = $res->GetNextElement())
        {
            $arFields = $ob->GetFields();
            $arProps = $ob->GetProperties();

            $values =[];
            foreach($arProps["HIT"]["VALUE_ENUM_ID"] as $value){
                $values[$value] = $value;
            }

            if(empty($values)){
                $values = false;
            }

            $arUserGroups = [2];
            $arDiscounts = CCatalogDiscount::GetDiscountByProduct( $arFields["ID"], $arUserGroups, "N", 2, "s2" );

            if(!empty($arDiscounts)){
                $values[self::ACTION_ENUM_ID]=self::ACTION_ENUM_ID;
            }else{
                unset($values[self::ACTION_ENUM_ID]);
            }
            if(empty($values)){
                $values = false;
            }

        	CIBlockElement::SetPropertyValuesEx($arFields["ID"], self::CRM_IBLOCK_ID, array("HIT" => $values));
        }
        $page++;
        return "CIblockHandler::ClearAction($page);";
    }

    private function ChangeImportMaterial(&$arFields) {
        if ($arFields["IBLOCK_ID"] == "26") {
            CModule::IncludeModule("iblock");
            $obCurrentUpdateElement = CIBlockElement::GetList([], ["ID"=>$arFields["ID"], "IBLOCK_ID"=>$arFields["IBLOCK_ID"]],false,false, ["ID", "IBLOCK_ID", "PROPERTY_MATERIAL",
                "PROPERTY_MATERIAL2"]);
            while ($rsElement = $obCurrentUpdateElement->fetch()) {

                if($rsElement["PROPERTY_MATERIAL_VALUE"]) {
                    foreach ($rsElement["PROPERTY_MATERIAL_VALUE"] as $enum_id => $value) {
                        $arCurrentMaterial[$enum_id] = $enum_id;
                    }
                }

                if ($rsElement["PROPERTY_MATERIAL2_VALUE"]) {
                    $material2[$rsElement["PROPERTY_MATERIAL2_VALUE"]] = $rsElement["PROPERTY_MATERIAL2_VALUE"];
                }
            }

            if (!empty($material2)) {
                $property_enums = CIBlockPropertyEnum::GetList(Array("DEF" => "DESC", "SORT" => "ASC"), Array("IBLOCK_ID" => 26, "CODE" => "MATERIAL", "VALUE" => $material2));
                while ($enum = $property_enums->GetNext()) {
                    $arCurrentMaterial[$enum["ID"]] = $enum["ID"];
                }
            }
            global $USER;
            if(empty($arCurrentMaterial)){
                $arCurrentMaterial = false;
            }
            CIBlockElement::SetPropertyValuesEx($arFields["ID"], $arFields["IBLOCK_ID"], array("MATERIAL" => array_values($arCurrentMaterial)));


        }
    }
    
    private function DontUpdateRasmerSd(&$arFields) {
        if ($arFields["CODE"] == "RAZMER_SD") {
            unset($arFields["NAME"]);
        }
    }
    
    public function DisabledUpdateSort(&$arFields) {
        if($arFields["IBLOCK_ID"]==self::CRM_IBLOCK_ID) {
            unset($arFields["SORT"]);
        }
        
    }

    public function NullPriceOrder(&$arFields)
    {
        if($arFields["IBLOCK_ID"]==self::CRM_SKU_IBLOCK_ID && $arFields["ACTIVE"]=="N") {
            $order = new WsOrder();
            $order->NullProductPriceByProductID($arFields["ID"]);
        }

        $arSection = $arFields["IBLOCK_SECTION"];
        if($arFields["IBLOCK_SECTION"]!=""){
            $resSection = CIBlockSection::GetNavChain(false, $arFields["IBLOCK_SECTION"][0],["ID","NAME"]);
            while ($arResSection = $resSection->GetNext()){

                $arSection[]=$arResSection["ID"];
            }
        }
        $arSection = array_unique($arSection);
        if(in_array(self::SECTION_DONT_SHOW, $arSection)) {
            $res = CIBlockElement::GetList(
                [],
                [
                    "IBLOCK_ID"=>self::CRM_SKU_IBLOCK_ID, 
                    "PROPERTY_CML2_LINK"=>$arFields["ID"],
                ],
                false,
                false,
                [
                    "ID"
                ]
            );
            $order = new WsOrder();
            while ($arRes =$res->GetNext()){
                $order->NullProductPriceByProductID($arRes["ID"]);
            }
        }
    }

    public function CheckActive(&$arFields){
        if($arFields["IBLOCK_ID"]==self::CRM_SKU_IBLOCK_ID && $arFields["ID"]>0    ){
            $arRes = CIBlockElement::GetList(
                [],
                [
                    "ID"=>$arFields["ID"],
                    "IBLOCK_ID"=>self::CRM_SKU_IBLOCK_ID,
                ],
                false,
                false,
                [
                    "ID",
                    "IBLOCK_ID",
                    "PROPERTY_CML2_LINK.ID",
                ]
            )->GetNext();

            if($arRes["PROPERTY_CML2_LINK_ID"]>0) {
                $res = CIBlockElement::GetList(
                    [],
                    [
                        "PROPERTY_CML2_LINK" => $arRes["PROPERTY_CML2_LINK_ID"],
                        "IBLOCK_ID"          => self::CRM_SKU_IBLOCK_ID,
                    ],
                    false,
                    false,
                    [
                        "ID",
                        "IBLOCK_ID",
                        "ACTIVE",
                    ]
                );
                $arLoadProductArray["ACTIVE"]="N";
                while($arResAll = $res->GetNext()){
                    if($arResAll["ACTIVE"]=="Y"){
                        $arLoadProductArray["ACTIVE"]="Y";
                        break;
                    }
                }

                $el = new CIBlockElement;
                $res = $el->Update($arRes["PROPERTY_CML2_LINK_ID"], $arLoadProductArray);
            }
        }
    }
    
    
    public function AbotrPropertyValue(&$arFields){
        
        if($arFields["IBLOCK_ID"]==self::CRM_IBLOCK_ID){
            $arRes = CIBlockElement::GetList(
                [],
                [
                    "ID"=> $arFields["ID"]
                ],
                false,
                false,
                [
                    "ID",
                    "IBLOCK_ID",
                    "PROPERTY_HIT",
                    "PROPERTY_POL",
                    "PROPERTY_317",
                    "PROPERTY_SEZON",
                ]
            )->GetNext();
    
            $arProp["HIT"] = [];
            if(!empty($arRes["PROPERTY_HIT_VALUE"])){
                $property_enums = CIBlockPropertyEnum::GetList(
                        Array("DEF"=>"DESC", "SORT"=>"ASC"),
                        Array(
                                "IBLOCK_ID"=>30,
                                "CODE"=>"HIT",
                                "VALUE"=>$arRes["PROPERTY_HIT_VALUE"]
                        )
                );
                while($enum_fields = $property_enums->GetNext())
                {
                    $arProp["HIT"][]=$enum_fields["ID"];
                }
            }
            $arProp["320"] = [];
            if(!empty($arRes["PROPERTY_317_VALUE"])){
                
                $property_enums = CIBlockPropertyEnum::GetList(
                        Array("DEF"=>"DESC", "SORT"=>"ASC"),
                        Array(
                                "IBLOCK_ID"=>30,
                                "PROPERTY_ID"=>320,
                                "VALUE"=>$arRes["PROPERTY_317_VALUE"]
                        )
                );
                while($enum_fields = $property_enums->GetNext())
                {
                    $arProp["320"][]=$enum_fields["ID"];
                }
            }
            
            $arProp["POL"] = [];
            if(!empty($arRes["PROPERTY_POL_VALUE"])){
                
                $property_enums = CIBlockPropertyEnum::GetList(
                        Array("DEF"=>"DESC", "SORT"=>"ASC"),
                        Array(
                                "IBLOCK_ID"=>30,
                                "CODE"=>"POL",
                                "VALUE"=>$arRes["PROPERTY_POL_VALUE"]
                        )
                );
                while($enum_fields = $property_enums->GetNext())
                {
                    $arProp["POL"][]=$enum_fields["ID"];
                }
            }
          
            $arProp["SEZON"] = [];
            if($arRes["PROPERTY_SEZON_VALUE"]){
                $property_enums = CIBlockPropertyEnum::GetList(
                        Array("DEF"=>"DESC", "SORT"=>"ASC"),
                        Array(
                                "IBLOCK_ID"=>30,
                                "CODE"=>"SEZON",
                                "VALUE"=>$arRes["PROPERTY_SEZON_VALUE"]
                        )
                );
                while($enum_fields = $property_enums->GetNext())
                {
                    $arProp["SEZON"]=$enum_fields["ID"];
                }
            }
           
        }
        
        $res = CIBlockElement::GetList(
                [],
                [
                        "PROPERTY_140"=>$arFields["ID"],
                ],
                false,
                false,
                [
                        "ID",
                        "IBLOCK_ID",
                        "PROPERTY_140",
                ]
        );
        
        while ($arRes = $res->GetNext()){
            CIBlockElement::SetPropertyValuesEx($arRes["ID"], 30,$arProp);
        }
    }
    
}//close class
