<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Invoice</title>
		<style>
			.invoice-box {
				max-width: 800px;
				margin: auto;
				padding: 30px;
				border: 1px solid #eee;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
				font-size: 16px;
				line-height: 24px;
				font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
				color: rgb(36, 51, 50);
			}

			.invoice-box table {
				width: 100%;
				line-height: inherit;
				text-align: left;
			}

			.invoice-box table td {
				padding: 5px;
				vertical-align: top;
			}

			.invoice-box table tr td:nth-child(2) {
				text-align: right;
			}

			.invoice-box table tr.top table td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.top table td.title {
				font-size: 45px;
				line-height: 45px;
				color: #242e2d;
			}

			.invoice-box table tr.information table td {
				padding-bottom: 40px;
			}

			.invoice-box table tr.heading td {
				background: #c2f9ee;
				border-bottom: 1px solid #ddd;
				font-weight: bold;
			}

			.invoice-box table tr.details td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.item td {
				border-bottom: 1px solid #eee;
			}

			.invoice-box table tr.item.last td {
				border-bottom: none;
			}

			.invoice-box table tr.total td:nth-child(2) {
				border-top: 2px solid #eee;
				font-weight: bold;
			}

			@media only screen and (max-width: 600px) {
				.invoice-box table tr.top table td {
					width: 100%;
					display: block;
					text-align: center;
				}

				.invoice-box table tr.information table td {
					width: 100%;
					display: block;
					text-align: center;
				}
			}

			/** RTL **/
			.invoice-box.rtl {
				direction: rtl;
				font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
			}

			.invoice-box.rtl table {
				text-align: right;
			}

			.invoice-box.rtl table tr td:nth-child(2) {
				text-align: left;
			}
		</style>
	</head>

	<body>
		<div class="invoice-box">
			<table cellpadding="0" cellspacing="0">
				
				<tr class="top">
					<td colspan="2">
						<table>
							<tr>
								<td>
									<h1>Invoice</h1>
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="information">
					<td colspan="2">
						<table>
							<tr>

								<td>
									{{ $dealer_name }}<br />
									{{ $dealer_email }}<br />
								</td>

								<td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
								<td style="text-align: left">
									<b >Invoice No</b> <br/>
									<b class="text-bold">Start Date:</b> <br/>
									<b class="text-bold">Expire Date:</b> <br/>
								</td>

								<td >
								{{ $invoice_number }}<br />
								{{ $park_in_date }}<br />
								{{ $park_out_date }}<br />
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>

			<table cellpadding="0" cellspacing="0">
				<tr class="heading" align="center">
					<td>Location</td>
					<td>Lane</td>
					<td>Duration</td>
					<td>Plots</td>
					<td>Amount</td>
					<td></td>
				</tr>
				@foreach ($plots as $index => $plot)
				<tr class="item" align="center">
					@if ($index === 0)
						<td style="border:none">{{ $location_name }}</td>
						<td style="border:none">{{ $line_name }}</td>
						<td style="border:none">{{ $plot['duration'] }}</td>
						<td style="border:none">{{ $plot['plot_name'] }}</td>
						<td style="border:none">£{{ $plot['rent'] }}</td>
					@else
						<td style="border:none"></td>
						<td style="border:none"></td>
						<td style="border:none"></td>
						<td style="border:none">{{ $plot['plot_name'] }}</td>
						<td style="border:none">£{{ $plot['rent'] }}</td>
					@endif
				</tr>
				@endforeach
				<tr align="center">
					<td style="border:none"></td>
					<td style="border:none"></td>
					<td style="border:none"></td>
					<td style="border-bottom: 1px solid #eee;border-top: 1px solid #eee;">Total</td>
					<td style="border-bottom: 1px solid #eee;border-top: 1px solid #eee">£{{ $amount_paid }}</td>
				</tr>
				<tr align="center">
					<td></td>
					<td></td>
					<td></td>
					<td style="border-bottom: 1px solid #eee; background-color: #dffef8;"><b>Amount Paid</b></td>
					<td style="border-bottom: 1px solid #eee; background-color: #dffef8;">£{{ $amount_paid }}</td>
				</tr>
			</table>
			<br/>
		</div>
	</body>
</html>