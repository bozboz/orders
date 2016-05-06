@extends('admin::overview')

@section('report_header')
{{--
	{{ Form::open(['action' => $controller . '@downloadCsv', 'method' => 'GET']) }}
		{{ Form::hidden('date', Input::get('date')) }}
		{{ Form::hidden('state', Input::get('state')) }}
		{{ Form::hidden('per-page', Input::get('per-page')) }}
		{{ Form::hidden('customer', Input::get('customer')) }}
		{{ Form::submit('Download CSV', ['class' => 'btn btn-primary pull-right']) }}
	{{ Form::close() }}
--}}

	<h1>{{ $heading }}</h1>

	@include('admin::partials.sort-alert')

	{!! $report->getHeader() !!}
@stop

@section('report_footer')
	{{ $report->getFooter() }}
@stop
