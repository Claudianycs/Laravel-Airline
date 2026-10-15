<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

abstract class CrudController extends Controller
{
    protected string $model;

    protected string $resource;

    protected string $title;

    protected array $search = [];

    protected array $with = [];

    abstract protected function fields(): array;

    abstract protected function rules(Request $request, ?Model $record = null): array;

    protected function persist(Model $record, array $data): Model
    {
        $record->fill($data)->save();

        return $record;
    }

    protected function data(?Model $record = null): array
    {
        return ['resource' => $this->resource, 'title' => $this->title, 'fields' => $this->fields(), 'record' => $record, 'model' => $this->model];
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', $this->model);
        $query = $this->model::query()->with($this->with);
        if ($request->filled('q')) {
            $q = mb_substr($request->string('q')->toString(), 0, 100);
            $query->where(function ($query) use ($q) {
                foreach ($this->search as $field) {
                    $query->orWhere($field, 'like', '%'.$q.'%');
                }
            });
        }

        return view('crud.index', $this->data() + ['records' => $query->latest('id')->paginate(12)->withQueryString()]);
    }

    public function create()
    {
        Gate::authorize('create', $this->model);

        return view('crud.form', $this->data(new $this->model));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', $this->model);
        $record = $this->persist(new $this->model, $request->validate($this->rules($request)));

        return redirect()->route($this->resource.'.show', $record)->with('success', 'Cadastro criado com sucesso.');
    }

    public function show(string $record)
    {
        $record = $this->model::with($this->with)->findOrFail($record);
        Gate::authorize('view', $record);

        return view('crud.show', $this->data($record));
    }

    public function edit(string $record)
    {
        $record = $this->model::findOrFail($record);
        Gate::authorize('update', $record);

        return view('crud.form', $this->data($record));
    }

    public function update(Request $request, string $record)
    {
        $record = $this->model::findOrFail($record);
        Gate::authorize('update', $record);
        $this->persist($record, $request->validate($this->rules($request, $record)));

        return redirect()->route($this->resource.'.show', $record)->with('success', 'Cadastro atualizado com sucesso.');
    }

    public function destroy(string $record)
    {
        $record = $this->model::findOrFail($record);
        Gate::authorize('delete', $record);
        try {
            DB::transaction(function () use ($record) {
                $record = $this->model::lockForUpdate()->findOrFail($record->id);
                Gate::authorize('delete', $record);
                $record->delete();
            });
        } catch (QueryException $e) {
            if (! in_array($e->getCode(), ['23000', '23503', '19'])) {
                throw $e;
            } throw ValidationException::withMessages(['record' => 'Este cadastro possui registros vinculados e não pode ser excluído.']);
        }

        return redirect()->route($this->resource.'.index')->with('success', 'Cadastro excluído.');
    }
}
