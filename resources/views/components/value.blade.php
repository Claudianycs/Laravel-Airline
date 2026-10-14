@props(['name','field','record'])
@php $value=$record->{$name}; if(is_bool($value)) $value=(int)$value; @endphp
@if($name==='price') R$ {{ number_format($record->price_cents/100,2,',','.') }}
@elseif($name==='status')<x-status :value="$value"/>
@elseif(($field['type'] ?? '')==='select') {{ $field['options'][$value] ?? '—' }}
@elseif($value instanceof \DateTimeInterface) {{ $value->format(($field['type'] ?? '')==='date' ? 'd/m/Y' : 'd/m/Y H:i') }}
@else {{ $value ?? '—' }} @endif
