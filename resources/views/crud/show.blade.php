@extends('layouts.app')
@section('title',$title.' · Detalhes')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4"><h1 class="page-heading h3 mb-0">{{ $title }} · Detalhes</h1>@can('update',$record)<a class="btn btn-primary" href="{{ route($resource.'.edit',$record) }}">Editar cadastro</a>@endcan</div>
<div class="card"><div class="card-body p-4"><dl class="row mb-0">
@foreach($fields as $name=>$field)@if(($field['type'] ?? '')!=='password')<dt class="col-sm-4 text-secondary mb-3">{{ $field['label'] }}</dt><dd class="col-sm-8 mb-3"><x-value :name="$name" :field="$field" :record="$record"/></dd>@endif @endforeach
<dt class="col-sm-4 text-secondary">Cadastrado em</dt><dd class="col-sm-8">{{ $record->created_at->format('d/m/Y H:i') }}</dd>
</dl></div></div>
<a class="btn btn-outline-secondary mt-3" href="{{ auth()->user()->can('viewAny',$record) ? route($resource.'.index') : route('dashboard') }}">Voltar</a>
@endsection
