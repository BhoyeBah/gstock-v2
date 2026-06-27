@extends('back.layouts.admin')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Nouveau devis</h4>
    @include('back.quotes._form', [
        'action' => route('quotes.store'),
        'quote' => null,
        'method' => null,
    ])
</div>
@endsection
