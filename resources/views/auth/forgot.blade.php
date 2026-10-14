@extends('layouts.app')
@section('title','Recuperar senha')
@section('content')
<div class="card auth-card"><div class="card-body p-4 p-md-5"><div class="metric-icon mb-4"><i aria-hidden="true" class="bi bi-airplane"></i></div><h1 class="page-heading h3">Recuperar senha</h1><p class="text-secondary mb-4">Informe seu e-mail para receber as instruções.</p><form method="POST" action="{{ route('password.email') }}">@csrf<x-field name="email" :field="['label'=>'E-mail','type'=>'email']"/><button class="btn btn-primary w-100">Enviar link de recuperação</button></form><a class="d-block text-center small mt-4" href="{{ route('login') }}">Voltar para entrar</a></div></div>
@endsection
