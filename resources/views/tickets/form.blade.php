@extends('layouts.app')
@section('title',$ticket->exists ? 'Atualizar passagem' : 'Vender passagem')
@section('content')
<h1 class="page-heading h3 mb-4">{{ $ticket->exists ? 'Atualizar passagem · '.$ticket->reference : 'Vender ou reservar passagem' }}</h1>
<div class="card"><div class="card-body p-4"><div class="alert alert-info small">Operação demonstrativa. Pagamentos e estornos são simulados.</div>
<form method="POST" action="{{ $ticket->exists ? route('tickets.update',$ticket) : route('tickets.store') }}">@csrf @if($ticket->exists) @method('PUT') @endif
@if($ticket->exists)
<p><strong>{{ $ticket->customer->name }}</strong> · {{ $ticket->flight->label }} · R$ {{ number_format($ticket->price_cents/100,2,',','.') }}</p>
<p>Situação atual: <x-status :value="$ticket->status"/></p>
@else
@php $flightOptions=$flights->mapWithKeys(fn($f)=>[$f->id=>$f->label.' · '.$f->departure_at->format('d/m H:i').' · R$ '.number_format($f->price_cents/100,2,',','.').' · '.$f->available_seats.' lugares'])->all(); @endphp
<x-field name="flight_id" :field="['label'=>'Voo','type'=>'select','options'=>$flightOptions]"/>
<x-field name="customer_id" :field="['label'=>'Passageiro','type'=>'select','options'=>$customers->pluck('label','id')->all()]"/>
<p class="small text-secondary">Não encontrou o passageiro? <a href="{{ route('customers.create') }}">Cadastre seus dados e documento.</a></p>
@endif
<x-field name="status" :field="['label'=>'Operação','type'=>'select','options'=>$ticket->exists ? ($ticket->status==='reserved' ? ['paid'=>'Pagar reserva (simulado)','cancelled'=>'Cancelar passagem'] : ['cancelled'=>'Cancelar passagem']) : ['paid'=>'Vender (pagamento simulado)','reserved'=>'Reservar']]"/>
<x-field name="payment_method" :field="['label'=>'Forma de pagamento (obrigatória para pagamento)','type'=>'select','required'=>false,'options'=>auth()->user()->isStaff() ? ['pix'=>'Pix','card'=>'Cartão','cash'=>'Dinheiro'] : ['pix'=>'Pix','card'=>'Cartão']]"/>
<div class="d-flex gap-2 mt-4"><button class="btn btn-primary" @disabled($ticket->status==='cancelled')>Confirmar operação</button><a href="{{ $ticket->exists ? route('tickets.show',$ticket) : route('tickets.index') }}" class="btn btn-outline-secondary">Voltar</a></div>
</form></div></div>
@endsection
