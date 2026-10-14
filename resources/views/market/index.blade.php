@extends('layouts.app')
@section('title','Comprar passagens')
@section('content')
<div class="hero mb-4"><div class="eyebrow mb-2">Voe com a Aurora</div><h1 class="h2 fw-bold">Para onde vamos?</h1><p class="mb-0">Encontre seu destino, escolha seu voo e comece uma nova história.</p></div>
<div class="card mb-4"><div class="card-body p-4"><form method="GET" class="row g-3 align-items-end">
@foreach(['origin_id'=>'De onde você sai?','destination_id'=>'Qual é seu destino?'] as $name=>$label)<div class="col-md-3"><label class="form-label" for="{{ $name }}">{{ $label }}</label><select class="form-select" id="{{ $name }}" name="{{ $name }}"><option value="">Todos os aeroportos</option>@foreach($airports as $airport)<option value="{{ $airport->id }}" @selected(request($name)==$airport->id)>{{ $airport->city }} ({{ $airport->iata }})</option>@endforeach</select></div>@endforeach
<div class="col-md-3"><label class="form-label" for="date">Data da viagem</label><input class="form-control" id="date" name="date" type="date" value="{{ request('date') }}" min="{{ now()->toDateString() }}"></div><div class="col-md-3"><button class="btn btn-primary w-100"><i aria-hidden="true" class="bi bi-search me-2"></i>Buscar voos</button></div>
</form></div></div>
<div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 fw-bold mb-0">Voos disponíveis</h2><span class="small text-secondary">{{ $flights->total() }} voo(s) encontrado(s)</span></div>
<div class="row g-4">@forelse($flights as $flight)<div class="col-md-6 col-xl-4"><div class="card h-100"><div class="card-body p-4">
<div class="d-flex justify-content-between mb-4"><span class="small fw-semibold">{{ $flight->airline->name }} · {{ $flight->code }}</span><span class="badge bg-light text-secondary">{{ $flight->departure_at->format('d/m') }}</span></div>
<div class="d-flex align-items-center"><div class="route-code">{{ $flight->origin->iata }}</div><div class="route-line"><i aria-hidden="true" class="bi bi-airplane"></i></div><div class="route-code">{{ $flight->destination->iata }}</div></div>
<div class="d-flex justify-content-between small text-secondary mb-3"><span>{{ $flight->origin->city }}</span><span>{{ $flight->destination->city }}</span></div>
<div class="d-flex justify-content-between border-bottom pb-3 mb-3"><span>{{ $flight->departure_at->format('H:i') }} <span class="small text-secondary">Partida</span></span><span>{{ $flight->arrival_at->format('H:i') }} <span class="small text-secondary">Chegada</span></span></div>
<div class="small text-secondary mb-1">Por passageiro</div><div class="flight-price mb-2">R$ {{ number_format($flight->price_cents/100,2,',','.') }}</div><p class="small text-secondary">{{ $flight->available_seats }} lugar(es) disponível(is) · {{ $flight->aircraft->model }}</p>
@if($flight->available_seats>0)
 @auth @can('buy-tickets')<a class="btn btn-primary w-100" href="{{ route('market.show',$flight) }}">Escolher voo</a>@else<a class="btn btn-primary w-100" href="{{ route('tickets.create',['flight_id'=>$flight->id]) }}">Vender passagem</a>@endcan @else<a class="btn btn-primary w-100" href="{{ route('login') }}">Entrar para comprar</a>@endauth
@else<button class="btn btn-secondary w-100" disabled>Esgotado</button>@endif
</div></div></div>@empty<div class="col-12"><div class="card empty-state"><i aria-hidden="true" class="bi bi-airplane fs-1 mb-3"></i><h3 class="h5">Nenhum voo encontrado</h3><p>Experimente outras datas ou destinos.</p><a href="{{ route('market.index') }}">Limpar filtros</a></div></div>@endforelse</div><div class="mt-4">{{ $flights->links() }}</div>
@endsection
