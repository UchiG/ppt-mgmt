<?php

// Data functions (insert, update, delete, form) for table property_photos

// This script and data application was generated by AppGini, https://bigprof.com/appgini
// Download AppGini for free from https://bigprof.com/appgini/download/

function property_photos_insert(&$error_message = '') {
	global $Translation;

	// mm: can member insert record?
	$arrPerm = getTablePermissions('property_photos');
	if(!$arrPerm['insert']) {
		$error_message = $Translation['no insert permission'];
		return false;
	}

	$data = [
		'property' => Request::lookup('property', ''),
		'photo' => Request::fileUpload('photo', [
			'maxSize' => 2048000,
			'types' => 'jpg|jpeg|gif|png|webp',
			'noRename' => false,
			'dir' => '',
			'success' => function($name, $selected_id) {
				Thumbnail::create($name, getThumbnailSpecs('property_photos', 'photo', 'tv'));
				Thumbnail::create($name, getThumbnailSpecs('property_photos', 'photo', 'dv'));
			},
			'failure' => function($selected_id, $fileRemoved) {
				if(!strlen(Request::val('SelectedID'))) return '';

				/* for empty upload fields, when saving a copy of an existing record, copy the original upload field */
				return existing_value('property_photos', 'photo', Request::val('SelectedID'));
			},
		]),
		'description' => Request::val('description', ''),
	];

	// record owner is current user
	$recordOwner = getLoggedMemberID();

	$recID = tableInsert('property_photos', $data, $recordOwner, $error_message);

	// if this record is a copy of another record, copy children if applicable
	if(strlen(Request::val('SelectedID')) && $recID !== false)
		property_photos_copy_children($recID, Request::val('SelectedID'));

	return $recID;
}

function property_photos_copy_children($destination_id, $source_id) {
	global $Translation;
	$requests = []; // array of curl handlers for launching insert requests
	$eo = ['silentErrors' => true];
	$safe_sid = makeSafe($source_id);
	$currentUsername = getLoggedMemberID();
	$errorMessage = '';

	// launch requests, asynchronously
	curl_batch($requests);
}

function property_photos_delete($selected_id, $AllowDeleteOfParents = false, $skipChecks = false) {
	// insure referential integrity ...
	global $Translation;
	$selected_id = makeSafe($selected_id);

	// mm: can member delete record?
	if(!check_record_permission('property_photos', $selected_id, 'delete')) {
		return $Translation['You don\'t have enough permissions to delete this record'];
	}

	// hook: property_photos_before_delete
	if(function_exists('property_photos_before_delete')) {
		$args = [];
		if(!property_photos_before_delete($selected_id, $skipChecks, getMemberInfo(), $args))
			return $Translation['Couldn\'t delete this record'] . (
				!empty($args['error_message']) ?
					'<div class="text-bold">' . strip_tags($args['error_message']) . '</div>'
					: '' 
			);
	}

	sql("DELETE FROM `property_photos` WHERE `id`='{$selected_id}'", $eo);

	// hook: property_photos_after_delete
	if(function_exists('property_photos_after_delete')) {
		$args = [];
		property_photos_after_delete($selected_id, getMemberInfo(), $args);
	}

	// mm: delete ownership data
	sql("DELETE FROM `membership_userrecords` WHERE `tableName`='property_photos' AND `pkValue`='{$selected_id}'", $eo);
}

function property_photos_update(&$selected_id, &$error_message = '') {
	global $Translation;

	// mm: can member edit record?
	if(!check_record_permission('property_photos', $selected_id, 'edit')) return false;

	$data = [
		'property' => Request::lookup('property', ''),
		'photo' => Request::fileUpload('photo', [
			'maxSize' => 2048000,
			'types' => 'jpg|jpeg|gif|png|webp',
			'noRename' => false,
			'dir' => '',
			'id' => $selected_id,
			'success' => function($name, $selected_id) {
				Thumbnail::create($name, getThumbnailSpecs('property_photos', 'photo', 'tv'));
				Thumbnail::create($name, getThumbnailSpecs('property_photos', 'photo', 'dv'));
			},
			'removeOnRequest' => true,
			'remove' => function($selected_id) {
				// do nothing: preserve removed files on server.
			},
			'failure' => function($selected_id, $fileRemoved) {
				if($fileRemoved) return '';
				return existing_value('property_photos', 'photo', $selected_id);
			},
		]),
		'description' => Request::val('description', ''),
	];

	// get existing values
	$old_data = getRecord('property_photos', $selected_id);
	if(is_array($old_data)) {
		$old_data = array_map('makeSafe', $old_data);
		$old_data['selectedID'] = makeSafe($selected_id);
	}

	$data['selectedID'] = makeSafe($selected_id);

	// hook: property_photos_before_update
	if(function_exists('property_photos_before_update')) {
		$args = ['old_data' => $old_data];
		if(!property_photos_before_update($data, getMemberInfo(), $args)) {
			if(isset($args['error_message'])) $error_message = $args['error_message'];
			return false;
		}
	}

	$set = $data; unset($set['selectedID']);
	foreach ($set as $field => $value) {
		$set[$field] = ($value !== '' && $value !== NULL) ? $value : NULL;
	}

	if(!update(
		'property_photos', 
		backtick_keys_once($set), 
		['`id`' => $selected_id], 
		$error_message
	)) {
		echo $error_message;
		echo '<a href="property_photos_view.php?SelectedID=' . urlencode($selected_id) . "\">{$Translation['< back']}</a>";
		exit;
	}


	update_calc_fields('property_photos', $data['selectedID'], calculated_fields()['property_photos']);

	// hook: property_photos_after_update
	if(function_exists('property_photos_after_update')) {
		if($row = getRecord('property_photos', $data['selectedID'])) $data = array_map('makeSafe', $row);

		$data['selectedID'] = $data['id'];
		$args = ['old_data' => $old_data];
		if(!property_photos_after_update($data, getMemberInfo(), $args)) return;
	}

	// mm: update record update timestamp
	set_record_owner('property_photos', $selected_id);
}

function property_photos_form($selectedId = '', $allowUpdate = true, $allowInsert = true, $allowDelete = true, $separateDV = true, $templateDV = '', $templateDVP = '') {
	// function to return an editable form for a table records
	// and fill it with data of record whose ID is $selectedId. If $selectedId
	// is empty, an empty form is shown, with only an 'Add New'
	// button displayed.

	global $Translation;
	$eo = ['silentErrors' => true];
	$noUploads = $row = $urow = $jsReadOnly = $jsEditable = $lookups = null;
	$noSaveAsCopy = false;
	$hasSelectedId = strlen($selectedId) > 0;

	// mm: get table permissions
	$arrPerm = getTablePermissions('property_photos');
	$allowInsert = ($arrPerm['insert'] ? true : false);
	$allowUpdate = $hasSelectedId && check_record_permission('property_photos', $selectedId, 'edit');
	$allowDelete = $hasSelectedId && check_record_permission('property_photos', $selectedId, 'delete');

	if(!$allowInsert && !$hasSelectedId)
		// no insert permission and no record selected
		// so show access denied error -- except if TVDV: just hide DV
		return $separateDV ? $Translation['tableAccessDenied'] : '';

	if($hasSelectedId && !check_record_permission('property_photos', $selectedId, 'view'))
		return $Translation['tableAccessDenied'];

	// print preview?
	$dvprint = $hasSelectedId && Request::val('dvprint_x') != '';

	$showSaveNew = !$dvprint && ($allowInsert && !$hasSelectedId);
	$showSaveChanges = !$dvprint && $allowUpdate && $hasSelectedId;
	$showDelete = !$dvprint && $allowDelete && $hasSelectedId;
	$showSaveAsCopy = !$dvprint && ($allowInsert && $hasSelectedId && !$noSaveAsCopy);
	$fieldsAreEditable = !$dvprint && (($allowInsert && !$hasSelectedId) || ($allowUpdate && $hasSelectedId) || $showSaveAsCopy);

	$filterer_property = Request::val('filterer_property');

	// populate filterers, starting from children to grand-parents

	// unique random identifier
	$rnd1 = ($dvprint ? rand(1000000, 9999999) : '');
	// combobox: property
	$combo_property = new DataCombo;

	if($hasSelectedId) {
		if(!($row = getRecord('property_photos', $selectedId))) {
			return error_message($Translation['No records found'], 'property_photos_view.php', false);
		}
		$combo_property->SelectedData = $row['property'];
		$urow = $row; /* unsanitized data */
		$row = array_map('safe_html', $row);
	} else {
		$filterField = Request::val('FilterField');
		$filterOperator = Request::val('FilterOperator');
		$filterValue = Request::val('FilterValue');
		$combo_property->SelectedData = $filterer_property;
	}
	$combo_property->HTML = '<span id="property-container' . $rnd1 . '"></span><input type="hidden" name="property" id="property' . $rnd1 . '" value="' . html_attr($combo_property->SelectedData) . '">';
	$combo_property->MatchText = '<span id="property-container-readonly' . $rnd1 . '"></span><input type="hidden" name="property" id="property' . $rnd1 . '" value="' . html_attr($combo_property->SelectedData) . '">';

	ob_start();
	?>

	<script>
		// initial lookup values
		AppGini.current_property__RAND__ = { text: "", value: "<?php echo addslashes($hasSelectedId ? $urow['property'] : htmlspecialchars($filterer_property, ENT_QUOTES)); ?>"};

		jQuery(function() {
			setTimeout(function() {
				if(typeof(property_reload__RAND__) == 'function') property_reload__RAND__();
			}, 50); /* we need to slightly delay client-side execution of the above code to allow AppGini.ajaxCache to work */
		});
		function property_reload__RAND__() {
		<?php if($fieldsAreEditable) { ?>

			$j("#property-container__RAND__").select2({
				/* initial default value */
				initSelection: function(e, c) {
					$j.ajax({
						url: 'ajax_combo.php',
						dataType: 'json',
						data: { id: AppGini.current_property__RAND__.value, t: 'property_photos', f: 'property' },
						success: function(resp) {
							c({
								id: resp.results[0].id,
								text: resp.results[0].text
							});
							$j('[name="property"]').val(resp.results[0].id);
							$j('[id=property-container-readonly__RAND__]').html('<span class="match-text" id="property-match-text">' + resp.results[0].text + '</span>');
							if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=properties_view_parent]').hide(); } else { $j('.btn[id=properties_view_parent]').show(); }


							if(typeof(property_update_autofills__RAND__) == 'function') property_update_autofills__RAND__();
						}
					});
				},
				width: '100%',
				formatNoMatches: function(term) { return '<?php echo addslashes($Translation['No matches found!']); ?>'; },
				minimumResultsForSearch: 5,
				loadMorePadding: 200,
				ajax: {
					url: 'ajax_combo.php',
					dataType: 'json',
					cache: true,
					data: function(term, page) { return { s: term, p: page, t: 'property_photos', f: 'property' }; },
					results: function(resp, page) { return resp; }
				},
				escapeMarkup: function(str) { return str; }
			}).on('change', function(e) {
				AppGini.current_property__RAND__.value = e.added.id;
				AppGini.current_property__RAND__.text = e.added.text;
				$j('[name="property"]').val(e.added.id);
				if(e.added.id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=properties_view_parent]').hide(); } else { $j('.btn[id=properties_view_parent]').show(); }


				if(typeof(property_update_autofills__RAND__) == 'function') property_update_autofills__RAND__();
			});

			if(!$j("#property-container__RAND__").length) {
				$j.ajax({
					url: 'ajax_combo.php',
					dataType: 'json',
					data: { id: AppGini.current_property__RAND__.value, t: 'property_photos', f: 'property' },
					success: function(resp) {
						$j('[name="property"]').val(resp.results[0].id);
						$j('[id=property-container-readonly__RAND__]').html('<span class="match-text" id="property-match-text">' + resp.results[0].text + '</span>');
						if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=properties_view_parent]').hide(); } else { $j('.btn[id=properties_view_parent]').show(); }

						if(typeof(property_update_autofills__RAND__) == 'function') property_update_autofills__RAND__();
					}
				});
			}

		<?php } else { ?>

			$j.ajax({
				url: 'ajax_combo.php',
				dataType: 'json',
				data: { id: AppGini.current_property__RAND__.value, t: 'property_photos', f: 'property' },
				success: function(resp) {
					$j('[id=property-container__RAND__], [id=property-container-readonly__RAND__]').html('<span class="match-text" id="property-match-text">' + resp.results[0].text + '</span>');
					if(resp.results[0].id == '<?php echo empty_lookup_value; ?>') { $j('.btn[id=properties_view_parent]').hide(); } else { $j('.btn[id=properties_view_parent]').show(); }

					if(typeof(property_update_autofills__RAND__) == 'function') property_update_autofills__RAND__();
				}
			});
		<?php } ?>

		}
	</script>
	<?php

	$lookups = str_replace('__RAND__', $rnd1, ob_get_clean());


	// code for template based detail view forms

	// open the detail view template
	if($dvprint) {
		$template_file = is_file("./{$templateDVP}") ? "./{$templateDVP}" : './templates/property_photos_templateDVP.html';
		$templateCode = @file_get_contents($template_file);
	} else {
		$template_file = is_file("./{$templateDV}") ? "./{$templateDV}" : './templates/property_photos_templateDV.html';
		$templateCode = @file_get_contents($template_file);
	}

	// process form title
	$templateCode = str_replace('<%%DETAIL_VIEW_TITLE%%>', 'Property photo details', $templateCode);
	$templateCode = str_replace('<%%RND1%%>', $rnd1, $templateCode);
	$templateCode = str_replace('<%%EMBEDDED%%>', (Request::val('Embedded') ? 'Embedded=1' : ''), $templateCode);
	// process buttons
	if($showSaveNew) {
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-success" id="insert" name="insert_x" value="1"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save New'] . '</button>', $templateCode);
	} elseif($showSaveAsCopy) {
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="insert" name="insert_x" value="1"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save As Copy'] . '</button>', $templateCode);
	} else {
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '', $templateCode);
	}

	// 'Back' button action
	if(Request::val('Embedded')) {
		$backAction = 'AppGini.closeParentModal(); return false;';
	} else {
		$backAction = 'return true;';
	}

	if($hasSelectedId) {
		if(!Request::val('Embedded')) $templateCode = str_replace('<%%DVPRINT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="dvprint" name="dvprint_x" value="1" title="' . html_attr($Translation['Print Preview']) . '"><i class="glyphicon glyphicon-print"></i> ' . $Translation['Print Preview'] . '</button>', $templateCode);
		if($allowUpdate)
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '<button type="submit" class="btn btn-success btn-lg" id="update" name="update_x" value="1" title="' . html_attr($Translation['Save Changes']) . '"><i class="glyphicon glyphicon-ok"></i> ' . $Translation['Save Changes'] . '</button>', $templateCode);
		else
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);

		if($allowDelete)
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '<button type="submit" class="btn btn-danger" id="delete" name="delete_x" value="1" title="' . html_attr($Translation['Delete']) . '"><i class="glyphicon glyphicon-trash"></i> ' . $Translation['Delete'] . '</button>', $templateCode);
		else
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);

		$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>', $templateCode);
	} else {
		$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);

		// if not in embedded mode and user has insert only but no view/update/delete,
		// remove 'back' button
		if(
			$allowInsert
			&& !$allowUpdate && !$allowDelete && !$arrPerm['view']
			&& !Request::val('Embedded')
		)
			$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '', $templateCode);
		elseif($separateDV)
			$templateCode = str_replace(
				'<%%DESELECT_BUTTON%%>', 
				'<button
					type="submit" 
					class="btn btn-default" 
					id="deselect" 
					name="deselect_x" 
					value="1" 
					onclick="' . $backAction . '" 
					title="' . html_attr($Translation['Back']) . '">
						<i class="glyphicon glyphicon-chevron-left"></i> ' .
						$Translation['Back'] .
				'</button>',
				$templateCode
			);
		else
			$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '', $templateCode);
	}

	// set records to read only if user can't insert new records and can't edit current record
	if(!$fieldsAreEditable) {
		$jsReadOnly = '';
		$jsReadOnly .= "\tjQuery('#property').prop('disabled', true).css({ color: '#555', backgroundColor: '#fff' });\n";
		$jsReadOnly .= "\tjQuery('#property_caption').prop('disabled', true).css({ color: '#555', backgroundColor: 'white' });\n";
		$jsReadOnly .= "\tjQuery('#photo').replaceWith('<div class=\"form-control-static\" id=\"photo\">' + (jQuery('#photo').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('.select2-container').hide();\n";

		$noUploads = true;
	} else {
		// temporarily disable form change handler till time and datetime pickers are enabled
		$jsEditable = "\tjQuery('form').eq(0).data('already_changed', true);";
		$jsEditable .= "\tjQuery('form').eq(0).data('already_changed', false);"; // re-enable form change handler
	}

	// process combos
	$templateCode = str_replace('<%%COMBO(property)%%>', $combo_property->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(property)%%>', $combo_property->MatchText, $templateCode);
	$templateCode = str_replace('<%%URLCOMBOTEXT(property)%%>', urlencode($combo_property->MatchText), $templateCode);

	/* lookup fields array: 'lookup field name' => ['parent table name', 'lookup field caption'] */
	$lookup_fields = ['property' => ['properties', 'Property'], ];
	foreach($lookup_fields as $luf => $ptfc) {
		$pt_perm = getTablePermissions($ptfc[0]);

		// process foreign key links
		if(($pt_perm['view'] && isDetailViewEnabled($ptfc[0])) || $pt_perm['edit']) {
			$templateCode = str_replace("<%%PLINK({$luf})%%>", '<button type="button" class="btn btn-default view_parent" id="' . $ptfc[0] . '_view_parent" title="' . html_attr($Translation['View'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-eye-open"></i></button>', $templateCode);
		}

		// if user has insert permission to parent table of a lookup field, put an add new button
		if($pt_perm['insert'] /* && !Request::val('Embedded')*/) {
			$templateCode = str_replace("<%%ADDNEW({$ptfc[0]})%%>", '<button type="button" class="btn btn-default add_new_parent" id="' . $ptfc[0] . '_add_new" title="' . html_attr($Translation['Add New'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-plus text-success"></i></button>', $templateCode);
		}
	}

	// process images
	$templateCode = str_replace('<%%UPLOADFILE(id)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(property)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(photo)%%>', ($noUploads ? '' : "<div>{$Translation['upload image']}</div>" . '<input type="file" name="photo" id="photo" data-filetypes="jpg|jpeg|gif|png|webp" data-maxsize="2048000" style="max-width: calc(100% - 1.5rem);" accept="capture=camera,image/*">' . '<i class="text-danger clear-upload hidden pull-right" style="margin-top: -.1em; font-size: large;">&times;</i>'), $templateCode);
	if($allowUpdate && $row['photo'] != '') {
		$templateCode = str_replace('<%%REMOVEFILE(photo)%%>', '<input type="checkbox" name="photo_remove" id="photo_remove" value="1"> <label for="photo_remove" style="color: red; font-weight: bold;">'.$Translation['remove image'].'</label>', $templateCode);
	} else {
		$templateCode = str_replace('<%%REMOVEFILE(photo)%%>', '', $templateCode);
	}
	$templateCode = str_replace('<%%UPLOADFILE(description)%%>', '', $templateCode);

	// process values
	if($hasSelectedId) {
		if( $dvprint) $templateCode = str_replace('<%%VALUE(id)%%>', safe_html($urow['id']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(id)%%>', html_attr($row['id']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode($urow['id']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(property)%%>', safe_html($urow['property']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(property)%%>', html_attr($row['property']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(property)%%>', urlencode($urow['property']), $templateCode);
		$row['photo'] = ($row['photo'] != '' ? $row['photo'] : 'blank.gif');
		if( $dvprint) $templateCode = str_replace('<%%VALUE(photo)%%>', safe_html($urow['photo']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(photo)%%>', html_attr($row['photo']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(photo)%%>', urlencode($urow['photo']), $templateCode);
		if($fieldsAreEditable) {
			$templateCode = str_replace('<%%HTMLAREA(description)%%>', '<textarea name="description" id="description" rows="5">' . safe_html(htmlspecialchars_decode($row['description'])) . '</textarea>', $templateCode);
		} else {
			$templateCode = str_replace('<%%HTMLAREA(description)%%>', '<div id="description" class="form-control-static">' . $row['description'] . '</div>', $templateCode);
		}
		$templateCode = str_replace('<%%VALUE(description)%%>', nl2br($row['description']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(description)%%>', urlencode($urow['description']), $templateCode);
	} else {
		$templateCode = str_replace('<%%VALUE(id)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(property)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(property)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(photo)%%>', 'blank.gif', $templateCode);
		$templateCode = str_replace('<%%HTMLAREA(description)%%>', '<textarea name="description" id="description" rows="5"></textarea>', $templateCode);
	}

	// process translations
	$templateCode = parseTemplate($templateCode);

	// clear scrap
	$templateCode = str_replace('<%%', '<!-- ', $templateCode);
	$templateCode = str_replace('%%>', ' -->', $templateCode);

	// hide links to inaccessible tables
	if(Request::val('dvprint_x') == '') {
		$templateCode .= "\n\n<script>\$j(function() {\n";
		$arrTables = getTableList();
		foreach($arrTables as $name => $caption) {
			$templateCode .= "\t\$j('#{$name}_link').removeClass('hidden');\n";
			$templateCode .= "\t\$j('#xs_{$name}_link').removeClass('hidden');\n";
		}

		$templateCode .= $jsReadOnly;
		$templateCode .= $jsEditable;

		if(!$hasSelectedId) {
		}

		$templateCode.="\n});</script>\n";
	}

	// ajaxed auto-fill fields
	$templateCode .= '<script>';
	$templateCode .= '$j(function() {';


	$templateCode.="});";
	$templateCode.="</script>";
	$templateCode .= $lookups;

	// handle enforced parent values for read-only lookup fields
	$filterField = Request::val('FilterField');
	$filterOperator = Request::val('FilterOperator');
	$filterValue = Request::val('FilterValue');

	// don't include blank images in lightbox gallery
	$templateCode = preg_replace('/blank.gif" data-lightbox=".*?"/', 'blank.gif"', $templateCode);

	// don't display empty email links
	$templateCode=preg_replace('/<a .*?href="mailto:".*?<\/a>/', '', $templateCode);

	/* default field values */
	$rdata = $jdata = get_defaults('property_photos');
	if($hasSelectedId) {
		$jdata = get_joined_record('property_photos', $selectedId);
		if($jdata === false) $jdata = get_defaults('property_photos');
		$rdata = $row;
	}
	$templateCode .= loadView('property_photos-ajax-cache', ['rdata' => $rdata, 'jdata' => $jdata]);

	// hook: property_photos_dv
	if(function_exists('property_photos_dv')) {
		$args = [];
		property_photos_dv(($hasSelectedId ? $selectedId : FALSE), getMemberInfo(), $templateCode, $args);
	}

	return $templateCode;
}