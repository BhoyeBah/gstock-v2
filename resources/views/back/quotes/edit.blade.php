@extends('back.layouts.admin')

@section('content')
<div class="container mt-4">
    <h4 class="mb-3">Modifier le devis</h4>
    @include('back.quotes._form', [
        'action' => route('quotes.update', $quote),
        'quote' => $quote,
        'method' => 'PUT',
    ])
</div>
@endsection
