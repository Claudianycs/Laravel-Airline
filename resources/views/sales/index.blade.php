@extends('layouts.app')
@section('title','Relatório de vendas')
@section('content')
<h1 class="page-heading h3 mb-4">Relatório de vendas</h1>
<div class="card mb-4"><div class="card-body p-4"><div class="text-secondary">Total de vendas simuladas ativas</div><div class="metric-value">R$ {{ number_format($total/100,2,',','.') }}</div><p class="small text-secondary mb-0">Reservas e passagens canceladas não compõem o total. Nenhuma cobrança real foi realizada.</p></div></div>
<div class="card"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Localizador</th><th>Passageiro</th><th>Voo</th><th>Canal</th><th>Pagamento</th><th>Data</th><th>Valor</th></tr></thead><tbody>@forelse($sales as $ticket)<tr><td><a href="{{ route('tickets.show',$ticket) }}">{{ $ticket->reference }}</a></td><td>{{ $ticket->customer->name }}</td><td>{{ $ticket->flight->code }}</td><td>{{ $ticket->seller?->name ?? 'Portal do cliente' }}</td><td>{{ ['pix'=>'Pix','card'=>'Cartão','cash'=>'Dinheiro'][$ticket->payment_method] }}</td><td>{{ $ticket->paid_at->format('d/m/Y H:i') }}</td><td>R$ {{ number_format($ticket->price_cents/100,2,',','.') }}</td></tr>@empty<tr><td colspan="7" class="empty-state">Nenhuma venda registrada.</td></tr>@endforelse</tbody></table></div><div class="p-3">{{ $sales->links() }}</div></div>
@endsection
