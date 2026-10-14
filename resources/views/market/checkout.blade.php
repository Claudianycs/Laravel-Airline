@extends('layouts.app')
@section('title','Confirmar viagem')
@section('content')
<h1 class="page-heading h3 mb-4">Sua próxima viagem</h1>
<div class="row g-4"><div class="col-lg-7"><div class="card"><div class="card-body p-4"><h2 class="h5 mb-3">Passageiro</h2><p class="mb-1 fw-semibold">{{ $customer->name }}</p><p class="text-secondary">{{ $customer->email }} · Documento: {{ $customer->document ?? 'Não informado' }}</p><a href="{{ route('profile.edit') }}" class="small">Atualizar dados do passageiro</a><hr>
<h2 class="h5 mb-3">Reserva e pagamento</h2><div class="alert alert-info small">Compra demonstrativa: nenhuma cobrança real será realizada. Cada passagem corresponde a um passageiro.</div>
@if(!$customer->document)<div class="alert alert-warning">Complete seu documento no <a href="{{ route('profile.edit') }}">perfil de passageiro</a> antes de comprar.</div>@endif
<form method="POST" action="{{ route('tickets.store') }}">@csrf<input type="hidden" name="flight_id" value="{{ $flight->id }}">
<x-field name="status" :field="['label'=>'Como deseja continuar?','type'=>'select','options'=>['paid'=>'Comprar com pagamento simulado','reserved'=>'Reservar sem pagamento']]"/>
<x-field name="payment_method" :field="['label'=>'Forma de pagamento simulado','type'=>'select','required'=>false,'options'=>['pix'=>'Pix','card'=>'Cartão']]"/>
<p class="small text-secondary">Reservas ocupam um lugar até a partida ou o cancelamento. Cancelamentos são permitidos antes da partida; valores e estornos são simulados.</p>
<button class="btn btn-primary w-100" @disabled(!$customer->document || $flight->available_seats===0)>Confirmar passagem</button>
</form></div></div></div>
<div class="col-lg-5"><div class="card"><div class="card-body p-4"><div class="eyebrow text-secondary mb-3">Resumo da viagem · {{ $flight->code }}</div><h2 class="h4 fw-bold">{{ $flight->origin->city }} → {{ $flight->destination->city }}</h2><dl class="row mt-4"><dt class="col-5">Companhia</dt><dd class="col-7">{{ $flight->airline->name }}</dd><dt class="col-5">Partida</dt><dd class="col-7">{{ $flight->departure_at->format('d/m/Y H:i') }}</dd><dt class="col-5">Chegada</dt><dd class="col-7">{{ $flight->arrival_at->format('d/m/Y H:i') }}</dd><dt class="col-5">Aeronave</dt><dd class="col-7">{{ $flight->aircraft->model }}</dd></dl><hr><div class="d-flex justify-content-between align-items-center"><span>Total</span><strong class="flight-price">R$ {{ number_format($flight->price_cents/100,2,',','.') }}</strong></div></div></div></div></div>
@endsection
