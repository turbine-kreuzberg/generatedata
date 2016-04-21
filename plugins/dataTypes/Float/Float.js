/*global $:false,define:false*/
define([
	"manager",
	"constants",
	"lang",
	"generator"
], function(manager, C, L, generator) {

	"use strict";

	var MODULE_ID = "data-type-Float";
	var LANG = L.dataTypePlugins.Float;


	var _loadRow = function(rowNum, data) {
		return {
			execute: function() {
				$("#dtFloatMin_" + rowNum).val(data.min);
				$("#dtFloatMax_" + rowNum).val(data.max);
			},
			isComplete: function() { return true; }
		};
	};

	var _saveRow = function(rowNum) {
		return {
			min: $("#dtFloatMin_" + rowNum).val(),
			max: $("#dtFloatMax_" + rowNum).val()
		};
	};

	var _validate = function(rows) {
		var visibleProblemRows = [];
		var problemFields      = [];

		var intOnly = /^[\-\d]+$/;
		for (var i=0; i<rows.length; i++) {
			var numWordsMin = $.trim($("#dtFloatMin_" + rows[i]).val());
			var visibleRowNum = generator.getVisibleRowOrderByRowNum(rows[i]);

			var hasError = false;
			if (numWordsMin === "" || !intOnly.test(numWordsMin)) {
				hasError = true;
				problemFields.push($("#dtFloatMin_" + rows[i]));
			}
			var numWordsMax = $.trim($("#dtFloatMax_" + rows[i]).val());
			if (numWordsMax === "" || !intOnly.test(numWordsMax)) {
				hasError = true;
				problemFields.push($("#dtFloatMax_" + rows[i]));
			}
			if (hasError) {
				visibleProblemRows.push(visibleRowNum);
			}
		}

		var errors = [];
		if (visibleProblemRows.length) {
			errors.push({ els: problemFields, error: LANG.incomplete_fields + " <b>" + visibleProblemRows.join(", ") + "</b>"});
		}
		return errors;
	};

	manager.registerDataType(MODULE_ID, {
		validate: _validate,
		loadRow: _loadRow,
		saveRow: _saveRow
	});
});
