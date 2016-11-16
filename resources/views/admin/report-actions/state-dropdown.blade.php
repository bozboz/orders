@if ($actions->count())
	<div{!! HTML::attributes($dropdownAttributes) !!}>
		<button{!! HTML::attributes($attributes) !!}>
			@if ($icon)
				<i class="fa {{ $icon }}"></i>
			@endif
			{{ $label }}
				<span class="caret"></span>
				<span class="sr-only">Toggle Dropdown</span>
		</button>
		<ul class="dropdown-menu" role="menu">
			@foreach($actions as $action)
				<li>
					{!! $action->render() !!}
				</li>
			@endforeach
		</ul>
	</div>
@else
	<div class="btn" style="cursor: unset;">
		{{$label}}
	</div>
@endif
