@extends('layouts.app')

@section('content')
<div class="futuristic-card">
    <h2 class="text-center mb-4">Kompresi File Modern</h2>
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <form action="{{ route('compress.process') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="file" class="form-label">Pilih file untuk dikompres:</label>
            <input type="file" class="form-control" name="file" id="file" required>
            @error('file')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label class="form-label">Mode Kompresi:</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="mode" id="lossless" value="lossless" checked>
                <label class="form-check-label" for="lossless">Lossless (tanpa penurunan kualitas)</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="mode" id="maximal" value="maximal">
                <label class="form-check-label" for="maximal">Maksimal (ukuran sekecil mungkin, boleh lossy)</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">Kompres</button>
    </form>
</div>
@endsection 