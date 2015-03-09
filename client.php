<!DOCTYPE HTML>
<html lang = "en">
<head>
<title>Telnet to ESP8266</title>
  <meta charset = "UTF-8" />
  <script src="jquery-2.1.3.min.js"></script>
  <script src="esp.js"></script>
  <script language="javascript" type="text/javascript">
	var target = "index.php";
  </script>

	<link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
	<script src="//code.jquery.com/jquery-1.10.2.js"></script>
	<script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>



<script>

var code_list = ["=node.heap()","dofile(\"\")"];

if (localStorage) {
    var ip_address = '10.10.10.17';
    var port = '2323';
    if (localStorage.getItem('ip_address')) {
    	ip_address = localStorage.getItem('ip_address');
    } 
    if (localStorage.getItem('port')) {
    	ip_address = localStorage.getItem('port');
    } 
}

(function( $ ) {
		$.widget( "custom.combobox", {
			_create: function() {
				this.wrapper = $( "<span>" )
					.addClass( "custom-combobox" )
					.insertAfter( this.element );

				this.element.hide();
				this._createAutocomplete();
				this._createShowAllButton();
			},

			_createAutocomplete: function() {
				var selected = this.element.children( ":selected" ),
					value = selected.val() ? selected.text() : "";

				this.input = $( "<input>" )
					.appendTo( this.wrapper )
					.val( value )
					.attr( "title", "" )
					.attr( "id", "code" )
					.attr( "name", "code" )
					.addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
					.autocomplete({
						delay: 0,
						minLength: 0,
						/*source: $.proxy( this, "_source" )*/
						source: code_list
					})
					.tooltip({
						tooltipClass: "ui-state-highlight"
					});

			},

			_createShowAllButton: function() {
				var input = this.input,
					wasOpen = false;

				$( "<a>" )
					.attr( "tabIndex", -1 )
					.tooltip()
					.appendTo( this.wrapper )
					.button({
						icons: {
							primary: "ui-icon-triangle-1-s"
						},
						text: false
					})
					.removeClass( "ui-corner-all" )
					.addClass( "custom-combobox-toggle ui-corner-right" )
					.mousedown(function() {
						wasOpen = input.autocomplete( "widget" ).is( ":visible" );
					})
					.click(function() {
						input.focus();

						// Close if already visible
						if ( wasOpen ) {
							return;
						}

						// Pass empty string as value to search for, displaying all results
						input.autocomplete( "search", "" );
					});
			},

/*			_source: function( request, response ) {
				var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
				response( code_list.map(function() {
					var text = $( this );
					if ( this.value && ( !request.term || matcher.test(text) ) )
						return {
							label: text,
							value: text,
							option: this
						};
				}) );
			},
*/
/*			_removeIfInvalid: function( event, ui ) {

				// Selected an item, nothing to do
				if ( ui.item ) {
					return;
				}

				// Search for a match (case-insensitive)
				var value = this.input.val(),
					valueLowerCase = value.toLowerCase(),
					valid = false;
				this.element.children( "option" ).each(function() {
					if ( $( this ).text().toLowerCase() === valueLowerCase ) {
						this.selected = valid = true;
						return false;
					}
				});

				// Found a match, nothing to do
				if ( valid ) {
					return;
				}

				// Remove invalid value
				this.input
					.val( "" )
					.attr( "title", value + " didn't match any item" )
					.tooltip( "open" );
				this.element.val( "" );
				this._delay(function() {
					this.input.tooltip( "close" ).attr( "title", "" );
				}, 2500 );
				this.input.autocomplete( "instance" ).term = "";
			},*/

			_destroy: function() {
				this.wrapper.remove();
				this.element.show();
			}
		});
	})( jQuery );

	$(function() {
		$("#ip_address").val(ip_address).change(function() {
			ip_address = $(this).val();
	    	localStorage.setItem('ip_address', ip_address);			
		});
		$("#port").val(port).change(function() {
			port = $(this).val();
	    	localStorage.setItem('port', port);			
		});
		
	
		$( "#code_list" ).combobox();
		$(this).esprun('llist');
		$(this).esprun('rlist');
		
		
	});




	</script>
<style>

#cmd {
	font-size: 10px; background-color: #eee; color:#888;
}
#buttons {
	margin-left: 50px;
}
#content {
	width: 100%;
	margin: 0 auto;
}
#rfiles, #lfiles {
	float: left;
	min-height: 100px;
	width: 45%;
	height: auto;
	background: #efefef;
	margin-right: 20px;
}

.file {
	width: 100%;
	height: 25px;
	border-bottom: 1px solid #aaa;
}
.file .file_name  {
	display:inline-block;
	width: 50%;
	overflow: hidden;
}
.file button {
	float: right;
}


.custom-combobox {
position: relative;
display: inline-block;
}
.custom-combobox-toggle {
position: absolute;
top: 0;
bottom: 0;
margin-left: -1px;
padding: 0;
}
.custom-combobox-input {
margin: 0;
padding: 5px 10px;
}

</style>



</head>
<body onload = "">
    <fieldset>
         <legend>Remote settings </legend>
    	Esp8266 module IP <input id="ip_address" value="" />:<input id="port" value="" />
    </fieldset>

    <fieldset>

<span id="code_list" ></span>               
<span id="buttons">
      <button type = "button"
              onclick = "$(this).esprun('run')">
        execute
      </button>

      <button type = "button"
              onclick = "$(this).esprun('llist')">
        list local files
      </button>

      <button type = "button"
              onclick = "$(this).esprun('rlist')">
        list remote files
      </button>
</span>

    </fieldset>
  <pre id="cmd">Cmd here</pre>
  <pre id="output">Response from ESP will be here</pre>

<div id="files">
	<div id="lfiles"></div>
	<div id="rfiles"></div>
</div>

	<div id="template_lfile" style="display:none">
		<div class="file">
			<span class="file_name"></span>
			<span class="file_size"></span> B
			<button class="cmd_file" onclick = "$(this).esprun('put')">upload</button>
		</div>
	</div>

	<div id="template_rfile" style="display:none">
		<div class="file">
			<span class="file_name"></span>
			<span class="file_size"></span> B
			<button class="cmd_file" onclick = "$(this).esprun('do')">run</button>
			<button class="cmd_file" onclick = "$(this).esprun('get')">show</button>
			<button class="cmd_file" onclick = "$(this).esprun('down')">down</button>
		</div>
	</div>

</body>
</html>


