document.addEventListener("DOMContentLoaded", () => {

	if( document.body.classList.contains('toplevel_page_dokan') && document.querySelector('.withdraw-requests').hasChildNodes() ) {

		let csvButton = document.getElementById('export-csv-selector-top');
		const month = ["January","February","March","April","May","June","July","August","September","October","November","December"];
		let  date = new Date();
		let csv;
		let filename = 'withdraw-request-' + month[date.getMonth()] +'-'+ date.getFullYear() + '.csv';
		let url  = dokan.rest.root + dokan.rest.version + '/withdraw?status=pending';
		try {
				fetch(url, {
					method: 'GET',
					headers: {
						'Content-Type': 'application/json; charset=UTF-8',
						'X-WP-Nonce': dokan.rest.nonce
					},
				}).then((response) => {
					return response.json();
				}).then((withdrawls) => {
					if(withdrawls.length>0){
						let columnDelimiter = ',';
						let lineDelimiter = '\n';
						let withdrawData = [];
						let _withdraw = '';
						withdrawls.forEach((withdraw, index) => {
							withdrawData.push(
								{
									name: withdraw.user.first_name +' '+ withdraw.user.last_name,
									details: withdraw.details.paypal.email,
									payment_method: withdraw.method_title,
									amount: withdraw.amount,
									status: withdraw.status,
								}
							);
						});
						let csvKeys = Object.keys(withdrawData[0]);
						_withdraw += csvKeys.join(columnDelimiter);
						_withdraw += lineDelimiter;



						withdrawData.forEach((item) => {
							let ctr = 0;
							csvKeys.forEach((key) => {
								if (ctr > 0) _withdraw += columnDelimiter;
								_withdraw += item[key];
								ctr++;
							});
							_withdraw += lineDelimiter;
						});
						
						const blob = new Blob([_withdraw], { type: 'text/csv;charset=utf-8,' });
						const csvUrl = URL.createObjectURL(blob);

						// let csvUrl = encodeURI(_withdraw);
						let link = document.createElement('a');
						link.setAttribute('href', csvUrl);
						link.setAttribute('download', filename);
						link.setAttribute('class', 'button action');
						link.innerText = "Export CSV";

						let csvButton = document.createElement('div');
						csvButton.setAttribute('class', 'alignleft actions');
						csvButton.appendChild(link);

						document.querySelector('.tablenav').appendChild(csvButton);


						//jQuery('.tablenav').append('<div class="alignleft actions"><a id="export-csv-selector-top" class="button action" href="javascript:void(0)">Export CSV</a></div>');
					}
				});
			} catch (error) {
				console.error("There has been a problem with your fetch operation:", error);
			}
	}
});
