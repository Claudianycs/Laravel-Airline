@extends('layouts.app')
@section('title','Uma nova jornada')
@section('content')
<div class="card auth-card"><div class="card-body p-4 p-md-5"><div class="metric-icon mb-4"><i aria-hidden="true" class="bi bi-airplane"></i></div><h1 class="page-heading h3">Uma nova jornada</h1><p class="text-secondary mb-4">Crie sua conta de cliente para comprar passagens.</p><form method="POST" action="{{ route('register') }}">@csrf<x-field name="name" :field="['label'=>'Nome completo']"/><x-field name="email" :field="['label'=>'E-mail','type'=>'email']"/><x-field name="password" :field="['label'=>'Senha','type'=>'password']"/><p class="small text-secondary">Use pelo menos 8 caracteres, incluindo letras e números.</p><x-field name="password_confirmation" :field="['label'=>'Confirme a senha','type'=>'password']"/><button class="btn btn-primary w-100">Criar conta</button></form><p class="text-center small mt-4 mb-0">Já tem uma conta? <a href="{{ route('login') }}">Entrar</a></p></div></div>
@endsection
