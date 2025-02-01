<?php
// This script and data application was generated by AppGini, https://bigprof.com/appgini
// Download AppGini for free from https://bigprof.com/appgini/download/

	include_once(__DIR__ . '/lib.php');
	@include_once(__DIR__ . '/hooks/units.php');
	include_once(__DIR__ . '/units_dml.php');

	// mm: can the current member access this page?
	$perm = getTablePermissions('units');
	if(!$perm['access']) {
		echo error_message($Translation['tableAccessDenied']);
		exit;
	}

	$x = new DataList;
	$x->TableName = 'units';

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = [
		"`units`.`id`" => "id",
		"IF(    CHAR_LENGTH(`properties1`.`property_name`), CONCAT_WS('',   `properties1`.`property_name`), '') /* Property */" => "property",
		"`units`.`unit_number`" => "unit_number",
		"`units`.`photo`" => "photo",
		"`units`.`status`" => "status",
		"`units`.`size`" => "size",
		"IF(    CHAR_LENGTH(`properties1`.`country`), CONCAT_WS('',   `properties1`.`country`), '') /* Country */" => "country",
		"IF(    CHAR_LENGTH(`properties1`.`street`), CONCAT_WS('',   `properties1`.`street`), '') /* Street */" => "street",
		"IF(    CHAR_LENGTH(`properties1`.`City`), CONCAT_WS('',   `properties1`.`City`), '') /* City */" => "city",
		"IF(    CHAR_LENGTH(`properties1`.`State`), CONCAT_WS('',   `properties1`.`State`), '') /* State */" => "state",
		"IF(    CHAR_LENGTH(`properties1`.`ZIP`), CONCAT_WS('',   `properties1`.`ZIP`), '') /* Postal code */" => "postal_code",
		"`units`.`rooms`" => "rooms",
		"`units`.`bathroom`" => "bathroom",
		"`units`.`features`" => "features",
		"FORMAT(`units`.`market_rent`, 0)" => "market_rent",
		"CONCAT('$', FORMAT(`units`.`rental_amount`, 2))" => "rental_amount",
		"CONCAT('$', FORMAT(`units`.`deposit_amount`, 2))" => "deposit_amount",
		"`units`.`description`" => "description",
	];
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = [
		1 => '`units`.`id`',
		2 => '`properties1`.`property_name`',
		3 => 3,
		4 => 4,
		5 => 5,
		6 => 6,
		7 => '`properties1`.`country`',
		8 => '`properties1`.`street`',
		9 => '`properties1`.`City`',
		10 => '`properties1`.`State`',
		11 => '`properties1`.`ZIP`',
		12 => 12,
		13 => '`units`.`bathroom`',
		14 => 14,
		15 => '`units`.`market_rent`',
		16 => '`units`.`rental_amount`',
		17 => '`units`.`deposit_amount`',
		18 => 18,
	];

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = [
		"`units`.`id`" => "id",
		"IF(    CHAR_LENGTH(`properties1`.`property_name`), CONCAT_WS('',   `properties1`.`property_name`), '') /* Property */" => "property",
		"`units`.`unit_number`" => "unit_number",
		"`units`.`photo`" => "photo",
		"`units`.`status`" => "status",
		"`units`.`size`" => "size",
		"IF(    CHAR_LENGTH(`properties1`.`country`), CONCAT_WS('',   `properties1`.`country`), '') /* Country */" => "country",
		"IF(    CHAR_LENGTH(`properties1`.`street`), CONCAT_WS('',   `properties1`.`street`), '') /* Street */" => "street",
		"IF(    CHAR_LENGTH(`properties1`.`City`), CONCAT_WS('',   `properties1`.`City`), '') /* City */" => "city",
		"IF(    CHAR_LENGTH(`properties1`.`State`), CONCAT_WS('',   `properties1`.`State`), '') /* State */" => "state",
		"IF(    CHAR_LENGTH(`properties1`.`ZIP`), CONCAT_WS('',   `properties1`.`ZIP`), '') /* Postal code */" => "postal_code",
		"`units`.`rooms`" => "rooms",
		"`units`.`bathroom`" => "bathroom",
		"`units`.`features`" => "features",
		"FORMAT(`units`.`market_rent`, 0)" => "market_rent",
		"CONCAT('$', FORMAT(`units`.`rental_amount`, 2))" => "rental_amount",
		"CONCAT('$', FORMAT(`units`.`deposit_amount`, 2))" => "deposit_amount",
		"`units`.`description`" => "description",
	];
	// Fields that can be filtered
	$x->QueryFieldsFilters = [
		"`units`.`id`" => "ID",
		"IF(    CHAR_LENGTH(`properties1`.`property_name`), CONCAT_WS('',   `properties1`.`property_name`), '') /* Property */" => "Property",
		"`units`.`unit_number`" => "Unit",
		"`units`.`status`" => "Status",
		"`units`.`size`" => "Area (sq. feet)",
		"IF(    CHAR_LENGTH(`properties1`.`country`), CONCAT_WS('',   `properties1`.`country`), '') /* Country */" => "Country",
		"IF(    CHAR_LENGTH(`properties1`.`street`), CONCAT_WS('',   `properties1`.`street`), '') /* Street */" => "Street",
		"IF(    CHAR_LENGTH(`properties1`.`City`), CONCAT_WS('',   `properties1`.`City`), '') /* City */" => "City",
		"IF(    CHAR_LENGTH(`properties1`.`State`), CONCAT_WS('',   `properties1`.`State`), '') /* State */" => "State",
		"IF(    CHAR_LENGTH(`properties1`.`ZIP`), CONCAT_WS('',   `properties1`.`ZIP`), '') /* Postal code */" => "Postal code",
		"`units`.`rooms`" => "Rooms",
		"`units`.`bathroom`" => "Bathroom",
		"`units`.`features`" => "Features",
		"`units`.`market_rent`" => "Market rent",
		"`units`.`rental_amount`" => "Rental amount",
		"`units`.`deposit_amount`" => "Deposit amount",
		"`units`.`description`" => "Description",
	];

	// Fields that can be quick searched
	$x->QueryFieldsQS = [
		"`units`.`id`" => "id",
		"IF(    CHAR_LENGTH(`properties1`.`property_name`), CONCAT_WS('',   `properties1`.`property_name`), '') /* Property */" => "property",
		"`units`.`unit_number`" => "unit_number",
		"`units`.`status`" => "status",
		"`units`.`size`" => "size",
		"IF(    CHAR_LENGTH(`properties1`.`country`), CONCAT_WS('',   `properties1`.`country`), '') /* Country */" => "country",
		"IF(    CHAR_LENGTH(`properties1`.`street`), CONCAT_WS('',   `properties1`.`street`), '') /* Street */" => "street",
		"IF(    CHAR_LENGTH(`properties1`.`City`), CONCAT_WS('',   `properties1`.`City`), '') /* City */" => "city",
		"IF(    CHAR_LENGTH(`properties1`.`State`), CONCAT_WS('',   `properties1`.`State`), '') /* State */" => "state",
		"IF(    CHAR_LENGTH(`properties1`.`ZIP`), CONCAT_WS('',   `properties1`.`ZIP`), '') /* Postal code */" => "postal_code",
		"`units`.`rooms`" => "rooms",
		"`units`.`bathroom`" => "bathroom",
		"`units`.`features`" => "features",
		"FORMAT(`units`.`market_rent`, 0)" => "market_rent",
		"CONCAT('$', FORMAT(`units`.`rental_amount`, 2))" => "rental_amount",
		"CONCAT('$', FORMAT(`units`.`deposit_amount`, 2))" => "deposit_amount",
		"`units`.`description`" => "description",
	];

	// Lookup fields that can be used as filterers
	$x->filterers = ['property' => 'Property', ];

	$x->QueryFrom = "`units` LEFT JOIN `properties` as properties1 ON `properties1`.`id`=`units`.`property` ";
	$x->QueryWhere = '';
	$x->QueryOrder = '';

	$x->AllowSelection = 1;
	$x->HideTableView = ($perm['view'] == 0 ? 1 : 0);
	$x->AllowDelete = $perm['delete'];
	$x->AllowMassDelete = true;
	$x->AllowInsert = $perm['insert'];
	$x->AllowUpdate = $perm['edit'];
	$x->SeparateDV = 1;
	$x->AllowDeleteOfParents = 0;
	$x->AllowFilters = 1;
	$x->AllowSavingFilters = 1;
	$x->AllowSorting = 1;
	$x->AllowNavigation = 1;
	$x->AllowPrinting = 1;
	$x->AllowPrintingDV = 1;
	$x->AllowCSV = 1;
	$x->AllowAdminShowSQL = 0;
	$x->RecordsPerPage = 10;
	$x->QuickSearch = 1;
	$x->QuickSearchText = $Translation['quick search'];
	$x->ScriptFileName = 'units_view.php';
	$x->RedirectAfterInsert = 'units_view.php';
	$x->TableTitle = 'Units';
	$x->TableIcon = 'resources/table_icons/change_password.png';
	$x->PrimaryKey = '`units`.`id`';
	$x->DefaultSortField = '2';
	$x->DefaultSortDirection = 'asc';

	$x->ColWidth = [90, 40, 60, 60, 60, 100, 55, 40, 45, 70, 150, 60, 100, 100, ];
	$x->ColCaption = ['Property', 'Unit', 'Cover photo', 'Status', 'Area (sq. feet)', 'Street', 'City', 'State', 'Rooms', 'Bathroom', 'Features', 'Rental amount', 'Applications/Leases', 'Unit photos', ];
	$x->ColFieldName = ['property', 'unit_number', 'photo', 'status', 'size', 'street', 'city', 'state', 'rooms', 'bathroom', 'features', 'rental_amount', '%applications_leases.unit%', '%unit_photos.unit%', ];
	$x->ColNumber  = [2, 3, 4, 5, 6, 8, 9, 10, 12, 13, 14, 16, -1, -1, ];

	// template paths below are based on the app main directory
	$x->Template = 'templates/units_templateTV.html';
	$x->SelectedTemplate = 'templates/units_templateTVS.html';
	$x->TemplateDV = 'templates/units_templateDV.html';
	$x->TemplateDVP = 'templates/units_templateDVP.html';

	$x->ShowTableHeader = 1;
	$x->TVClasses = "";
	$x->DVClasses = "";
	$x->HasCalculatedFields = false;
	$x->AllowConsoleLog = false;
	$x->AllowDVNavigation = true;

	// hook: units_init
	$render = true;
	if(function_exists('units_init')) {
		$args = [];
		$render = units_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// hook: units_header
	$headerCode = '';
	if(function_exists('units_header')) {
		$args = [];
		$headerCode = units_header($x->ContentType, getMemberInfo(), $args);
	}

	if(!$headerCode) {
		include_once(__DIR__ . '/header.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/header.php');
		echo str_replace('<%%HEADER%%>', ob_get_clean(), $headerCode);
	}

	echo $x->HTML;

	// hook: units_footer
	$footerCode = '';
	if(function_exists('units_footer')) {
		$args = [];
		$footerCode = units_footer($x->ContentType, getMemberInfo(), $args);
	}

	if(!$footerCode) {
		include_once(__DIR__ . '/footer.php'); 
	} else {
		ob_start();
		include_once(__DIR__ . '/footer.php');
		echo str_replace('<%%FOOTER%%>', ob_get_clean(), $footerCode);
	}
