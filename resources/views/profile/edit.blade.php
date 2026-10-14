@extends('layouts.app')
@section('title','Meu perfil de passageiro')
@section('content')
<h1 class="page-heading h3 mb-4">Meu perfil de passageiro</h1>
<div class="card"><div class="card-body p-4"><p class="text-secondary">Estes dados identificam o passageiro da compra. O e-mail de contato pode ser diferente do e-mail de acesso.</p>
<form method="POST" action="{{ route('profile.update') }}">@csrf @method('PUT')<div class="row">
@foreach(['name'=>['label'=>'Nome completo'],'email'=>['label'=>'E-mail de contato','type'=>'email'],'document'=>['label'=>'Documento','maxlength'=>30],'phone'=>['label'=>'Telefone','required'=>false],'birth_date'=>['label'=>'Data de nascimento','type'=>'date','required'=>false]] as $name=>$field)<div class="col-md-6"><x-field :name="$name" :field="$field" :record="$customer"/></div>@endforeach
</div><button class="btn btn-primary mt-2">Salvar perfil</button></form></div></div>
@endsection
