$(function() {
	
			$.fn.esprun = function(action) {
					var params = { 
						code: $("#code").val(),
						cmd: action,
						'port': port,
						'ip': ip_address
					}
					if ( (action =='get')||(action=='down')||(action=='do')||(action=='put') ) {
						params.file=$(this).data('file');
					}
					$.post( target, params)
					 .done(function( data ) {
						if(typeof data !='object') {
							data  = $.parseJSON(data);
						}
						if(typeof data =='object') {
							$("#output").html(data.response);
							$("#cmd").html(data.cmd);
							if (data.files) {
								if (action=='rlist') {
									var filesblock = $("#rfiles");
									var tpl = "#template_rfile";
									$("#output").html('');
 								} else if (action=='llist') {
									var filesblock = $("#lfiles");
									var tpl = "#template_lfile";
 								}
								filesblock.html("");
								for(var i=0;i<data.files.length;i++) {
									var file=data.files[i].file;
									var size=data.files[i].size;
									var html = $(tpl).children().clone();
									html.find('.file_name').text(file);
									html.find('.file_size').text(size);
									html.find('.cmd_file').attr('data-file', file);
									html.appendTo(filesblock);
								}
							}
							
							if (action=='down') {
								$(this).esprun('llist');
							}
							if (action=='put') {
								$(this).esprun('rlist');
							}
						} else {
							$("#output").html('Error receiving data from server/ESP, see JS console');
							console.log(data);
						}
					})
					 .fail(function() {
							$("#output").html('failed');
					})
			}




});



