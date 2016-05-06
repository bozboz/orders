@extends('admin::edit')

@section('main')
	{{ Form::model($model, ['method' => $method, 'action' => $action, 'role' => 'form', 'files' => true]) }}
	<div class="form-row discrete">
		@include('admin::partials.save')
		@include('admin::partials.listing')
	</div>

	<h2>Order Summary ({{ $model->created_at->format('Y-m-d') }})</h2>
	@if ($model->user)
		<h4>
			By {{ HTML::linkRoute(
				'admin.customers.edit',
				$model->user->first_name . ' ' . $model->user->last_name,
				[ $model->user_id ]
			) }}
		</h4>
	@endif

	@foreach($model->relatedOrders()->orderBy('created_at')->get() as $order)
		<div class="related-orders">
			{{ HTML::linkRoute('admin.orders.edit', sprintf('%s - Related Order (%s)', $order->created_at, format_money($order->totalPrice())), [$order->id]) }}
		</div>
	@endforeach

	@if ($model->parent)
		<div class="related-orders">
			{{ HTML::linkRoute('admin.orders.edit', sprintf('%s - Parent Order (%s)', $model->parent->created_at, format_money($model->parent->totalPrice())), [$model->parent->id]) }}
		</div>
	@endif

		@foreach($fields as $field)
			 <div class="form-group{{{ ($field->getErrors($errors)) ? ' bs-callout bs-callout-danger' : '' }}}">
				{{ $field->getLabel() }}
				{{ $field->getInput() }}
				{{ $field->getErrors($errors) }}
			</div>
		@endforeach
	{{ Form::close() }}

	<div class="summary-inner">
		{{ Form::open(['route' => ['admin.orders.refund', $model->id]])}}
		<table class="table summary-list">
			<thead>
				<tr>
					<td></td>
					<td>Item</td>
					<td align="right">Quantity</td>
					<td align="right">Net Amount</td>
					<td align="right">Total</td>
					<td align="right">Refund</td>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2"></td>
					<td align="right">{{ $model->totalQuantity() }}</td>
					<td align="right">{{ format_money($model->subTotal()) }}</td>
					<td align="right">{{ format_money($model->totalPrice()) }}</td>
					<td align="right"></td>
				</tr>
				<tr>
					<td colspan="5">
						{{ $errors->first('refund', '<p><strong>:message</strong></p>') }}
					</td>
					<td align="right">
						{{ Form::submit('Refund', ['class' => 'btn pull-right btn-danger space-left']) }}
					</td>
				</tr>
			</tfoot>
			<tbody>
			@foreach($model->items()->with('orderable')->get() as $item)
				<tr class="summary-item">
					<td class="summary-detail image">
						<a href="{{ $item->image }}">
							<img width="40" class="summary-image" src="{{ $item->image }}"/>
						</a>
					</td>
					<td class="summary-detail name">
						{{ $item->name }}
						@if (method_exists($item->orderable, 'description'))
							<p class="summary" style="color: #888">{{ $item->orderable->description() }}</p>
						@endif
					</td>
					<td class="summary-detail checkout-quantity" align="right">{{ $item->quantity }}</td>
					<td class="summary-detail checkout-quantity" align="right">{{ format_money($item->total_price_pence_ex_vat) }}</td>
					<td class="summary-detail price" align="right">{{ format_money($item->total_price_pence) }}</td>
					<td align="right">{{ Form::text('items[' . $item->id . ']', $item->quantity, [
						'style' => 'width: 30px; text-align: center'
					]) }}</td>
				</tr>
			@endforeach
			</body>
		</table>
		{{ Form::close() }}
	</div>

	<div class="addresses">
		<div class="billing">
			<h3>Billing Address</h3>
			@if ($model->billingAddress)
				@foreach($model->billingAddress->parts() as $value)
					@if ($value)
						{{ $value }}<br>
					@endif
				@endforeach
			@endif
		</div>
		<div class="shipping">
			<h3>Shipping Address</h3>
			@if ($model->shippingAddress)
				@foreach($model->shippingAddress->parts() as $value)
					@if ($value)
						{{ $value }}<br>
					@endif
				@endforeach
			@endif
		</div>
	</div>

	<div class="form-row">
		@include('admin::partials.listing')
	</div>

@stop
