@extends('layouts.main')

@section('submenu')
	<div class="subnav">
		<div class="container">
			<div class="row">
				<ul class="inline">
					<li class="hidden-xs">
						<span>Версия фреймворка:</span>
					</li>
					@foreach($documentedVersions as $version)
						<li @if(Route::current()->parameter('version') == $version) class="active" @endif>
							<a href="{{ route('documentation', $version) }}">{{ $version }}</a>
						</li>
					@endforeach
					<li class="float-right {{ activeClassName('documentation.status') }}">
						<a href="{{ route('documentation.status') }}">Прогресс перевода</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
@stop