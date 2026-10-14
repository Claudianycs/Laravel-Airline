@extends('layouts.app')
@section('title',($record->exists ? 'Editar' : 'Novo cadastro').' · '.$title)
@section('content')
<h1 class="page-heading h3 mb-4">{{ $record->exists ? 'Editar cadastro' : 'Novo cadastro' }} · {{ $title }}</h1>
<div class="card"><div class="card-body p-4">
<form action="{{ $record->exists ? route($resource.'.update',$record) : route($resource.'.store') }}" method="POST">
 @csrf @if($record->exists) @method('PUT') @endif
 <div class="row">@foreach($fields as $name=>$field)<div class="col-md-6"><x-field :name="$name" :field="$field" :record="$record"/></div>@endforeach</div>
 @if($resource==='flights')<p class="text-secondary small">Preços em reais. Voos com passagens emitidas preservam rota, aeronave e horários. Cancelar um voo também cancela suas passagens.</p>@endif
 <div class="d-flex gap-2 mt-3"><button class="btn btn-primary">Salvar cadastro</button><a class="btn btn-outline-secondary" href="{{ $record->exists ? route($resource.'.show',$record) : route($resource.'.index') }}">Cancelar</a></div>
</form>
</div></div>
@endsection
