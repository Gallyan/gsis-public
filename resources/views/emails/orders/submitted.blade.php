@extends('emails.layout')

@section('title')
    {{ __('Order :id submitted', ['id' => $order->id], $locale) }}
@endsection

@section('content')
    {{ __('Dear :name', [ 'name' => $full_name ], $locale) }},<br /><br />
    {{ __('mail-order-submitted', [
        'id' => $order->id,
        'subject' => $order->subject ], $locale) }}.<br /><br />

    @include('emails.button', [
        'link' => route('edit-order', $order),
        'text' => __('Show my order') ])

    <br />

    {!! __('mail-ending', [], $locale) !!}
@endsection