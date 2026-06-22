@extends('layouts.admin')

@section('title', ($result->exists ? 'Editar' : 'Cargar') . ' resultado | ' . config('app.name'))

@section('content')
    <h1 class="h3 mb-2">{{ $result->exists ? 'Editar resultado' : 'Cargar resultado manual' }}</h1>
    <p class="text-muted mb-4">El resultado de un sorteo específico: fecha, número(s) ganador(es) y, opcionalmente, premio mayor y desglose de premios. Esto es lo que se muestra en la página pública de la lotería y alimenta las estadísticas de frecuencia de números.</p>

    <form method="POST" action="{{ $result->exists ? route('admin.resultados.update', $result) : route('admin.resultados.store') }}" novalidate>
        @csrf
        @if ($result->exists) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label for="lottery_id" class="form-label">Lotería</label>
                <select class="form-select @error('lottery_id') is-invalid @enderror" id="lottery_id" name="lottery_id" required aria-required="true">
                    <option value="">Selecciona una lotería</option>
                    @foreach ($lotteries as $lottery)
                        <option value="{{ $lottery->id }}" @selected(old('lottery_id', $result->lottery_id) === $lottery->id)>{{ $lottery->name }}</option>
                    @endforeach
                </select>
                @error('lottery_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-3">
                <label for="draw_date" class="form-label">Fecha del sorteo</label>
                <input type="date" class="form-control @error('draw_date') is-invalid @enderror" id="draw_date" name="draw_date" value="{{ old('draw_date', $result->draw_date?->format('Y-m-d')) }}" required aria-required="true">
                @error('draw_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-3">
                <label for="draw_number" class="form-label">N.º de sorteo</label>
                <input type="number" class="form-control" id="draw_number" name="draw_number" value="{{ old('draw_number', $result->draw_number) }}">
            </div>

            <div class="col-12">
                <label for="numbers" class="form-label">Números (separados por coma)</label>
                <input type="text" class="form-control @error('numbers') is-invalid @enderror" id="numbers" name="numbers" placeholder="1234, 56" value="{{ old('numbers', $result->exists ? implode(', ', $result->numbers) : '') }}" required aria-required="true" aria-describedby="numbers-help">
                <div id="numbers-help" class="form-text">Ej: número principal, serie — según la lotería.</div>
                @error('numbers') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label for="prize_breakdown" class="form-label">Desglose de premios (JSON, opcional)</label>
                <textarea class="form-control @error('prize_breakdown') is-invalid @enderror" id="prize_breakdown" name="prize_breakdown" rows="3">{{ old('prize_breakdown', $result->prize_breakdown ? json_encode($result->prize_breakdown, JSON_PRETTY_PRINT) : '') }}</textarea>
                @error('prize_breakdown') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 col-md-4">
                <label for="jackpot_amount" class="form-label">Monto del premio mayor</label>
                <input type="number" step="0.01" class="form-control" id="jackpot_amount" name="jackpot_amount" value="{{ old('jackpot_amount', $result->jackpot_amount) }}">
            </div>

            <div class="col-12 col-md-4">
                <label for="currency" class="form-label">Moneda</label>
                <input type="text" class="form-control" id="currency" name="currency" value="{{ old('currency', $result->currency ?? 'COP') }}">
            </div>

            <div class="col-12 col-md-4">
                <label for="source_url" class="form-label">URL de origen</label>
                <input type="url" class="form-control" id="source_url" name="source_url" value="{{ old('source_url', $result->source_url) }}">
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_verified" name="is_verified" value="1" @checked(old('is_verified', $result->is_verified))>
                    <label class="form-check-label" for="is_verified">Marcar como verificado</label>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary mt-4">{{ $result->exists ? 'Guardar cambios' : 'Guardar resultado' }}</button>
        <a href="{{ route('admin.resultados.index') }}" class="btn btn-link">Cancelar</a>
    </form>
@endsection
