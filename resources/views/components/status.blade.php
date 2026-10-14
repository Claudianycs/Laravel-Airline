@props(['value'])
@php $labels=['scheduled'=>'Programado','completed'=>'Concluído','reserved'=>'Reservada','paid'=>'Pagamento simulado','cancelled'=>'Cancelado']; $colors=['scheduled'=>'primary','completed'=>'secondary','reserved'=>'warning','paid'=>'success','cancelled'=>'danger']; @endphp
<span class="badge bg-{{ $colors[$value] ?? 'secondary' }}-subtle text-{{ $colors[$value] ?? 'secondary' }} border border-{{ $colors[$value] ?? 'secondary' }}-subtle">{{ $labels[$value] ?? $value }}</span>
