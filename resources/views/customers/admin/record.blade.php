@extends('admin::edit')

@section('heading', 'Customer ' . $model->name)

@section('main')
	@parent

	<h2>Addresses</h2>
	<a href="{{ route('admin.customer.address.create', $model->id) }}" class="btn btn-primary">New Address</a>
	<hr>
	<div class="container-fluid">
	@foreach ($addresses as $address)
		<div class="customer__address col-sm-12 col-md-6 col-lg-4">
			{{ Form::open(['method' => 'POST', 'route' => ['admin.customer.address.update', $address->id]]) }}
				<table class="table table-striped table-condensed">
					@foreach ($address->parts() as $key => $value)
						<tr>
							@if ($key == 'customer_id')
								{{ Form::hidden($key, $value)  }}
							@else
								<th>{{ ucwords(str_replace('_', ' ', $key)) }}:</th>
								<td>{{ Form::text($key, $value, ['class' => 'form-control'])  }}</td>
							@endif
						</tr>
					@endforeach
					<tfoot>
						<tr>
							<td colspan="2" align="right">
								{{ Form::button('Update', ['class' => 'btn btn-success', 'type' => 'submit', 'name' => '_method', 'value' => 'PUT']) }}
								{{ Form::button('Delete', ['class' => 'btn btn-danger', 'type' => 'submit', 'name' => '_method', 'value' => 'DELETE']) }}
							</td>
						</tr>
					</tfoot>
				</table>
				{{ Form::hidden('after_save', 'continue') }}
			{{ Form::close() }}
		</div>
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
							{!! $item->name !!}<br>
						@endforeach
					</td>
					<td>{{ format_money($order->totalPrice()) }}</td>
					<td style="text-align: left">{{ $order->getFiniteState() }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@stop
