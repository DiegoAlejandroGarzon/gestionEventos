@extends('../themes/' . $activeTheme . '/' . $activeLayout)

@section('subhead')
    <title>Logs</title>
@endsection

@section('subcontent')
<div class="p-6">

    <h2 class="text-2xl font-bold mb-4">Auditoría del Sistema</h2>

    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
        <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}">
        <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}">
        <input type="text" name="module" placeholder="Módulo" class="form-input" value="{{ request('module') }}">
        <button class="btn btn-primary">Filtrar</button>
    </form>

    <table class="table-auto w-full text-left border">
        <thead class="bg-slate-100">
            <tr>
                <th class="p-2">Fecha</th>
                <th class="p-2">Usuario</th>
                <th class="p-2">Acción</th>
                <th class="p-2">Módulo</th>
                <th class="p-2">IP</th>
                <th class="p-2">Resultado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
                <tr class="border-b">
                    <td class="p-2">{{ $log->created_at }}</td>
                    <td class="p-2">{{ $log->user->name ?? 'N/A' }}</td>
                    <td class="p-2">{{ $log->action }}</td>
                    <td class="p-2">{{ $log->module }}</td>
                    <td class="p-2">{{ $log->ip }}</td>
                    <td class="p-2">{{ $log->result }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
