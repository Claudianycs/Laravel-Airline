@extends('layouts.app')
@section('title',$title)
@section('content')
<div class="d-flex justify-content-between align-items-center gap-3 mb-4"><div><div class="eyebrow text-secondary">Operação e cadastro</div><h1 class="page-heading h3 mt-1 mb-0">{{ $title }}</h1></div>@can('create', $model)<a class="btn btn-primary" href="{{ route($resource.'.create') }}"><i aria-hidden="true" class="bi bi-plus-lg me-1"></i>Novo cadastro</a>@endcan</div>
<div class="card">
 <div class="card-header"><form method="GET" class="d-flex gap-2" role="search"><label class="visually-hidden" for="q">Pesquisar</label><input id="q" name="q" value="{{ request('q') }}" class="form-control" placeholder="Pesquisar em {{ mb_strtolower($title) }}" maxlength="100"><button class="btn btn-outline-primary">Buscar</button>@if(request('q'))<a class="btn btn-outline-secondary" href="{{ route($resource.'.index') }}">Limpar</a>@endif</form></div>
 <div class="table-responsive"><table class="table mb-0"><thead><tr>@foreach($fields as $name=>$field)@if(($field['type'] ?? '')!=='password')<th>{{ $field['label'] }}</th>@endif @endforeach<th>Ações</th></tr></thead><tbody>
 @forelse($records as $record)<tr>@foreach($fields as $name=>$field)@if(($field['type'] ?? '')!=='password')<td><x-value :name="$name" :field="$field" :record="$record"/></td>@endif @endforeach
 <td><div class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route($resource.'.show',$record) }}">Ver</a>@can('update',$record)<a class="btn btn-sm btn-outline-secondary" href="{{ route($resource.'.edit',$record) }}">Editar</a>@endcan @can('delete',$record)<form action="{{ route($resource.'.destroy',$record) }}" method="POST" data-confirm="Excluir este cadastro? Registros vinculados impedem a exclusão.">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Excluir</button></form>@endcan</div></td></tr>
 @empty<tr><td colspan="{{ count($fields)+1 }}"><div class="empty-state"><i aria-hidden="true" class="bi bi-inbox fs-1 d-block mb-2"></i>Nenhum cadastro encontrado.</div></td></tr>@endforelse
 </tbody></table></div><div class="p-3">{{ $records->links() }}</div>
</div>
@endsection
