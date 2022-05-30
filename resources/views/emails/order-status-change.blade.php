@extends('emails.layout')

@section('title')
    {{ __('Order').' '.$order->id.' '.__($status) }}
@endsection

@section('content')
    {{ __('Dear :name', [ 'name' => $name ]) }},<br /><br />

    {{ __('mail-order-'.$status, [
        'id' => $order->id,
        'subject' => $order->subject,
        'manager' => $manager ]) }}.<br /><br />

    @include('emails.button', [
        'link' => route('edit-order', $order),
        'text' => __('Show my order') ])

    <br />

    {!! __('mail-ending') !!}
@endsection