@props(['name','field','record'=>null])
@php
 $type=$field['type'] ?? 'text'; $value=$record?->{$name};
 if ($name==='price') $value=$record?->exists ? number_format($record->price_cents/100,2,'.','') : null;
 if ($value instanceof \DateTimeInterface) $value=$value->format($type==='date' ? 'Y-m-d' : 'Y-m-d\TH:i');
 if (is_bool($value)) $value=(int)$value;
 $value=$type==='password' ? '' : old($name,$value);
 $required=$field['required'] ?? true;
@endphp
<div class="mb-3">
 <label class="form-label" for="{{ $name }}">{{ $field['label'] }}@if($required)<span class="text-danger" aria-hidden="true"> *</span>@endif</label>
 @if($type==='select')
 <select id="{{ $name }}" name="{{ $name }}" class="form-select @error($name) is-invalid @enderror" @required($required)>
  <option value="">Selecione...</option>
  @foreach($field['options'] as $key=>$label)<option value="{{ $key }}" @selected((string)$value === (string)$key)>{{ $label }}</option>@endforeach
 </select>
 @else
 <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $value }}" class="form-control @error($name) is-invalid @enderror" @required($required) @if(isset($field['min'])) min="{{ $field['min'] }}" @endif @if(isset($field['max'])) max="{{ $field['max'] }}" @endif @if(isset($field['step'])) step="{{ $field['step'] }}" @endif maxlength="{{ $field['maxlength'] ?? 255 }}" @if($type==='password') autocomplete="new-password" @endif>
 @endif
 @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
