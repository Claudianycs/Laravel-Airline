@extends('layouts.app')
@section('title','Nova senha')
@section('content')
<div class="card auth-card"><div class="card-body p-4 p-md-5"><div class="metric-icon mb-4"><i aria-hidden="true" class="bi bi-airplane"></i></div><h1 class="page-heading h3">Nova senha</h1><p class="text-secondary mb-4">Escolha uma nova senha para sua conta.</p><form method="POST" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}"><x-field name="email" :field="['label'=>'E-mail','type'=>'email']" :record="(object)['email'=>$email]"/><x-field name="password" :field="['label'=>'Nova senha','type'=>'password']"/><x-field name="password_confirmation" :field="['label'=>'Confirme a nova senha','type'=>'password']"/><button class="btn btn-primary w-100">Redefinir senha</button></form></div></div>
@endsection
