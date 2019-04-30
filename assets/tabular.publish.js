$(document).on('ready', function() {

	$('.ftab-table').each(function(index, elem) {

		// get data-ftab attributes
		var that = $(this);
		var tabData = that.next();
		// console.log(tabData.attr('value'));
		// var tableData = that.data('ftab-rows') || "[]";
		var tableData = tabData.attr('value') || "[]";
		if (tableData == "") {
			tableData = "[]";
		}
		var tableHeader = that.data('ftab-columns') || "";
		//
		var hasHeader = that.data('ftab-header') || false;		
		if (hasHeader == "true" || hasHeader == "yes" || hasHeader == "1") {
			hasHeader = true;
		} else {
			hasHeader = false;
		}

		// 
		var data = JSON.parse(tableData);
		if (data === null || data === undefined || !$.isArray(data)) {
			// error
			return;
		}

		var columns = tableHeader.split(',');
		if (columns.length == 0) {
			return;
		}
		if (data.length > 0) {
			if (columns.length > 0 && 
				columns.length != Object.keys(data[0]).length) {
				// error
				return;
			}	
		}

		var headerRow = $("<tr>");
		var headerCellWidth = 90 / columns.length;
		var hfields = [];	
		for (var i=0; i<columns.length; i++) {
			var idx = i+1;
			hfields.push({ 
				type: "text",
				name: columns[i],
				title: columns[i],
				css: "ftb-cell" + idx.toString(),
				width: headerCellWidth.toString() + '%',
				// headercss: "ftb-column" + idx.toString()
			});
			var headerCell =  $("<th>");
			headerCell.addClass("jsgrid-header-cell ftb-column" + idx.toString());
			headerCell.css('width', headerCellWidth.toString() + '%');
			headerCell.text(columns[i]);
			headerRow.append(headerCell);
		}
		hfields.push({ type: "control", editButton: false, modeSwitchButton: false });

		that.jsGrid({
			height: "300px",
			width: "90%",
	 
			heading : (hasHeader ? true : false),
			inserting: true,
			editing: true,
			// sorting: true,
			// paging: true,
			data: data,
			fields: hfields,
			onRefreshed: function(e) {
				// set data
				tabData.attr('value', JSON.stringify(e.grid.data));
			},
			
			headerRowRenderer: function() {		
				// add some other functions
				// copy this table to clipboard
				var copyBtn = $('<input class="jsgrid-button btn-copy" type="button" title="Copy">');
				copyBtn.on('click', $.proxy(function(e) {
					window.localStorage.setItem("kv_clipboard", JSON.stringify(this.option("data")));
				}, this));				

				// paste clipboard data to this table
				var pasteBtn = $('<input class="jsgrid-button btn-paste" type="button" title="Paste">');
				pasteBtn.on('click', $.proxy(function(e) {
					// TODO : check if data is correctly parsed
					this.option("data", JSON.parse(window.localStorage.getItem("kv_clipboard")));
				}, this));

				// import data
				var importBtn = $('<input class="jsgrid-button btn-import" type="button" title="Import">');
				importBtn.on('click', $.proxy(function(e) {
					var inputElem = that.find("tr.jsgrid-insert-row > td:first > input");
					var content = inputElem.val();
					if (content !== undefined && content !== "") {
						var doc = null;

						try {
							doc = $.parseXML(content);
						}
						catch(error) {
							console.error(error);
							inputElem.val("");	
							return;
							// expected output: ReferenceError: nonExistentFunction is not defined
							// Note - error messages will vary depending on browser
						}

						if (doc !== null) {
							var docElem = doc.documentElement;						
							// children of docElem are the ROWS
							// check data
							var colNum = docElem.children[0].children.length;
							if (colNum !== columns.length) {
								// invalid data
								console.log("invalid data")
								return;
							}
	
							var data = [];
							for (var r=0; r < docElem.children.length; r++) {
								var row =  docElem.children[r];
								var tr = {};							
								for (var c=0; c<row.children.length; c++) {
									tr[columns[c]] = row.children[c].innerHTML;
								}
								data.push(tr);
							}
							this.option("data", data);
						}
						inputElem.val("");	
					}
				}, this));
				
				 
				var tableActionsCell = $("<th>");
				tableActionsCell.addClass("jsgrid-control-field jsgrid-align-center");
				tableActionsCell.css('width', '10%');
				tableActionsCell
					.append(copyBtn)
					.append(pasteBtn)
					.append(importBtn)

				headerRow.append(tableActionsCell);
				return headerRow;
			},
			
		});

	});
});