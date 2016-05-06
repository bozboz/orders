@extends('admin::form')

@section('heading', 'Customer ' . $model->name)

@section('main')
	@parent

	<h2>Addresses</h2>
	<div class="container-fluid">
	@foreach ($addresses as $address)
		{{ Form::open(['method' => 'PUT', 'route' => ['admin.customer.address.update', $model->id, $address->id]]) }}
		<div class="customer__address col-sm-12 col-md-6 col-lg-4">
			<table class="table table-striped table-condensed">
				@foreach ($address->parts() as $key => $value)
					<tr>
						<th>{{ ucwords(str_replace('_', ' ', $key)) }}:</th>
						<td>{{ Form::text($key, $value, ['class' => 'form-control'])  }}</td>
					</tr>
				@endforeach
				<tfoot>
					<tr>
						<td colspan="2" align="right">
							{{ Form::submit('Update', ['class' => 'btn btn-success']) }}
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
		{{ Form::hidden('after_save', 'continue') }}
		{{ Form::close() }}
	@endforeach
	</div>

	<h2>Order History</h2>

	<table class="table">
		<thead>
			<tr>
				<th colspan="2">Order</th>
				<th>Total</th>
				<th>State</th>
			</tr>
		</thead>
		<tbody>
			@foreach($orderHistory as $order)
				<tr>
					<td>{{ HTML::linkRoute('admin.orders.edit', '#' . $order->id, [$order->id]) }}</td>
					<td>
						@foreach($order->items as $item)
							{{ $item->name }}
						@endforeach
					</td>
					<td>{{ format_money($order->totalPrice()) }}</td>
					<td style="text-align: left">{{ $order->state->name }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@stop
